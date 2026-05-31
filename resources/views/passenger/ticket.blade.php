<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Електронний квиток - {{ $ticket->ticket_code }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-900 p-4 md:p-8 min-h-screen flex flex-col items-center justify-center font-sans antialiased selection:bg-indigo-500 selection:text-white">

    <div class="max-w-2xl w-full flex justify-between items-center mb-4 print:hidden">
        <a href="/dashboard" class="text-sm text-slate-400 hover:text-white transition">← Назад в кабінет</a>
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow transition cursor-pointer">
            🖨️ Роздрукувати / Зберегти в PDF
        </button>
    </div>

    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 text-slate-800 print:shadow-none print:border-none">
        
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white flex justify-between items-center">
            <div>
                <p class="text-xs uppercase tracking-widest opacity-75 font-bold">Посадковий талон / Boarding Pass</p>
                <h1 class="text-2xl font-black tracking-tight mt-1">AERO LINE</h1>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase tracking-widest opacity-75 font-bold">Код квитка</p>
                <p class="text-xl font-mono font-bold tracking-wider mt-1">{{ $ticket->ticket_code }}</p>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-3 items-center text-center mb-8">
                <div class="text-left">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Звідки / From</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ $ticket->dep_city }}</p>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <p class="text-lg font-bold text-indigo-600 tracking-widest">{{ $ticket->flight_number }}</p>
                    <div class="w-full border-t border-dashed border-slate-300 my-2 relative">
                        <span class="absolute -top-2 left-1/2 -translate-x-1/2 bg-white px-2 text-sm">✈️</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium uppercase">{{ $ticket->airplane_model }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Куди / To</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ $ticket->arr_city }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-slate-50 rounded-2xl p-5 border border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Пасажир / Passenger</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">{{ $ticket->passenger_name }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Паспорт / Passport</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5 font-mono">{{ $ticket->passport_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Дата та час / Date</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">{{ date('d.m.Y H:i', strtotime($ticket->departure_time)) }}</p>
                </div>
                <div class="text-center bg-indigo-50 border border-indigo-100 rounded-xl py-1">
                    <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Місце / Seat</p>
                    <p class="text-base font-black text-indigo-900">{{ $ticket->seat_number }}</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-dashed border-slate-200 flex flex-col items-center justify-center">
                <div class="w-full h-14 bg-[repeating-linear-gradient(90deg,transparent,transparent_2px,#1e293b_2px,#1e293b_4px,transparent_4px,transparent_8px,#1e293b_8px,#1e293b_12px)] opacity-85"></div>
                <p class="text-[10px] font-mono tracking-[0.5em] text-slate-400 mt-2 uppercase">*{{ $ticket->ticket_code }}*</p>
            </div>
        </div>

        <div class="bg-emerald-50 border-t border-emerald-100 px-8 py-3 flex justify-between items-center">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span> Електронний квиток активовано
            </span>
            <span class="text-[10px] text-slate-400 font-medium">Статус оплати: PAID</span>
        </div>
    </div>

</body>
</html>