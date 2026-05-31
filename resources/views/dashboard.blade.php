<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
     {{ __('Особистий кабінет пасажира') }}
        </h2>
    </x-slot>

    <div class="py-12  min-h-screen " >
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4  " style="background-color:white;  border-radius: 12px;">

            {{-- Виведення повідомлень про успіх або помилку --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-semibold shadow-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-semibold shadow-sm">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <div class="  bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <span>🔍 Пошук авіарейсів</span>
                </h3>

                <form action="{{ route('dashboard') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-4 bg-slate-50 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Звідки</label>
                        <select name="departure_city_id" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Оберіть місто вильоту</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('departure_city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Куди</label>
                        <select name="arrival_city_id" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Оберіть місто призначення</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('arrival_city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Дата вильоту</label>
                        <input type="date" name="departure_date" min="{{ date('Y-m-d') }}" value="{{ request('departure_date') }}" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition shadow-sm text-sm h-[42px] cursor-pointer">
                            Знайти рейси
                        </button>
                        @if(request()->anyFilled(['departure_city_id', 'arrival_city_id', 'departure_date']))
                            <a href="{{ route('dashboard') }}" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition text-sm h-[42px] flex items-center justify-center">
                                ❌
                            </a>
                        @endif
                    </div>
                </form>

                @if(request()->anyFilled(['departure_city_id', 'arrival_city_id', 'departure_date']))
                    <h4 class="text-md font-bold text-gray-700 mb-4">📍 Знайдені рейси:</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($availableFlights as $flight)
                            @php
                                $freeSeatsLeft = $flight->total_seats_count - $flight->occupied_seats_count;
                                $isFlightPast = \Carbon\Carbon::parse($flight->departure_time)->isPast();
                            @endphp

                            <div class="border rounded-xl p-5 shadow-sm flex flex-col justify-between transition {{ $isFlightPast ? 'bg-gray-50 opacity-75 border-gray-200' : 'bg-white hover:border-indigo-400' }}">
                                <div>
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-xs font-bold uppercase tracking-wider {{ $isFlightPast ? 'bg-gray-200 text-gray-600' : 'bg-indigo-100 text-indigo-800' }} px-2.5 py-1 rounded-full">
                                            Рейс {{ $flight->flight_number }}
                                        </span>
                                        
                                        @if($isFlightPast)
                                            <span class="text-xs font-bold uppercase tracking-wider bg-rose-50 border border-rose-100 text-rose-700 px-2 py-1 rounded">
                                                🚫 Рейс уже відбувся
                                            </span>
                                        @else
                                            <span class="text-xl font-extrabold text-emerald-600">
                                                {{ number_format($flight->base_price, 2, ',', ' ') }} ₴
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-lg font-bold text-gray-700 flex items-center space-x-2">
                                        <span class="{{ $isFlightPast ? 'text-gray-400 line-through' : '' }}">{{ $flight->dep_city }}</span>
                                        <span class="text-gray-400 text-sm">➔</span>
                                        <span class="{{ $isFlightPast ? 'text-gray-400 line-through' : '' }}">{{ $flight->arr_city }}</span>
                                    </div>

                                    <p class="text-xs text-gray-500 mt-2 flex items-center">
                                        📅 Виліт: {{ date('d.m.Y H:i', strtotime($flight->departure_time)) }}
                                    </p>
                                </div>

                                <div class="mt-5 pt-4 border-t border-gray-200">
                                    @if($isFlightPast)
                                        <button disabled class="w-full bg-gray-200 text-gray-400 text-center font-semibold py-2.5 px-4 rounded-lg cursor-not-allowed text-sm border border-gray-300">
                                            Бронювання закрите (Рейс відбувся)
                                        </button>
                                    @elseif($freeSeatsLeft <= 0)
                                        <button disabled class="w-full bg-gray-200 text-gray-400 text-center font-semibold py-2.5 px-4 rounded-lg cursor-not-allowed text-sm border border-gray-300">
                                            🚫 Місць немає (Всі квитки продано)
                                        </button>
                                    @else
                                        <form action="{{ route('passenger.book') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="flight_schedule_id" value="{{ $flight->id }}">
                                            <button type="submit" class="w-full bg-indigo-600 text-white text-center font-semibold py-2.5 px-4 rounded-lg hover:bg-indigo-700 transition shadow-sm text-sm cursor-pointer">
                                                Забронювати рейс (Вільні місця: {{ $freeSeatsLeft }})
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 bg-rose-50 text-rose-700 rounded-xl text-sm col-span-2 border border-rose-100">
                                🔍 На жаль, за вашим запитом рейсів не знайдено. Спробуйте обрати інші міста або змінити дату.
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="text-center py-10 px-4 bg-slate-50 rounded-xl border border-dashed border-gray-300">
                        <div class="text-4xl mb-3">🛫</div>
                        <h4 class="text-md font-bold text-gray-700 mb-1">Куди ви бажаєте полетіти?</h4>
                        <p class="text-sm text-gray-400 max-w-sm mx-auto">Скористайтеся пошуковою формою вище, оберіть напрямок та дату, щоб підібрати актуальні авіаквитки.</p>
                    </div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6">
                    📋 Ваші бронювання та квитки
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-gray-600">
                                <th class="p-4">Код квитка</th>
                                <th class="p-4">Пасажир</th>
                                <th class="p-4">Маршрут</th>
                                <th class="p-4">Виліт</th>
                                <th class="p-4 text-center">Місце</th>
                                <th class="p-4 text-center">Статус</th>
                                <th class="p-4 text-center">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($myTickets as $ticket)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-mono font-bold text-indigo-600">{{ $ticket->ticket_code }}</td>
                                    <td class="p-4 font-medium">{{ $ticket->passenger_name }}</td>
                                    <td class="p-4 font-semibold">{{ $ticket->dep_city }} ➔ {{ $ticket->arr_city }}</td>
                                    <td class="p-4 text-xs">{{ date('d.m.Y H:i', strtotime($ticket->departure_time)) }}</td>
                                    <td class="p-4 text-center font-bold text-gray-800">
                                        <span class="bg-gray-100 px-2 py-1 rounded border border-gray-200">{{ $ticket->seat_number }}</span>
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($ticket->booking_status == 'paid')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                🟢 Оплачено
                                            </span>
                                        @else
                                            <div class="flex flex-col items-center space-y-1">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                    🟡 Очікує оплати
                                                </span>

                                                @php
                                                    $now = \Carbon\Carbon::now();
                                                    $departure = \Carbon\Carbon::parse($ticket->departure_time);
                                                    $cancelTime = $departure->copy()->subHours(24);
                                                    $diffInMinutes = $now->diffInMinutes($cancelTime, false);
                                                @endphp

                                                @if($diffInMinutes > 0)
                                                    @php
                                                        $hoursLeft = floor($diffInMinutes / 60);
                                                        $minutesLeft = $diffInMinutes % 60;
                                                    @endphp
                                                    <span class="text-[11px] font-medium text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-100 animate-pulse">
                                                        ⏱️ Анулювання через: {{ $hoursLeft }}г {{ $minutesLeft }}хв
                                                    </span>
                                                @else
                                                    <span class="text-[11px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded">
                                                        ⏳ Час вичерпано!
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($ticket->booking_status == 'pending')
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('passenger.payment', ['booking_id' => $ticket->booking_id]) }}" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-700 transition shadow-sm whitespace-nowrap">
                                                    💳 Оплатити
                                                </a>
                                                
                                        <form action="{{ route('passenger.cancel_ticket', ['ticket_id' => $ticket->id]) }}" method="POST" onsubmit="return confirm('Ви впевнені, що бажаєте скасувати квиток для цього пасажира? Місце буде звільнено.');">
    @csrf
    @method('DELETE')
    <button type="submit" class="bg-rose-100 text-rose-700 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-rose-200 transition shadow-sm whitespace-nowrap cursor-pointer">
        ❌ Скасувати квиток
    </button>
</form>
                                            </div>
                                        @else
                                            @if(\Carbon\Carbon::parse($ticket->departure_time)->isPast())
                                                <span class="text-xs font-bold bg-gray-100 px-2.5 py-1 rounded-md text-gray-500">
                                                    🏁 Політ завершено
                                                </span>
                                            @else
                                                <div class="flex items-center justify-center space-x-2">
                                                    <span class="text-xs text-indigo-700 font-bold bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100 whitespace-nowrap">
                                                        ✨ Дійсний
                                                    </span>
                                                    <a href="{{ route('passenger.show_ticket', ['ticket_code' => $ticket->ticket_code]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-sm whitespace-nowrap">
                                                        📄 Квиток
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-400 text-sm">Ви ще не забронювали жодного квитка. Скористайтеся формою пошуку вище, щоб знайти потрібний рейс!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>