{{--
    x-breadcrumb
    A breadcrumb navigation component that automatically handles separators
    
    Usage:
    <x-breadcrumb>
        <x-breadcrumb-item href="/dashboard" icon="home">Dashboard</x-breadcrumb-item>
        <x-breadcrumb-item href="/students">Students</x-breadcrumb-item>
        <x-breadcrumb-item>John Doe</x-breadcrumb-item>
    </x-breadcrumb>
--}}

<nav class="mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-0">
        {{ $slot }}
    </ol>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add separators between breadcrumb items
    const breadcrumbs = document.querySelectorAll('nav[aria-label="Breadcrumb"] ol');
    breadcrumbs.forEach(function(breadcrumb) {
        const items = breadcrumb.querySelectorAll('li');
        items.forEach(function(item, index) {
            if (index > 0) {
                const separator = document.createElement('li');
                separator.className = 'flex items-center mx-2';
                separator.innerHTML = '<svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
                breadcrumb.insertBefore(separator, item);
            }
        });
    });
});
</script>