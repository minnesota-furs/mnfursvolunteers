@props(['id', 'buttonClass' => 'inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-sm hover:bg-gray-50', 'label' => 'Options'])
<div class="relative inline-block text-left">
    <div>
        <button id="menuButton{{ $id }}" onclick="openMenu({{ $id }})" type="button"
            {!! $attributes->merge(['class' => $buttonClass]) !!}
            aria-expanded="true" aria-haspopup="true">
            {{$label}}
            <x-heroicon-m-chevron-down class="-mr-1 h-5 w-5"/>
        </button>
    </div>

    {{-- Panel starts hidden inside the component; JS moves it to <body> on first open --}}
    <div id="optDropdown{{ $id }}"
        class="hidden absolute z-50 w-56 divide-y divide-gray-100 dark:divide-gray-600 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 scale-95 origin-top-right"
        role="menu" aria-orientation="vertical" aria-labelledby="menuButton{{ $id }}" tabindex="-1">
        {{ $slot }}
    </div>
</div>

<script>
    if (typeof window.__currentOpenDropdown === 'undefined') {
        window.__currentOpenDropdown = null;
    }

    function openMenu(id) {
        const dropdownMenu = document.getElementById('optDropdown' + id);
        const button = document.getElementById('menuButton' + id);

        // Close any currently open dropdown
        if (window.__currentOpenDropdown && window.__currentOpenDropdown !== dropdownMenu) {
            closeDropdown(window.__currentOpenDropdown);
        }

        if (dropdownMenu.classList.contains('hidden')) {
            // Teleport to <body> so no overflow/transform ancestor can clip it
            if (dropdownMenu.parentElement !== document.body) {
                document.body.appendChild(dropdownMenu);
            }

            // Position absolutely relative to document (scroll-aware)
            const rect = button.getBoundingClientRect();
            const scrollY = window.scrollY || window.pageYOffset;
            const scrollX = window.scrollX || window.pageXOffset;

            dropdownMenu.style.position = 'absolute';
            dropdownMenu.style.top  = (rect.bottom + scrollY + 4) + 'px';
            // Align right edge of menu with right edge of button
            dropdownMenu.style.left = 'auto';
            dropdownMenu.style.right = (document.documentElement.clientWidth - rect.right - scrollX) + 'px';

            dropdownMenu.classList.remove('hidden');
            dropdownMenu.offsetHeight; // force reflow before animation
            dropdownMenu.classList.add('transition', 'ease-out', 'duration-100');
            dropdownMenu.classList.remove('opacity-0', 'scale-95');
            dropdownMenu.classList.add('opacity-100', 'scale-100');
            window.__currentOpenDropdown = dropdownMenu;

            if (!window.__dropdownOutsideClickListener) {
                window.__dropdownOutsideClickListener = (event) => {
                    if (window.__currentOpenDropdown &&
                        !window.__currentOpenDropdown.contains(event.target) &&
                        !event.target.closest('[id^="menuButton"]')) {
                        closeDropdown(window.__currentOpenDropdown);
                        window.__currentOpenDropdown = null;
                    }
                };
                document.addEventListener('click', window.__dropdownOutsideClickListener);
            }
        } else {
            closeDropdown(dropdownMenu);
            window.__currentOpenDropdown = null;
        }
    }

    function closeDropdown(dropdown) {
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('transition', 'ease-in', 'duration-75', 'opacity-0', 'scale-95');
        setTimeout(() => dropdown.classList.add('hidden'), 75);
    }
</script>


