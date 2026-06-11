{{--
    SEO Breadcrumb Component
    Usage: <x-breadcrumb :items="$seo['breadcrumbs']" />
    Items: [['name' => 'Home', 'url' => '/'], ['name' => 'Courses', 'url' => '/departments']]
--}}
@props(['items' => [], 'class' => ''])

@if(count($items) > 1)
<nav aria-label="Breadcrumb" class="{{ $class }}">
    <ol class="flex flex-wrap items-center gap-1 text-xs text-blue-200" itemscope itemtype="https://schema.org/BreadcrumbList">
        @foreach($items as $i => $crumb)
            <li class="flex items-center gap-1"
                itemprop="itemListElement"
                itemscope
                itemtype="https://schema.org/ListItem">
                @if(!$loop->last)
                    <a href="{{ $crumb['url'] }}"
                       itemprop="item"
                       class="hover:text-white transition-colors">
                        <span itemprop="name">{{ $crumb['name'] }}</span>
                    </a>
                    <meta itemprop="position" content="{{ $i + 1 }}">
                    <span aria-hidden="true" class="text-blue-400">›</span>
                @else
                    <span itemprop="name" class="text-yellow-300">{{ $crumb['name'] }}</span>
                    <meta itemprop="item" content="{{ $crumb['url'] }}">
                    <meta itemprop="position" content="{{ $i + 1 }}">
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
