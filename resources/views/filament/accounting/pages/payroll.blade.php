<x-filament-panels::page>
    <div class="bg-white rounded-lg border border-gray-200 p-0 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Rekapitulasi Penggajian</h3>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">Periode:</span>
                    <div class="w-full sm:w-48">
                        {{ $this->form }}
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-50 transition-colors">
                    Ekspor Excel
                </button>
                <button class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <x-heroicon-o-calculator class="w-4 h-4" />
                    Generate Payroll
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-500">
                        <th class="p-4 font-semibold">Nama Pegawai</th>
                        <th class="p-4 font-semibold text-right">Gaji Pokok</th>
                        <th class="p-4 font-semibold text-right">Honor Lembur</th>
                        <th class="p-4 font-semibold text-right text-red-500">Potongan</th>
                        <th class="p-4 font-semibold text-right text-blue-600">Take Home Pay</th>
                        <th class="p-4 font-semibold text-center">Status</th>
                        <th class="p-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payrollData as $index => $pay)
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
                        <td class="p-4 text-sm font-medium text-red-600 text-right">
                            -{{ number_format($pay['deductions'], 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-sm font-bold text-gray-900 text-right">
                            {{ number_format($pay['thp'], 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($pay['status'] === 'Published')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    <x-heroicon-o-check-circle class="w-3 h-3 mr-1" />
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($pay['status'] === 'Draft')
                                <button wire:click="openEditModal({{ $index }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit Rincian</button>
                            @else
                                <button class="text-gray-400 text-sm font-medium cursor-not-allowed" disabled>Terkunci</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button class="px-6 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition-colors">
                Publish
            </button>
        </div>
    </div>

    {{-- Modal Edit Payroll --}}
    @if($isEditing)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl w-full max-w-3xl flex flex-col max-h-[90vh] animate-fade-in-up">
            
            <div class="p-6 border-b border-gray-200 bg-gray-50/50 flex justify-between items-start shrink-0 rounded-t-xl">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Edit Rincian Penggajian</h3>
                    <p class="text-sm text-gray-500 mt-1">Penyesuaian manual untuk variabel lembur dan potongan.</p>
                </div>
                <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-5">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Honor Lembur (Rp)</label>
                            <input type="number" wire:model.live="editOvertime" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-right font-medium text-gray-900" />
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

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">PPh 21</label>
                                <input type="number" wire:model.live="editDedPph21" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterlambatan</label>
                                <input type="number" wire:model.live="editDedLate" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Pinjaman / Kasbon</label>
                            <input type="number" wire:model.live="editDedLoan" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-red-500 outline-none text-sm text-right font-medium text-red-700" />
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

            <div class="p-4 border-t border-gray-200 flex justify-between items-center bg-gray-50 shrink-0 rounded-b-xl">
                <button wire:click="closeEditModal" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-100 transition-colors">
                    Batal
                </button>
                <div class="flex gap-3">
                    <button wire:click="savePayroll" class="px-5 py-2 bg-white border border-blue-600 text-blue-600 text-sm font-semibold rounded-md hover:bg-blue-50 transition-colors flex items-center gap-2">
                        <span wire:loading.remove wire:target="savePayroll">Simpan sebagai Draft</span>
                        <span wire:loading wire:target="savePayroll">Menyimpan...</span>
                    </button>
                    <button wire:click="publishPayroll" class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition-colors flex items-center gap-2">
                        <span wire:loading.remove wire:target="publishPayroll">
                            Simpan & Publish
                        </span>
                        <span wire:loading wire:target="publishPayroll">Mempublish...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
