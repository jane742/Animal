<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlightScheduleController extends Controller
{
    // Список усіх рейсів у розкладі
    public function index()
    {
        $schedules = DB::table('flight_schedules')
            ->join('flights', 'flight_schedules.flight_id', '=', 'flights.id')
            ->join('airplanes', 'flight_schedules.airplane_id', '=', 'airplanes.id')
            ->join('cities as dep', 'flights.departure_city_id', '=', 'dep.id')
            ->join('cities as arr', 'flights.arrival_city_id', '=', 'arr.id')
            ->select(
                'flight_schedules.*',
                'flights.flight_number',
                'airplanes.model as airplane_model',
                'dep.name as dep_name',
                'arr.name as arr_name'
            )
            ->orderBy('flight_schedules.departure_time', 'asc')
            ->get();

        return view('admin.flights.index', compact('schedules'));
    }

    // Форма створення нового рейсу
    public function create()
    {
        $flights = DB::table('flights')->get();
        $airplanes = DB::table('airplanes')->get();
        return view('admin.flights.create', compact('flights', 'airplanes'));
    }

    // Збереження нового рейсу з валідацією
    public function store(Request $request)
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
        ]);

        return redirect('/admin/flights')->with('success', 'Рейс успішно додано до розкладу!');
    }

    // Форма редагування рейсу та його ціни
    public function edit($id)
    {
        $schedule = DB::table('flight_schedules')->where('id', $id)->first();
        $flights = DB::table('flights')->get();
        $airplanes = DB::table('airplanes')->get();
        return view('admin.flights.edit', compact('schedule', 'flights', 'airplanes'));
    }

    // Оновлення даних рейсу та ціни квитка
    public function update(Request $request, $id)
    {
        $request->validate([
            'airplane_id' => 'required|exists:airplanes,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:scheduled,delayed,departed,arrived,cancelled'
        ]);

        DB::table('flight_schedules')->where('id', $id)->update([
            'airplane_id' => $request->airplane_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'base_price' => $request->base_price,
            'status' => $request->status,
        ]);

        return redirect('/admin/flights')->with('success', 'Рейс та ціни успішно оновлено!');
    }

    // Видалення рейсу з розкладу
    public function destroy($id)
    {
        DB::table('flight_schedules')->where('id', $id)->delete();
        return redirect('/admin/flights')->with('success', 'Рейс видалено!');
    }
}