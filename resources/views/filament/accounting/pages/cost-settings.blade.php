<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Tarif Lembur --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-warning-50 rounded-lg">
                        <x-heroicon-o-clock class="w-5 h-5 text-warning-600" />
                    </div>
                    <span>Tarif Lembur</span>
                </div>
            </x-slot>
            <x-slot name="description">Tarif dasar yang akan dikalikan dengan jumlah jam lembur valid dari absensi.</x-slot>
            
            <div class="flex flex-col gap-4 mt-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tarif per Jam (Rp)</label>
                    <input type="number" wire:model="overtime_rate" class="fi-input block w-full rounded-lg border-none py-1.5 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600" />
                </div>
                <x-filament::button wire:click="saveOvertime" class="w-full">
                    Simpan Tarif Lembur
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Potongan Keterlambatan --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-danger-50 rounded-lg">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-danger-600" />
                    </div>
                    <span>Denda Keterlambatan</span>
                </div>
            </x-slot>
            <x-slot name="description">Potongan tetap yang dikenakan per hari kerja jika status kehadiran terlambat.</x-slot>
            
            <div class="flex flex-col gap-4 mt-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Denda per Hari (Rp)</label>
                    <input type="number" wire:model="late_penalty" class="fi-input block w-full rounded-lg border-none py-1.5 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600" />
                </div>
                <x-filament::button color="danger" wire:click="savePenalty" class="w-full">
                    Simpan Denda
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- BPJS --}}
        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-success-50 rounded-lg">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-success-600" />
                    </div>
                    <span>Potongan BPJS & Asuransi</span>
                </div>
            </x-slot>
            <x-slot name="description">Persentase potongan wajib bulanan dari Gaji Pokok pegawai.</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Kesehatan (%)</label>
                    <div class="relative">
                        <input type="number" step="0.1" wire:model="bpjs_kes_percent" class="fi-input block w-full rounded-lg border-none py-1.5 pl-3 pr-8 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600" />
                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-500">%</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Ketenagakerjaan (%)</label>
                    <div class="relative">
                        <input type="number" step="0.1" wire:model="bpjs_tk_percent" class="fi-input block w-full rounded-lg border-none py-1.5 pl-3 pr-8 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600" />
                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-500">%</span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-start">
                <x-filament::button color="success" wire:click="saveBpjs">
                    Simpan Konfigurasi BPJS
                </x-filament::button>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
