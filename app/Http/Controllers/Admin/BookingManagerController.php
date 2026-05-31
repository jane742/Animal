<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingManagerController extends Controller
{
    // Список усіх бронювань у системі
    public function index()
    {
        $bookings = DB::table('bookings')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('flight_schedules', 'bookings.flight_schedule_id', '=', 'flight_schedules.id')
            ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
            ->select('bookings.*', 'users.name as user_name', 'flights.flight_number', 'flight_schedules.departure_time')
            ->orderBy('bookings.created_at', 'desc')
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    // Ручне створення бронювання адміном
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'flight_schedule_id' => 'required|exists:flight_schedules,id',
            'passenger_name' => 'required|string|max:255',
            'passport_number' => 'required|string|max:50',
            'seat_id' => 'required|exists:seats,id',
            'total_price' => 'required|numeric'
        ]);

        DB::transaction(function () use ($request) {
            // 1. Створюємо запис бронювання зі статусом "paid" (бо оформлює адмін)
            $bookingId = DB::table('bookings')->insertGetId([
                'user_id' => $request->user_id,
                'flight_schedule_id' => $request->flight_schedule_id,
                'total_price' => $request->total_price,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        
            // 2. Генерація унікального квитка з кодом
            DB::table('tickets')->insert([
                'booking_id' => $bookingId,
                'seat_id' => $request->seat_id,
                'passenger_name' => $request->passenger_name,
                'passport_number' => $request->passport_number,
                'ticket_code' => 'TKT-' . strtoupper(Str::random(8)),
                'created_at' => now(),
                'updated_at' => now()
            ]);
          });
    
        return redirect('/admin/bookings')->with('success', 'Бронювання та електронний квиток успішно оформлені в ручному режимі!');
    }

    // Скасування/Видалення бронювання (Дозволено лише якщо статус "pending")
    public function destroy($id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();

        if ($booking->status === 'paid') {
            return redirect('/admin/bookings')->with('error', 'Критична помилка: Не можна просто так видалити оплачене бронювання! Поверніть кошти або скасуйте квиток.');
        }

        // Якщо неоплачено - видаляємо (квитки видаляться каскадно завдяки ON DELETE CASCADE у вашій БД)
        DB::table('bookings')->where('id', $id)->delete();
        return redirect('/admin/bookings')->with('success', 'Неоплачене бронювання успішно анульовано.');
    }
}