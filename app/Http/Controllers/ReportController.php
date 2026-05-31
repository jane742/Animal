<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Агрегація: Рахуємо загальний виторг компанії
        $totalRevenue = DB::table('bookings')
            ->where('status', '=', 'paid')
            ->select(DB::raw('COALESCE(SUM(total_price), 0) as revenue'))
            ->first()->revenue;

        // 2. Агрегація: Знаходимо найвищу вартість квитка
        $maxTicketPrice = DB::table('bookings')
            ->where('status', '=', 'paid')
            ->max('total_price');

        // 3. Моніторинг пасажиропотоку: групуємо дані за напрямками
    // Моніторинг пасажиропотоку з урахуванням нової структури (Маршрут -> Розклад)
$popularDestinations = DB::table('flights')
    ->join('flight_schedules', 'flights.id', '=', 'flight_schedules.flight_id')
    ->join('bookings', 'flight_schedules.id', '=', 'bookings.flight_schedule_id')
    ->join('tickets', 'bookings.id', '=', 'tickets.booking_id')
    ->join('cities as dep_city', 'flights.departure_city_id', '=', 'dep_city.id')
    ->join('cities as arr_city', 'flights.arrival_city_id', '=', 'arr_city.id')
    ->where('bookings.status', '=', 'paid')
    ->select(
        'flights.flight_number', // Додали номер рейсу для інформативності
        'dep_city.name as departure_city',
        'arr_city.name as arrival_city',
        DB::raw('COUNT(tickets.id) as total_passengers')
    )
    ->groupBy('flights.id', 'flights.flight_number', 'dep_city.name', 'arr_city.name')
    ->orderBy('total_passengers', 'desc')
    ->get();

        // Передаємо пораховані дані у майбутній Blade-шаблон
        return view('admin.reports.index', compact('totalRevenue', 'maxTicketPrice', 'popularDestinations'));
    }
}