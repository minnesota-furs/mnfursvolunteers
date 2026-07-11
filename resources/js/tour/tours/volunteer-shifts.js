import { registerTour, findVisible } from '../engine';

const SHIFT_ID_KEY = 'mnfTourShiftId';

const steps = [
    {
        id: 'welcome',
        route: () => true,
        wildcard: true,
        title: 'Welcome to the Guided Tour',
        description: "This tour shows you how to browse events, pick up a volunteer shift, check your itinerary, and drop a shift if your plans change.",
        manual: true,
    },
    {
        id: 'open-volunteer-menu',
        route: () => true,
        wildcard: true,
        title: 'Volunteer Menu',
        description: 'Volunteer opportunities live under this Volunteer menu. Click it to open.',
        target: () => findVisible('#tour-volunteer-menu-trigger') || findVisible('#tour-mobile-menu-trigger'),
        samePage: true,
    },
    {
        id: 'click-event-assignments',
        route: () => true,
        wildcard: true,
        title: 'Event Assignments',
        description: 'This shows every upcoming event that\'s looking for volunteers. Click it to browse.',
        target: () => findVisible('[data-tour="tour-events-link"]'),
        openTrigger: ['tour-volunteer-menu-trigger', 'tour-mobile-menu-trigger'],
    },
    {
        id: 'pick-event',
        route: (r) => r === 'volunteer.events.index',
        title: 'Pick an Event',
        description: 'Each event lists the shifts it needs help with. Click an event to see what\'s available.',
        target: () => findVisible('[data-tour="tour-event-card"]'),
    },
    {
        id: 'sign-up-shift',
        route: (r) => r === 'volunteer.events.show',
        title: 'Sign Up for a Shift',
        description: 'Pick any open assignment and click "Sign Up" — you can sign up for as many shifts as you like, as long as they don\'t overlap.',
        target: () => findVisible('[data-tour="tour-signup-btn"]'),
        beforeAdvance: (el) => {
            const id = el && el.dataset ? el.dataset.shiftId : null;
            if (id) localStorage.setItem(SHIFT_ID_KEY, id);
        },
    },
    {
        id: 'back-to-events',
        route: (r) => r === 'volunteer.events.show',
        title: 'Nice, You\'re Signed Up!',
        description: 'Let\'s check your itinerary. Click "Back" to return to the events list.',
        target: () => document.getElementById('tour-back-to-events-btn'),
    },
    {
        id: 'click-itinerary',
        route: (r) => r === 'volunteer.events.index',
        title: 'Your Itinerary',
        description: 'Your itinerary shows everything you\'ve signed up for, across every event, with iCal calendar sync too. Click "My Full Itinerary" to view it.',
        target: () => document.getElementById('tour-itinerary-btn'),
    },
    {
        id: 'open-drop-menu',
        route: (r) => r === 'volunteer.events.my-shifts-all',
        title: 'Manage a Shift',
        description: 'Changed your mind about a shift? Click "Manage" next to it to see your options.',
        target: () => {
            const id = localStorage.getItem(SHIFT_ID_KEY);
            return id ? document.getElementById('menuButton' + id) : null;
        },
        samePage: true,
    },
    {
        id: 'drop-shift',
        route: (r) => r === 'volunteer.events.my-shifts-all',
        title: 'Drop the Shift',
        description: 'Click "Drop Volunteer Slot" to remove yourself — you\'ll be asked to confirm first, so nothing drops by accident. Don\'t want to actually drop it? Keep your shift and just finish the tour.',
        target: () => {
            const id = localStorage.getItem(SHIFT_ID_KEY);
            return id ? findVisible('[data-tour="tour-drop-shift-btn"]', document.getElementById('optDropdown' + id) || document) : null;
        },
        skipTo: 'tour-complete',
        skipLabel: 'Keep My Shift',
    },
    {
        id: 'tour-complete',
        route: (r) => r === 'volunteer.events.my-shifts-all',
        title: 'Tour Complete! 🎉',
        description: "You've picked up a shift, checked your itinerary, and know how to drop a shift if plans change. You're all set!",
        manual: true,
        finish: true,
    },
];

function clearScratch() {
    localStorage.removeItem(SHIFT_ID_KEY);
}

registerTour({
    id: 'volunteer-shifts',
    steps,
    onStart: clearScratch,
    onStop: clearScratch,
});
