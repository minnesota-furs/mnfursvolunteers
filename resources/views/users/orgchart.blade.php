<x-app-layout>
        <x-slot name="header">
            {{ __('All Users') }}
        </x-slot>

        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
        google.charts.load('current', { packages: ['orgchart'] });
        google.charts.setOnLoadCallback(drawChart);

        var orgChartData = @json($sectors); // Pass sectors, departments, and users from controller

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Parent');

            // Populate data dynamically from orgChartData
            orgChartData.forEach(sector => {
                // Add sector
                data.addRows([[{ v: `Sector${sector.id}`, f: sector.name }, '']]);

                // Add departments under sector
                sector.departments.forEach(department => {
                    data.addRows([[{ v: `Department${department.id}`, f: department.name }, `Sector${sector.id}`]]);

                    // Add users under department
                    department.users.forEach(user => {
                        data.addRows([[user.name, `Department${department.id}`]]);
                    });
                });
            });

            var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
            chart.draw(data, { allowHtml: true });
        }
        </script>


        <div class="py-12">
            <div id="chart_div"></div>
        </div>


        <script>
            var orgChartData = @json($sectors);
        </script>
</x-app-layout>