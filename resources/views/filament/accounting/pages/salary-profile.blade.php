<x-filament-panels::page>
    <div class="bg-white rounded-lg border border-gray-200 p-0 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Daftar Pegawai</h3>
                <p class="text-sm text-gray-500">Kelola dan sesuaikan gaji pokok untuk setiap karyawan.</p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="fi-input-wrapper flex rounded-lg ring-1 transition duration-75 bg-white focus-within:ring-2 ring-gray-950/10 focus-within:ring-primary-600 w-full sm:w-64">
                    <x-heroicon-m-magnifying-glass class="w-5 h-5 text-gray-400 ml-3 my-auto" />
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari NIK / Nama..." class="fi-input block w-full border-none py-1.5 pl-2 pr-3 text-sm text-gray-950 transition duration-75 focus:ring-0 bg-transparent" />
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="p-4 border-b border-gray-100 bg-white flex flex-wrap gap-4 items-end">
            {{ $this->form }}
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-500">
                        <th class="px-6 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('nik')">
                            <div class="flex items-center gap-1">NIK @if($sortColumn === 'nik') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-blue-600" /> @endif</div>
                        </th>
                        <th class="px-6 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('name')">
                            <div class="flex items-center gap-1">Nama Pegawai @if($sortColumn === 'name') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-blue-600" /> @endif</div>
                        </th>
                        <th class="px-6 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('dept')">
                            <div class="flex items-center gap-1">Departemen @if($sortColumn === 'dept') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-blue-600" /> @endif</div>
                        </th>
                        <th class="px-6 py-4 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('class')">
                            <div class="flex items-center gap-1">Kelas Jabatan @if($sortColumn === 'class') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-blue-600" /> @endif</div>
                        </th>
                        <th class="px-6 py-4 font-semibold text-right cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('salary')">
                            <div class="flex items-center justify-end gap-1">Gaji Pokok (Rp) @if($sortColumn === 'salary') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-blue-600" /> @endif</div>
                        </th>
                        <th class="px-6 py-4 font-semibold text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->filteredData as $index => $peg)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $peg['nik'] }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $peg['name'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $peg['dept'] }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $peg['class'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">
                            {{ number_format($peg['salary'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="openEditModal({{ $index }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500 text-sm">Tidak ada data pegawai yang sesuai dengan filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit --}}
    @if($isEditing)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl w-full max-w-md overflow-hidden animate-fade-in-up">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">Ubah Gaji Pokok</h3>
                <p class="text-sm text-gray-500">Sesuaikan gaji pokok berdasarkan ketentuan batas (*range*) kelas jabatan.</p>
            </div>
            
            <div class="p-6 space-y-4 bg-gray-50/30">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pegawai</label>
                    <input type="text" value="{{ $editName }}" disabled class="w-full border border-gray-200 bg-gray-100 rounded-md px-4 py-2 text-gray-600 cursor-not-allowed" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas Jabatan</label>
                    <input type="text" value="{{ $editClass }}" disabled class="w-full border border-gray-200 bg-gray-100 rounded-md px-4 py-2 text-gray-600 cursor-not-allowed" />
                    <p class="text-xs text-gray-500 mt-1">Range: Rp {{ number_format($editMinRange, 0, ',', '.') }} - Rp {{ number_format($editMaxRange, 0, ',', '.') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Gaji Pokok Baru (Rp)</label>
                    <input type="number" wire:model.defer="editSalary" class="w-full border @error('editSalary') border-red-500 @else border-gray-300 @enderror rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" />
                    @error('editSalary') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-white">
                <button wire:click="closeEditModal" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button wire:click="saveSalary" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="saveSalary">Simpan Perubahan</span>
                    <span wire:loading wire:target="saveSalary">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
