<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            🛡️ Панель адміністратора: Фінансова аналітика
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Навігація верхнього рівня --}}
            <div class="bg-white p-4 rounded-xl shadow-sm flex space-x-4 border">
                <a href="{{ route('admin.dashboard') }}" class="text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">
                    ⚙️ Міста та Літаки
                </a>
                <a href="{{ route('admin.flights.index') }}" class="text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">
                    🎫 Рейси та Ціни
                </a>
                <a href="{{ route('admin.reports.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm">
                    📊 Звіти та Дохід
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center space-x-4">
                    <div class="p-4 rounded-lg text-2xl" style="background-color: #e6f4ea; color: #137333;">💰</div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Загальний прибуток</p>
                        <h4 class="text-2xl font-black text-gray-800" style="color: #16a34a;">
                            {{ number_format($totalRevenue, 2, ',', ' ') }} ₴
                        </h4>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center space-x-4">
                    <div class="p-4 rounded-lg text-2xl" style="background-color: #e8f0fe; color: #1a73e8;">🎫</div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Всього продано квитків</p>
                        <h4 class="text-2xl font-black text-slate-800">{{ $totalTickets }} шт.</h4>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center space-x-4">
                    <div class="p-4 rounded-lg text-2xl" style="background-color: #fef7e0; color: #b06000;">📅</div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Продажі за цей місяць</p>
                        <h4 class="text-2xl font-black text-slate-800">{{ $monthlyTickets }} шт.</h4>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 md:col-span-1">
                    <h3 class="text-sm font-black text-slate-800 mb-4 uppercase tracking-wider">🔥 Топ-3 Популярні рейси</h3>
                    <div class="space-y-4">
                        @forelse($popularFlights as $index => $f)
                            <div class="p-3 bg-slate-50 rounded-xl border border-gray-100 flex justify-between items-center">
                                <div>
                                    <span class="text-xs font-bold text-indigo-600 font-mono">#{{ $index + 1 }} {{ $f->flight_number }}</span>
                                    <p class="text-xs font-semibold text-gray-700">{{ $f->dep_city }} → {{ $f->arr_city }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-slate-800 block">{{ $f->tickets_count }} квит.</span>
                                    <span class="text-xs text-green-600 font-medium">{{ number_format($f->total_earned, 0, ',', ' ') }} ₴</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Немає куплених квитків</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 md:col-span-2">
                    <h3 class="text-sm font-black text-slate-800 mb-4 uppercase tracking-wider">⚡ Останні операції (Продажі квитків)</h3>
                    <div class="overflow-x-auto border rounded-xl">
                        <table class="w-full text-left text-xs text-gray-700">
                            <thead>
                                <tr class="bg-slate-50 border-b text-gray-500 font-bold uppercase">
                                    <th class="p-3">Пасажир</th>
                                    <th class="p-3">Рейс</th>
                                    <th class="p-3">Місце</th>
                                    <th class="p-3">Ціна</th>
                                    <th class="p-3">Дата купівлі</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($recentBookings as $booking)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="p-3 font-semibold text-gray-800">{{ $booking->passenger_name }}</td>
                                        <td class="p-3 font-mono text-indigo-600 font-bold">
                                            {{ $booking->flight_number }} <span class="text-gray-400 font-sans font-normal">({{ $booking->dep_city }}→{{ $booking->arr_city }})</span>
                                        </td>
                                        <td class="p-3 font-mono bg-slate-50 text-center font-bold text-slate-700 border-x">{{ $booking->seat_number ?? 'Н/Д' }}</td>
                                        <td class="p-3 font-bold text-green-600">{{ number_format($booking->total_price, 2, ',', ' ') }} ₴</td>
                                        <td class="p-3 text-gray-400">{{ date('d.m.Y H:i', strtotime($booking->created_at)) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-6 text-center text-gray-400">Історія транзакцій порожня.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>