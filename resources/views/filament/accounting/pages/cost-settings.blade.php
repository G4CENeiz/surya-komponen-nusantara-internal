<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Tarif Lembur --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-orange-50 rounded-md">
                    <x-heroicon-o-clock class="w-5 h-5 text-orange-600" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Tarif Lembur</h3>
            </div>
            <p class="text-sm text-gray-500 mb-4">Tarif dasar yang akan dikalikan dengan jumlah jam lembur valid dari absensi.</p>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tarif per Jam (Rp)</label>
                <input type="text" value="{{ number_format($overtime_rate, 0, ',', '.') }}" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors w-full">
                Simpan Tarif Lembur
            </button>
        </div>

        {{-- Potongan Keterlambatan --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-red-50 rounded-md">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Denda Keterlambatan</h3>
            </div>
            <p class="text-sm text-gray-500 mb-4">Potongan tetap yang dikenakan per hari kerja jika status kehadiran terlambat.</p>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Denda per Hari (Rp)</label>
                <input type="text" value="{{ number_format($late_penalty, 0, ',', '.') }}" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none" />
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors w-full">
                Simpan Denda
            </button>
        </div>

        {{-- BPJS --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 md:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-green-50 rounded-md">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-green-600" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Potongan BPJS & Asuransi</h3>
            </div>
            <p class="text-sm text-gray-500 mb-6">Persentase potongan wajib bulanan dari Gaji Pokok pegawai.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">BPJS Kesehatan (%)</label>
                    <div class="relative">
                        <input type="number" value="{{ $bpjs_kes_percent }}" class="w-full border border-gray-300 rounded-md pl-4 pr-8 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" />
                        <span class="absolute right-3 top-2.5 text-gray-500">%</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">BPJS Ketenagakerjaan (%)</label>
                    <div class="relative">
                        <input type="number" value="{{ $bpjs_tk_percent }}" class="w-full border border-gray-300 rounded-md pl-4 pr-8 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" />
                        <span class="absolute right-3 top-2.5 text-gray-500">%</span>
                    </div>
                </div>
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors">
                Simpan Konfigurasi BPJS
            </button>
        </div>

    </div>
</x-filament-panels::page>
