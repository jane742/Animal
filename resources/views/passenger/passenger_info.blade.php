<x-app-layout>
<div class="max-w-2xl mx-auto p-6 bg-slate-50 min-h-screen">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">✈️ Оформлення пасажирів</h2>
    <p class="text-sm text-gray-500 mb-6">Будь ласка, вкажіть дані осіб, які летітимуть на обраних місцях.</p>

<!-- <form action="{{ route('passenger.save_tickets', $booking->id) }}" method="POST" id="passenger-form">
    @csrf

    <input type="hidden" name="action" id="form_action" value="pay_now" required>

    @foreach($selectedSeats as $index => $seat)
        <div class="bg-white border border-slate-200 p-5 mb-5 rounded-xl shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-indigo-600">Пасажир №{{ $index + 1 }}</h3>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-mono font-bold rounded-lg text-sm border border-indigo-100">
                    💺 Місце {{ $seat->seat_number }}
                </span>
            </div>
            
            <input type="hidden" name="passengers[{{ $index }}][seat_id]" value="{{ $seat->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider mb-1">Ім'я та Прізвище</label>
                    <input type="text" name="passengers[{{ $index }}][name]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider mb-1">Номер паспорта</label>
                    <input type="text" name="passengers[{{ $index }}][passport]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                </div>
            </div>
        </div>
    @endforeach
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4">
        <button type="button" onclick="submitFormWithAction('pay_now')" id="submit_btn_now" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-xl shadow text-sm">💳 Оплатити зараз</button>
        <button type="button" onclick="submitFormWithAction('pay_later')" id="submit_btn_later" class="w-full bg-amber-500 text-white font-bold py-3 px-4 rounded-xl shadow text-sm">⏳ Оплатити пізніше</button>
    </div>
</form> -->
<form action="{{ route('passenger.save_tickets', $booking->id) }}" method="POST" id="passenger-form">
    @csrf
    <input type="hidden" name="action" id="form_action" value="pay_now" required>

    @auth
        <div class="mb-6">
            <button type="button" id="autofill-btn"
                    style="background-color: #ccdeef; border-width:1px;border-style:solid;"
                    data-name="{{ auth()->user()->name }}" 
                    data-passport="{{ auth()->user()->passport_number ?? '' }}" {{-- Перевірте, як у вашій таблиці users називається поле паспорта --}}
                    onclick="autofillCurrentUser()"
                    class=" border-gray-400 text-black hover:bg-indigo-200 font-bold py-2 px-4 rounded-xl text-xs transition duration-150 shadow-sm cursor-pointer inline-flex items-center gap-1">
                👤 Заповнити моїми даними (Я лечу)
            </button>
        </div>
    @endauth

    @foreach($selectedSeats as $index => $seat)
        <div class="bg-white border border-slate-200 p-15 mb-5 rounded-xl shadow-sm passenger-card" style="padding:10px">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-indigo-600">Пасажир №{{ $index + 1 }}</h3>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-mono font-bold rounded-lg text-sm border border-indigo-100">
                    💺 Місце {{ $seat->seat_number }}
                </span>
            </div>
            
            <input type="hidden" name="passengers[{{ $index }}][seat_id]" value="{{ $seat->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider mb-1">Ім'я та Прізвище</label>
                    <input type="text" name="passengers[{{ $index }}][name]" required class="passenger-name w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider mb-1">Номер паспорта</label>
                    <input type="text" name="passengers[{{ $index }}][passport]" required class="passenger-passport w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                </div>
            </div>
        </div>
    @endforeach

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4">
        <button type="button" onclick="submitFormWithAction('pay_now')" id="submit_btn_now" class="w-full mb-3 text-white font-bold py-3 px-4 rounded-xl shadow text-sm" style="background-color: #10b981; " >💳 Оплатити зараз</button>
        
        <button type="button" onclick="submitFormWithAction('pay_later')" id="submit_btn_later" class="w-full bg-amber-500 text-white font-bold py-3 px-4 rounded-xl shadow text-sm">⏳ Оплатити пізніше</button>
    </div>
</form>
<script>
function submitFormWithAction(actionValue) {
    // 1. Записуємо у приховане поле те, що обрав користувач ('pay_now' або 'pay_later')
    document.getElementById('form_action').value = actionValue;
    
    // 2. Сабмітимо (відправляємо) форму на сервер
    document.getElementById('passenger-form').submit();
}
</script>
<script>
function autofillCurrentUser() {
    const btn = document.getElementById('autofill-btn');
    
    // 1. Зчитуємо дані користувача з дата-атрибутів кнопки
    const currentUserName = btn.getAttribute('data-name');
    const currentUserPassport = btn.getAttribute('data-passport');

    // 2. Знаходимо найперші інпути імені та паспорта на сторінці
    const firstNameInput = document.querySelector('.passenger-name');
    const firstPassportInput = document.querySelector('.passenger-passport');

    // 3. Якщо інпути знайдені — заповнюємо їх
    if (firstNameInput && firstPassportInput) {
        firstNameInput.value = currentUserName;
        firstPassportInput.value = currentUserPassport;

        // 4. 🔥 БЛОКУЄМО КНОПКУ, щоб її не можна було натиснути вдруге
        btn.disabled = true;
        btn.style.backgroundColor = "#e2e8f0"; // робимо сірою
        btn.style.color = "#94a3b8";
        btn.style.cursor = "not-allowed";
        btn.innerHTML = "✅ Дані профілю внесено";
    }
}



</script>
</div>
</x-app-layout>