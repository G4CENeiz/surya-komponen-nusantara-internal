<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Header & Filters --}}
        <x-filament::section>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Ringkasan Keuangan</h2>
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedMonth" class="fi-input block w-full rounded-lg border-none py-1.5 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                    <select wire:model.live="selectedYear" class="fi-input block w-full rounded-lg border-none py-1.5 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600">
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                    </select>
                </div>
            </div>
        </x-filament::section>
        
        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Total Gaji --}}
            <x-filament::section>
                <div class="flex items-center gap-2 mb-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-primary-600" />
                    <h3 class="font-semibold text-gray-600 text-sm">Total Beban Gaji</h3>
                </div>
                <div class="text-3xl font-bold text-gray-900 tracking-tight">
                    Rp {{ number_format($totalBaseSalary, 0, ',', '.') }}
                </div>
            </x-filament::section>

            {{-- Total Lembur --}}
            <x-filament::section>
                <div class="flex items-center gap-2 mb-2">
                    <x-heroicon-o-clock class="w-5 h-5 text-warning-500" />
                    <h3 class="font-semibold text-gray-600 text-sm">Total Honor Lembur</h3>
                </div>
                <div class="text-3xl font-bold text-gray-900 tracking-tight">
                    Rp {{ number_format($totalOvertime, 0, ',', '.') }}
                </div>
            </x-filament::section>

            {{-- Total Karyawan --}}
            <x-filament::section>
                <div class="flex items-center gap-2 mb-2">
                    <x-heroicon-o-users class="w-5 h-5 text-success-600" />
                    <h3 class="font-semibold text-gray-600 text-sm">Total Karyawan Aktif</h3>
                </div>
                <div class="text-3xl font-bold text-gray-900 tracking-tight flex items-baseline gap-2">
                    {{ number_format($totalEmployees, 0, ',', '.') }} <span class="text-sm text-gray-500 font-medium">Orang</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Periode {{ $periodText }}
                </p>
            </x-filament::section>
        </div>

        {{-- Custom Beautiful Salary Composition Chart --}}
        <x-filament::section>
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Komposisi Beban Gaji</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Distribusi pengeluaran periode {{ $periodText }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Total Beban</p>
                    <p class="text-xl font-bold text-primary-600 mt-0.5 tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                </div>
            </div>

            @php
                $pctBase = $totalExpense > 0 ? round(($totalBaseSalary / $totalExpense) * 100) : 0;
                $pctAllowance = $totalExpense > 0 ? round(($totalAllowance / $totalExpense) * 100) : 0;
                $pctOvertime = $totalExpense > 0 ? round(($totalOvertime / $totalExpense) * 100) : 0;
                $pctReimburse = $totalExpense > 0 ? round(($totalReimburse / $totalExpense) * 100) : 0;
            @endphp

            {{-- Stacked Progress Bar --}}
            <div class="w-full h-4 rounded-full flex overflow-hidden mb-6 bg-gray-100 ring-1 ring-inset ring-gray-200">
                @if($totalExpense > 0)
                    <div class="bg-primary-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: {{ $pctBase }}%;"></div>
                    <div class="bg-success-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: {{ $pctAllowance }}%;"></div>
                    <div class="bg-warning-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: {{ $pctOvertime }}%;"></div>
                    <div class="bg-danger-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: {{ $pctReimburse }}%;"></div>
                @endif
            </div>

            {{-- Legends --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-gray-50 ring-1 ring-gray-950/5 hover:ring-gray-950/10 transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-primary-500 shadow-sm"></div>
                        <span class="text-sm font-semibold text-gray-700">Gaji Pokok</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 tracking-tight">Rp {{ number_format($totalBaseSalary, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 font-medium mt-1">{{ $pctBase }}% dari total</div>
                </div>

                <div class="p-4 rounded-xl bg-gray-50 ring-1 ring-gray-950/5 hover:ring-gray-950/10 transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-success-500 shadow-sm"></div>
                        <span class="text-sm font-semibold text-gray-700">Tunjangan</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 tracking-tight">Rp {{ number_format($totalAllowance, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 font-medium mt-1">{{ $pctAllowance }}% dari total</div>
                </div>

                <div class="p-4 rounded-xl bg-gray-50 ring-1 ring-gray-950/5 hover:ring-gray-950/10 transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-warning-500 shadow-sm"></div>
                        <span class="text-sm font-semibold text-gray-700">Lembur</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 tracking-tight">Rp {{ number_format($totalOvertime, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 font-medium mt-1">{{ $pctOvertime }}% dari total</div>
                </div>

                <div class="p-4 rounded-xl bg-gray-50 ring-1 ring-gray-950/5 hover:ring-gray-950/10 transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-danger-500 shadow-sm"></div>
                        <span class="text-sm font-semibold text-gray-700">Reimburse</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 tracking-tight">Rp {{ number_format($totalReimburse, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 font-medium mt-1">{{ $pctReimburse }}% dari total</div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
