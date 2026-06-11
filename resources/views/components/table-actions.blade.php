{{--
    x-table-actions
    Full text action buttons for table rows: View / Edit / Delete.
    Props:
      show      - Route URL for view action (optional)
      edit      - Route URL for edit action (optional)
      destroy   - Route URL for delete action (optional)
      name      - Resource name for the confirm message (default: 'this record')
--}}
@props(['show' => null, 'edit' => null, 'destroy' => null, 'name' => 'this record'])

<div class="flex items-center justify-end gap-2">
    @if($show)
        <x-btn href="{{ $show }}" variant="view" size="sm">View</x-btn>
    @endif

    @if($edit)
        <x-btn href="{{ $edit }}" variant="edit" size="sm">Edit</x-btn>
    @endif

    @if($destroy)
        <form method="POST" action="{{ $destroy }}"
              onsubmit="return confirm('Are you sure you want to delete {{ addslashes($name) }}?')">
            @csrf
            @method('DELETE')
            <x-btn type="submit" variant="danger" size="sm">Delete</x-btn>
        </form>
    @endif
</div>
