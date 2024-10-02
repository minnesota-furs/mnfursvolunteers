@props(['id'])
<div class="relative inline-block text-left">
    <div>
        <button id="menuButton" onclick="openMenu({{ $id }})" type="button"
            {!! $attributes->merge(['class' => 'inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50']) !!}
            aria-expanded="true" aria-haspopup="true">
            Options
            <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <!--
    Dropdown menu, show/hide based on menu state.

    Entering: "transition ease-out duration-100"
    From: "transform opacity-0 scale-95"
    To: "transform opacity-100 scale-100"
    Leaving: "transition ease-in duration-75"
    From: "transform opacity-100 scale-100"
    To: "transform opacity-0 scale-95"
-->
    <div id="optDropdown{{ $id }}"
    class="hidden absolute right-0 z-10 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 scale-95 transition-transform" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
        {{ $slot }}
    </div>

    <script>
        function openMenu(id) {
            console.log('Opening ' + id);
            const dropdownMenu = document.getElementById('optDropdown' + id);

            // Check if the menu is hidden
            if (dropdownMenu.classList.contains('hidden')) {
                // Remove 'hidden' immediately so the dropdown is visible, then apply the transition classes
                dropdownMenu.classList.remove('hidden');

                // Force reflow to ensure the transition starts (important for proper animation)
                dropdownMenu.offsetHeight; // This forces a reflow

                // Add entering transition classes
                dropdownMenu.classList.add('transition', 'ease-out', 'duration-100');
                dropdownMenu.classList.remove('opacity-0', 'scale-95');
                dropdownMenu.classList.add('opacity-100', 'scale-100');
            } else {
                // Add leaving transition classes before hiding
                dropdownMenu.classList.remove('opacity-100', 'scale-100');
                dropdownMenu.classList.add('transition', 'ease-in', 'duration-75', 'opacity-0', 'scale-95');

                // Wait for the transition to finish before hiding it completely
                setTimeout(() => {
                    dropdownMenu.classList.add('hidden');
                }, 75); // Match the duration-75 class
            }
        }
    </script>
