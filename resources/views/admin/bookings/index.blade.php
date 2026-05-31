@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Журнал бронювань пасажирів</h1>

    @if(session('success'))
        <div class="bg-emerald-100 text-emerald-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 font-bold">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-bold">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Клієнт (Акаунт)</th>
                    <th class="p-4">Рейс</th>
                    <th class="p-4">Дата вильоту</th>
                    <th class="p-4">Сума</th>
                    <th class="p-4">Статус оплати</th>
                    <th class="p-4 text-center">Управління</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @foreach($bookings as $book)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 text-gray-400">#{{ $book->id }}</td>
                    <td class="p-4 font-medium">{{ $book->user_name }}</td>
                    <td class="p-4 font-bold text-indigo-600">{{ $book->flight_number }}</td>
                    <td class="p-4 text-xs">{{ $book->departure_time }}</td>
                    <td class="p-4 font-semibold">{{ number_format($book->total_price, 2) }} ₴</td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            @if($book->status == 'paid') bg-emerald-100 text-emerald-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ $book->status }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        @if($book->status == 'pending')
                            <form action="/admin/bookings/{{ $book->id }}" method="POST" onsubmit="return confirm('Анулювати це замовлення?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs transition">❌ Анулювати</button>
                            </form>
                        @else
                            <span class="text-xs text-emerald-600 font-semibold">🔒 Оплачено (Захищено)</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection