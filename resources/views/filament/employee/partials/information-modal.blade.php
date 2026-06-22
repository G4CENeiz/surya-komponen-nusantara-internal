@if(isset($pengumuman))
    <div>
        <div class="mb-6 flex items-center gap-3 border-b border-gray-100 pb-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border
                {{ $pengumuman['priority'] === 'Penting' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                {{ $pengumuman['priority'] }}
            </span>
            <span class="text-sm text-gray-500 flex items-center gap-1">
                <x-heroicon-o-calendar class="w-4 h-4" />
                {{ \Carbon\Carbon::parse($pengumuman['date'])->translatedFormat('d F Y') }}
            </span>
        </div>
        
        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
            {{ $pengumuman['content'] }}
        </div>
    </div>
@endif
