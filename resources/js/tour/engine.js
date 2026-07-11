/**
 * Generic guided-tour engine. Multiple tours (e.g. "admin-setup",
 * "volunteer-shifts") register their step list here; only one runs at a
 * time. State survives full page navigations via localStorage, namespaced
 * per tour id so progress on one tour never clobbers another.
 *
 * A tour definition is: { id, steps, onStart?, onStop? }
 * A step is: {
 *   id, route(routeName) -> bool, title, description,
 *   target?() -> Element|null,   // omit for manual/informational steps
 *   samePage?: true,             // action doesn't navigate (e.g. opens a dropdown)
 *   manual?: true,               // show a Next/Finish button instead of requiring a click
 *   finish?: true,               // Next button reads "Finish" and ends the tour
 *   wildcard?: true,             // route() matches any page (nav-level steps)
 *   openTrigger?: 'elementId' | ['id1', 'id2'], // auto-click the first visible one if target isn't visible yet (e.g. desktop dropdown vs. mobile hamburger)
 *   skipTo?: 'other-step-id',    // shows a "Skip section" button that jumps ahead
 *   beforeAdvance?(el),          // run right before marking the step done; el = the clicked target
 * }
 */

const ACTIVE_TOUR_KEY = 'mnfTourActiveId';

const TOURS = {};

export function registerTour({ id, steps, onStart, onStop }) {
    TOURS[id] = { steps, onStart, onStop };
}

function doneKey(tourId) {
    return `mnfTourDone:${tourId}`;
}

function getDone(tourId) {
    try {
        return JSON.parse(localStorage.getItem(doneKey(tourId)) || '[]');
    } catch (e) {
        return [];
    }
}

function setDone(tourId, ids) {
    localStorage.setItem(doneKey(tourId), JSON.stringify(ids));
}

function markDone(tourId, id) {
    const done = getDone(tourId);
    if (!done.includes(id)) {
        done.push(id);
        setDone(tourId, done);
    }
}

function unmarkStepsForRoute(tourId, steps, routeName) {
    // On a validation-error redisplay, "retry" whichever completed step is
    // specific to this exact route so its banner/glow reappears. Wildcard
    // nav steps are left alone — they'd otherwise reset on every page load.
    const done = getDone(tourId).filter((id) => {
        const step = steps.find((s) => s.id === id);
        return !(step && !step.wildcard && step.route(routeName));
    });
    setDone(tourId, done);
}

export function findVisible(selector, root = document) {
    // Only returns an element that's actually visible right now — callers
    // (the openTrigger fallback) rely on `null` to mean "not shown yet" so
    // they know to open the dropdown/panel it lives in.
    const els = root.querySelectorAll(selector);
    for (const el of els) {
        const style = window.getComputedStyle(el);
        if (style.display !== 'none' && style.visibility !== 'hidden' && el.offsetParent !== null) {
            return el;
        }
    }
    return null;
}

function activeTourId() {
    const id = localStorage.getItem(ACTIVE_TOUR_KEY);
    return id && TOURS[id] ? id : null;
}

export function stop() {
    const id = activeTourId();
    localStorage.removeItem(ACTIVE_TOUR_KEY);
    if (id) {
        localStorage.removeItem(doneKey(id));
        if (TOURS[id].onStop) TOURS[id].onStop();
    }
    teardown();
}

export function start(tourId) {
    const tour = TOURS[tourId];
    if (!tour) {
        console.error(`MNFTour: unknown tour "${tourId}"`);
        return;
    }
    localStorage.setItem(ACTIVE_TOUR_KEY, tourId);
    setDone(tourId, []);
    if (tour.onStart) tour.onStart();
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

function skipSection(tourId, steps, step) {
    const fromIndex = steps.indexOf(step);
    const toIndex = steps.findIndex((s) => s.id === step.skipTo);
    if (toIndex === -1) return;

    const done = getDone(tourId);
    for (let i = fromIndex; i < toIndex; i++) {
        if (!done.includes(steps[i].id)) done.push(steps[i].id);
    }
    setDone(tourId, done);
    render();
}

function findActiveStep(steps, done, routeName) {
    return steps.find((step) => !done.includes(step.id) && step.route(routeName));
}

function render() {
    teardown();
    const tourId = activeTourId();
    if (!tourId) return;

    const { steps } = TOURS[tourId];
    const routeName = window.tourRouteName || null;

    if (window.tourHasErrors) {
        unmarkStepsForRoute(tourId, steps, routeName);
    }

    const step = findActiveStep(steps, getDone(tourId), routeName);
    if (!step) return;

    renderBanner(tourId, steps, step);

    if (step.target) {
        attachToTarget(tourId, step, 0);
    }
}

function attachToTarget(tourId, step, attempt) {
    const el = step.target();
    if (!el) {
        // Target lives inside a closed dropdown (e.g. the user skipped
        // straight here) — open it once and keep polling for it to appear.
        if (attempt === 0 && step.openTrigger) {
            // openTrigger may be a single id or a list of candidate ids (e.g.
            // desktop dropdown trigger vs. mobile hamburger) — click whichever
            // one is actually visible on this viewport.
            const ids = Array.isArray(step.openTrigger) ? step.openTrigger : [step.openTrigger];
            for (const id of ids) {
                const trigger = findVisible('#' + id);
                if (trigger) {
                    trigger.click();
                    break;
                }
            }
        }
        if (attempt < 20) {
            pollTimer = setTimeout(() => attachToTarget(tourId, step, attempt + 1), 150);
        }
        return;
    }

    el.classList.add('mnf-tour-glow');
    glowEl = el;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const handler = () => {
        el.removeEventListener('click', handler);
        if (step.beforeAdvance) step.beforeAdvance(el);
        markDone(tourId, step.id);
        if (step.samePage) {
            pollTimer = setTimeout(render, 200);
        }
    };
    el.addEventListener('click', handler);
}

function renderBanner(tourId, steps, step) {
    const index = steps.indexOf(step) + 1;
    const total = steps.length;

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
            ${step.skipTo ? `<button type="button" class="mnf-tour-btn mnf-tour-btn--ghost" data-mnf-skip-section>${escapeHtml(step.skipLabel || 'Skip Section')} →</button>` : ''}
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
            markDone(tourId, step.id);
            render();
        });
    }

    const skipSectionBtn = bannerEl.querySelector('[data-mnf-skip-section]');
    if (skipSectionBtn) {
        skipSectionBtn.addEventListener('click', () => skipSection(tourId, steps, step));
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
