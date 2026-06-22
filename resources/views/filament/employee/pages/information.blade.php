<x-filament-panels::page>

    <div class="space-y-6">
        
        {{-- Bagian Penugasan --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-primary-600">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                    <span>Penugasan</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($penugasanList as $tugas)
                    @php
                        $isPast = \Carbon\Carbon::parse($tugas['end_date'])->endOfDay()->isPast();
                    @endphp
                    
                    <div class="flex flex-col p-5 bg-white ring-1 ring-gray-950/5 rounded-xl shadow-sm hover:ring-primary-500 transition-all {{ $isPast ? 'opacity-60 hover:opacity-100' : '' }}">
                        <h3 class="text-base font-bold text-gray-900 truncate" title="{{ $tugas['title'] }}">
                            {{ $tugas['title'] }}
                        </h3>
                        
                        <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                            <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                            <span class="truncate">{{ $tugas['location'] }}</span>
                        </div>

                        <div class="text-sm text-gray-600 line-clamp-3 my-4 flex-grow">
                            {{ $tugas['desc'] }}
                        </div>
                        
                        <div class="flex justify-between items-center text-xs text-gray-500 pt-3 border-t border-gray-100 mt-auto">
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                <span>{{ \Carbon\Carbon::parse($tugas['start_date'])->format('d M y') }}</span>
                            </div>
                            <x-heroicon-o-arrow-right class="w-3 h-3 text-gray-300" />
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                <span>{{ \Carbon\Carbon::parse($tugas['end_date'])->format('d M y') }}</span>
                            </div>
                        </div>

                        @if($tugas['status'] === 'Selesai')
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                @if(is_null($tugas['reimburse_status']))
                                    <div class="flex flex-col gap-2">
                                        <span class="text-xs text-gray-500">Ada pengeluaran tugas?</span>
                                        {{ ($this->requestReimburseAction)(['id' => $tugas['id']]) }}
                                    </div>
                                @elseif($tugas['reimburse_status'] === 'pending')
                                    <x-filament::badge color="warning" class="w-full justify-center" icon="heroicon-m-clock">
                                        Menunggu ACC
                                    </x-filament::badge>
                                @elseif($tugas['reimburse_status'] === 'approved')
                                    <x-filament::badge color="success" class="w-full justify-center" icon="heroicon-m-check-circle">
                                        Disetujui
                                    </x-filament::badge>
                                @elseif($tugas['reimburse_status'] === 'rejected')
                                    <div class="w-full flex flex-col gap-2">
                                        <x-filament::badge color="danger" class="w-full justify-center" icon="heroicon-m-x-circle">
                                            Ditolak
                                        </x-filament::badge>
                                        @if($tugas['reimburse_reason'])
                                            <p class="text-[10px] text-danger-600 bg-danger-50 p-2 rounded line-clamp-2">
                                                {{ $tugas['reimburse_reason'] }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="w-full text-center py-8 text-gray-500 md:col-span-2 xl:col-span-3">
                        <x-heroicon-o-check-circle class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                        <p class="text-sm">Tidak ada tugas yang tertunda.</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Bagian Pengumuman HRD --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-primary-600">
                    <x-heroicon-o-megaphone class="w-6 h-6" />
                    <span>Pengumuman</span>
                </div>
            </x-slot>
            
            <div class="flex flex-col gap-4">
                @forelse($pengumumanList as $pengumuman)
                    <div class="p-5 bg-white ring-1 ring-gray-950/5 rounded-xl shadow-sm hover:ring-primary-500 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">{{ $pengumuman['title'] }}</h3>
                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                    {{ \Carbon\Carbon::parse($pengumuman['date'])->translatedFormat('d F Y') }}
                                </div>
                            </div>
                            <x-filament::badge color="primary">
                                Info HRD
                            </x-filament::badge>
                        </div>
                        
                        <div class="text-sm text-gray-600 mt-4 line-clamp-2">
                            {{ trim(strip_tags($pengumuman['content'])) }}
                        </div>
                        
                        <button wire:click="mountAction('viewPengumuman', { id: {{ $pengumuman['id'] }} })" class="text-primary-600 hover:text-primary-800 text-sm font-bold mt-4 inline-flex items-center gap-1 transition-colors">
                            Baca Selengkapnya
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 ml-1" />
                        </button>
                    </div>
                @empty
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center flex flex-col items-center justify-center">
                        <x-heroicon-o-bell-slash class="w-12 h-12 text-gray-300 mb-3" />
                        <p class="text-sm text-gray-500 font-medium">Belum ada pengumuman baru dari HRD saat ini.</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

    </div>

    {{-- Render Action Modals --}}
    <x-filament-actions::modals />

</x-filament-panels::page>
