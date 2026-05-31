
<x-app-layout> <div class="p-6 max-w-6xl mx-auto text-gray-800">
    
    <div class="mb-6">
        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm">⬅️ Повернутися до розкладу</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border p-6 mb-8"  style="background-color:white;  border-radius: 12px;">
        <div class="flex justify-between items-center border-b pb-4 mb-4">
            <div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Рейс №</span>
                <h1 class="text-2xl font-black font-mono text-gray-900">{{ $flight->flight_number }}</h1>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-gray-400 uppercase">Літак</span>
                <p class="font-bold text-gray-700">{{ $flight->plane_model }} (Місць: {{ $flight->plane_capacity }})</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase">Маршрут</p>
                <p class="text-lg font-bold text-gray-800">{{ $flight->dep_city }} ➔ {{ $flight->arr_city }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase">Дата та час вильоту</p>
                <p class="text-lg font-bold text-gray-800">{{ date('d.m.Y H:i', strtotime($flight->departure_time)) }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase">Зайнято місць</p>
                <p class="text-lg font-bold text-emerald-600">{{ $passengers->count() }} / {{ $flight->plane_capacity }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden" style="background-color:white;  border-radius: 12px;">
        <div class="p-4 border-b bg-gray-50">
            <h2 class="font-bold text-gray-700">📋 Список зареєстрованих пасажирів</h2>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-xs font-bold uppercase text-gray-600 border-b">
                    <th class="p-4 w-24 text-center">💺 Місце</th>
                    <th class="p-4">💎 Клас</th>
                    <th class="p-4">👤 Ім'я пасажира</th>
                    <th class="p-4">📧 Email</th>
                    <th class="p-4">📅 Дата бронювання</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passengers as $passenger)
                    <tr class="hover:bg-slate-50 border-b transition">
                        <td class="p-4 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30">{{ $passenger->seat_number }}</td>
                        <td class="p-4 text-sm font-medium">
                @if($passenger->seat_class === 'business')
                    <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-md text-xs font-bold uppercase">Business</span>
                @elseif($passenger->seat_class === 'first')
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-md text-xs font-bold uppercase">First Class</span>
                @else
                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold uppercase">Economy</span>
                @endif
            </td>
                        <td class="p-4 font-semibold text-gray-800">{{ $passenger->passenger_name }}</td>
                        <td class="p-4 text-gray-600 text-sm">{{ $passenger->passenger_email }}</td>
                        <td class="p-4 text-xs text-gray-400 font-mono">{{ date('d.m.Y H:i', strtotime($passenger->booking_date)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400">На цей рейс ще не продано жодного квитка.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>