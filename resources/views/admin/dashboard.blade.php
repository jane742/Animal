<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            🛡️ Панель адміністратора: База даних авіалінії
        </h2>
    </x-slot>

    <div class="py-6 bg-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Сповіщення --}}
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

            {{-- Навігація верхнього рівня (для майбутніх рейсів) --}}
  {{-- Жива навігація верхнього рівня --}}
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center space-x-2">
                            <span>🌆 Міста сполучення</span>
                        </h3>

                        <form action="{{ route('admin.cities.store') }}" method="POST" class="flex items-center space-x-2 mb-6">
                            @csrf
                            <input type="text" name="name" placeholder="Назва міста (напр. Лондон)" required 
                                   class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-4 py-2 rounded-lg transition whitespace-nowrap cursor-pointer">
                                ➕ Додати
                            </button>
                        </form>

                        <div class="border rounded-xl overflow-hidden">
                            <table class="w-full text-left text-sm text-gray-700">
                                <body class="divide-y">
                                    @forelse($cities as $city)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-3 font-semibold text-gray-800" id="city-name-{{ $city->id }}">{{ $city->name }}</td>
                                            <td class="p-3 text-right flex justify-end space-x-2">
                                                <button onclick="editCity({{ $city->id }}, '{{ $city->name }}')" class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-md text-xs font-bold hover:bg-amber-100 cursor-pointer">
                                                    ✏️
                                                </button>

                                                <form action="{{ route('admin.cities.destroy', $city->id) }}" method="POST" onsubmit="return confirm('Видалити місто?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-md text-xs font-bold hover:bg-rose-100 cursor-pointer">
                                                        ❌
                                                    </button>
                                                </form>

                                                {{-- Прихована форма для виконання Update --}}
                                                <form id="edit-city-form-{{ $city->id }}" action="{{ route('admin.cities.update', $city->id) }}" method="POST" class="hidden">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="name" id="edit-city-input-{{ $city->id }}">
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="p-4 text-center text-gray-400">Міст у базі немає</td></tr>
                                    @endforelse
                                </body>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center space-x-2">
                        <span>✈️ Авіапарк (Літаки)</span>
                    </h3>

                    <form action="{{ route('admin.airplanes.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-6 bg-slate-50 p-3 rounded-xl border">
                        @csrf
                        <div class="sm:col-span-2">
                            <input type="text" name="model" placeholder="Модель (Boeing 737)" required 
                                   class="w-full border-gray-300 rounded-lg text-sm bg-white focus:ring-indigo-500">
                        </div>
                        <div>
                            <input type="number" name="seats_count" placeholder="Місць (max 300)" required min="1" max="300"
                                   class="w-full border-gray-300 rounded-lg text-sm bg-white focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-3">
                           <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-sm py-2 rounded-lg transition cursor-pointer">
    ➕ Додати новий борт та згенерувати місця
</button>
                        </div>
                    </form>

                    <div class="border rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm text-gray-700">
                            <body class="divide-y">
                                @forelse($airplanes as $plane)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="p-3">
                                            <div class="font-bold text-gray-800" id="plane-model-{{ $plane->id }}">{{ $plane->model }}</div>
                                            <div class="text-xs text-gray-400">Конфігурація: Авто-карта місць згенерована</div>
                                        </td>
                                        <td class="p-3 text-right flex justify-end space-x-2 items-center h-full pt-5">
                                            <button onclick="editPlane({{ $plane->id }}, '{{ $plane->model }}')" class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-md text-xs font-bold hover:bg-amber-100 cursor-pointer">
                                                ✏️
                                            </button>

                                            <form action="{{ route('admin.airplanes.destroy', $plane->id) }}" method="POST" onsubmit="return confirm('Видалити цей літак та ВСІ його місця з бази?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-md text-xs font-bold hover:bg-rose-100 cursor-pointer">
                                                    ❌
                                                </button>
                                            </form>

                                            {{-- Прихована форма для виконання Update літака --}}
                                            <form id="edit-plane-form-{{ $plane->id }}" action="{{ route('admin.airplanes.update', $plane->id) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="model" id="edit-plane-input-{{ $plane->id }}">
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="p-4 text-center text-gray-400">Авіапарк пустий</td></tr>
                                @endforelse
                            </body>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function editCity(id, currentName) {
            let newName = prompt("Введіть нову назву для міста:", currentName);
            if (newName != null && newName.trim() !== "") {
                document.getElementById('edit-city-input-' + id).value = newName;
                document.getElementById('edit-city-form-' + id).submit();
            }
        }

        function editPlane(id, currentModel) {
            let newModel = prompt("Введіть нову модель літака:", currentModel);
            if (newModel != null && newModel.trim() !== "") {
                document.getElementById('edit-plane-input-' + id).value = newModel;
                document.getElementById('edit-plane-form-' + id).submit();
            }
        }
    </script>
</x-app-layout>