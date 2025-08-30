
<table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 rounded-lg overflow-hidden shadow-sm">
    <caption class="text-lg font-semibold text-left rtl:text-right text-gray-900 bg-white dark:text-white dark:bg-gray-800">
        CSV Import Instructions
        <p class="my-1 text-sm font-normal text-gray-500 dark:text-gray-400">
            You can mass import users/volunteers by uploading a CSV File. To upload a CSV file, you should follow the following format with the following columns. The first row isn't processed and is just headers for your reference.
            <a href="{{ asset('csv/import_shifts_example.csv') }}" download="import_example.csv" class="text-blue-600 hover:text-blue-800">Download Example CSV</a>
        </p>
        <p class="my-1 mb-5 text-sm font-normal text-gray-500 dark:text-gray-400">
            For double_hours, use 1 for true and 0 for false.
        </p>
        Example CSV:
    </caption>
    <thead class="text-xs text-gray-700 uppercase bg-slate-100 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-6 py-3">
                name
            </th>
            <th scope="col" class="px-6 py-3">
                start_time
            </th>
            <th scope="col" class="px-6 py-3">
                end_time
            </th>
            <th scope="col" class="px-6 py-3">
                max_volunteers
            </th>
            <th scope="col" class="px-6 py-3">
                description
            </th>
            <th scope="col" class="px-6 py-3">
                double_hours
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="bg-slate-50 dark:bg-gray-800 dark:border-gray-700">
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                Registration Desk
            </th>
            <td class="px-6 py-4">
                2025-09-10 09:00:00
            </td>
            <td class="px-6 py-4">
                2025-09-10 12:00:00
            </td>
            <td class="px-6 py-4">
                3
            </td>
            <td class="px-6 py-4">
                Help with checking in attendees and handing out badges.
            </td>
            <td class="px-6 py-4">
                0
            </td>
        </tr>
        
    </tbody>
</table>
