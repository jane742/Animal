@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Управління розкладом та тарифами</h1>
        <a href="/admin/flights/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">➕ Додати рейс</a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 text-emerald-800 p-4 rounded-lg mb-6 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-bold">
                <tr>
                    <th class="p-4">Рейс</th>
                    <th class="p-4">Маршрут</th>
                    <th class="p-4">Літак</th>
                    <th class="p-4">Виліт / Приліт</th>
                    <th class="p-4">Базова ціна</th>
                    <th class="p-4">Статус</th>
                    <th class="p-4 text-center">Дії</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @foreach($schedules as $row)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-bold text-indigo-600">{{ $row->flight_number }}</td>
                    <td class="p-4">{{ $row->dep_name }} ➔ {{ $row->arr_name }}</td>
                    <td class="p-4 text-gray-500">{{ $row->airplane_model }}</td>
                    <td class="p-4 text-xs">
                        <div>🛫 {{ $row->departure_time }}</div>
                        <div class="mt-1">🛬 {{ $row->arrival_time }}</div>
                    </td>
                    <td class="p-4 font-semibold text-emerald-600">{{ number_format($row->base_price, 2) }} ₴</td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            @if($row->status == 'scheduled') bg-blue-100 text-blue-800
                            @elseif($row->status == 'delayed') bg-amber-100 text-amber-800
                            @elseif($row->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ $row->status }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="/admin/flights/{{ $row->id }}/edit" class="bg-amber-500 text-white px-3 py-1 rounded hover:bg-amber-600 text-xs transition">Редагувати / Ціна</a>
                            <form action="/admin/flights/{{ $row->id }}" method="POST" onsubmit="return confirm('Видалити цей рейс?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs transition">Видалити</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection