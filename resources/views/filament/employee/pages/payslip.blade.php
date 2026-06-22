<x-filament-panels::page>
    <div class="w-full flex flex-col gap-6">
        
        {{-- Header / Info Pegawai --}}
        <div class="bg-white rounded-lg border border-gray-200 p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-bold text-gray-900 tracking-tight">Periode:</h2>
                        <div class="w-48">
                            {{ $this->form }}
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-4 flex items-center gap-2">
                        <x-heroicon-o-identification class="w-4 h-4" />
                        {{ $gaji['kelas_jabatan'] }}
                    </p>
                </div>
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Unduh PDF
                </button>
            </div>
        </div>

        {{-- Grid Pendapatan & Potongan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Pendapatan --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden flex flex-col">
                <div class="bg-blue-50 border-b border-blue-200 px-8 py-4 flex items-center gap-2">
                    <x-heroicon-o-plus-circle class="w-5 h-5 text-blue-600" />
                    <h3 class="font-bold text-blue-900">Pendapatan</h3>
                </div>
                <div class="p-8 flex-grow flex flex-col">
                    @foreach($gaji['pendapatan'] as $label => $amount)
                    <div class="flex justify-between items-center text-sm border-b border-gray-200 last:border-0 py-3 first:pt-0 last:pb-0">
                        <span class="text-gray-600">{{ $label }}</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <span class="font-bold text-gray-700">Total Pendapatan</span>
                    <span class="font-bold text-blue-600">Rp {{ number_format($gaji['total_pendapatan'], 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Potongan --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden flex flex-col">
                <div class="bg-red-50 border-b border-red-200 px-8 py-4 flex items-center gap-2">
                    <x-heroicon-o-minus-circle class="w-5 h-5 text-red-600" />
                    <h3 class="font-bold text-red-900">Potongan</h3>
                </div>
                <div class="p-8 flex-grow flex flex-col">
                    @foreach($gaji['potongan'] as $label => $amount)
                    <div class="flex justify-between items-center text-sm border-b border-gray-200 last:border-0 py-3 first:pt-0 last:pb-0">
                        <span class="text-gray-600">{{ $label }}</span>
                        <span class="font-medium text-red-600">- Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <span class="font-bold text-gray-700">Total Potongan</span>
                    <span class="font-bold text-red-600">- Rp {{ number_format($gaji['total_potongan'], 0, ',', '.') }}</span>
                </div>
            </div>

        </div>

        {{-- Take Home Pay --}}
        <div class="bg-white rounded-lg border border-gray-200 p-8 flex flex-col sm:flex-row justify-between items-center gap-4 mt-2">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Take-Home Pay</h3>
                <p class="text-sm text-gray-500 mt-1">Total yang diterima ke rekening Anda</p>
            </div>
            <div class="text-3xl font-bold text-blue-600 tracking-tight">
                Rp {{ number_format($gaji['take_home_pay'], 0, ',', '.') }}
            </div>
        </div>

    </div>
</x-filament-panels::page>
