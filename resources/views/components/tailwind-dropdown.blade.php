@props(['id', 'buttonClass' => 'inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-sm hover:bg-gray-50', 'label' => 'Options'])
<div class="relative inline-block text-left">
    <div>
        <button id="menuButton" onclick="openMenu({{ $id }})" type="button"
            {!! $attributes->merge(['class' => $buttonClass]) !!}
            aria-expanded="true" aria-haspopup="true">
            {{$label}}
            <x-heroicon-m-chevron-down class="-mr-1 h-5 w-5"/>
        </button>
    </div>

    <div id="optDropdown{{ $id }}"
        class="hidden absolute right-0 z-50 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 scale-95 transition-transform" 
        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
        {{ $slot }}
    </div>
</div>

<script>
    // Store reference to open dropdown
    var currentOpenDropdown = null;

    function openMenu(id) {
        const dropdownMenu = document.getElementById('optDropdown' + id);

        // Close any currently open dropdowns
        if (currentOpenDropdown && currentOpenDropdown !== dropdownMenu) {
            closeDropdown(currentOpenDropdown);
        }

        if (dropdownMenu.classList.contains('hidden')) {
            dropdownMenu.classList.remove('hidden');
            dropdownMenu.offsetHeight; // Force reflow
            dropdownMenu.classList.add('transition', 'ease-out', 'duration-100');
            dropdownMenu.classList.remove('opacity-0', 'scale-95');
            dropdownMenu.classList.add('opacity-100', 'scale-100');
            currentOpenDropdown = dropdownMenu;

            // Attach click listener if not already set
            if (!window.__dropdownOutsideClickListener) {
                window.__dropdownOutsideClickListener = (event) => {
                    if (currentOpenDropdown && !currentOpenDropdown.contains(event.target) &&
                        !event.target.closest(`#menuButton`)) {
                        closeDropdown(currentOpenDropdown);
                        currentOpenDropdown = null;
                    }
                };
                document.addEventListener('click', window.__dropdownOutsideClickListener);
            }

        } else {
            closeDropdown(dropdownMenu);
            currentOpenDropdown = null;
        }
    }

    function closeDropdown(dropdown) {
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('transition', 'ease-in', 'duration-75', 'opacity-0', 'scale-95');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 75);
    }
</script>

