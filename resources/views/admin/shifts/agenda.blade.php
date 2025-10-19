<x-app-layout>
    @section('title', 'Agenda View - ' . $event->name)
    <x-slot name="header">
        Agenda View: {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Events
        </a>
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-clock class="w-4 inline"/> Manage Shifts
        </a>
        <a href="{{ route('admin.events.allShifts', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-list-bullet class="w-4 inline"/> List View
        </a>
    </x-slot>

    <style>
        .agenda-calendar {
            display: grid;
            grid-template-columns: 80px repeat(auto-fit, minmax(150px, 1fr));
            gap: 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        
        .dark .agenda-calendar {
            background: #1f2937;
            border-color: #374151;
        }
        
        .time-label {
            grid-column: 1;
            padding: 12px 8px;
            font-size: 0.75rem;
            color: #6b7280;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            font-weight: 500;
            background: #f9fafb;
        }
        
        .dark .time-label {
            background: #111827;
            color: #9ca3af;
            border-color: #374151;
        }
        
        .day-header {
            padding: 12px;
            font-weight: 600;
            text-align: center;
            border-bottom: 2px solid #d1d5db;
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .dark .day-header {
            background: #374151;
            color: #f3f4f6;
            border-color: #4b5563;
        }
        
        .time-slot {
            padding: 4px;
            border-right: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            min-height: 60px;
            position: relative;
        }
        
        .dark .time-slot {
            border-color: #374151;
        }
        
        .shift-block {
            position: absolute;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            z-index: 1;
        }
        
        .shift-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 100 !important;
        }
        
        .shift-block-empty {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #f87171;
            color: #991b1b;
        }
        
        .shift-block-partial {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fbbf24;
            color: #92400e;
        }
        
        .shift-block-full {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
            color: #065f46;
        }
        
        .dark .shift-block-empty {
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            border-color: #ef4444;
            color: #fecaca;
        }
        
        .dark .shift-block-partial {
            background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
            border-color: #f59e0b;
            color: #fde68a;
        }
        
        .dark .shift-block-full {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            border-color: #10b981;
            color: #d1fae5;
        }
        
        .shift-name {
            font-weight: 600;
            margin-bottom: 2px;
            line-height: 1.2;
        }
        
        .shift-time {
            font-size: 0.65rem;
            opacity: 0.8;
            margin-bottom: 2px;
        }
        
        .shift-coverage {
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .coverage-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 6px;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 700;
            margin-top: 2px;
        }
        
        .coverage-badge-empty {
            background: #fca5a5;
            color: #7f1d1d;
        }
        
        .coverage-badge-partial {
            background: #fcd34d;
            color: #78350f;
        }
        
        .coverage-badge-full {
            background: #6ee7b7;
            color: #064e3b;
        }
        
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        .dark .stats-card {
            background: #1f2937;
            border-color: #374151;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .dark .stat-label {
            color: #9ca3af;
        }
        
        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .dark .progress-bar {
            background: #374151;
        }
        
        .progress-fill {
            height: 100%;
            transition: width 0.3s;
            border-radius: 9999px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        
        .dark .legend-item {
            background: #374151;
            border-color: #4b5563;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="space-y-6">
        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stats-card">
                <div class="stat-value text-blue-600 dark:text-blue-400">{{ $shifts->count() }}</div>
                <div class="stat-label">Total Shifts</div>
            </div>
            <div class="stats-card">
                <div class="stat-value text-purple-600 dark:text-purple-400">{{ $totalSlots }}</div>
                <div class="stat-label">Total Volunteer Slots</div>
            </div>
            <div class="stats-card">
                <div class="stat-value text-green-600 dark:text-green-400">{{ $filledSlots }}</div>
                <div class="stat-label">Filled Slots</div>
            </div>
            <div class="stats-card">
                <div class="stat-value 
                    @if($coveragePercent >= 80) text-green-600 dark:text-green-400
                    @elseif($coveragePercent >= 50) text-yellow-600 dark:text-yellow-400
                    @else text-red-600 dark:text-red-400
                    @endif">
                    {{ $coveragePercent }}%
                </div>
                <div class="stat-label">Coverage Rate</div>
                <div class="progress-bar">
                    <div class="progress-fill 
                        @if($coveragePercent >= 80) bg-green-500
                        @elseif($coveragePercent >= 50) bg-yellow-500
                        @else bg-red-500
                        @endif"
                        style="width: {{ $coveragePercent }}%">
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Coverage Legend</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="legend-item">
                    <div class="legend-color bg-red-100 border-red-400 dark:bg-red-900 dark:border-red-500"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Empty (0% filled)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color bg-yellow-100 border-yellow-400 dark:bg-yellow-900 dark:border-yellow-500"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Partial (1-99% filled)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color bg-green-100 border-green-400 dark:bg-green-900 dark:border-green-500"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Full (100% filled)</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        @if($shifts->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                <x-heroicon-o-calendar class="w-16 h-16 mx-auto text-gray-400 mb-4"/>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">No shifts scheduled</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Create shifts to see the agenda view</p>
            </div>
        @else
            @foreach($shiftsByDate as $date => $dayShifts)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </h3>
                    
                    <div class="agenda-calendar" style="grid-template-columns: 80px 1fr;">
                        <!-- Header -->
                        <div class="time-label" style="border-bottom: 2px solid #d1d5db;"></div>
                        <div class="day-header">Shifts</div>
                        
                        <!-- Time slots -->
                        @for($hour = $earliestHour; $hour < $latestHour; $hour++)
                            <div class="time-label">
                                {{ \Carbon\Carbon::createFromTime($hour, 0)->format('g:00 A') }}
                            </div>
                            <div class="time-slot" style="position: relative; min-height: 80px;">
                                @foreach($dayShifts as $shift)
                                    @php
                                        $shiftStart = $shift->start_time;
                                        $shiftEnd = $shift->end_time;
                                        $slotStart = \Carbon\Carbon::parse($date)->setTime($hour, 0, 0);
                                        $slotEnd = \Carbon\Carbon::parse($date)->setTime($hour + 1, 0, 0);
                                        
                                        // Check if shift overlaps with this time slot
                                        $overlaps = $shiftStart->lt($slotEnd) && $shiftEnd->gt($slotStart);
                                        
                                        $blockClass = '';
                                        $badgeClass = '';
                                        $topPercent = 0;
                                        $heightPx = 0;
                                        $signupCount = 0;
                                        $maxVolunteers = 0;
                                        $coveragePercent = 0;
                                        $leftPercent = 0;
                                        $widthPercent = 100;
                                        
                                        if ($overlaps) {
                                            // Calculate position and height
                                            $startMinute = max(0, $shiftStart->diffInMinutes($slotStart));
                                            $durationMinutes = min(60, $shiftEnd->diffInMinutes(max($slotStart, $shiftStart)));
                                            
                                            $topPercent = ($startMinute / 60) * 100;
                                            $heightPercent = ($durationMinutes / 60) * 100;
                                            
                                            // Calculate coverage
                                            $signupCount = $shift->users->count();
                                            $maxVolunteers = $shift->max_volunteers;
                                            $coveragePercent = $maxVolunteers > 0 ? ($signupCount / $maxVolunteers) * 100 : 0;
                                            
                                            $blockClass = 'shift-block-empty';
                                            $badgeClass = 'coverage-badge-empty';
                                            if ($coveragePercent >= 100) {
                                                $blockClass = 'shift-block-full';
                                                $badgeClass = 'coverage-badge-full';
                                            } elseif ($coveragePercent > 0) {
                                                $blockClass = 'shift-block-partial';
                                                $badgeClass = 'coverage-badge-partial';
                                            }
                                            
                                            // Calculate actual height in pixels based on shift duration
                                            $totalDurationHours = $shiftEnd->diffInMinutes($shiftStart) / 60;
                                            $heightPx = $totalDurationHours * 80; // 80px per hour
                                            
                                            // Calculate column positioning for overlapping shifts
                                            if (isset($shiftPositions[$shift->id])) {
                                                $position = $shiftPositions[$shift->id];
                                                $columnCount = $position['columns'];
                                                $columnIndex = $position['column'];
                                                
                                                // Calculate width and left position based on columns
                                                $widthPercent = (100 / $columnCount) - 0.5; // -0.5 for small gap
                                                $leftPercent = ($columnIndex / $columnCount) * 100;
                                            } else {
                                                // Default to full width if no position data
                                                $widthPercent = 99.5;
                                                $leftPercent = 0;
                                            }
                                        }
                                    @endphp
                                        
                                    @if($overlaps && ($shiftStart->hour == $hour || ($shiftStart->lt($slotStart) && $hour == $earliestHour)))
                                            <div class="shift-block {{ $blockClass }}" 
                                                 style="top: {{ $topPercent }}%; height: {{ $heightPx }}px; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;"
                                                 onclick="window.location='{{ route('admin.events.shifts.edit', [$event, $shift]) }}'">
                                                <div class="shift-name">{{ $shift->name }}</div>
                                                <div class="shift-time">
                                                    {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                                                </div>
                                                <div class="coverage-badge {{ $badgeClass }}">
                                                    @if($coveragePercent >= 100)
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @elseif($coveragePercent > 0)
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                    {{ $signupCount }}/{{ $maxVolunteers }}
                                                </div>
                                                @if($shift->double_hours)
                                                    <div class="mt-1">
                                                        <x-heroicon-m-star class="w-3 h-3 inline" title="Double Hours"/>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Quick Actions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"/>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200">Quick Tips</h4>
                    <ul class="text-xs text-blue-700 dark:text-blue-300 mt-2 space-y-1 list-disc list-inside">
                        <li>Click on any shift block to edit its details</li>
                        <li>Color coding shows staffing levels at a glance</li>
                        <li>Overlapping shifts are displayed side-by-side in columns</li>
                        <li>Shifts spanning multiple hours show their full duration</li>
                        <li>Star icon indicates double-hours credit</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
