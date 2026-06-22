<x-filament-panels::page>

    <div class="space-y-12">
        
        {{-- Bagian Penugasan --}}
        <div>
            <div class="mb-8 border-b border-gray-200 pb-4">
                <h2 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-blue-600" />
                    Penugasan
                </h2>
            </div>

            <div class="flex overflow-x-auto gap-6 pb-6 snap-x snap-mandatory hide-scrollbar" style="scrollbar-width: thin;">
                @forelse($penugasanList as $tugas)
                    @php
                        $isPast = \Carbon\Carbon::parse($tugas['end_date'])->endOfDay()->isPast();
                    @endphp
                    
                    <div class="group relative {{ $isPast ? 'bg-gray-50 opacity-60 hover:opacity-100' : 'bg-white' }} border border-gray-200 rounded-lg p-6 flex flex-col transition-all duration-200 hover:border-blue-500 overflow-hidden shrink-0 snap-start w-72 lg:w-80">
                        
                        <h3 class="text-lg font-bold text-gray-900 mb-2 tracking-tight group-hover:text-blue-600 transition-colors duration-200">{{ $tugas['title'] }}</h3>
                        
                        <div class="flex items-center gap-1 text-sm text-gray-500 mb-4">
                            <x-heroicon-o-map-pin class="w-4 h-4" />
                            <span>{{ $tugas['location'] }}</span>
                        </div>

                        <p class="text-sm text-gray-600 line-clamp-3 mb-8 flex-grow">
                            {{ $tugas['desc'] }}
                        </p>
                        
                        <div class="flex items-center justify-between text-xs font-medium pt-4 border-t border-gray-100 text-gray-500">
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-4 h-4" />
                                <span>{{ \Carbon\Carbon::parse($tugas['start_date'])->format('d M y') }}</span>
                            </div>
                            <x-heroicon-o-arrow-right class="w-3 h-3 text-gray-300" />
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-4 h-4" />
                                <span>{{ \Carbon\Carbon::parse($tugas['end_date'])->format('d M y') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="w-full bg-gray-50 border border-gray-200 rounded-lg p-12 text-center flex flex-col items-center justify-center">
                        <x-heroicon-o-check-circle class="w-12 h-12 text-gray-300 mb-3" />
                        <p class="text-sm text-gray-500 font-medium">Luar biasa! Tidak ada tugas yang tertunda.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Bagian Pengumuman HRD --}}
        <div>
            <div class="mb-8 border-b border-gray-200 pb-4">
                <h2 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <x-heroicon-o-megaphone class="w-6 h-6 text-blue-600" />
                    Pengumuman
                </h2>
            </div>
            
            <div class="grid grid-cols-1 gap-6">
                @forelse($pengumumanList as $pengumuman)
                    <div class="group relative bg-white border border-gray-200 rounded-lg p-6 flex flex-col transition-colors duration-200 hover:border-blue-500 overflow-hidden">
                        
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ $pengumuman['title'] }}</h3>
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                    {{ \Carbon\Carbon::parse($pengumuman['date'])->translatedFormat('d F Y') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border
                                {{ $pengumuman['priority'] === 'Penting' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                {{ $pengumuman['priority'] }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                {{ $pengumuman['content'] }}
                            </p>
                            
                            <button wire:click="mountAction('viewPengumuman', { id: {{ $pengumuman['id'] }} })" class="text-blue-600 hover:text-blue-800 text-sm font-bold mt-3 inline-flex items-center gap-1 transition-colors">
                                Baca Selengkapnya
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 ml-1" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center flex flex-col items-center justify-center">
                        <x-heroicon-o-bell-slash class="w-12 h-12 text-gray-300 mb-3" />
                        <p class="text-sm text-gray-500 font-medium">Belum ada pengumuman baru dari HRD saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Render Action Modals --}}
    <x-filament-actions::modals />

</x-filament-panels::page>
