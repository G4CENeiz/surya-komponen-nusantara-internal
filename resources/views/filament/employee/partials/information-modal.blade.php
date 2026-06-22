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
        
        <div class="prose prose-sm prose-blue max-w-none text-gray-700">
            {!! $pengumuman['content'] !!}
        </div>

        @if(!empty($pengumuman['attachment_path']))
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-500" />
                    Lampiran File
                </h4>
                <a href="{{ Storage::url($pengumuman['attachment_path']) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 hover:border-gray-300 transition-colors group">
                    <x-heroicon-o-document-arrow-down class="w-5 h-5 text-blue-600 group-hover:text-blue-700" />
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Unduh Lampiran</span>
                </a>
            </div>
        @endif
    </div>
@endif
