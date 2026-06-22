<x-filament-panels::page>
    <div class="w-full flex flex-col gap-5 mb-8">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Total Gaji --}}
            <div class="bg-white rounded-lg border border-gray-200 p-5 flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-blue-600" />
                    <h3 class="font-bold text-gray-700 text-sm">Total Beban Gaji</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    Rp 425.500.000
                </div>
                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1 text-green-600">
                    <x-heroicon-o-arrow-trending-up class="w-3.5 h-3.5" />
                    +2.4% dari bulan lalu
                </p>
            </div>

            {{-- Total Lembur --}}
            <div class="bg-white rounded-lg border border-gray-200 p-5 flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-clock class="w-5 h-5 text-orange-500" />
                    <h3 class="font-bold text-gray-700 text-sm">Total Honor Lembur</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    Rp 32.400.000
                </div>
                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1 text-red-500">
                    <x-heroicon-o-arrow-trending-up class="w-3.5 h-3.5" />
                    +15% dari bulan lalu
                </p>
            </div>

            {{-- Total Karyawan --}}
            <div class="bg-white rounded-lg border border-gray-200 p-5 flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-users class="w-5 h-5 text-purple-600" />
                    <h3 class="font-bold text-gray-700 text-sm">Total Karyawan Digaji</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    1.200 <span class="text-sm text-gray-500 font-medium">Orang</span>
                </div>
                <p class="text-xs text-gray-500 mt-1.5">
                    Periode Juni 2026
                </p>
            </div>
        </div>

        {{-- Custom Beautiful Salary Composition Chart --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex justify-between items-end mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Komposisi Beban Gaji</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Distribusi pengeluaran periode Juni 2026</p>
                </div>
                <div class="text-right">
                    <p class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Total Beban</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5">Rp 477.900.000</p>
                </div>
            </div>

            {{-- Stacked Progress Bar --}}
            <div class="w-full h-3 rounded-full flex overflow-hidden mb-4">
                <div class="bg-blue-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: 89%;" title="Gaji Pokok: Rp 425.500.000"></div>
                <div class="bg-orange-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: 7%;" title="Honor Lembur: Rp 32.400.000"></div>
                <div class="bg-emerald-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: 3%;" title="BPJS Kesehatan: Rp 15.000.000"></div>
                <div class="bg-violet-500 h-full transition-all duration-500 hover:brightness-110 cursor-pointer" style="width: 1%;" title="BPJS Ketenagakerjaan: Rp 5.000.000"></div>
            </div>

            {{-- Legends --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 rounded-lg bg-blue-50/50 border border-blue-100 hover:border-blue-200 transition-colors">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                        <span class="text-xs font-semibold text-gray-700">Gaji Pokok</span>
                    </div>
                    <div class="text-base font-bold text-gray-900">Rp 425,5 Jt</div>
                    <div class="text-[11px] text-gray-500 font-medium mt-0.5">89% dari total</div>
                </div>

                <div class="p-3 rounded-lg bg-orange-50/50 border border-orange-100 hover:border-orange-200 transition-colors">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-500"></div>
                        <span class="text-xs font-semibold text-gray-700">Lembur</span>
                    </div>
                    <div class="text-base font-bold text-gray-900">Rp 32,4 Jt</div>
                    <div class="text-[11px] text-gray-500 font-medium mt-0.5">7% dari total</div>
                </div>

                <div class="p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 hover:border-emerald-200 transition-colors">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs font-semibold text-gray-700">BPJS Kes.</span>
                    </div>
                    <div class="text-base font-bold text-gray-900">Rp 15,0 Jt</div>
                    <div class="text-[11px] text-gray-500 font-medium mt-0.5">3% dari total</div>
                </div>

                <div class="p-3 rounded-lg bg-violet-50/50 border border-violet-100 hover:border-violet-200 transition-colors">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-violet-500"></div>
                        <span class="text-xs font-semibold text-gray-700">BPJS TK.</span>
                    </div>
                    <div class="text-base font-bold text-gray-900">Rp 5,0 Jt</div>
                    <div class="text-[11px] text-gray-500 font-medium mt-0.5">1% dari total</div>
                </div>
            </div>
        </div>

        {{-- Pending Approvals --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4">Menunggu Verifikasi Keuangan</h3>
            
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center p-3.5 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-md">
                            <x-heroicon-o-document-text class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Rekap Lembur Periode 1-15 Juni</h4>
                            <p class="text-xs text-gray-500">Dikirim oleh HRD 2 jam lalu</p>
                        </div>
                    </div>
                    <button wire:click="openOvertimeReview" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700 transition-colors">
                        Tinjau & Validasi
                    </button>
                </div>

                <div class="flex justify-between items-center p-3.5 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-md">
                            <x-heroicon-o-banknotes class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Draft Penggajian Juni 2026</h4>
                            <p class="text-xs text-gray-500">Dibuat otomatis oleh sistem</p>
                        </div>
                    </div>
                    <a href="/accounting/payrolls" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-50 transition-colors inline-block text-center">
                        Lanjutkan Draf
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Validasi Lembur --}}
    @if($isReviewingOvertime)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl w-full max-w-2xl flex flex-col max-h-[90vh] animate-fade-in-up">
            <div class="p-5 border-b border-gray-200 bg-gray-50/50 flex justify-between items-start shrink-0 rounded-t-xl">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Validasi Rekap Lembur</h2>
                    <p class="text-sm text-gray-500 mt-1">Periode 1-15 Juni 2026. Dikirim oleh HRD.</p>
                </div>
                <button wire:click="closeOvertimeReview" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="p-5 overflow-y-auto flex-1">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-blue-800 font-medium">Total Jam Lembur</span>
                        <span class="text-lg font-bold text-blue-900">128 Jam</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-blue-800 font-medium">Total Pegawai Lembur</span>
                        <span class="text-lg font-bold text-blue-900">24 Orang</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-blue-200 pt-2 mt-2">
                        <span class="text-sm text-blue-800 font-medium">Estimasi Biaya Tambahan</span>
                        <span class="text-xl font-black text-blue-900">Rp 3.200.000</span>
                    </div>
                </div>

                <h4 class="font-bold text-gray-900 mb-3 text-sm">Rincian per Departemen</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 border-b">Departemen</th>
                                <th class="px-4 py-3 border-b">Total Pegawai</th>
                                <th class="px-4 py-3 border-b">Total Jam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr><td class="px-4 py-3">Produksi</td><td class="px-4 py-3">15 Orang</td><td class="px-4 py-3">80 Jam</td></tr>
                            <tr><td class="px-4 py-3">Gudang</td><td class="px-4 py-3">5 Orang</td><td class="px-4 py-3">30 Jam</td></tr>
                            <tr><td class="px-4 py-3">IT & Sistem</td><td class="px-4 py-3">4 Orang</td><td class="px-4 py-3">18 Jam</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50 shrink-0 rounded-b-xl">
                <button wire:click="closeOvertimeReview" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-100 transition-colors">
                    Tutup
                </button>
                <button wire:click="approveOvertime" class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="approveOvertime">Validasi & Setujui</span>
                    <span wire:loading wire:target="approveOvertime">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
