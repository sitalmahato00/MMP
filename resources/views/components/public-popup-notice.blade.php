@props(['notices' => collect()])

@php
    $popupList = collect($notices)->values();
@endphp

@if($popupList->isNotEmpty())
<div
    x-data="{
        isOpen: true,
        currentIndex: 0,
        total: {{ $popupList->count() }},
        init() {
            if (this.isOpen) {
                document.body.classList.add('overflow-hidden');
            }
            this.$watch('isOpen', (val) => {
                if (val) {
                    document.body.classList.add('overflow-hidden');
                } else {
                    document.body.classList.remove('overflow-hidden');
                }
            });
        },
        dismissCurrent() {
            if (this.currentIndex + 1 < this.total) {
                this.currentIndex++;
            } else {
                this.isOpen = false;
                document.body.classList.remove('overflow-hidden');
            }
        }
    }"
    x-show="isOpen"
    x-cloak
    @keydown.escape.window="dismissCurrent()"
    class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-6 bg-black/60 backdrop-blur-sm transition-opacity duration-200 overscroll-contain"
    role="dialog"
    aria-modal="true"
>
    {{-- Modal Box --}}
    <div
        @click.outside="dismissCurrent()"
        class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-200"
    >
        @foreach($popupList as $index => $notice)
            @php
                $publishedDate = $notice->published_at ?? $notice->created_at;
                $dateBs = $notice->popup_from_bs ?: bsDate($publishedDate, 'Y-m-d');
                $dateAd = $publishedDate ? $publishedDate->format('M d, Y') : date('M d, Y');
                
                // Find primary preview image
                $imageAttachment = $notice->attachments?->firstWhere('is_image', true);
                $imageUrl = $imageAttachment
                    ? $imageAttachment->url
                    : ($notice->attachment && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $notice->attachment) ? asset('storage/' . $notice->attachment) : null);
            @endphp

            <div x-show="currentIndex === {{ $index }}" class="flex flex-col h-full">
                {{-- Header (Navy Blue Tone matching institutional design) --}}
                <div class="bg-[#0B2E6B] text-white px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between gap-3 shadow-md relative z-10">
                    {{-- Left Date Badge --}}
                    <div class="flex items-center gap-2.5 flex-shrink-0 bg-white/10 rounded-xl px-2.5 py-1.5 border border-white/15">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-600 text-white shadow-sm flex-shrink-0">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <div class="text-[11px] leading-tight">
                            <p class="font-bold text-white tracking-tight">{{ $dateBs }}</p>
                            <p class="text-[10px] text-blue-200 font-medium">{{ $dateAd }}</p>
                        </div>
                    </div>

                    {{-- Notice Title & Counter --}}
                    <div class="flex-1 min-w-0 pr-2">
                        @if($popupList->count() > 1)
                            <div class="mb-1">
                                <span class="inline-flex items-center gap-1 bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    Notice <span x-text="currentIndex + 1"></span> of {{ $popupList->count() }}
                                </span>
                            </div>
                        @endif
                        <h3 class="text-xs sm:text-sm md:text-[15px] font-bold text-white leading-snug line-clamp-2" title="{{ $notice->title }}">
                            {{ $notice->title }}
                        </h3>
                    </div>

                    {{-- Close / Next Button --}}
                    <button
                        type="button"
                        @click="dismissCurrent()"
                        class="flex-shrink-0 p-1.5 rounded-full text-white/80 hover:text-white hover:bg-white/15 transition-colors focus:outline-none"
                        :title="currentIndex + 1 < total ? 'Close this and view next notice' : 'Close popup'"
                        aria-label="Close notice"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body (Image / Content Area) --}}
                <div class="flex-1 overflow-y-auto bg-slate-100 dark:bg-slate-950 p-3 sm:p-4 max-h-[60vh] flex flex-col items-center justify-start text-center">
                    @if($imageUrl)
                        <div class="w-full flex items-center justify-center">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $notice->title }}"
                                class="w-full h-auto object-contain rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 bg-white"
                                loading="lazy"
                            >
                        </div>
                    @else
                        {{-- Clean Institutional Announcement Letterhead Layout if no image uploaded --}}
                        <div class="w-full bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm text-left space-y-4">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <span class="inline-flex rounded-full bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-700 uppercase tracking-wider">
                                    Official Notice
                                </span>
                                <h4 class="mt-2 text-base sm:text-lg font-bold text-slate-900 dark:white">
                                    {{ $notice->title }}
                                </h4>
                            </div>
                            <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed space-y-2 prose dark:prose-invert max-w-none">
                                {!! nl2br(e((string) $notice->content)) !!}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="bg-white dark:bg-slate-900 px-4 py-3 sm:px-5 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 relative z-10">
                    {{-- Sequential Progress Indicator --}}
                    @if($popupList->count() > 1)
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                Notice <span class="font-bold text-[#0B2E6B] dark:text-blue-400" x-text="currentIndex + 1"></span> of {{ $popupList->count() }}
                            </span>
                        </div>
                    @else
                        <div></div>
                    @endif

                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            @click="dismissCurrent()"
                            class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition focus:outline-none"
                        >
                            <span x-text="(currentIndex + 1 < total) ? 'Next Notice &rarr;' : 'Close'"></span>
                        </button>
                        <a
                            href="{{ route('public.notice.show', $notice->slug) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] px-5 py-2 text-xs font-bold text-white transition shadow-sm hover:shadow focus:outline-none"
                        >
                            <span>View Details</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
