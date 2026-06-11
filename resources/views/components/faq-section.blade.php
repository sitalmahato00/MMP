{{--
    SEO FAQ Section Component
    Usage: <x-faq-section :faqs="$faqs" title="Frequently Asked Questions" />
    FAQs: [['question' => '...', 'answer' => '...']]
--}}
@props([
    'faqs'  => [],
    'title' => 'Frequently Asked Questions',
    'class' => '',
])

@if(!empty($faqs))
<section class="py-10 {{ $class }}" aria-labelledby="faq-heading">
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
        <h2 id="faq-heading" class="text-2xl font-bold font-serif text-[#003D82] dark:text-blue-300 mb-6">
            {{ $title }}
        </h2>
        <div class="space-y-4" itemscope itemtype="https://schema.org/FAQPage">
            @foreach($faqs as $faq)
                <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden"
                     itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"
                     x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-slate-800 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-controls="faq-answer-{{ $loop->index }}">
                        <span itemprop="name">{{ $faq['question'] }}</span>
                        <svg class="h-5 w-5 text-slate-500 transition-transform flex-shrink-0"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="faq-answer-{{ $loop->index }}"
                         x-show="open"
                         x-cloak
                         class="px-5 pb-4 text-slate-600 dark:text-slate-300 text-sm leading-relaxed border-t border-slate-100 dark:border-slate-700"
                         itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <div class="pt-4" itemprop="text">
                            {!! nl2br(e($faq['answer'])) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
