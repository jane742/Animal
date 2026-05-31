<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            🛡️ Панель адміністратора: Розклад та Тарифи
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Повідомлення --}}
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

            {{-- Верхня навігація адміна --}}
 <div class="bg-white p-4 rounded-xl shadow-sm flex space-x-4 border">
    <a href="{{ route('admin.dashboard') }}" class="text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">
        ⚙️ Міста та Літаки
    </a>
    <a href="{{ route('admin.flights.index') }}" class="text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">
        🎫 Рейси та Ціни
    </a>
    <a href="{{ route('admin.reports.index') }}" class="text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">
        📊 Звіти та Дохід
    </a>
</div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h3 class="text-md font-black text-slate-800 mb-4 uppercase tracking-wider">1. Створити Маршрут</h3>
                    
                    <form action="{{ route('admin.flights.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Номер рейсу</label>
                            <input type="text" name="flight_number" placeholder="Напр: UA-777" required
                                   class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Місто вильоту</label>
                            <select name="departure_city_id" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">Оберіть звідки...</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Місто призначення</label>
                            <select name="arrival_city_id" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">Оберіть куди...</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

            <button type="submit" 
        style="background-color: #16a34a; font-weight: 700;" 
        class="w-full text-white py-2 px-4 rounded-lg text-sm transition hover:opacity-90 cursor-pointer">
    ⚙️ Зафіксувати напрямок
</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 col-span-1 md:col-span-2">
                    <h3 class="text-md font-black text-slate-800 mb-4 uppercase tracking-wider">2. Призначити у розклад та встановити тариф</h3>
                    
                    <form action="{{ route('admin.schedules.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Оберіть рейс (напрямок)</label>
                            <select name="flight_id" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">Оберіть маршрут...</option>
                                @foreach($flights as $flight)
                                    <option value="{{ $flight->id }}">{{ $flight->flight_number }} ({{ $flight->dep_city }} ➔ {{ $flight->arr_city }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Призначити борт (літак)</label>
                            <select name="airplane_id" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">Оберіть літак...</option>
                                @foreach($airplanes as $plane)
                                    <option value="{{ $plane->id }}">{{ $plane->model }} (меж: {{ $plane->total_seats }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Дата та час вильоту</label>
                            <input type="datetime-local" name="departure_time" required min="{{ date('Y-m-d\TH:i') }}"
                                   class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
                        </div>
<div>
    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">📅 Дата та час прильоту</label>
    <input type="datetime-local" name="arrival_time" required
           class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
</div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Базова вартість квитка (₴)</label>
                            <input type="number" name="base_price" placeholder="Напр: 2450" required min="1" step="0.01"
                                   class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
                        </div>

                        <div class="sm:col-span-2 pt-2">
             <button type="submit" style="background-color: #16a34a; font-weight: 700;" 
        class="w-full text-white py-2 px-4 rounded-lg text-sm transition hover:opacity-90 cursor-pointer">
    🚀 Опублікувати рейс у продаж
</button>
                        </div>
                    </form>
                </div>

            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-black text-slate-800 mb-4">📋 ДІЮЧИЙ РОЗКЛАД АВІАКОМПАНІЇ</h3>
                <div class="bg-white p-5 rounded-2xl shadow-sm border mb-8 text-gray-800">
    <form action="{{ request()->url() }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        
        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">📅 Дата вильоту</label>
            <input type="date" name="filter_date" value="{{ request('filter_date') }}"
                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">🛫 Звідки</label>
            <select name="filter_departure_city" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Всі міста</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ request('filter_departure_city') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">🛬 Куди</label>
            <select name="filter_arrival_city" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Всі міста</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ request('filter_arrival_city') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">✈️ Літак</label>
            <select name="filter_airplane" 
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Всі літаки</option>
                @foreach($airplanes as $airplane)
                    <option value="{{ $airplane->id }}" {{ request('filter_airplane') == $airplane->id ? 'selected' : '' }}>
                        {{ $airplane->model }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex space-x-2">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-sm transition shadow-sm">
                Фільтрувати
            </button>
            
            @if(request()->hasAny(['filter_date', 'filter_departure_city', 'filter_arrival_city', 'filter_airplane']))
                <a href="{{ request()->url() }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-3 rounded-xl text-sm transition text-center flex items-center justify-center" title="Скинути">
                    ✕
                </a>
            @endif
        </div>

    </form>
</div>
                <div class="overflow-x-auto border rounded-xl">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-xs font-bold uppercase text-gray-600 tracking-wider">
                                <th class="p-4">Рейс</th>
                                <th class="p-4">Маршрут</th>
                                <th class="p-4">Борт літака</th>
                                <th class="p-4">Час вильоту</th>
                                <th class="p-4 text-center">Ціна тарифу</th>
                                <th class="p-4 text-center">Дія</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                      @forelse($flights as $flight)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-mono font-bold text-indigo-600">{{ $flight->flight_number }}</td>
                                    <td class="p-4 font-semibold text-gray-800">{{ $flight->dep_city }} ➔ {{ $flight->arr_city }}</td>
                                    <td class="p-4 text-gray-600 text-xs font-medium">{{ $flight->plane_model }}</td>
                                    <td class="p-4 font-mono text-xs">{{ date('d.m.Y H:i', strtotime($flight->departure_time)) }}</td>
                                    <td class="p-4 text-center text-emerald-600 font-extrabold text-md">
                                        {{ number_format($flight->base_price, 2, ',', ' ') }} ₴
                                    </td>
                                    <td class="p-4 text-center flex justify-center">
                                        <form action="{{ route('admin.schedules.destroy', $flight->id) }}" method="POST" onsubmit="return confirm('Зняти цей рейс з розкладу?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-rose-50 text-rose-700 border border-rose-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-rose-100 transition cursor-pointer">
                                                ❌ Зняти з рейсу
                                            </button>
                                        </form>

<div class="flex items-center space-x-2">
<a href="{{ route('admin.schedules.details', $flight->id) }}" 
   class="px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-black rounded-lg text-xs font-bold transition shadow-sm flex items-center">
    ℹ️ Пасажири
</a>    
<div x-data="{ open: false }" class="inline-block text-left">
        <button @click="open = !open" 
                class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow-sm"
                style="background-color: #f59e0b;">
            ✏️ Ціна
        </button>

        <div x-show="open" @click.away="open = false" 
             class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl border p-4 z-50 text-gray-800">
            
            <form action="{{ route('admin.schedules.update-price', $flight->id) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Нова вартість ($)</label>
                <input type="number" name="price" value="{{ $flight->base_price }}" required min="1"
                       class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3">
                
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="open = false" class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700">Скасувати</button>
                    <button type="submit" class="px-3 py-1 bg-green-600 text-white font-bold rounded-lg text-xs hover:opacity-90" style="background-color: #16a34a;">Зберегти</button>
                </div>
            </form>
            
        </div>
    </div>
</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400">Розклад порожній. Створіть перший виліт за допомогою форми вище.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>