/**
 * Componente Alpine del formulario de reserva: filtra estilistas según el
 * servicio y pide al servidor los huecos libres para el día elegido.
 */
export default ({ catalog, serviceId, stylistId, date, time, url }) => ({
    catalog,
    serviceId: serviceId || null,
    stylistId: stylistId || null,
    date,
    time: time || null,
    slots: [],
    loading: false,

    init() {
        if (this.serviceId && this.stylistId) {
            this.loadSlots(true);
        }
    },

    get service() {
        return this.catalog.find((s) => s.id === this.serviceId) ?? null;
    },

    get stylist() {
        return this.service?.stylists.find((s) => s.id === this.stylistId) ?? null;
    },

    get prettyDate() {
        if (!this.date) return '—';
        const [y, m, d] = this.date.split('-').map(Number);
        return new Date(y, m - 1, d).toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
    },

    selectService(id) {
        this.serviceId = id;
        // Si el estilista elegido no hace este servicio se deselecciona
        // (o se elige solo, cuando únicamente hay uno disponible).
        if (!this.stylist) {
            this.stylistId = this.service.stylists.length === 1 ? this.service.stylists[0].id : null;
        }
        this.stylistId ? this.loadSlots() : this.reset();
    },

    reset() {
        this.slots = [];
        this.time = null;
    },

    async loadSlots(keepTime = false) {
        if (!this.serviceId || !this.stylistId || !this.date) return;

        const previous = this.time;
        this.reset();
        this.loading = true;

        try {
            const params = new URLSearchParams({ service_id: this.serviceId, stylist_id: this.stylistId, date: this.date });
            const response = await fetch(`${url}?${params}`, { headers: { Accept: 'application/json' } });
            this.slots = response.ok ? (await response.json()).slots : [];
            if (keepTime && this.slots.includes(previous)) this.time = previous;
        } finally {
            this.loading = false;
        }
    },
});
