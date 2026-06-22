<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Riwayat Pengajuan Anda
        </x-slot>

        <div class="flex flex-col sm:flex-row justify-between gap-4 mb-4">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                </div>
                <input wire:model.live="searchQuery" type="text" placeholder="Cari ID atau Keterangan..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-primary-500 focus:border-primary-500 block w-full">
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                {{ $this->form }}
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-left divide-y table-auto fi-ta-table">
                <thead class="bg-gray-50 text-sm font-medium text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">ID Pengajuan</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Tanggal</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Jenis</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Keterangan</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-900">
                    @forelse($this->pengajuanList as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium">{{ $item['id'] }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d F Y') }}</td>
                            <td class="px-4 py-3">
                                <x-filament::badge :color="str_starts_with($item['type'], 'Cuti') ? 'primary' : ($item['type'] === 'Lembur' ? 'warning' : 'danger')">
                                    {{ $item['type'] }}
                                </x-filament::badge>
                            </td>
                            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
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
    </x-filament::section>

</x-filament-panels::page>
