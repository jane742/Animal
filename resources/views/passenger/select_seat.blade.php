<x-app-layout>
    <div class="py-12 bg-slate-900 min-h-screen flex items-center justify-center">
        <div class="max-w-xl w-full bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
            
            <div class="text-center mb-6">
                <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Крок 2 з 3</p>
                <h2 class="text-2xl font-black text-slate-850 mt-1">💺 Виберіть місце в салоні</h2>
                <p class="text-sm text-gray-400 mt-1">Оберіть будь-яке вільне крісло на схемі літака</p>
            </div>

            <div class="flex justify-center space-x-6 text-xs font-semibold mb-8 bg-slate-50 p-3 rounded-xl border">
                <div class="flex items-center space-x-2">
                    <span style="background-color: #10b981;" class="w-4 h-4 rounded  block shadow-sm"></span> <span>Вільне</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span style="background-color: #ef4444;" class="w-4 h-4 rounded block shadow-sm"></span> <span>Зайняте</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-4 rounded bg-indigo-600 block shadow-sm"></span> <span>Ваш вибір</span>
                </div>
            </div>

            <div class="bg-slate-100 border rounded-2xl p-6 max-w-sm mx-auto shadow-inner">
                @if ($errors->any())
    <div style="background-color: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <ul style="list-style-type: disc; padding-left: 1.25rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('passenger.save_seat', $booking->id) }}" method="POST" id="seating-form">
    @csrf
    <input type="hidden" name="flight_schedule_id" value="{{ $schedule->id }}">
    
    <input type="hidden" name="selected_seats" id="selected-seats-input" value="">

    <div class="text-center text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-4">✈️ Носова частина літака (Кокпіт)</div>
    
    <div class="grid grid-cols-3 gap-3 text-center mb-6">
        @foreach($allSeats as $seat)
            @php
                $isOccupied = in_array($seat->id, $occupiedSeatIds);
            @endphp

            @if($isOccupied)
                <button disabled type="button" 
                        style="background-color: #ef4444; border: 2px solid #b91c1c; color: white; cursor: not-allowed; opacity: 0.85; box-shadow: inset 0 -3px 0 #b91c1c;"
                        class="font-mono font-bold py-3 rounded-xl text-xs shadow-sm">
                    {{ $seat->seat_number }}
                </button>
            @else
                <button type="button" 
                        onclick="selectThisSeat(this, {{ $seat->id }}, '{{ $seat->seat_number }}')" 
                        style="background-color: #10b981; border: 2px solid #047857; color: white; cursor: pointer; box-shadow: inset 0 -3px 0 #047857;"
                        class="seat-btn font-mono font-bold py-3 rounded-xl text-xs transition duration-150 shadow-sm active:scale-95">
                    {{ $seat->seat_number }}
                </button>
            @endif
        @endforeach
    </div>
    
    <div class="pt-2">
        <button type="submit" id="submit_btn" disabled 
                style="background-color: #e2e8f0; color: #94a3b8; cursor: not-allowed;"
                class="w-full font-bold py-3 px-4 rounded-xl shadow transition duration-150 text-sm">
            ➡️ Продовжити до введення даних (Обрано місць: <span id="seats-counter">0</span>)
        </button>
    </div>
</form>

<script>
    // Масив для збереження ID обраних місць
    let selectedSeats = [];

    function selectThisSeat(buttonElement, seatId, seatNumber) {
        // Перевіряємо, чи це місце вже було обране
        const index = selectedSeats.indexOf(seatId);

        if (index === -1) {
            // 1. Якщо НЕ обране — додаємо в масив
            selectedSeats.push(seatId);
            
            // Змінюємо стиль кнопки на синій (активний) колір
            buttonElement.style.backgroundColor = "#3b82f6";
            buttonElement.style.borderColor = "#1d4ed8";
            buttonElement.style.boxShadow = "inset 0 -3px 0 #1d4ed8";
        } else {
            // 2. Якщо вже було обране — видаляємо з масиву (скасування вибору)
            selectedSeats.splice(index, 1);
            
            // Повертаємо початковий зелений колір
            buttonElement.style.backgroundColor = "#10b981";
            buttonElement.style.borderColor = "#047857";
            buttonElement.style.boxShadow = "inset 0 -3px 0 #047857";
        }

        // 3. Оновлюємо значення прихованого інпуту для Laravel (перетворюємо масив у рядок "101,102")
        document.getElementById('selected-seats-input').value = selectedSeats.join(',');

        // 4. Оновлюємо лічильник кількості місць у кнопці
        document.getElementById('seats-counter').innerText = selectedSeats.length;

        // 5. Керуємо доступністю кнопки "Продовжити"
        const submitBtn = document.getElementById('submit_btn');
        
        if (selectedSeats.length > 0) {
            // Активуємо кнопку (робимо яскраво-фіолетовою/індиго)
            submitBtn.disabled = false;
            submitBtn.style.backgroundColor = "#4f46e5";
            submitBtn.style.color = "#ffffff";
            submitBtn.style.cursor = "pointer";
        } else {
            // Блокуємо назад, якщо розклікали всі місця
            submitBtn.disabled = true;
            submitBtn.style.backgroundColor = "#e2e8f0";
            submitBtn.style.color = "#94a3b8";
            submitBtn.style.cursor = "not-allowed";
        }
    }
</script>

        </div>
    </div>

</x-app-layout>