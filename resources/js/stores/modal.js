const triggers = new Map();

export default {
    state: {},

    open(id) {
        if (!this.isOpen(id)) triggers.set(id, document.activeElement);
        this.setState(id, {open: true});
    },

    close(id) {
        this.setState(id, {open: false});
        const trigger = triggers.get(id);
        triggers.delete(id);
        if (trigger instanceof HTMLElement && trigger.isConnected) {
            requestAnimationFrame(() => trigger.focus());
        }
    },

    isOpen(id) {
        return Boolean(this.state[id]?.open);
    },

    toggle(id) {
        if (this.isOpen(id)) this.close(id);
        else this.open(id);
    },

    isLoading(id) {
        return Boolean(this.state[id]?.loading);
    },

    setLoading(id, loading) {
        this.setState(id, typeof loading === 'object' ? loading : {loading});
    },

    setState(id, data) {
        this.state[id] = {...this.state[id], ...data};
    },

    keepFocus(event) {
        const elements = [...event.currentTarget.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        )].filter(element => element.getClientRects().length && element.tabIndex >= 0);
        const first = elements[0];
        const last = elements.at(-1);
        if (!first || !last) return;
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
};
