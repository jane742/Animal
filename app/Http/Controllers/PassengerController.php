<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PassengerController extends Controller
{
    /**
     * Головна сторінка кабінету пасажира
     */
public function index(Request $request)
{
    $userId = Auth::id();
$now = Carbon::now();
$in24Hours = Carbon::now()->addHours(24);
    // 1. Авто-скасування протермінованих броней (< 24 годин до вильоту)
    $expiredBookings = DB::table('bookings')
        ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
        ->where('bookings.status', '=', 'pending')
     ->whereBetween('flight_schedules.departure_time', [$now, $in24Hours])
        ->pluck('bookings.id')
        ->toArray();

    if (!empty($expiredBookings)) {
        DB::transaction(function () use ($expiredBookings) {
            DB::table('tickets')->whereIn('booking_id', $expiredBookings)->delete();
            DB::table('bookings')->whereIn('id', $expiredBookings)->delete();
        });
    }

    // 2. Список міст для випадаючих списків форми пошуку
    $cities = DB::table('cities')->orderBy('name', 'asc')->get();

    // 3. Обробка пошуку рейсів
    $hasSearch = $request->filled('departure_city_id') || 
                 $request->filled('arrival_city_id') || 
                 $request->filled('departure_date');

    $availableFlights = collect(); // за замовчуванням пуста колекція

    if ($hasSearch) {
        $query = DB::table('flight_schedules')
            ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
            ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
            ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
            ->where('flight_schedules.status', '=', 'scheduled');

        if ($request->filled('departure_city_id')) {
            $query->where('flights.departure_city_id', $request->departure_city_id);
        }

        if ($request->filled('arrival_city_id')) {
            $query->where('flights.arrival_city_id', $request->arrival_city_id);
        }

        if ($request->filled('departure_date')) {
            $query->whereDate('flight_schedules.departure_time', $request->departure_date);
        }

        $availableFlights = $query->select(
            'flight_schedules.*', 
            'flights.flight_number', 
            'dep.name as dep_city', 
            'arr.name as arr_city',
            DB::raw('(SELECT COUNT(*) FROM seats WHERE seats.airplane_id = flight_schedules.airplane_id) as total_seats_count'),
            DB::raw('(SELECT COUNT(*) FROM tickets 
                      JOIN bookings ON tickets.booking_id = bookings.id 
                      WHERE bookings.flight_schedule_id = flight_schedules.id 
                      AND bookings.status IN ("paid", "pending")) as occupied_seats_count')
        )->get();
    }

    // 4. Отримання квитків та бронювань поточного користувача для таблиці
    $myTickets = DB::table('tickets')
        ->join('bookings', 'tickets.booking_id', '=', 'bookings.id')
        ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
        ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
        ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
        ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
        ->join('seats', 'tickets.seat_id', '=', 'seats.id')
        ->where('bookings.user_id', $userId)
        ->select(
            'tickets.*',
            'bookings.id as booking_id',
            'bookings.status as booking_status',
            'flights.flight_number',
            'dep.name as dep_city',
            'arr.name as arr_city',
            'flight_schedules.departure_time',
            'seats.seat_number'
        )
        ->orderBy('bookings.created_at', 'desc')
        ->get();

    // Точна передача всіх трьох змінних у Blade
    return view('dashboard', compact('cities', 'availableFlights', 'myTickets'));
}

    /**
     * Логіка миттєвого бронювання квитка
     */
/**
 * Етап 1: Створення попереднього бронювання (Pending)
 */
public function book(Request $request)
{
    // 1. Валідація (наприклад, перевірка паспорта, якщо вона у вас тут)
    $request->validate([
        'flight_schedule_id' => 'required|integer',
    ]);

    // Перевіряємо, чи у користувача заповнений паспорт у профілі
    if (empty(auth()->user()->passport_number)) {
        return back()->withErrors(['passport' => 'Будь ласка, вкажіть номер паспорта у вашому профілі перед бронюванням.']);
    }

    // 2. Отримуємо рейс, щоб дізнатися ціну
    $schedule = DB::table('flight_schedules')->where('id', $request->flight_schedule_id)->first();

    // 3. Створюємо запис у таблиці bookings (як у вас і було)
    $bookingId = DB::table('bookings')->insertGetId([
        'user_id' => auth()->id(),
        'flight_schedule_id' => $request->flight_schedule_id,
        'total_price' => $schedule->base_price, // Початкова ціна за 1 місце
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 4. 🔥 ОСОБЛИВИЙ РЯДОК: Перенаправляємо на вибір місць!
    // Ми використовуємо точну назву з вашого web.php ('passenger.select_seat')
    // І передаємо саме 'booking_id' => $bookingId, як вимагає ваш маршрут {booking_id}
    return redirect()->route('passenger.select_seat', ['booking_id' => $bookingId]);
}

public function selectSeat($booking_id)
{
    // 1. Знаходимо саме бронювання
    $booking = DB::table('bookings')->where('id', $booking_id)->first();

    if (!$booking) {
        abort(404, 'Бронювання не знайдено');
    }

    // 2. Через бронювання знаходимо розклад рейсу (щоб дізнатися airplane_id)
    $schedule = DB::table('flight_schedules')->where('id', $booking->flight_schedule_id)->first();

    // 3. Витягуємо всі місця для цього літака
    $allSeats = DB::table('seats')
        ->where('airplane_id', $schedule->airplane_id)
        ->orderBy('seat_number', 'asc')
        ->get();

    // 4. Шукаємо вже ЗАЙНЯТІ місця на цей рейс іншими пасажирами
    $occupiedSeatIds = DB::table('tickets')
        ->join('bookings', 'tickets.booking_id', '=', 'bookings.id')
        ->where('bookings.flight_schedule_id', $booking->flight_schedule_id)
        ->pluck('tickets.seat_id')
        ->toArray();

    // 5. Передаємо дані у блейд моделі літака
    // Передаємо і $booking, і $schedule, щоб блейд не сварився на "undefined variable"
    return view('passenger.select_seat', compact('booking', 'schedule', 'allSeats', 'occupiedSeatIds'));
}

/**
 * Етап 1.6: Збереження обраного місця та створення квитка
 */
public function saveSeat(Request $request, $booking_id)
{ 
    // 1. Валідація
    $request->validate([
        'selected_seats' => 'required|string',
    ]);

    // 2. 🔥 ПЕРЕВІРТЕ ЦЕЙ РЯДОК: Перетворюємо рядок "1,2" у масив [1, 2]
    $seatIds = explode(',', $request->selected_seats);

    // 3. Бронювання
    $booking = DB::table('bookings')->where('id', $booking_id)->first();
    if (!$booking) {
        abort(404, 'Бронювання не знайдено');
    }

    // 4. Розклад рейсу
    $schedule = DB::table('flight_schedules')->where('id', $booking->flight_schedule_id)->first();

    // 5. Рахуємо суму
    $totalPrice = $schedule->base_price * count($seatIds);

    // 6. Оновлюємо суму в базі
    DB::table('bookings')->where('id', $booking_id)->update([
        'total_price' => $totalPrice,
        'updated_at' => now()
    ]);

    // 7. 🔥 ПЕРЕВІРТЕ ЦЕЙ РЯДОК: Отримуємо обрані місця з бази
    // Має бути саме ->whereIn('id', $seatIds), а не ->where('id', ...)
    $selectedSeats = DB::table('seats')->whereIn('id', $seatIds)->get();

    // 8. Передаємо у в'юшку
    return view('passenger.passenger_info', compact('booking', 'schedule', 'selectedSeats'));
}


public function storeTickets(Request $request, $booking_id)
{
    // 1. Валідація отриманих даних пасажирів
    $request->validate([
        'passengers' => 'required|array|min:1',
        'passengers.*.seat_id' => 'required|integer',
        'passengers.*.name' => 'required|string|max:255',
        'passengers.*.passport' => 'required|string|max:100',
    ]);

    // 2. Використовуємо транзакцію бази даних, щоб усе збереглося надійно
// ... (початок методу storeTickets та валідація залишаються без змін)

    DB::transaction(function () use ($request, $booking_id) {
        
        foreach ($request->passengers as $passengerData) {
            
            // 🔥 ЗАПОБІЖНИК: Перевіряємо, чи вже є такий квиток у базі
            $ticketExists = DB::table('tickets')
                ->where('booking_id', $booking_id)
                ->where('seat_id', $passengerData['seat_id'])
                ->exists();

            // Якщо квитка на це місце для цієї броні ще немає — створюємо його
            if (!$ticketExists) {
                DB::table('tickets')->insert([
                    'booking_id' => $booking_id,
                    'seat_id' => $passengerData['seat_id'],
                    'passenger_name' => $passengerData['name'],
                    'passport_number' => $passengerData['passport'],
                    'ticket_code' => 'TC-' . Str::upper(Str::random(8)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Оновлюємо статус бронювання
        DB::table('bookings')->where('id', $booking_id)->update([
            'status' => 'pending',
            'updated_at' => now(),
        ]);
    });

// ... (далі ваш редирект if ($request->action === 'pay_now') і т.д.)

if ($request->action === 'pay_now') {
        // Натиснули "Оплатити зараз" -> перенаправляємо на вашу сторінку оплати
        return redirect()->route('passenger.payment', ['booking_id' => $booking_id])
            ->with('success', 'Пасажирів оформлено! Будь ласка, перейдіть до оплати.');
    } else {
        // Натиснули "Оплатити пізніше" -> відправляємо в особистий кабінет
        return redirect()->route('dashboard')
            ->with('success', 'Квитки успішно заброньовано! Ви можете оплатити їх пізніше в особистому кабінеті.');
    }
}

/**
 * Етап 2: Показ сторінки симуляції банку
 */
public function showPayment($booking_id)
{
    $booking = DB::table('bookings')
        ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
        ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
        ->where('bookings.id', $booking_id)
        ->where('bookings.user_id', Auth::id())
        ->select('bookings.*', 'flights.flight_number')
        ->first();

    if (!$booking || $booking->status !== 'pending') {
        return redirect('/dashboard')->with('error', 'Бронювання не знайдено або вже оплачено.');
    }

    return view('passenger.payment', compact('booking'));
}

/**
 * Етап 3: Обробка «успішної» оплати та генерація квитка
 */
public function processPayment(Request $request, $booking_id)
{
    // Знаходимо бронь разом із часом вильоту
    $booking = DB::table('bookings')
        ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
        ->where('bookings.id', $booking_id)
        ->where('bookings.user_id', Auth::id())
        ->where('bookings.status', 'pending')
        ->select('bookings.*', 'flight_schedules.departure_time')
        ->first();

    if (!$booking) {
        return redirect('/dashboard')->with('error', 'Помилка: Бронювання не знайдено або вже оплачено.');
    }

    // Перевіряємо, чи не вичерпано тайм-ліміт (24 години до вильоту)
    $hoursToDeparture = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($booking->departure_time), false);

    if ($hoursToDeparture < 24) {
        // Якщо пасажир запізнився з оплатою — видаляємо цю бронь
        DB::transaction(function () use ($booking_id) {
            DB::table('tickets')->where('booking_id', $booking_id)->delete();
            DB::table('bookings')->where('id', $booking_id)->delete();
        });

        return redirect('/dashboard')->with('error', 'Час на оплату цього бронювання вичерпано (до вильоту залишилось менше 24 годин). Місце анульовано.');
    }

    // Якщо все добре — оновлюємо статус на paid
    DB::table('bookings')->where('id', $booking_id)->update([
        'status' => 'paid',
        'updated_at' => now()
    ]);

    return redirect('/dashboard')->with('success', 'Оплата пройшла успішно! Ваш квиток тепер активовано.');
}

/**
 * Скасування та видалення незавершеного бронювання (Pending)
 */
public function cancelTicket($ticket_id)
{
    // 1. Знаходимо квиток пасажира, якого треба видалити
    $ticket = DB::table('tickets')->where('id', $ticket_id)->first();

    if (!$ticket) {
        return redirect()->back()->with('error', 'Квиток не знайдено.');
    }

    // Запам'ятовуємо ID бронювання перед видаленням квитка
    $bookingId = $ticket->booking_id;

    DB::transaction(function () use ($ticket_id, $ticket, $bookingId) {
        // 2. Видаляємо сам квиток пасажира (місце звільняється автоматично)
        DB::table('tickets')->where('id', $ticket_id)->delete();

        // 3. Рахуємо, скільки квитків ЗАЛИШИЛОСЯ в цьому бронюванні
        $remainingTicketsCount = DB::table('tickets')->where('booking_id', $bookingId)->count();

        if ($remainingTicketsCount === 0) {
            // Якщо це був останній пасажир у броні — видаляємо або скасовуємо все бронювання повністю
            DB::table('bookings')->where('id', $bookingId)->delete(); 
            // (Або замість delete() можна зробити update(['status' => 'cancelled']))
        } else {
            // Якщо інші пасажири ще летять — перераховуємо ціну за залишкові місця
            $booking = DB::table('bookings')->where('id', $bookingId)->first();
            $schedule = DB::table('flight_schedules')->where('id', $booking->flight_schedule_id)->first();

            // Нова сума = ціна рейсу помножена на кількість пасажирів, що залишилися
            $newTotalPrice = $schedule->base_price * $remainingTicketsCount;

            DB::table('bookings')->where('id', $bookingId)->update([
                'total_price' => $newTotalPrice,
                'updated_at' => now()
            ]);
        }
    });

    return redirect()->back()->with('success', 'Квиток пасажира скасовано, суму замовлення перераховано.');
}
/**
 * Відображення електронного квитка (Посадкового талона)
 */
public function showTicket($ticket_code)
{
    // Отримуємо детальну інформацію про квиток
    $ticket = DB::table('tickets')
        ->join('bookings', 'tickets.booking_id', '=', 'bookings.id')
        ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
        ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
        ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
        ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
        ->join('seats', 'tickets.seat_id', '=', 'seats.id')
        ->join('airplanes', 'flight_schedules.airplane_id', '=', 'airplanes.id')
        ->where('tickets.ticket_code', $ticket_code)
        ->where('bookings.user_id', Auth::id()) // Захист: тільки свій квиток
        ->where('bookings.status', 'paid')     // Захист: тільки оплачені
        ->select(
            'tickets.*',
            'flights.flight_number',
            'dep.name as dep_city',
            'arr.name as arr_city',
            'flight_schedules.departure_time',
            'seats.seat_number',
            'airplanes.model as airplane_model'
        )
        ->first();

    // Якщо квиток не знайдено або він не оплачений — повертаємо помилку
    if (!$ticket) {
        return redirect('/dashboard')->with('error', 'Електронний квиток не знайдено або він ще не оплачений.');
    }

    return view('passenger.ticket', compact('ticket'));
}

public function processPassengers(Request $request)
{
    // Отримуємо масив ID місць з нашого прихованого поля
    $seatIds = explode(',', $request->selected_seats); // перетворює "100,102" в [100, 102]

    // Витягуємо інформацію про ці місця з бази (щоб знати їхні номери)
    $selectedSeats = DB::table('seats')->whereIn('id', $seatIds)->get();
    
    $schedule = DB::table('flight_schedules')->where('id', $request->flight_schedule_id)->first();

    // Передаємо ці місця у блейд форми пасажирів (код якого ми писали у попередній відповіді)
    return view('bookings.passenger_info', compact('schedule', 'selectedSeats'));
}
}