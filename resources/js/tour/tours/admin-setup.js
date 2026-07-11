import { registerTour, findVisible } from '../engine';

const EVENT_NAME_KEY = 'mnfTourEventName';
const SHOW_PAST_KEY = 'mnfTourShowedPast';

const steps = [
    {
        id: 'welcome',
        route: () => true,
        wildcard: true,
        title: 'Welcome to the Guided Tour',
        description: "This tour walks you through the core admin workflow: creating a Fiscal Ledger to track volunteer hours, creating a Volunteer Event, and building a series of Shifts volunteers can sign up for.",
        manual: true,
    },
    {
        id: 'open-settings-1',
        route: () => true,
        wildcard: true,
        title: 'Settings Menu',
        description: 'Administrative tools like Ledgers, Volunteer Events, and Departments live under this Settings menu. Click it to open.',
        target: () => findVisible('#tour-settings-trigger') || findVisible('#tour-mobile-menu-trigger'),
        samePage: true,
        skipTo: 'click-volunteer-events',
        skipLabel: 'Skip Ledger Setup',
    },
    {
        id: 'click-ledgers',
        route: () => true,
        wildcard: true,
        title: 'Ledgers',
        description: 'A Fiscal Ledger represents a reporting period (like a fiscal year) that volunteer hours are tracked against. Click "Ledgers" to view or create one.',
        target: () => findVisible('[data-tour="tour-ledgers-link"]'),
        openTrigger: ['tour-settings-trigger', 'tour-mobile-menu-trigger'],
        skipTo: 'click-volunteer-events',
        skipLabel: 'Skip Ledger Setup',
    },
    {
        id: 'create-ledger',
        route: (r) => r === 'ledger.index',
        title: 'Create a New Ledger',
        description: "Every organization needs at least one active ledger. Click \"Create Ledger\" to set one up — you'll define a name and a start/end date range. Most orgs only need to do this once a year.",
        target: () => document.getElementById('tour-create-ledger-btn'),
        skipTo: 'click-volunteer-events',
        skipLabel: 'Skip Ledger Setup',
    },
    {
        id: 'save-ledger',
        route: (r) => r === 'ledger.create',
        title: 'Fill Out the Ledger',
        description: "Give your ledger a name (e.g. \"Fiscal Year 2026\") and a start/end date range, then click Save.",
        target: () => document.getElementById('tour-ledger-save-btn'),
        skipTo: 'click-volunteer-events',
        skipLabel: 'Skip Ledger Setup',
    },
    {
        id: 'open-settings-2',
        route: (r) => r === 'ledger.index',
        title: 'Ledger Created!',
        description: "Now let's set up your first Volunteer Event. Click the Settings menu again.",
        target: () => findVisible('#tour-settings-trigger') || findVisible('#tour-mobile-menu-trigger'),
        samePage: true,
        skipTo: 'click-volunteer-events',
        skipLabel: 'Skip Ledger Setup',
    },
    {
        id: 'click-volunteer-events',
        route: () => true,
        wildcard: true,
        title: 'Volunteer Events',
        description: 'Volunteer Events are the conventions, meetings, or activities that volunteers sign up to help with. Click "Volunteer Events" to manage them.',
        target: () => findVisible('[data-tour="tour-volunteer-events-link"]'),
        openTrigger: ['tour-settings-trigger', 'tour-mobile-menu-trigger'],
    },
    {
        id: 'create-event',
        route: (r) => r === 'admin.events.index',
        title: 'Create a New Event',
        description: 'Click "Create New Event" to set up an event volunteers can sign up for — you\'ll give it a name, dates, location, and visibility.',
        target: () => document.getElementById('tour-create-event-btn'),
    },
    {
        id: 'save-event',
        route: (r) => r === 'admin.events.create',
        title: 'Fill Out the Event',
        description: 'Enter a name, description, dates, and visibility for your event, then click "Create Event".',
        target: () => document.getElementById('tour-event-save-btn'),
        beforeAdvance: () => {
            const nameField = document.getElementById('name');
            if (nameField) localStorage.setItem(EVENT_NAME_KEY, nameField.value.trim());
        },
    },
    {
        id: 'manage-shifts',
        route: (r) => r === 'admin.events.index',
        title: 'Manage Shifts',
        description: 'Event created! Now let\'s add shifts volunteers can sign up for. Click "Manage Shifts" next to your new event.',
        target: () => {
            const wanted = (localStorage.getItem(EVENT_NAME_KEY) || '').trim();
            if (!wanted) return null;

            let matchedRow = null;
            for (const row of document.querySelectorAll('tr[data-tour-event-name]')) {
                if (row.getAttribute('data-tour-event-name').trim() === wanted) {
                    matchedRow = row;
                    break;
                }
            }

            if (matchedRow) {
                // Desktop: the link is right there in the row.
                const desktopLink = findVisible('[data-tour="manage-shifts-link"]', matchedRow);
                if (desktopLink) return desktopLink;

                // Mobile: it's inside a collapsed "•••" row menu whose panel
                // gets teleported to <body> once opened — at that point only
                // one row's menu can be open, so a global lookup is safe.
                const openLink = findVisible('[data-tour="manage-shifts-link"]');
                if (openLink) return openLink;

                const rowMenuTrigger = matchedRow.querySelector('[data-tour="row-menu-trigger"]');
                if (rowMenuTrigger) rowMenuTrigger.click();
                return null;
            }

            // Not in the default (upcoming-only) list — likely because the
            // event's dates are already in the past (e.g. left at today's
            // default time). Retry once with "Show past events" enabled.
            const showPast = document.getElementById('show_past');
            if (showPast && !showPast.checked && !localStorage.getItem(SHOW_PAST_KEY)) {
                localStorage.setItem(SHOW_PAST_KEY, '1');
                showPast.checked = true;
                showPast.form.submit();
            }
            return null;
        },
    },
    {
        id: 'create-series',
        route: (r) => r === 'admin.events.shifts.index',
        title: 'Create a Shift Series',
        description: 'Instead of creating shifts one at a time, a Shift Series lets you generate a whole recurring set at once — e.g. six 2-hour shifts back to back. Click "Create Shift Series" to try it.',
        target: () => document.getElementById('tour-create-series-btn'),
    },
    {
        id: 'save-series',
        route: (r) => r === 'admin.events.shifts.create-series',
        title: 'Configure the Series',
        description: 'Set the series name, first shift start time, duration, number of occurrences, and gap between shifts. Watch the live preview update, then click Create to generate all the shifts at once.',
        target: () => document.getElementById('tour-series-save-btn'),
    },
    {
        id: 'tour-complete',
        route: (r) => r === 'admin.events.shifts.index',
        title: 'Tour Complete! 🎉',
        description: "You've created a Volunteer Event and a series of Shifts. Volunteers can now sign up for these shifts. Explore the rest of the admin tools whenever you're ready.",
        manual: true,
        finish: true,
    },
];

function clearScratch() {
    localStorage.removeItem(EVENT_NAME_KEY);
    localStorage.removeItem(SHOW_PAST_KEY);
}

registerTour({
    id: 'admin-setup',
    steps,
    onStart: clearScratch,
    onStop: clearScratch,
});
