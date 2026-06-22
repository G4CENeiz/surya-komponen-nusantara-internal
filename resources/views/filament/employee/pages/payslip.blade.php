<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Header Section --}}
        <x-filament::section>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">Periode Gaji</h2>
                        <div class="w-48">
                            {{ $this->form }}
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 flex items-center gap-1.5">
                        <x-heroicon-o-identification class="w-4 h-4 text-gray-400" />
                        <span>Kelas Jabatan: <span class="font-medium text-gray-700">{{ $gaji['kelas_jabatan'] ?? '-' }}</span></span>
                    </div>
                </div>
                
                <x-filament::button color="gray" icon="heroicon-o-arrow-down-tray">
                    Unduh PDF
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Details Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Pendapatan --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-primary-600">
                        <x-heroicon-o-plus-circle class="w-5 h-5" />
                        <span>Pendapatan</span>
                    </div>
                </x-slot>

                <div class="flex flex-col divide-y divide-gray-100">
                    @forelse($gaji['pendapatan'] ?? [] as $label => $amount)
                        <div class="flex justify-between items-center py-3 text-sm">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-3 text-sm text-gray-500 italic">Belum ada rincian.</div>
                    @endforelse
                </div>

                <x-slot name="footer">
                    <div class="flex justify-between items-center font-bold">
                        <span class="text-gray-700">Total Pendapatan</span>
                        <span class="text-primary-600">Rp {{ number_format($gaji['total_pendapatan'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </x-slot>
            </x-filament::section>

            {{-- Potongan --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-danger-600">
                        <x-heroicon-o-minus-circle class="w-5 h-5" />
                        <span>Potongan</span>
                    </div>
                </x-slot>

                <div class="flex flex-col divide-y divide-gray-100">
                    @forelse($gaji['potongan'] ?? [] as $label => $amount)
                        <div class="flex justify-between items-center py-3 text-sm">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-medium text-danger-600">- Rp {{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-3 text-sm text-gray-500 italic">Tidak ada potongan.</div>
                    @endforelse
                </div>

                <x-slot name="footer">
                    <div class="flex justify-between items-center font-bold">
                        <span class="text-gray-700">Total Potongan</span>
                        <span class="text-danger-600">- Rp {{ number_format($gaji['total_potongan'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </x-slot>
            </x-filament::section>

        </div>

        {{-- Take Home Pay Section --}}
        <x-filament::section class="bg-primary-50 ring-primary-500/50">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Take-Home Pay</h3>
                    <p class="text-sm text-gray-600 mt-1">Total yang diterima ke rekening Anda</p>
                </div>
                <div class="text-3xl font-bold text-primary-600 tracking-tight">
                    Rp {{ number_format($gaji['take_home_pay'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
