

<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <caption class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-white dark:text-white dark:bg-gray-800">
            CSV Importing Users
            <p class="mt-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                You can mass import users/volunteers by uploading a CSV File. To upload a CSV file, you should follow the following format with the following columns. The first row isn't processed and is just headers for your reference.
                <a href="{{ asset('csv/import_example.csv') }}" download="import_example.csv" class="text-blue-600 hover:text-blue-800">Download Example CSV</a>
            </p>
            <p class="mt-2 mb-5 text-sm font-normal text-gray-500 dark:text-gray-400">
                If you leave the password field blank, it will default to the first name, last name and a exclamation mark (!) at the end (All lower case).
            </p>
            Example CSV:
        </caption>
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Email
                </th>
                <th scope="col" class="px-6 py-3">
                    Password
                </th>
                <th scope="col" class="px-6 py-3">
                    First Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Last Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Sector (ID)
                </th>
                <th scope="col" class="px-6 py-3">
                    Department (ID)
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Dragon Snail
                </th>
                <td class="px-6 py-4">
                    dsnail55@example.com
                </td>
                <td class="px-6 py-4">
                    
                </td>
                <td class="px-6 py-4">
                    Jason
                </td>
                <td class="px-6 py-4">
                    Bourne
                </td>
                <td class="px-6 py-4">
                    1
                </td>
                <td class="px-6 py-4">
                    5
                </td>
            </tr>
            
        </tbody>
    </table>
</div>
