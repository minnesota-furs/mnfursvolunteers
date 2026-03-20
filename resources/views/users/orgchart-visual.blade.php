<x-app-layout>
    <x-slot name="header">
        Org Chart (Visual)
    </x-slot>

    <div class="py-4 px-4 sm:px-6 lg:px-8">
        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('orgchart') }}"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:underline mr-1">
                    &larr; List view
                </a>
                <button id="btn-expand-all"
                        class="px-3 py-1.5 rounded-md text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    Expand All
                </button>
                <button id="btn-collapse-all"
                        class="px-3 py-1.5 rounded-md text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    Collapse All
                </button>
                <button id="btn-fit-view"
                        class="px-3 py-1.5 rounded-md text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    Fit View
                </button>
            </div>
            {{-- Legend --}}
            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-brand-green inline-block"></span> Sector
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-brand-brown inline-block"></span> Department
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Member
                </span>
            </div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
            Click a sector or department node to expand/collapse. Click a member node to view their profile. Scroll to zoom, drag to pan.
        </p>

        <div id="chart-container"
             class="w-full rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
             style="height: 720px; cursor: grab;">
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
    (function () {
        const isDark      = document.documentElement.classList.contains('dark');
        const textColor   = isDark ? '#e5e7eb' : '#374151';
        const mutedColor  = isDark ? '#9ca3af' : '#6b7280';
        const linkStroke  = isDark ? '#4b5563' : '#d1d5db';
        const bgFill      = isDark ? '#111827' : '#ffffff';
        const headColor   = isDark ? '#fbbf24' : '#92400e';
        const noHeadColor = isDark ? '#6b7280' : '#9ca3af';

        const COLORS = {
            root:       '#6b7280',
            sector:     '#007848',
            department: '#44392b',
            user:       '#3b82f6',
        };
        const RADII = { root: 11, sector: 10, department: 8, user: 5 };
        const LEVEL_WIDTH = 280;
        const NODE_GAP    = 38;

        const container = document.getElementById('chart-container');
        const cw = container.clientWidth;
        const ch = container.clientHeight;

        const svg = d3.select(container)
            .append('svg')
            .attr('width', cw)
            .attr('height', ch)
            .style('background', bgFill);

        const g = svg.append('g');

        const zoomBeh = d3.zoom()
            .scaleExtent([0.03, 6])
            .on('zoom', e => g.attr('transform', e.transform));

        svg.call(zoomBeh).on('dblclick.zoom', null);

        const treeFn = d3.tree().nodeSize([NODE_GAP, LEVEL_WIDTH]);

        const root = d3.hierarchy(@json($tree));
        root.x0 = 0;
        root.y0 = 0;

        // Start with department children (users) collapsed
        root.descendants().forEach(d => {
            if (d.data.type === 'department' && d.children) {
                d._children = d.children;
                d.children   = null;
            }
        });

        let uid = 0;

        function update(source) {
            const dur = 350;

            treeFn(root);
            const nodes = root.descendants();
            const links  = root.links();

            // Horizontal layout: y = horizontal, x = vertical
            nodes.forEach(d => { d.y = d.depth * LEVEL_WIDTH; });

            // ── LINKS ─────────────────────────────────────────────────────────
            const diag = d3.linkHorizontal().x(d => d.y).y(d => d.x);

            const link = g.selectAll('path.link').data(links, d => d.target._vid);

            link.enter()
                .insert('path', 'g')
                .attr('class', 'link')
                .attr('fill', 'none')
                .attr('stroke', linkStroke)
                .attr('stroke-width', 1.5)
                .attr('d', () => {
                    const o = { x: source.x0, y: source.y0 };
                    return diag({ source: o, target: o });
                })
                .merge(link)
                .transition().duration(dur)
                .attr('d', diag);

            link.exit()
                .transition().duration(dur)
                .attr('d', () => {
                    const o = { x: source.x, y: source.y };
                    return diag({ source: o, target: o });
                })
                .remove();

            // ── NODES ─────────────────────────────────────────────────────────
            const node = g.selectAll('g.node')
                .data(nodes, d => d._vid || (d._vid = ++uid));

            const enter = node.enter()
                .append('g')
                .attr('class', 'node')
                .attr('transform', () => `translate(${source.y0},${source.x0})`)
                .style('cursor', 'pointer')
                .on('click', (evt, d) => {
                    evt.stopPropagation();
                    if (d.data.type === 'user') {
                        if (d.data.url) window.location.href = d.data.url;
                        return;
                    }
                    if (d.children) {
                        d._children = d.children;
                        d.children   = null;
                    } else {
                        d.children   = d._children;
                        d._children  = null;
                    }
                    update(d);
                });

            // Circle
            enter.append('circle')
                .attr('r', 0)
                .attr('stroke', bgFill)
                .attr('stroke-width', 2.5);

            // Name label
            const nameText = enter.append('text')
                .attr('class', 'lbl-name')
                .attr('dy', d => d.data.type === 'department' ? '-0.15em' : '0.35em')
                .attr('x', d => (RADII[d.data.type] ?? 5) + 9)
                .attr('text-anchor', 'start')
                .attr('font-size', d => {
                    if (d.data.type === 'sector') return '13px';
                    if (d.data.type === 'department') return '12px';
                    return '11px';
                })
                .attr('font-weight', d => d.data.type === 'user' ? '400' : '600')
                .attr('fill', textColor)
                .text(d => d.data.name);

            // White/dark outline behind text for readability over link lines
            nameText.clone(true).lower()
                .attr('fill', 'none')
                .attr('stroke', bgFill)
                .attr('stroke-width', 4)
                .attr('stroke-linejoin', 'round');

            // Department head subtitle
            enter.filter(d => d.data.type === 'department')
                .append('text')
                .attr('class', 'lbl-head')
                .attr('dy', '1.35em')
                .attr('x', d => (RADII[d.data.type] ?? 5) + 9)
                .attr('text-anchor', 'start')
                .attr('font-size', '10px')
                .attr('fill', d => d.data.head ? headColor : noHeadColor)
                .text(d => d.data.head ? ('★ ' + d.data.head) : '☆ No Dept Head');

            // Merge enter + update
            const merged = enter.merge(node);

            merged.transition().duration(dur)
                .attr('transform', d => `translate(${d.y},${d.x})`);

            merged.select('circle')
                .transition().duration(dur)
                .attr('r', d => RADII[d.data.type] ?? 5)
                .attr('fill', d => {
                    const base = d3.color(COLORS[d.data.type]);
                    return d._children ? base.darker(0.5).toString() : base.toString();
                });

            // Exit
            const ex = node.exit()
                .transition().duration(dur)
                .attr('transform', () => `translate(${source.y},${source.x})`)
                .remove();
            ex.select('circle').attr('r', 0);
            ex.select('text').attr('fill-opacity', 0);

            // Store positions for next transition
            nodes.forEach(d => { d.x0 = d.x; d.y0 = d.y; });
        }

        function fitView(animated) {
            const b = g.node().getBBox();
            if (!b.width || !b.height) return;
            const pad   = 60;
            const scale = Math.min(0.9, Math.min((cw - pad) / b.width, (ch - pad) / b.height));
            const tx    = cw / 2 - (b.x + b.width  / 2) * scale;
            const ty    = ch / 2 - (b.y + b.height / 2) * scale;
            const t     = d3.zoomIdentity.translate(tx, ty).scale(scale);
            if (animated) {
                svg.transition().duration(500).call(zoomBeh.transform, t);
            } else {
                svg.call(zoomBeh.transform, t);
            }
        }

        update(root);
        requestAnimationFrame(() => fitView(false));

        document.getElementById('btn-expand-all').addEventListener('click', () => {
            root.descendants().forEach(d => {
                if (d._children) { d.children = d._children; d._children = null; }
            });
            update(root);
            requestAnimationFrame(() => fitView(true));
        });

        document.getElementById('btn-collapse-all').addEventListener('click', () => {
            root.descendants().slice(1).forEach(d => {
                if (d.children) { d._children = d.children; d.children = null; }
            });
            update(root);
            requestAnimationFrame(() => fitView(true));
        });

        document.getElementById('btn-fit-view').addEventListener('click', () => fitView(true));
    })();
    </script>
</x-app-layout>
