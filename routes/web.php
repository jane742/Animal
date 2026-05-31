<?php
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\FlightScheduleController;
use App\Http\Controllers\Admin\BookingManagerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;

// Головна сторінка сайту (публічна)
use Illuminate\Support\Facades\DB;

Route::get('/', function (Request $request) {
    // 1. Отримуємо параметри пошуку з форми
    $departureCity = $request->input('departure_city');
    $arrivalCity = $request->input('arrival_city');
    $departureDate = $request->input('departure_date');

    $flights = null;

    // 2. Якщо користувач обрав міста чи дату — робимо пошук
    if ($departureCity || $arrivalCity || $departureDate) {
        $query = DB::table('flight_schedules')
            // 🔗 З'єднуємо розклад з таблицею рейсів flights, щоб дістатися до id міст
            ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
            // 🔗 Тепер через таблицю flights приєднуємо назви міст вильоту та прильоту
            ->join('cities as dep_city', 'flights.departure_city_id', '=', 'dep_city.id')
            ->join('cities as arr_city', 'flights.arrival_city_id', '=', 'arr_city.id')
            // 🔗 Приєднуємо модель літака
            ->join('airplanes', 'flight_schedules.airplane_id', '=', 'airplanes.id')
            ->select(
                'flight_schedules.*',
                'dep_city.name as departure_city_name',
                'arr_city.name as arrival_city_name',
                'airplanes.model as airplane_model'
            );

        // Фільтруємо за містами (тепер вказуємо, що вони лежать у таблиці flights)
        if ($departureCity) {
            $query->where('flights.departure_city_id', $departureCity);
        }
        if ($arrivalCity) {
            $query->where('flights.arrival_city_id', $arrivalCity);
        }
        if ($departureDate) {
            $query->whereDate('flight_schedules.departure_time', $departureDate);
        }

        $flights = $query->get();
    }

    // 3. Завантажуємо список усіх міст для випадаючих списків форми
    $cities = DB::table('cities')->orderBy('name')->get();

    return view('welcome', compact('cities', 'flights', 'departureDate'));
});

// Автоматичний редірект після успішного входу (розумна точка входу)
Route::get('/home', function () {
    $user = Auth::user();
    if ($user && ($user->role === 'admin' || $user->role === 'analyst')) {
        return redirect('/admin/reports');
    }
    return redirect('/dashboard');
})->middleware(['auth'])->name('home');


// ==========================================
// 🧑‍✈️ ЗОНА АДМІНІСТРАТОРА ТА АНАЛІТИКА (Захищена)
// ==========================================    
    // Керування рейсами та бронюваннями (Лише для Admin)
    Route::middleware(['role:admin'])->group(function () {
  Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/schedules/{id}/details', [AdminController::class, 'showDetails'])->name('admin.schedules.details');
    // Маршрути для міст
    Route::post('/admin/cities', [\App\Http\Controllers\AdminController::class, 'storeCity'])->name('admin.cities.store');
    Route::put('/admin/cities/{id}', [\App\Http\Controllers\AdminController::class, 'updateCity'])->name('admin.cities.update');
    Route::delete('/admin/cities/{id}', [\App\Http\Controllers\AdminController::class, 'destroyCity'])->name('admin.cities.destroy');

    // Маршрути для літаків
    Route::post('/admin/airplanes', [\App\Http\Controllers\AdminController::class, 'storeAirplane'])->name('admin.airplanes.store');
    Route::put('/admin/airplanes/{id}', [\App\Http\Controllers\AdminController::class, 'updateAirplane'])->name('admin.airplanes.update');
    Route::delete('/admin/airplanes/{id}', [\App\Http\Controllers\AdminController::class, 'destroyAirplane'])->name('admin.airplanes.destroy');

Route::get('/admin/flights', [\App\Http\Controllers\AdminController::class, 'flightsIndex'])->name('admin.flights.index');
    Route::post('/admin/flights', [\App\Http\Controllers\AdminController::class, 'storeFlight'])->name('admin.flights.store');
    Route::post('/admin/schedules', [\App\Http\Controllers\AdminController::class, 'storeSchedule'])->name('admin.schedules.store');
    Route::delete('/admin/schedules/{id}', [\App\Http\Controllers\AdminController::class, 'destroySchedule'])->name('admin.schedules.destroy');
Route::get('/admin/reports', [\App\Http\Controllers\AdminController::class, 'reportsIndex'])->name('admin.reports.index');

Route::patch('/admin/schedules/{id}/update-price', [AdminController::class, 'updatePrice'])
    ->name('admin.schedules.update-price');

    });

// ==========================================
// 🧳 КАБІНЕТ ПАСАЖИРА (Захищений)
// ==========================================
Route::middleware(['auth', 'role:passenger'])->group(function () {
    // Головна сторінка кабінету (тепер відкриває метод index нашого контролера)
    
    
// Маршрут для фінального збереження квитків усіх пасажирів
Route::post('/dashboard/booking/{booking_id}/store-tickets', [\App\Http\Controllers\PassengerController::class, 'storeTickets'])->name('passenger.save_tickets');
    // Обробка кнопки бронювання
    Route::post('/dashboard/book', [\App\Http\Controllers\PassengerController::class, 'book'])->name('passenger.book');
    Route::get('/dashboard', [\App\Http\Controllers\PassengerController::class, 'index'])->name('dashboard');
    
Route::get('/dashboard/ticket/{ticket_code}', [\App\Http\Controllers\PassengerController::class, 'showTicket'])->name('passenger.show_ticket');

Route::get('/dashboard/booking/{booking_id}/select-seat', [\App\Http\Controllers\PassengerController::class, 'selectSeat'])->name('passenger.select_seat');

Route::post('/dashboard/booking/{booking_id}/save-seat', [\App\Http\Controllers\PassengerController::class, 'saveSeat'])->name('passenger.save_seat');
    // Нові платіжні маршрути
    Route::get('/dashboard/payment/{booking_id}', [\App\Http\Controllers\PassengerController::class, 'showPayment'])->name('passenger.payment');
    Route::post('/dashboard/payment/{booking_id}', [\App\Http\Controllers\PassengerController::class, 'processPayment']);
// Маршрут видаляє тільки один конкретний квиток пасажира
Route::delete('/dashboard/ticket/{ticket_id}/cancel', [\App\Http\Controllers\PassengerController::class, 'cancelTicket'])->name('passenger.cancel_ticket');});

