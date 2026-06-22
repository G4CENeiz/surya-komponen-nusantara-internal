<x-filament-panels::page>

    {{-- Submission type cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

        {{-- Leave application card --}}
        <div class="group relative bg-white border border-gray-200 rounded-lg p-8 flex flex-col items-center text-center transition-colors duration-200 hover:border-blue-500 overflow-hidden">
            {{-- Icon box --}}
            <div class="w-16 h-16 bg-blue-50 text-blue-600 flex items-center justify-center mb-6 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200">
                <x-heroicon-o-calendar-days class="w-8 h-8" />
            </div>

            {{-- Card title --}}
            <h3 class="text-xl font-bold text-gray-900 mb-8 tracking-tight">Pengajuan Cuti</h3>

            {{-- Action button --}}
            <button wire:click="mountAction('createLeave')" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3 px-5 rounded-md transition-colors duration-200">
                <span>Buat Pengajuan</span>
                <x-heroicon-o-arrow-right class="w-4 h-4" />
            </button>
        </div>

        {{-- Overtime application card --}}
        <div class="group relative bg-white border border-gray-200 rounded-lg p-8 flex flex-col items-center text-center transition-colors duration-200 hover:border-blue-500 overflow-hidden">
            {{-- Icon box --}}
            <div class="w-16 h-16 bg-blue-50 text-blue-600 flex items-center justify-center mb-6 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200">
                <x-heroicon-o-clock class="w-8 h-8" />
            </div>

            {{-- Card title --}}
            <h3 class="text-xl font-bold text-gray-900 mb-8 tracking-tight">Pengajuan Lembur</h3>

            {{-- Action button --}}
            <button wire:click="mountAction('createOvertime')" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3 px-5 rounded-md transition-colors duration-200">
                <span>Buat Pengajuan</span>
                <x-heroicon-o-arrow-right class="w-4 h-4" />
            </button>
        </div>

        {{-- Sick leave card --}}
        <div class="group relative bg-white border border-gray-200 rounded-lg p-8 flex flex-col items-center text-center transition-colors duration-200 hover:border-blue-500 overflow-hidden">
            {{-- Icon box --}}
            <div class="w-16 h-16 bg-blue-50 text-blue-600 flex items-center justify-center mb-6 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200">
                <x-heroicon-o-face-frown class="w-8 h-8" />
            </div>

            {{-- Card title --}}
            <h3 class="text-xl font-bold text-gray-900 mb-8 tracking-tight">Pengajuan Sakit</h3>

            {{-- Action button --}}
            <button wire:click="mountAction('createSickLeave')" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3 px-5 rounded-md transition-colors duration-200">
                <span>Buat Pengajuan</span>
                <x-heroicon-o-arrow-right class="w-4 h-4" />
            </button>
        </div>

    </div>

    {{-- Daftar Pengajuan --}}
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {{-- Table Header & Filters --}}
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-gray-900">Riwayat Pengajuan Anda</h2>
            
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Search --}}
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                    </div>
                    <input wire:model.live="searchQuery" type="text" placeholder="Cari ID atau Keterangan..." class="pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-lg text-sm font-semibold text-gray-900 focus:ring-0 focus:outline-none block w-full">
                </div>

                {{-- Filters --}}
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    {{ $this->form }}
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-white">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID Pengajuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->pengajuanList as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d F Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ str_starts_with($item['type'], 'Cuti') ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $item['type'] === 'Lembur' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $item['type'] === 'Sakit' ? 'bg-orange-100 text-orange-800' : '' }}">
                                    {{ $item['type'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $item['desc'] }}
                                @if($item['attachment'])
                                    <span class="ml-2 inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                        <x-heroicon-s-paper-clip class="w-3 h-3" /> Lampiran
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border
                                    {{ $item['status'] === 'approved' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                    {{ $item['status'] === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                    {{ $item['status'] === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                    {{ $item['status'] === 'approved' ? 'Disetujui' : ($item['status'] === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Tidak ada data pengajuan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Render Action Modals --}}
    <x-filament-actions::modals />

</x-filament-panels::page>
