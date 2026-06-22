<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Daftar Pegawai</x-slot>
        <x-slot name="description">Kelola dan sesuaikan gaji pokok untuk setiap karyawan.</x-slot>

        {{-- Filters & Search --}}
        <div class="flex flex-col sm:flex-row gap-4 items-center mb-4">
            {{-- Search --}}
            <div class="w-full sm:w-72">
                <div class="fi-input-wrapper flex rounded-lg ring-1 transition duration-75 bg-white focus-within:ring-2 ring-gray-950/10 focus-within:ring-primary-600 w-full h-[38px] shadow-sm">
                    <x-heroicon-m-magnifying-glass class="w-5 h-5 text-gray-400 ml-3 my-auto" />
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari NIK / Nama..." class="fi-input block w-full border-none py-1.5 pl-2 pr-3 text-sm text-gray-950 transition duration-75 focus:ring-0 bg-transparent h-full" />
                </div>
            </div>

            {{-- Filters --}}
            <div class="w-full sm:w-[450px]">
                {{ $this->form }}
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-left divide-y table-auto fi-ta-table">
                <thead class="bg-gray-50 text-sm font-medium text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('nik')">
                            <div class="flex items-center gap-1">NIK @if($sortColumn === 'nik') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-primary-600" /> @endif</div>
                        </th>
                        <th class="w-full px-4 py-3 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('name')">
                            <div class="flex items-center gap-1">Nama Pegawai @if($sortColumn === 'name') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-primary-600" /> @endif</div>
                        </th>
                        <th class="px-4 py-3 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('dept')">
                            <div class="flex items-center gap-1">Departemen @if($sortColumn === 'dept') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-primary-600" /> @endif</div>
                        </th>
                        <th class="px-4 py-3 font-semibold cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('class')">
                            <div class="flex items-center gap-1">Kelas Jabatan @if($sortColumn === 'class') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-primary-600" /> @endif</div>
                        </th>
                        <th class="px-4 py-3 font-semibold text-right cursor-pointer hover:bg-gray-100 transition" wire:click="sortBy('salary')">
                            <div class="flex items-center justify-end gap-1">Gaji Pokok (Rp) @if($sortColumn === 'salary') <x-heroicon-m-arrows-up-down class="w-3 h-3 text-primary-600" /> @endif</div>
                        </th>
                        <th class="px-4 py-3 font-semibold text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-900">
                    @forelse($this->paginatedData as $index => $emp)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $emp['nik'] }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 truncate max-w-[200px]">{{ $emp['name'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $emp['dept'] }}</td>
                        <td class="px-4 py-3">
                            <x-filament::badge color="info">
                                {{ $emp['class'] }}
                            </x-filament::badge>
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-900 text-right">
                            {{ number_format($emp['salary'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="openEditModal({{ $index }})" class="text-primary-600 hover:text-primary-800 font-medium transition-colors">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">Tidak ada data pegawai yang sesuai dengan filter.</td>
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
                (Total {{ count($this->filteredData) }} data)
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
    </x-filament::section>

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
