<x-guestv2-layout
    ogTitle="MNFurs Volunteers Demo"
    ogDescription="Demo environment information and evaluation notes."
    ogImage="{{URL('/images/dashboard/image1.jpg')}}"
    ogUrl="{{ url()->current() }}"
    ogType="website"
>
    <div class="relative isolate">
        <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-purple-100/70 to-transparent"></div>
        <div class="mx-auto max-w-5xl px-6 py-20 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">Demo Environment</h1>
                <p class="mt-4 text-lg text-gray-600">This playground is for evaluation purposes only.</p>
            </div>

            <div class="mt-12 rounded-2xl bg-white/90 shadow-xl ring-1 ring-gray-200">
                <div class="p-8 sm:p-10">
                    <h2 class="text-2xl font-semibold text-gray-900">What this demo represents</h2>
                    <p class="mt-3 text-gray-600">
                        The full application is open-source and free to use. This demo environment is a shared instance of the application that allows you to explore the features and functionality without needing to set up your own hosting environment. It is intended for evaluation purposes only and may have limitations compared to a self-hosted or managed hosting deployment.
                    </p>

                    <div class="mt-8 grid gap-6 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 bg-white p-6">
                            <h3 class="text-lg font-semibold text-gray-900">No limitations!</h3>
                            <p class="mt-2 text-gray-500">This is not commercial software and doesn't have hidden features that require payment.</p>
                            <ul class="mt-3 space-y-2 text-gray-600 list-disc list-inside">
                                <li>Unlimited volunteers</li>
                                <li>Unlimited admins</li>
                                <li>No feature restrictions</li>
                                <li>Free Updates & Community Support</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Demo Disclaimer</h3>
                            <ul class="mt-3 space-y-2 text-gray-600">
                                <li>The site goes to sleep after one hour of inactivity.</li>
                                <li>After sleep, the first request may be slower while services start back up.</li>
                                <li>This delay does not occur when hosting the application for real use. This just keeps costs low for me to host your demo environment.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Deployment options</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
                                <h4 class="text-sm font-semibold text-gray-900">Self-hosted</h4>
                                <p class="mt-2 text-sm text-gray-600">
                                    Your Information Technology team hosts the application on existing hardware and manages updates internally.
                                </p>
                                <ul class="mt-3 space-y-1 text-sm text-gray-600 list-disc list-inside">
                                    <li>PHP 8.2 or newer</li>
                                    <li>Database (MariaDB 10.3+, MySQL 5.7+ or PostgreSQL 10.0+)</li>
                                    <li>Local persistent disk or S3 object storage</li>
                                </ul>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-purple-50 to-fuchsia-50 p-4">
                                <h4 class="text-sm font-semibold text-gray-900">Managed hosting</h4>
                                <p class="mt-2 text-sm text-gray-600">
                                    Work with us for managed hosting, maintenance, and upkeep.
                                </p>
                                <p class="mt-3 text-sm text-gray-600">
                                    Costs are approximately $14 per month and can easily scale as your usage grows and expands.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 rounded-xl bg-purple-50 p-6">
                        <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-900">
                                    Thanks for evaluating this demo. If you plan to deploy, you can host it on your preferred
                                    platform with full performance and no demo limitations.
                                </p>
                                <p class="text-sm pt-3 text-purple-900">
                                    Not sure how to do this or where to start? Let us help you! We can walk you through it, or host it for you.
                                </p>
                            </div>
                            <a href="https://github.com/drraccoony/mnfursvolunteers" target="_blank" rel="noopener"
                                class="inline-flex items-center text-center justify-center rounded-md bg-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-700">
                                View the GitHub repository
                            </a>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-600">
                        Thanks for supporting open source software and this non-commercial software.
                    </p>
                    <p class="mt-1 text-sm text-gray-600 text-right">
                        -Shawn McHenry (Aka Rico)
                    </p>
                </div>
            </div>
            <p class="mt-16 text-center text-sm font-medium text-gray-600">
                Happily Developed with <span class="text-red-500">&#10084;&#65039;</span> in Minneapolis, MN
            </p>
        </div>
    </div>
</x-guestv2-layout>
