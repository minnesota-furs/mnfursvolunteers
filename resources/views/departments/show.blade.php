<x-app-layout>

    @auth

        <x-slot name="header">
            {{ __('Department:') }} {{ $department->name }}
        </x-slot>

        <x-slot name="actions">
            @if( Auth::user()->isAdmin() )
                <a href="{{route('departments.edit', $department->id)}}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Edit
                </a>
                <a href="{{route('departments.delete_confirm', $department->id)}}"
                    class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
                </a>
            @endif
        </x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    {{-- Start Left Column --}}
                    <div>
                        <div class="px-4 sm:px-0">
                            <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Department Information</h3>
                        </div>
                        <div class="mt-6 border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Department Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $department->name }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Description</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $department->description }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Parent Sector</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $department->parent_sector_name }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">Department Members</dt>
                                    <dd class="mt-2 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                      <ul role="list" class="divide-y divide-gray-100 rounded-md border border-gray-200">
                                        @foreach($department->users as $user)
                                        <li class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">
                                          <div class="flex w-0 flex-1 items-center">
                                            <x-heroicon-o-user class="w-4 inline"/>
                                            <div class="ml-4 flex min-w-0 flex-1 gap-2">
                                              <span class="truncate font-medium">{{$user->name}}</span>
                                            </div>
                                          </div>
                                          <div class="ml-4 flex-shrink-0">
                                            <a href="{{route('users.show', $user->id)}}" class="font-medium text-indigo-600 hover:text-indigo-500">View</a>
                                          </div>
                                        </li>
                                        @endforeach
                                      </ul>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endauth
</x-app-layout>
