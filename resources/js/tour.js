import '../css/tour.css';

/**
 * Guided admin onboarding tour.
 *
 * State survives full page navigations via localStorage: an "active" flag and a
 * list of completed step ids. On every page load we find the first step that
 * isn't complete yet and whose `route` matches the page we're on, then render
 * a bottom banner and (if it has a target) glow-highlight the element to click.
 */

const ACTIVE_KEY = 'mnfTourActive';
const DONE_KEY = 'mnfTourDone';
const EVENT_NAME_KEY = 'mnfTourEventName';
const SHOW_PAST_KEY = 'mnfTourShowedPast';

const STEPS = [
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
        target: () => document.getElementById('tour-settings-trigger'),
        samePage: true,
        skipTo: 'click-volunteer-events',
    },
    {
        id: 'click-ledgers',
        route: () => true,
        wildcard: true,
        title: 'Ledgers',
        description: 'A Fiscal Ledger represents a reporting period (like a fiscal year) that volunteer hours are tracked against. Click "Ledgers" to view or create one.',
        target: () => findVisible('[data-tour="tour-ledgers-link"]'),
        openTrigger: 'tour-settings-trigger',
        skipTo: 'click-volunteer-events',
    },
    {
        id: 'create-ledger',
        route: (r) => r === 'ledger.index',
        title: 'Create a New Ledger',
        description: "Every organization needs at least one active ledger. Click \"Create Ledger\" to set one up — you'll define a name and a start/end date range. Most orgs only need to do this once a year.",
        target: () => document.getElementById('tour-create-ledger-btn'),
        skipTo: 'click-volunteer-events',
    },
    {
        id: 'save-ledger',
        route: (r) => r === 'ledger.create',
        title: 'Fill Out the Ledger',
        description: "Give your ledger a name (e.g. \"Fiscal Year 2026\") and a start/end date range, then click Save.",
        target: () => document.getElementById('tour-ledger-save-btn'),
        skipTo: 'click-volunteer-events',
    },
    {
        id: 'open-settings-2',
        route: (r) => r === 'ledger.index',
        title: 'Ledger Created!',
        description: "Now let's set up your first Volunteer Event. Click the Settings menu again.",
        target: () => document.getElementById('tour-settings-trigger'),
        samePage: true,
        skipTo: 'click-volunteer-events',
    },
    {
        id: 'click-volunteer-events',
        route: () => true,
        wildcard: true,
        title: 'Volunteer Events',
        description: 'Volunteer Events are the conventions, meetings, or activities that volunteers sign up to help with. Click "Volunteer Events" to manage them.',
        target: () => findVisible('[data-tour="tour-volunteer-events-link"]'),
        openTrigger: 'tour-settings-trigger',
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
            const rows = document.querySelectorAll('tr[data-tour-event-name]');
            for (const row of rows) {
                if (row.getAttribute('data-tour-event-name').trim() === wanted) {
                    return findVisible('[data-tour="manage-shifts-link"]', row);
                }
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

function getDone() {
    try {
        return JSON.parse(localStorage.getItem(DONE_KEY) || '[]');
    } catch (e) {
        return [];
    }
}

function setDone(ids) {
    localStorage.setItem(DONE_KEY, JSON.stringify(ids));
}

function markDone(id) {
    const done = getDone();
    if (!done.includes(id)) {
        done.push(id);
        setDone(done);
    }
}

function unmarkStepsForRoute(routeName) {
    // On a validation-error redisplay, "retry" whichever completed step is
    // specific to this exact route so its banner/glow reappears. Wildcard nav
    // steps (route: () => true) are left alone — they'd otherwise reset on
    // every single page load.
    const done = getDone().filter((id) => {
        const step = STEPS.find((s) => s.id === id);
        return !(step && !step.wildcard && step.route(routeName));
    });
    setDone(done);
}

function findVisible(selector, root = document) {
    // Only returns an element that's actually visible right now — callers
    // (attachToTarget's openTrigger fallback) rely on `null` to mean "not
    // shown yet" so they know to open the dropdown it lives in.
    const els = root.querySelectorAll(selector);
    for (const el of els) {
        const style = window.getComputedStyle(el);
        if (style.display !== 'none' && style.visibility !== 'hidden' && el.offsetParent !== null) {
            return el;
        }
    }
    return null;
}

function isActive() {
    return localStorage.getItem(ACTIVE_KEY) === '1';
}

function stop() {
    localStorage.removeItem(ACTIVE_KEY);
    localStorage.removeItem(DONE_KEY);
    localStorage.removeItem(EVENT_NAME_KEY);
    localStorage.removeItem(SHOW_PAST_KEY);
    teardown();
}

function start() {
    localStorage.setItem(ACTIVE_KEY, '1');
    setDone([]);
    localStorage.removeItem(EVENT_NAME_KEY);
    localStorage.removeItem(SHOW_PAST_KEY);
    render();
}

let glowEl = null;
let bannerEl = null;
let pollTimer = null;

function teardown() {
    if (glowEl) {
        glowEl.classList.remove('mnf-tour-glow');
        glowEl = null;
    }
    if (bannerEl) {
        bannerEl.remove();
        bannerEl = null;
    }
    if (pollTimer) {
        clearTimeout(pollTimer);
        pollTimer = null;
    }
    document.body.style.paddingBottom = '';
}

function skipSection(step) {
    const fromIndex = STEPS.indexOf(step);
    const toIndex = STEPS.findIndex((s) => s.id === step.skipTo);
    if (toIndex === -1) return;

    const done = getDone();
    for (let i = fromIndex; i < toIndex; i++) {
        if (!done.includes(STEPS[i].id)) done.push(STEPS[i].id);
    }
    setDone(done);
    render();
}

function findActiveStep(routeName) {
    const done = getDone();
    return STEPS.find((step) => !done.includes(step.id) && step.route(routeName));
}

function render() {
    teardown();
    if (!isActive()) return;

    const routeName = window.tourRouteName || null;

    if (window.tourHasErrors) {
        unmarkStepsForRoute(routeName);
    }

    const step = findActiveStep(routeName);
    if (!step) return;

    renderBanner(step);

    if (step.target) {
        attachToTarget(step, 0);
    }
}

function attachToTarget(step, attempt) {
    const el = step.target();
    if (!el) {
        // Target lives inside a closed dropdown (e.g. the user skipped
        // straight here and never opened Settings) — open it once and
        // keep polling for the now-visible element.
        if (attempt === 0 && step.openTrigger) {
            const trigger = document.getElementById(step.openTrigger);
            if (trigger) trigger.click();
        }
        if (attempt < 20) {
            pollTimer = setTimeout(() => attachToTarget(step, attempt + 1), 150);
        }
        return;
    }

    el.classList.add('mnf-tour-glow');
    glowEl = el;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const handler = () => {
        el.removeEventListener('click', handler);
        if (step.beforeAdvance) step.beforeAdvance();
        markDone(step.id);
        if (step.samePage) {
            pollTimer = setTimeout(render, 200);
        }
    };
    el.addEventListener('click', handler);
}

function renderBanner(step) {
    const index = STEPS.indexOf(step) + 1;
    const total = STEPS.length;

    bannerEl = document.createElement('div');
    bannerEl.className = 'mnf-tour-banner';
    bannerEl.innerHTML = `
        <div class="mnf-tour-banner__body">
            <div class="mnf-tour-banner__title">${escapeHtml(step.title)}</div>
            <div class="mnf-tour-banner__desc">${escapeHtml(step.description)}</div>
            ${step.target ? '<div class="mnf-tour-banner__hint">↑ Click the highlighted item to continue</div>' : ''}
        </div>
        <div class="mnf-tour-banner__actions">
            <span class="mnf-tour-banner__step">Step ${index} of ${total}</span>
            ${step.manual ? `<button type="button" class="mnf-tour-btn mnf-tour-btn--primary" data-mnf-next>${step.finish ? 'Finish' : 'Next'}</button>` : ''}
            ${step.skipTo ? '<button type="button" class="mnf-tour-btn mnf-tour-btn--ghost" data-mnf-skip-section>Skip Ledger Setup →</button>' : ''}
            <button type="button" class="mnf-tour-btn mnf-tour-btn--ghost" data-mnf-skip>Skip Tour</button>
        </div>
    `;
    document.body.appendChild(bannerEl);
    document.body.style.paddingBottom = bannerEl.offsetHeight + 'px';

    const nextBtn = bannerEl.querySelector('[data-mnf-next]');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (step.finish) {
                stop();
                return;
            }
            markDone(step.id);
            render();
        });
    }

    const skipSectionBtn = bannerEl.querySelector('[data-mnf-skip-section]');
    if (skipSectionBtn) {
        skipSectionBtn.addEventListener('click', () => skipSection(step));
    }

    bannerEl.querySelector('[data-mnf-skip]').addEventListener('click', () => stop());
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', render);

window.MNFTour = { start, stop };
