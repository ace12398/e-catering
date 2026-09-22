import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Chart = Chart;

// 1. THEME STORE
Alpine.store('theme', {
    current: localStorage.getItem('caterflow_theme') || 'dark',
    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('caterflow_theme', this.current);
    }
});

// 2. SIDEBAR STORE
Alpine.store('sidebar', {
    open: true,
    toggle() {
        this.open = !this.open;
    }
});

// 3. NOTIFICATION TOAST STORE
Alpine.store('notification', {
    show: false,
    message: '',
    notify(msg) {
        this.message = msg;
        this.show = true;
        setTimeout(() => { this.show = false; }, 3000);
    }
});

// 4. MODAL STORE
Alpine.store('modal', {
    active: null,
    open(modalId) { this.active = modalId; },
    close() { this.active = null; }
});

// 5. ADAPTIVE WORKSPACE STORE
Alpine.store('workspace', {
    editMode: false,
    preset: 'standar',

    toggleEditMode() {
        this.editMode = !this.editMode;
        if (this.editMode) {
            Alpine.store('notification').notify('✨ Mode Penyesuaian Tampilan Aktif: Sesuaikan komponen tampilan Anda.');
        }
    },
});

Alpine.start();
