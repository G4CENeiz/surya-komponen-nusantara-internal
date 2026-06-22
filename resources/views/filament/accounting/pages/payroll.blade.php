<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            @if($isViewingHistory)
                Riwayat Penggajian: Bulan {{ str_pad($historyMonth, 2, '0', STR_PAD_LEFT) }} - {{ $historyYear }}
            @else
                Rekapitulasi Penggajian
            @endif
        </x-slot>
        <x-slot name="description">
            @if($isViewingHistory)
                Melihat arsip data. Data ini sudah final dan tidak dapat diubah.
            @else
                Kelola pencairan dan rekapan gaji bulan berjalan.
            @endif
        </x-slot>

        {{-- Filters & Search --}}
        <div class="flex flex-col sm:flex-row gap-4 sm:items-center mb-4">
            {{-- Search --}}
            <div class="w-full sm:w-72">
                <div class="fi-input-wrapper flex rounded-lg ring-1 transition duration-75 bg-white focus-within:ring-2 ring-gray-950/10 focus-within:ring-primary-600 w-full h-[38px] shadow-sm">
                    <x-heroicon-m-magnifying-glass class="w-5 h-5 text-gray-400 ml-3 my-auto" />
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari NIK / Nama..." class="fi-input block w-full border-none py-1.5 pl-2 pr-3 text-sm text-gray-950 transition duration-75 focus:ring-0 bg-transparent h-full" />
                </div>
            </div>

            {{-- Filters --}}
            <div class="w-full sm:flex-1">
                {{ $this->form }}
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-left divide-y table-auto fi-ta-table">
                <thead class="bg-gray-50 text-sm font-medium text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Nama Pegawai</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs text-right">Honor Lembur</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs text-right">Reimburse</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs text-right">Total Potongan</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Take Home Pay</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Status</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-900">
                    @forelse($this->paginatedPayrollData as $index => $pay)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="p-4">
                            <div class="font-semibold text-gray-900">{{ $pay['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $pay['nik'] }}</div>
                        </td>
                        <td class="p-4 text-sm font-medium text-gray-700 text-right">
                            {{ number_format($pay['basic'], 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-sm font-medium text-gray-700 text-right">
                            +{{ number_format($pay['overtime'], 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-sm font-medium text-green-600 text-right">
                            +{{ number_format($pay['reimburse'], 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-sm font-medium text-red-600 text-right">
                            -{{ number_format($pay['deductions'], 0, ',', '.') }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($pay['thp'], 0, ',', '.') }}</div>
                        </td>
                        <td class="p-4">
                            @if(strtolower($pay['status']) === 'published')
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">Published</span>
                            @elseif(strtolower($pay['status']) === 'paid')
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">Paid</span>
                            @elseif(strtolower($pay['status']) === 'verified')
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">Verified</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isViewingHistory)
                                <button wire:click="openEmployeeHistoryDetail({{ $pay['user_id'] }})" class="text-primary-600 hover:text-primary-800 text-sm font-medium transition-colors border border-primary-200 px-3 py-1.5 rounded-md bg-primary-50 hover:bg-primary-100 inline-flex items-center gap-1">
                                    <x-heroicon-o-eye class="w-4 h-4" /> Detail
                                </button>
                            @else
                                @if(strtolower($pay['status']) !== 'published')
                                <button wire:click="openEditModal({{ $index }})" class="text-primary-600 hover:text-primary-800 text-sm font-medium transition-colors">
                                    Edit Rincian
                                </button>
                                @else
                                <span class="text-sm text-gray-400 italic">Terkunci</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">Tidak ada data ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($this->totalPages > 1)
        <div class="mt-4 flex justify-between items-center text-sm">
            <span class="text-gray-600">
                Menampilkan halaman <strong>{{ $currentPage }}</strong> dari <strong>{{ $this->totalPages }}</strong> 
                (Total {{ count($this->filteredPayrollData) }} data)
            </span>
            <div class="flex gap-2">
                <x-filament::button color="gray" wire:click="previousPage" :disabled="$currentPage <= 1" size="sm">
                    Prev
                </x-filament::button>
                <x-filament::button color="gray" wire:click="nextPage" :disabled="$currentPage >= $this->totalPages" size="sm">
                    Next
                </x-filament::button>
            </div>
        </div>
        @endif
        
        @if(!$isViewingHistory)
        <div class="mt-6 flex justify-end">
            <x-filament::button color="success" wire:click="publishAll" wire:confirm="Anda yakin ingin mempublish semua status Draft pada bulan ini? Data yang terpublish tidak bisa diubah lagi." icon="heroicon-o-paper-airplane">
                Publish Semua Draft Bulan Ini
            </x-filament::button>
        </div>
        @endif
    </x-filament::section>

    {{-- History Penggajian Bulan Sebelumnya --}}
    @if(count($payrollHistory) > 0)
    <div class="mt-8 bg-white rounded-lg border border-gray-200 p-0 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Riwayat Penggajian</h3>
            <p class="text-sm text-gray-500">Arsip rekapitulasi penggajian yang sudah lewat.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-500">
                        <th class="p-4 font-semibold">Periode (Bulan - Tahun)</th>
                        <th class="p-4 font-semibold text-center">Jumlah Karyawan</th>
                        <th class="p-4 font-semibold text-right">Total Take Home Pay</th>
                        <th class="p-4 font-semibold text-center">Status Keseluruhan</th>
                        <th class="p-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payrollHistory as $hist)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4 font-medium text-gray-900">
                            {{ str_pad($hist['month'], 2, '0', STR_PAD_LEFT) }} - {{ $hist['year'] }}
                        </td>
                        <td class="p-4 text-center text-gray-700">
                            {{ $hist['count'] }} Orang
                        </td>
                        <td class="p-4 text-right font-medium text-gray-700">
                            Rp {{ number_format($hist['total_thp'], 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($hist['status'] === 'Paid')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    {{ $hist['status'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $hist['status'] }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button wire:click="exportHistoryRow({{ $hist['month'] }}, {{ $hist['year'] }})" class="text-gray-600 hover:text-green-600 transition-colors" title="Ekspor Excel">
                                    <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                </button>
                                <button wire:click="generateHistoryRow({{ $hist['month'] }}, {{ $hist['year'] }})" class="text-gray-600 hover:text-blue-600 transition-colors" title="Generate Payroll">
                                    <x-heroicon-o-document-text class="w-5 h-5" />
                                </button>
                                <button wire:click="viewHistoryDetail({{ $hist['month'] }}, {{ $hist['year'] }})" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 transition-colors ml-2 border border-blue-200 px-2 py-1 rounded-md bg-blue-50 hover:bg-blue-100">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    Detail
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Modal Edit Payroll --}}
    @if($isEditing)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl w-full max-w-4xl flex flex-col max-h-[90vh] animate-fade-in-up">
            
            <div class="p-6 border-b border-gray-200 bg-gray-50/50 flex justify-between items-start shrink-0 rounded-t-xl">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Edit Rincian Penggajian</h3>
                    <p class="text-sm text-gray-500 mt-1">Penyesuaian manual untuk variabel lembur dan potongan.</p>
                </div>
                <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto overflow-x-hidden space-y-5 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pegawai</label>
                    <input type="text" value="{{ $editName }}" disabled class="w-full border border-gray-200 bg-gray-100 rounded-md px-4 py-2 text-gray-600 cursor-not-allowed font-medium" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Kolom Kiri: Pemasukan -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-gray-900 border-b pb-2 flex items-center gap-2">
                            <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-green-600"/> Pemasukan
                        </h4>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Gaji Pokok</label>
                            <input type="text" value="{{ number_format($editBasic, 0, ',', '.') }}" disabled class="w-full border border-gray-200 bg-gray-100 rounded-md px-3 py-2 text-gray-600 cursor-not-allowed text-right font-medium" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tunjangan Jabatan</label>
                            <input type="number" wire:model.live="editAllowance" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-right font-medium text-gray-900" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Lembur (Jam)</label>
                                <input type="number" wire:model.live="editOvertimeHours" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-right font-medium text-gray-900" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Total (Rp)</label>
                                <input type="number" wire:model.live="editOvertime" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-right font-medium text-gray-900" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Reimbursement (Tugas)</label>
                            <input type="number" wire:model.live="editReimburse" disabled class="w-full border border-gray-200 bg-gray-100 rounded-md px-3 py-2 text-green-700 font-medium cursor-not-allowed text-right" />
                            <p class="text-[10px] text-gray-500 mt-1">*Hanya reimburse yang sudah di-ACC</p>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Potongan -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-gray-900 border-b pb-2 flex items-center gap-2 text-red-600">
                            <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-600"/> Rincian Potongan
                        </h4>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">BPJS Kesehatan</label>
                                <input type="number" wire:model.live="editDedBpjsKesehatan" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">BPJS Ketenagakerjaan</label>
                                <input type="number" wire:model.live="editDedBpjsKetenagakerjaan" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">PPh 21</label>
                                <input type="number" wire:model.live="editDedPph21" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterlambatan</label>
                                <div class="flex gap-2">
                                    <input type="number" wire:model.live="editLateFrequency" class="w-14 border border-gray-300 rounded-md px-1 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-center font-medium text-gray-900" placeholder="x" title="Frekuensi Telat" />
                                    <input type="number" wire:model.live="editDedLate" class="flex-1 min-w-0 border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" placeholder="Total (Rp)" title="Total Potongan" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center border-t border-red-100 pt-3 mt-3">
                            <span class="text-sm font-bold text-gray-700">Total Potongan</span>
                            <span class="text-sm font-bold text-red-600">- Rp {{ number_format($editDeductions, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <label class="block text-lg font-bold text-blue-900 tracking-tight">Take Home Pay</label>
                        <span class="text-3xl font-black text-blue-700">Rp {{ number_format($editThp, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-between items-center rounded-b-xl">
                <button wire:click="closeEditModal" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-white transition-colors">
                    Batal
                </button>
                <div class="flex gap-3">
                    <button wire:click="saveDraft" class="px-5 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                        Simpan
                    </button>
                    <button wire:click="savePayroll" class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        Simpan & Tandai ACC
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Detail Riwayat Karyawan --}}
    @if($employeeHistoryModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-lg w-full max-w-xl flex flex-col max-h-[90vh] shadow-xl">
            <div class="p-6 border-b border-gray-100 flex justify-between items-start shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Detail Riwayat Kehadiran
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $employeeHistoryDetails['name'] ?? 'Unknown' }}</p>
                </div>
                <button wire:click="closeEmployeeHistoryDetail" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-8">
                @if(!empty($employeeHistoryDetails))
                    {{-- Keterlambatan --}}
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 mb-3 border-b border-gray-100 pb-2">
                            Riwayat Terlambat ({{ count($employeeHistoryDetails['lates']) }} kali)
                        </h4>
                        @if(count($employeeHistoryDetails['lates']) > 0)
                            <ul class="text-sm text-gray-700 space-y-2">
                                @foreach($employeeHistoryDetails['lates'] as $late)
                                    <li class="flex justify-between items-center py-1 border-b border-gray-50 last:border-0">
                                        <span>Tanggal {{ \Carbon\Carbon::parse($late['date'])->format('d M Y') }}</span>
                                        <span class="text-gray-900 font-medium">In: {{ \Carbon\Carbon::parse($late['check_in_time'])->format('H:i') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada catatan keterlambatan.</p>
                        @endif
                    </div>

                    {{-- Lembur --}}
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 mb-3 border-b border-gray-100 pb-2">
                            Riwayat Lembur ({{ count($employeeHistoryDetails['overtimes']) }} kali)
                        </h4>
                        @if(count($employeeHistoryDetails['overtimes']) > 0)
                            <ul class="text-sm text-gray-700 space-y-2">
                                @foreach($employeeHistoryDetails['overtimes'] as $ot)
                                    <li class="flex justify-between items-center py-1 border-b border-gray-50 last:border-0">
                                        <span>Tanggal {{ \Carbon\Carbon::parse($ot['date'])->format('d M Y') }}</span>
                                        <span class="text-gray-900 font-medium">{{ round($ot['duration_minutes']/60, 1) }} Jam</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada catatan lembur.</p>
                        @endif
                    </div>

                    {{-- Cuti / Izin --}}
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 mb-3 border-b border-gray-100 pb-2">
                            Cuti / Izin ({{ count($employeeHistoryDetails['timeoffs']) }} kali)
                        </h4>
                        @if(count($employeeHistoryDetails['timeoffs']) > 0)
                            <ul class="text-sm text-gray-700 space-y-2">
                                @foreach($employeeHistoryDetails['timeoffs'] as $to)
                                    <li class="flex justify-between items-center py-1 border-b border-gray-50 last:border-0">
                                        <span>{{ \Carbon\Carbon::parse($to['start_date'])->format('d M') }} - {{ \Carbon\Carbon::parse($to['end_date'])->format('d M Y') }}</span>
                                        <span class="text-gray-900 font-medium uppercase">{{ $to['type'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada catatan cuti/izin.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
