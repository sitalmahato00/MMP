import './bootstrap';
import Alpine from 'alpinejs';
import NepaliDate from 'nepali-date-converter';

window.NepaliDate = NepaliDate;
window.Alpine = Alpine;

// ─── BS Datepicker Alpine Component ──────────────────────────
Alpine.data('bsDatePicker', (uid, initialValue) => ({
    open: false,
    dropUp: false,
    bsValue: initialValue || '',
    adValue: '',
    viewYear: 2083,
    viewMonth: 0,
    selectedYear: null,
    selectedMonth: null,
    selectedDay: null,
    calendarCells: [],
    todayBS: null,
    todayLabel: '',

    monthNames: [
        'Baisakh', 'Jestha', 'Asar', 'Shrawan',
        'Bhadra', 'Aswin', 'Kartik', 'Mangsir',
        'Poush', 'Magh', 'Falgun', 'Chaitra'
    ],
    dayNames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],

    get yearRange() {
        const years = [];
        for (let y = 2040; y <= 2099; y++) years.push(y);
        return years;
    },

    init() {
        try {
            const today = new NepaliDate();
            this.todayBS = { year: today.getYear(), month: today.getMonth(), day: today.getDate() };
            this.todayLabel = today.format('DD MMMM YYYY');

            if (this.bsValue && this.bsValue.trim()) {
                this._parseValue(this.bsValue.trim());
            } else {
                this.viewYear = this.todayBS.year;
                this.viewMonth = this.todayBS.month;
            }
            this._syncAD();
            this.buildCalendar();
        } catch (e) {
            console.warn('[BS Datepicker] Init error:', e);
        }
    },

    _parseValue(val) {
        const parts = val.replace(/\//g, '-').split('-');
        if (parts.length === 3) {
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            if (y >= 2000 && y <= 2099 && m >= 0 && m <= 11 && d >= 1 && d <= 32) {
                this.selectedYear = y;
                this.selectedMonth = m;
                this.selectedDay = d;
                this.viewYear = y;
                this.viewMonth = m;
                return true;
            }
        }
        return false;
    },

    _syncAD() {
        if (this.selectedYear && this.selectedDay) {
            try {
                const nd = new NepaliDate(this.selectedYear, this.selectedMonth, this.selectedDay);
                const ad = nd.getAD();
                const mm = String(ad.month + 1).padStart(2, '0');
                const dd = String(ad.date).padStart(2, '0');
                this.adValue = `${ad.year}-${mm}-${dd}`;
            } catch { this.adValue = ''; }
        } else {
            this.adValue = '';
        }
    },

    _formatBS(y, m, d) {
        return `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    },

    openCalendar() {
        this.open = true;
        this._calcDropDirection();
    },

    toggleCalendar() {
        this.open = !this.open;
        if (this.open) this._calcDropDirection();
    },

    _calcDropDirection() {
        this.$nextTick(() => {
            const wrap = this.$el;
            if (!wrap) return;
            const rect = wrap.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            this.dropUp = spaceBelow < 340;
        });
    },

    buildCalendar() {
        const cells = [];
        try {
            const firstDay = new NepaliDate(this.viewYear, this.viewMonth, 1);
            const startDow = firstDay.getDay();

            let daysInMonth = 30;
            try {
                for (let d = 28; d <= 32; d++) {
                    try { new NepaliDate(this.viewYear, this.viewMonth, d); daysInMonth = d; } catch { break; }
                }
            } catch {}

            for (let i = 0; i < startDow; i++) {
                cells.push({ key: 'e' + i, day: 0, isToday: false, isSelected: false });
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const isToday = this.todayBS && d === this.todayBS.day && this.viewMonth === this.todayBS.month && this.viewYear === this.todayBS.year;
                const isSelected = d === this.selectedDay && this.viewMonth === this.selectedMonth && this.viewYear === this.selectedYear;
                cells.push({ key: 'd' + d, day: d, isToday, isSelected });
            }
        } catch (e) {
            console.warn('[BS Datepicker] Build error:', e);
        }
        this.calendarCells = cells;
    },

    selectDate(day) {
        this.selectedYear = this.viewYear;
        this.selectedMonth = this.viewMonth;
        this.selectedDay = day;
        this.bsValue = this._formatBS(this.viewYear, this.viewMonth, day);
        this._syncAD();
        this.buildCalendar();
        this.open = false;

        // Trigger change event for any listeners
        this.$nextTick(() => {
            const el = this.$refs.input;
            if (el) el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    },

    onManualInput() {
        if (this._parseValue(this.bsValue)) {
            this._syncAD();
            this.buildCalendar();
        }
    },

    prevMonth() {
        if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
        else { this.viewMonth--; }
        this.buildCalendar();
    },

    nextMonth() {
        if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
        else { this.viewMonth++; }
        this.buildCalendar();
    },

    goToday() {
        if (this.todayBS) {
            this.selectDate(this.todayBS.day);
            this.viewYear = this.todayBS.year;
            this.viewMonth = this.todayBS.month;
            this.buildCalendar();
        }
    },
}));

Alpine.start();
