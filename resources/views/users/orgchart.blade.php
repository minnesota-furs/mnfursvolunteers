<x-app-layout>
        <x-slot name="header">
            Org Chart
        </x-slot>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.1.52/go.js"></script>
        <style>
            #orgChartDiv {
            width: 100%;
            height: 600px;
            border: 0px solid lightgray;
            }
        </style>

        <div class="py-0">
            <div id="orgChartDiv"></div>
        </div>


        <script>
            var nodeDataArray = @json($nodes); // Laravel passes the org chart data as JSON
            // Create a GoJS Diagram
            function init() {
              var $ = go.GraphObject.make;
          
              var diagram = $(go.Diagram, "orgChartDiv", {
                initialAutoScale: go.Diagram.Uniform, // Fits the diagram to the viewport
                contentAlignment: go.Spot.Center,     // Center the content
                layout: $(go.TreeLayout, {            // Organize the chart hierarchically
                  angle: 90,                          // Top-to-bottom layout
                  layerSpacing: 50
                }),
                "undoManager.isEnabled": true,        // Enable undo & redo
                "toolManager.mouseWheelBehavior": go.ToolManager.WheelZoom, // Enable zooming with the mouse wheel
              });
          
              // Define the link template
              diagram.linkTemplate = $(
                go.Link, go.Link.Orthogonal,
                { selectable: false }, // Links are not selectable
                $(go.Shape, { strokeWidth: 2, stroke: "#444" })
              );
          
              // Define node data (sectors, departments, users)
              var nodeDataArray = @json($nodes); // Laravel passes the org chart data as JSON
          
              // Create the model for the diagram
              diagram.model = new go.TreeModel(nodeDataArray);
          
              // Enable panning (click and drag) and zooming (scroll to zoom)
              diagram.toolManager.draggingTool.isEnabled = true;
              diagram.toolManager.panningTool.isEnabled = true;
          
              // Enable node collapse/expand functionality
              diagram.nodeTemplate =
                $(go.Node, "Vertical",
                  $(go.Panel, "Auto",
                    $(go.Shape, "RoundedRectangle", new go.Binding("fill", "fillColor")),
                    $(go.TextBlock, { margin: 8, stroke: "white" }, new go.Binding("text", "name"), new go.Binding("stroke", "textColor"))
                  ),
                  $("TreeExpanderButton")  // Add a button to collapse/expand sub-trees
                );
            }
          
            // Initialize the diagram
            init();
          </script>
</x-app-layout>