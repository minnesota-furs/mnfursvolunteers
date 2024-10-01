<x-app-layout>
    <x-slot name="header">
        {{ __('Edit User: ...') }}
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto">
            <div class="">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            {{-- Start Left Column --}}
                            <div>
                                <div class="px-4 sm:px-0">
                                    <h3 class="text-base font-semibold leading-7 text-gray-900">Volunteer / User Information</h3>
                                </div>
                                <div class="mt-6 border-t border-gray-100">
                                    <dl class="divide-y divide-gray-100">
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Full name</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                <x-text-input id="name" name="name" type="text" class="h-6 block w-64 text-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                            </dd>
                                        </div>
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Email address</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                <x-text-input id="email" name="email" type="email" class="h-6 block w-64 text-sm" :value="old('email', $user->email)" required autocomplete="email" />
                                            </dd>
                                        </div>
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                <x-select-input id="email" name="email" type="email" class="h-10 block text-sm" :value="old('active', $user->active)" required>
                                                    <option value="0">Active</option>
                                                    <option value="1">Inactive</option>
                                                </x-select-input>
                                            </dd>
                                        </div>
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Notes</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                <x-textarea-input id="email" rows="4" name="email" type="email" class="block w-full text-sm" :value="old('email', $user->email)">lol</x-textarea-input>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div>
                            {{-- Start Right Column --}}
                            <div>
                                <div class="px-4 sm:px-0">
                                    <h3 class="text-base font-semibold leading-7 text-gray-900">Role Information</h3>
                                    {{-- <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Information involving their staff involvement with the group</p> --}}
                                </div>
                                <div class="mt-6 border-t border-gray-100">
                                    <dl class="divide-y divide-gray-100">
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Primary Sector</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                <x-select-input id="sector" name="sector" type="sector" class="h-10 block text-sm" :value="old('sector', $user->sector)">
                                                @foreach($sectors as $sector)
                                                    <option value="{{$sector->id}}">{{$sector->name}}</option>
                                                @endforeach
                                                </x-select-input>
                                            </dd>
                                        </div>
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Primary Dept</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                Administrative</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Snail</dt>
                                            <dd class="mt-1 text-sm leading-6 text-orange-700 sm:col-span-2 sm:mt-0">False</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 border-t pt-6">
                        <div class="sm:flex sm:items-center">
                          <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold leading-6 text-gray-900">Hour Log</h1>
                            <p class="mt-2 text-sm text-gray-700">Transactional log of recently logged hours for this volunteer/user.</p>
                          </div>
                          <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                            <button type="button" class="block rounded-md bg-brand-green px-2 py-1 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">New Hour Log</button>
                          </div>
                        </div>
                        <div class="mt-8 flow-root">
                          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                              <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                  <tr>
                                    <th scope="col" class="whitespace-nowrap py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0 ">Transaction ID</th>
                                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Sector, Department</th>
                                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">Amount</th>
                                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">Notes</th>
                                    <th scope="col" class="relative whitespace-nowrap py-3.5 pl-3 pr-4 sm:pr-0">
                                      <span class="sr-only">Edit</span>
                                    </th>
                                  </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                  <tr>
                                    <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm text-gray-500 sm:pl-0">5021</td>
                                    <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-500">Furry Migration, Dealers Den</td>
                                    <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500">23.5 hrs</td>
                                    <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500">Yes</td>
                                    <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a href="#" class="text-blue-600 hover:text-blue-800">Edit<span class="sr-only">, AAPS0L</span></a>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
