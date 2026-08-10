export default function commandPalette(availableFeatures, userSearchUrl) {
    return {
        isOpen: false,
        query: '',
        availableFeatures,
        users: [],
        isSearching: false,
        activeIndex: 0,
        openedAt: 0,
        searchTimeout: null,
        abortController: null,

        init() {
            window.addEventListener('keydown', (event) => {
                if (event.shiftKey && event.code === 'Space') {
                    if (!this.isOpen && this.isTextFieldFocused(event.target)) {
                        return;
                    }

                    event.preventDefault();

                    if (event.repeat) {
                        return;
                    }

                    if (!this.isOpen) {
                        this.open();
                        return;
                    }

                    if (Date.now() - this.openedAt >= 1000) {
                        this.close();
                    }
                }
            });
        },

        isTextFieldFocused(target) {
            if (!target) {
                return false;
            }

            if (target.isContentEditable) {
                return true;
            }

            const tagName = target.tagName;

            if (tagName === 'TEXTAREA' || tagName === 'SELECT') {
                return true;
            }

            if (tagName === 'INPUT') {
                const nonTextTypes = ['checkbox', 'radio', 'button', 'submit', 'reset', 'range', 'color', 'file'];

                return !nonTextTypes.includes(target.type);
            }

            return false;
        },

        get matchingFeatures() {
            const searchQuery = this.query.trim().toLocaleLowerCase();

            if (!searchQuery) {
                return this.availableFeatures;
            }

            return this.availableFeatures.filter((feature) =>
                [feature.title, feature.description, ...feature.keywords]
                    .join(' ')
                    .toLocaleLowerCase()
                    .includes(searchQuery),
            );
        },

        get results() {
            return [
                ...this.matchingFeatures.map((feature) => ({ ...feature, type: 'feature' })),
                ...this.users.map((user) => ({
                    ...user,
                    description: [user.email, user.vol_code].filter(Boolean).join(' · '),
                    type: 'user',
                })),
            ];
        },

        open() {
            this.isOpen = true;
            this.openedAt = Date.now();
            this.query = '';
            this.users = [];
            this.activeIndex = 0;
            this.$nextTick(() => this.$refs.searchInput.focus());
        },

        close() {
            this.isOpen = false;
            this.abortController?.abort();
            clearTimeout(this.searchTimeout);
        },

        search() {
            this.activeIndex = 0;
            this.users = [];
            clearTimeout(this.searchTimeout);
            this.abortController?.abort();

            if (this.query.trim().length < 2) {
                this.isSearching = false;
                return;
            }

            this.searchTimeout = setTimeout(() => this.searchUsers(), 200);
        },

        async searchUsers() {
            this.isSearching = true;
            this.abortController = new AbortController();

            try {
                const url = new URL(userSearchUrl, window.location.origin);
                url.searchParams.set('query', this.query.trim());

                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: this.abortController.signal,
                });

                if (!response.ok) {
                    throw new Error('Command Palette search failed.');
                }

                const payload = await response.json();
                this.users = payload.users;
            } catch (error) {
                if (error.name !== 'AbortError') {
                    this.users = [];
                }
            } finally {
                this.isSearching = false;
            }
        },

        moveActive(direction) {
            if (!this.results.length) {
                return;
            }

            this.activeIndex = (this.activeIndex + direction + this.results.length) % this.results.length;
            this.$nextTick(() => {
                this.$refs.results?.querySelector(`[data-result-index="${this.activeIndex}"]`)
                    ?.scrollIntoView({ block: 'nearest' });
            });
        },

        visitActive() {
            const result = this.results[this.activeIndex];

            if (result) {
                window.location.assign(result.url);
            }
        },
    };
}
