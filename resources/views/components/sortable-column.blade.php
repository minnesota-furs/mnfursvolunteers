<a href="{{ route('users.index', ['sort' => $column, 'direction' => $nextDirection]) }}"
    class="flex items-center">
     {{ $label }}
     @if($sort === $column)
         <span class="ml-2">
             @if($direction === 'asc')
                 &uarr; <!-- Up arrow for ascending -->
             @else
                 &darr; <!-- Down arrow for descending -->
             @endif
         </span>
     @endif
 </a>
 