// ==========================================
// 👤 ЗАГАЛЬНІ МАРШРУТИ ПРОФІЛЮ (Для всіх авторизованих користувачів)
// ==========================================
// Адмін, аналітик та пасажир мають право редагувати свій профіль
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/run-migrations-secret-123', function () {
    try {
        // Команда fresh ОБОВ'ЯЗКОВО видалить стару таблицю users і створить її заново
        Artisan::call('migrate:fresh', ['--force' => true]);
        return 'Базу даних повністю очищено та успішно перестворено з нуля!';
    } catch (\Exception $e) {
        return 'Помилка міграції: ' . $e->getMessage();
    }
});


Route::get('/clear-cache-secret-123', function () {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        return 'Кеш успішно очищено! Перевіряйте головну сторінку.';
    } catch (\Exception $e) {
        return 'Помилка очищення кешу: ' . $e->getMessage();
    }
});
Route::get('/dashboard', [\App\Http\Controllers\PassengerController::class, 'index'])->name('dashboard');



Route::get('/seed-my-database-789', function () {
    try {
        // Запускаємо головний сидер проєкту
        Artisan::call('db:seed', ['--force' => true]);
        
        return 'Базу даних успішно заповнено тестовими містами, рейсами та літаками!';
    } catch (\Exception $e) {
        return 'Помилка заповнення бази: ' . $e->getMessage();
    }
});
Route::get('/create-admin-xyz', function () {
    try {
        // Перевіряємо, чи немає вже адміна з такою поштою, щоб не було дублів
        $adminExists = User::where('email', 'admin@avia.com')->exists();
        
        if ($adminExists) {
            return 'Адмін з такою поштою вже існує в системі!';
        }

        // Створюємо користувача
        $admin = User::create([
            'name' => 'Головний Адміністратор',
            'email' => 'admin@avia.com',
            'password' => Hash::make('SuperSecretPassword123'), // Твій пароль для входу
            'role' => 'admin', // Або та логіка ролей, яку ти використовуєш
        ]);

        return 'Адміна успішно створено! Логін: admin@avia.com, Пароль:11111111';
    } catch (\Exception $e) {
        return 'Помилка створення адміна: ' . $e->getMessage();
    }
});


Route::get('/setup-shop-789', function () {
    try {
        // 1. СТВОРЮЄМО АДМІНА (якщо його ще немає)
        $adminEmail = 'admin@avia.com';
        $admin = User::where('email', $adminEmail)->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Головний Адміністратор',
                'email' => $adminEmail,
                'password' => Hash::make('SuperSecretPassword123'),
                'role' => 'admin', 
            ]);
            $adminStatus = "Адміна ($adminEmail) створено!";
        } else {
            $adminStatus = "Адмін вже існував.";
        }

        // 2. ЗАПОВНЮЄМО МІСТА ТА РЕЙСИ
        // Перевіряємо, чи є таблиця 'cities' (або як вона у тебе називається в міграціях)
        // Якщо твої таблиці називаються інакше, просто підправ назви 'cities' та 'flights' / 'flight_schedules'
        
        // Приклад ручного вставлення через DB-фасад, щоб не прив'язуватися до моделей
        if (DB::table('cities')->count() == 0) {
            DB::table('cities')->insert([
                ['id' => 1, 'name' => 'Київ', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Львів', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Одеса', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $citiesStatus = "Міста додано!";
        } else {
            $citiesStatus = "Міста вже були в базі.";
        }

        // Заповнюємо розклад рейсів (підстав свої назви колонок, якщо вони відрізняються)
        if (DB::table('flight_schedules')->count() == 0) {
            DB::table('flight_schedules')->insert([
                [
                    'flight_number' => 'PS-101',
                    'departure_city_id' => 1, // Київ
                    'arrival_city_id' => 2,   // Львів
                    'departure_time' => Carbon::now()->addHours(5), // Виліт через 5 годин (якраз підпаде під наш новий фільтр < 24 год)
                    'price' => 1500,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'flight_number' => 'PS-202',
                    'departure_city_id' => 2, // Львів
                    'arrival_city_id' => 3,   // Одеса
                    'departure_time' => Carbon::now()->addDays(2), // Виліт через 2 дні
                    'price' => 1800,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            $flightsStatus = "Тестові рейси додано!";
        } else {
            $flightsStatus = "Рейси вже були в базі.";
        }

        return "Успіх! <br> 1. $adminStatus <br> 2. $citiesStatus <br> 3. $flightsStatus";

    } catch (\Exception $e) {
        return 'Помилка налаштування бази: ' . $e->getMessage();
    }
});

// Підключаємо маршрути автентифікації від Breeze (Login, Register, Logout)
require __DIR__.'/auth.php';