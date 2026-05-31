<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{
    /**
     * Головна сторінка адмінки (огляд міст та літаків)
     */
    public function index()
    {
        $cities = DB::table('cities')->orderBy('name', 'asc')->get();
        $airplanes = DB::table('airplanes')->orderBy('model', 'asc')->get();

        return view('admin.dashboard', compact('cities', 'airplanes'));
    }

    // ==========================================
    // 🌆 УПРАВЛІННЯ МІСТАМИ (C.R.U.D.)
    // ==========================================

    public function storeCity(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:cities,name']);

        DB::table('cities')->insert([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Місто успішно додано до системи!');
    }

    public function updateCity(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        DB::table('cities')->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Назву міста оновлено.');
    }

    public function destroyCity($id)
    {
        // Перевіряємо, чи не прив'язане місто до якихось рейсів
        $hasFlights = DB::table('flights')
            ->where('departure_city_id', $id)
            ->orWhere('arrival_city_id', $id)
            ->exists();

        if ($hasFlights) {
            return redirect()->back()->with('error', 'Неможливо видалити місто, оскільки воно прив’язане до існуючих авіарейсів!');
        }

        DB::table('cities')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Місто успішно видалено з бази.');
    }

    // ==========================================
    // ✈️ УПРАВЛІННЯ ЛІТАКАМИ (C.R.U.D.)
    // ==========================================

public function storeAirplane(Request $request)
{
    $request->validate([
        'model' => 'required|string|max:255',
        'seats_count' => 'required|integer|min:1|max:300'
    ]);

    DB::transaction(function () use ($request) {
        // 1. Створюємо літак (виправлено назву стовпчика на total_seats)
        $airplaneId = DB::table('airplanes')->insertGetId([
            'model' => $request->model,
            'total_seats' => $request->seats_count, // 👈 Тут тепер точно total_seats
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Автоматично генеруємо місця для цього літака в таблиці `seats`
        for ($i = 1; $i <= $request->seats_count; $i++) {
            $row = ceil($i / 3);
            $letter = ['A', 'B', 'C'][($i - 1) % 3];
            $seatNumber = $row . $letter;

            DB::table('seats')->insert([
                'airplane_id' => $airplaneId,
                'seat_number' => $seatNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
});

    return redirect()->back()->with('success', 'Літак успішно додано. Карта місць згенерована автоматично!');
}

    public function updateAirplane(Request $request, $id)
    {
        $request->validate(['model' => 'required|string|max:255']);

        DB::table('airplanes')->where('id', $id)->update([
            'model' => $request->model,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Модель літака оновлено.');
    }

    public function destroyAirplane($id)
    {
        // Перевіряємо, чи немає активних розкладів для цього літака
        $hasSchedules = DB::table('flight_schedules')->where('airplane_id', $id)->exists();

        if ($hasSchedules) {
            return redirect()->back()->with('error', 'Неможливо видалити літак, бо він призначений на активні рейси!');
        }

        DB::transaction(function () use ($id) {
            // Видаляємо місця літака
            DB::table('seats')->where('airplane_id', $id)->delete();
            // Видаляємо сам літак
            DB::table('airplanes')->where('id', $id)->delete();
        });

        return redirect()->back()->with('success', 'Літак та його місця успішно видалені.');
    }
// ==========================================
// 🛫 УПРАВЛІННЯ РЕЙСАМИ ТА РОЗКЛАДОМ
// ==========================================

/**
 * Сторінка керування розкладом та цінами
 */
public function flightsIndex(\Illuminate\Http\Request $request)
{
    // Отримуємо міста та літаки для випадаючих списків у формах
    // $cities = DB::table('cities')->orderBy('name', 'asc')->get();
    // $airplanes = DB::table('airplanes')->orderBy('model', 'asc')->get();

    // // Отримуємо список сухих маршрутів (flights)
    // $flights = DB::table('flights')
    //     ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
    //     ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
    //     ->select('flights.*', 'dep.name as dep_city', 'arr.name as arr_city')
    //     ->orderBy('flights.flight_number', 'asc')
    //     ->get();

    // // Отримуємо активний розклад вильотів (flight_schedules) з цінами
    // $schedules = DB::table('flight_schedules')
    //     ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
    //     ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
    //     ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
    //     ->join('airplanes', 'flight_schedules.airplane_id', '=', 'airplanes.id')
    //     ->select(
    //         'flight_schedules.*', 
    //         'flights.flight_number', 
    //         'dep.name as dep_city', 
    //         'arr.name as arr_city', 
    //         'airplanes.model as plane_model'
    //     )
    //     ->orderBy('flight_schedules.departure_time', 'asc')
    //     ->get();
    // Передаємо все у ваш блейд цієї сторінки (підставте вашу реальну назву файла)
    //return view('admin.prices_schedule', compact('schedules', 'cities', 'airplanes'));

    // 1. Зчитуємо фільтри з URL
    $filterDate = $request->input('filter_date');
    $filterDepCity = $request->input('filter_departure_city');
    $filterArrCity = $request->input('filter_arrival_city');
    $filterAirplane = $request->input('filter_airplane');

    // 2. Будуємо запит із join-ами через таблицю flights
    $query = DB::table('flight_schedules')
            ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
            ->join('cities as dep_city', 'flights.departure_city_id', '=', 'dep_city.id')
            ->join('cities as arr_city', 'flights.arrival_city_id', '=', 'arr_city.id')
            ->join('airplanes', 'flight_schedules.airplane_id', '=', 'airplanes.id')
            ->select(
                'flight_schedules.*',
                'flights.flight_number as flight_number',
                'dep_city.name as dep_city',
                'arr_city.name as arr_city',
                'airplanes.model as plane_model' // 🔥 МІНЯЄМО НА plane_model
            )
                  
        ->orderBy('flight_schedules.departure_time', 'asc');

    // 3. Фільтруємо дані за вибором адміна
    if ($filterDate) {
        $query->whereDate('flight_schedules.departure_time', $filterDate);
    }
    if ($filterDepCity) {
        $query->where('flights.departure_city_id', $filterDepCity);
    }
    if ($filterArrCity) {
        $query->where('flights.arrival_city_id', $filterArrCity);
    }
    if ($filterAirplane) {
        $query->where('flight_schedules.airplane_id', $filterAirplane);
    }

   $flights = $query->get();

    // 4. Дані для випадаючих списків (міста та літаки)
    $cities = DB::table('cities')->orderBy('name')->get();
    $airplanes = DB::table('airplanes')->orderBy('model')->get();
return view('admin.flights', compact('flights', 'cities', 'airplanes'));
}

/**
 * 1. Створення базового маршруту (Рейсу)
 */
public function storeFlight(Request $request)
{
    $request->validate([
        'flight_number' => 'required|string|max:50|unique:flights,flight_number',
        'departure_city_id' => 'required|exists:cities,id|different:arrival_city_id',
        'arrival_city_id' => 'required|exists:cities,id',
    ]);

    DB::table('flights')->insert([
        'flight_number' => strtoupper($request->flight_number),
        'departure_city_id' => $request->departure_city_id,
        'arrival_city_id' => $request->arrival_city_id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Маршрут рейсу успішно створено!');
}

/**
 * 2. Призначення рейсу в розклад (Виліт, літак та ціна)
 */
public function storeSchedule(Request $request)
{
    $request->validate([
        'flight_id' => 'required|exists:flights,id',
        'airplane_id' => 'required|exists:airplanes,id',
      'departure_time' => 'required|date|after:now',
    'arrival_time' => 'required|date|after:departure_time',
        'base_price' => 'required|numeric|min:0',
    ]);

    DB::table('flight_schedules')->insert([
        'flight_id' => $request->flight_id,
        'airplane_id' => $request->airplane_id,
        'departure_time' => $request->departure_time,
        'arrival_time' => $request->arrival_time,
        'base_price' => $request->base_price,
        'status' => 'scheduled',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Рейс успішно додано до розкладу. Пасажири вже бачать його в пошуку!');
}

/**
 * 3. Швидке видалення розкладу
 */
public function destroySchedule($id)
{
    // Перевіряємо, чи немає куплених квитків на цей виліт
    $hasTickets = DB::table('bookings')->where('flight_schedule_id', $id)->exists();

    if ($hasTickets) {
        return redirect()->back()->with('error', 'Неможливо видалити рейс з розкладу, бо на нього вже є бронювання або куплені квитки!');
    }

    DB::table('flight_schedules')->where('id', $id)->delete();
    return redirect()->back()->with('success', 'Рейс успішно видалено з розкладу.');
}

// ==========================================
// 📊 АНАЛІТИКА ТА ЗВІТИ
// ==========================================

public function reportsIndex()
{
    // 1. Загальний прибуток (рахуємо суму лише для оплачених або підтверджених бронювань)
    // Примітка: перевірте, як у вашій базі називається статус ('confirmed', 'paid' тощо)
   $totalRevenue = DB::table('bookings')
        ->whereIn('status', ['confirmed', 'paid']) 
        ->sum('total_price');

    // 2. Усього продано квитків
    $totalTickets = DB::table('bookings')
        ->whereIn('status', ['confirmed', 'paid'])
        ->count();

    // 3. Продано квитків за поточний місяць
    $monthlyTickets = DB::table('bookings')
        ->whereIn('status', ['confirmed', 'paid'])
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();

    // 4. Топ-3 найпопулярніших рейсів (групуємо за номерами рейсів)
    $popularFlights = DB::table('bookings')
        ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
        ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
        ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
        ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
        ->select(
            'flights.flight_number', 
            'dep.name as dep_city', 
            'arr.name as arr_city', 
            DB::raw('COUNT(bookings.id) as tickets_count'),
            DB::raw('SUM(bookings.total_price) as total_earned')
        )
        ->whereIn('bookings.status', ['confirmed', 'paid'])
        ->groupBy('flights.flight_number', 'dep.name', 'arr.name')
        ->orderBy('tickets_count', 'desc')
        ->limit(3)
        ->get();

    // 5. Останні 5 куплених квитків для живої стрічки

$recentBookings = DB::table('bookings')
    ->join('users', 'bookings.user_id', '=', 'users.id')
    ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
    ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
    ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
    ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
    
    // 1️⃣ Спочатку зв'язуємо бронювання з квитками
    // 💡 Перевірте, як у вас називається поле зв'язку (наприклад, tickets.booking_id чи навпаки)
    ->join('tickets', 'bookings.id', '=', 'tickets.booking_id') 
    
    // 2️⃣ Тепер зв'язуємо квитки з місцями через tickets.seat_id
    ->join('seats', 'tickets.seat_id', '=', 'seats.id') 
    
    ->select(
        'bookings.id',
        'bookings.created_at',
        'bookings.total_price',
        'users.name as passenger_name', 
        'flights.flight_number',
        'dep.name as dep_city',
        'arr.name as arr_city',
        'seats.seat_number' // 👈 Тепер успішно дістаємо текстовий номер місця!
    )
    ->orderBy('bookings.created_at', 'desc')
    ->limit(5)
    ->get();

return view('admin.reports', compact(
        'totalRevenue', 
        'totalTickets', 
        'monthlyTickets', 
        'popularFlights', 
        'recentBookings'
    ));
    
}
public function updatePrice(Request $request, $id)
{
    // 1. Валідація даних
    $request->validate([
        'price' => ['required', 'numeric', 'min:1'],
    ]);

    // 2. Оновлення в базі даних
    \Illuminate\Support\Facades\DB::table('flight_schedules')
        ->where('id', $id)
        ->update([
            'base_price' => $request->price,
            'updated_at' => now(),
        ]);

    // 3. Повернення назад із повідомленням
    return redirect()->back()->with('success', 'Вартість квитка успішно змінено!');
}


public function showDetails($id)
{
    // 1. Отримуємо повну інформацію про сам рейс
    $flight = DB::table('flight_schedules')
        ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
        ->join('cities as dep_city', 'flights.departure_city_id', '=', 'dep_city.id')
        ->join('cities as arr_city', 'flights.arrival_city_id', '=', 'arr_city.id')
        ->join('airplanes', 'flight_schedules.airplane_id', '=', 'airplanes.id')
        ->select(
            'flight_schedules.*',
            'flights.flight_number as flight_number',
            'dep_city.name as dep_city',
            'arr_city.name as arr_city',
            'airplanes.model as plane_model',
            'airplanes.total_seats as plane_capacity'
        )
        ->where('flight_schedules.id', $id)
        ->first();

    // Якщо такий рейс не знайдено — повертаємо 404 помилку
    if (!$flight) {
        abort(404, 'Рейс не знайдено');
    }

    // 2. Отримуємо список куплених квитків та пасажирів на цей рейс
    // ⚠️ Увага: перевірте назви таблиць (tickets, users) та полів (seat_number) відповідно до вашої БД!
// 2. Витягуємо пасажирів, зв'язуючи користувача через таблицю bookings
    $passengers = DB::table('tickets')
        // 🔗 З'єднуємо квиток із місцем
        ->join('seats', 'tickets.seat_id', '=', 'seats.id')
        // 🔗 З'єднуємо квиток із бронюванням
        ->join('bookings', 'tickets.booking_id', '=', 'bookings.id')
        // 🔗 Тепер з'єднуємо БРОНЮВАННЯ з користувачем (оскільки user_id лежить там)
        ->join('users', 'bookings.user_id', '=', 'users.id') 
        ->select(
            'users.name as passenger_name',
            'users.email as passenger_email',
            'seats.seat_number as seat_number',
            'seats.class as seat_class', // 
            'tickets.created_at as booking_date'
        )
        // Фільтруємо за розкладом рейсу, який закріплений у бронюванні
        ->where('bookings.flight_schedule_id', $id) 
        ->orderBy('seats.seat_number', 'asc')
        ->get();
       
return view('admin.shedules.details', compact('flight', 'passengers'));
}

}