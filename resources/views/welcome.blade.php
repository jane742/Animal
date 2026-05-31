<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlyHigh — Бронювання авіаквитків</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 font-sans antialiased text-gray-900">

    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">✈️</span>
                <span class="text-xl font-black tracking-tight text-indigo-600">FlyHigh</span>
            </div>
            
            <nav class="flex items-center space-x-4">
                @if (Route::has('login'))
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">⚙️ Адмін-панель</a>
                        @endif
                        <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-gray-700 hover:text-indigo-600 transition">Мій кабінет</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-600 hover:text-indigo-600 transition">Увійти</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm">Реєстрація</a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <main>
        <div class="relative bg-indigo-900 text-white py-24 px-4 overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=2073');"></div>
            
            <div class="relative max-w-4xl mx-auto text-center space-y-6">
                <h1 class="text-4xl md:text-5xl font-black tracking-tight leading-tight">
                    Знайдіть свій наступний незабутній політ
                </h1>
                <p class="text-indigo-200 text-lg max-w-xl mx-auto">
                    Швидкий пошук авіаквитків, зручний вибір місць у салоні літака та прозора аналітика цін.
                </p>

                <div class="bg-white rounded-2xl shadow-2xl p-6 text-gray-800 text-left mt-12 border border-gray-100">
                    <form action="{{ url('/') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-400 mb-1.5 tracking-wider">🛫 Звідки</label>
                            <select name="departure_city" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="" disabled selected>Оберіть місто...</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-400 mb-1.5 tracking-wider">🛬 Куди</label>
                            <select name="arrival_city" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="" disabled selected>Куди летимо?</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-400 mb-1.5 tracking-wider">📅 Дата вильоту</label>
                            <input type="date" name="departure_date" required min="{{ date('Y-m-to') }}"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <button type="submit" style="background-color: #16a34a;" class="w-full text-white font-bold py-2.5 px-4 rounded-xl text-sm transition hover:opacity-95 shadow-md flex items-center justify-center space-x-2 cursor-pointer">
                                <span>🔍 Знайти рейси</span>
                            </button>
                        </div>

                    </form>
                    @if(isset($flights))
    <div class="mt-12 max-w-6xl mx-auto px-4">
        <h3 class="text-xl font-bold text-gray-800 mb-6">🔍 Знайдені варіанти перельоту:</h3>

        @if($flights->isEmpty())
            <div class="bg-white p-8 rounded-2xl shadow-sm text-center border">
                <span class="text-4xl">📭</span>
                <h4 class="text-lg font-bold text-gray-700 mt-4">На жаль, на цю дату рейсів не знайдено</h4>
                <p class="text-gray-500 text-sm mt-1">Спробуйте змінити міста або обрати іншу дату вильоту.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($flights as $flight)
                    <div class="bg-white rounded-2xl shadow-sm border p-6 flex flex-col md:flex-row justify-between items-center gap-4 transition hover:shadow-md">
                        
                        <div class="flex items-center space-x-6">
                            <div class="text-center">
                                <span class="block font-black text-xl text-gray-900">{{ date('H:i', strtotime($flight->departure_time)) }}</span>
                                <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">{{ $flight->departure_city_name }}</span>
                            </div>
                            
                            <div class="text-gray-300 flex flex-col items-center">
                                <span class="text-xs font-semibold text-gray-400">✈️ {{ $flight->airplane_model }}</span>
                                <div class="w-24 border-t-2 border-dashed border-gray-300 my-1"></div>
                            </div>

                            <div class="text-center">
                                <span class="block font-black text-xl text-gray-900">{{ date('H:i', strtotime($flight->arrival_time)) }}</span>
                                <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">{{ $flight->arrival_city_name }}</span>
                            </div>
                        </div>

                        <div class="text-sm text-gray-500 text-center">
                            <span class="block font-medium">📅 {{ date('d.m.Y', strtotime($flight->departure_time)) }}</span>
                        </div>

                        <div class="text-center md:text-right flex flex-col items-center md:items-end">
                            <span class="text-xs text-gray-400 font-bold uppercase">Ціна</span>
                            <span class="text-2xl font-black text-indigo-600">${{ $flight->base_price }}</span>
                            
                            <a href="{{ route('login') }}" class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2 rounded-xl text-sm shadow-sm transition">
                                🎫 Забронювати квиток
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
                </div>
                </div>
        </div>

        <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl mb-3">🛋️</div>
                    <h3 class="font-bold text-gray-800 mb-2">Інтерактивна карта салону</h3>
                    <p class="text-sm text-gray-500">Обирайте найкращі місця біля вікна або з додатковим простором для ніг прямо під час купівлі.</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl mb-3">⚡</div>
                    <h3 class="font-bold text-gray-800 mb-2">Миттєве бронювання</h3>
                    <p class="text-sm text-gray-500">Жодних затримок. Квиток та унікальний номер крісла фіксуються за вами в ту ж саму секунду.</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl mb-3">🛡️</div>
                    <h3 class="font-bold text-gray-800 mb-2">Надійні авіалайнери</h3>
                    <p class="text-sm text-gray-500">Наш флот складається виключно із сучасних бортів, що пройшли повну технічну перевірку.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-gray-400 py-8 text-center text-xs border-t border-gray-700">
        <p>&copy; {{ date('Y') }} FlyHigh. Всі права захищено. Створено з любов'ю на Laravel & Tailwind CSS.</p>
    </footer>

</body>
</html>