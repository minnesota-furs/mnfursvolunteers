<x-app-layout>
    @section('title', 'Email Users with Tag: ' . $tag->name)
    <x-slot name="header">
        Users with Tag: {{ $tag->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.tags.show', $tag)}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Tag Details
        </a>
        <a href="{{ route('admin.tags.index') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-tag class="w-4 inline"/> All Tags
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Email</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($users as $user)
                                    <tr class="">
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0 text-gray-900 dark:text-gray-100">
                                            {{$user->name}}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300" href="mailto:{{ $user->name }}<{{ $user->email }}>">{{$user->email}}</a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a href="{{ route('users.show', $user->id) }}" class="text-brand-green hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 px-2">View User</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 dark:text-gray-400 text-center" colspan="3">
                                            <p class="font-semibold">No users with this tag.</p>
                                            <p class="">No users have been assigned this tag yet.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
                <div class="mt-6">
                    <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Quick Copy Email List (BCC)</h1>
                    <div class="py-2">
                    <p class="text-xs text-gray-700 dark:text-gray-300">This is all users with the "{{ $tag->name }}" tag. If you are using this to mass email users, please use the BCC field to protect their privacy.</p>
                    @if($users->count() > 20)
                    <p class="text-xs text-red-500 dark:text-red-400"><span class="font-bold">There are a lot of email recipients</span>, you may want to use a newsletter platform to avoid deliverability issues due to spam protections.</p>
                    @endif
                    </div>
                    <x-textarea-input id="email-list" rows="6" name="email-list" class="block w-full text-sm" readonly>{{ $users->pluck('email')->join(', ') }}</x-textarea-input>
                    @if($users->count() > 0)
                    <a href="mailto:?bcc={{ urlencode($bccList) }}"
                        class="mt-2 inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <x-heroicon-s-envelope class="w-4 h-4 mr-2"/>
                        Email All Users ("Mailto:" Method)
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
