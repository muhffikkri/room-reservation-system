@if ($paginator->hasPages())
    @php($buttonClass = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-full px-4 text-xs font-bold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600')
    <nav class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Navigasi halaman">
        <p class="text-xs text-slate-500">Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</p>
        <div class="flex flex-wrap items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="{{ $buttonClass }} bg-white/40 text-slate-400" aria-disabled="true">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $buttonClass }} clay-button-white text-slate-600">Sebelumnya</a>
            @endif
            <div class="hidden items-center gap-2 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-1 text-slate-500">…</span>
                    @else
                        @foreach ($element as $page => $url)
                            @if ($page === $paginator->currentPage())
                                <span aria-current="page" aria-label="Halaman {{ $page }}" class="{{ $buttonClass }} landing-button bg-gradient-to-r from-blue-600 to-blue-500 text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="{{ $buttonClass }} clay-button-white text-slate-600">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
            <span class="text-xs font-semibold text-slate-600 sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $buttonClass }} clay-button-white text-slate-600">Berikutnya</a>
            @else
                <span class="{{ $buttonClass }} bg-white/40 text-slate-400" aria-disabled="true">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
