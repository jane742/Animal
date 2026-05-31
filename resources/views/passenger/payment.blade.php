<x-app-layout>
    <div class="py-12 bg-slate-100 min-h-screen flex items-center justify-center">
        <div class="max-w-6xl w-full bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">💳 AeroPay Secure</h2>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2 py-1 rounded">Test Mode</span>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 mb-6 text-sm text-slate-600 space-y-2">
                <div class="flex justify-between"><span>Рейс:</span><span class="font-bold text-slate-800">{{ $booking->flight_number }}</span></div>
                <div class="flex justify-between"><span>Номер броні:</span><span>#{{ $booking->id }}</span></div>
                <div class="flex justify-between border-t pt-2 mt-2 text-base font-bold text-slate-800">
                    <span>До сплати:</span><span class="text-emerald-600">{{ number_format($booking->total_price, 2) }} ₴</span>
                </div>
            </div>
<div style="padding:10px"> 
            <form action="/dashboard/payment/{{ $booking->id }}" method="POST" class="space-x-4 p-12">
                @csrf
                <div> 
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Номер картки</label>
                    <input type="text" required placeholder="4441 •••• •••• 4444" class="w-full border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-center tracking-widest text-lg">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Термін дії</label>
                        <input type="text" required placeholder="MM/YY" class="w-full border-gray-200 rounded-lg text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CVC2 / CVV</label>
                        <input type="password" required placeholder="•••" class="w-full border-gray-200 rounded-lg text-center">
                    </div>
                </div>

                <button type="submit" style="background-color: #10b981; " class="w-full mt-6 hover:bg-emerald-700 text-black font-bold py-3 px-4 rounded-xl shadow-md transition duration-150">
                    Підтвердити та оплатити
                </button>
            </form>
            
            <a href="/dashboard" style="color: #eb1e1e; "class="block text-center mt-4 hover:underline">❌ Скасувати та повернутись</a>
        </div>
        </div>
    </div>
</x-app-layout>