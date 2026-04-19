import './bootstrap';
import Alpine from 'alpinejs';
import NepaliDate from 'nepali-date-converter';
import Chart from 'chart.js/auto';

window.NepaliDate = NepaliDate;
window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.16)';
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.96)';
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 12;
Chart.defaults.plugins.tooltip.displayColors = false;
Chart.defaults.elements.line.tension = 0.38;

// ─── BS Datepicker Alpine Component ──────────────────────────
Alpine.data('bsDatePicker', (uid, initialValue) => ({
    open: false,
    dropUp: false,
    popupLeft: 0,
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
        this._calcPopupPlacement();
    },

    toggleCalendar() {
        this.open = !this.open;
        if (this.open) this._calcPopupPlacement();
    },

    _calcPopupPlacement() {
        this.$nextTick(() => {
            const wrap = this.$el;
            if (!wrap) return;

            const viewportPadding = 8;
            const panel = this.$refs.panel;
            const rect = wrap.getBoundingClientRect();
            const popupWidth = panel ? panel.getBoundingClientRect().width : 320;
            const popupHeight = panel ? panel.getBoundingClientRect().height : 340;

            const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
            const spaceAbove = rect.top - viewportPadding;
            this.dropUp = spaceBelow < popupHeight && spaceAbove > spaceBelow;

            const desiredLeft = rect.left;
            const maxLeft = Math.max(viewportPadding, window.innerWidth - popupWidth - viewportPadding);
            const clampedLeft = Math.min(Math.max(desiredLeft, viewportPadding), maxLeft);
            this.popupLeft = clampedLeft - rect.left;
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

            this.$nextTick(() => {
                const el = this.$refs.input;
                if (el) {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
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

const dashboardKpiStyles = {
    blue:    { bg: 'bg-blue-50',    text: 'text-blue-600',    trend: 'bg-blue-50 text-blue-600' },
    emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', trend: 'bg-emerald-50 text-emerald-600' },
    violet:  { bg: 'bg-violet-50',  text: 'text-violet-600',  trend: 'bg-violet-50 text-violet-600' },
    amber:   { bg: 'bg-amber-50',   text: 'text-amber-600',   trend: 'bg-amber-50 text-amber-600' },
    indigo:  { bg: 'bg-indigo-50',  text: 'text-indigo-600',  trend: 'bg-indigo-50 text-indigo-600' },
    rose:    { bg: 'bg-rose-50',    text: 'text-rose-600',    trend: 'bg-rose-50 text-rose-600' },
    // Legacy fallbacks
    red:     { bg: 'bg-red-50',     text: 'text-red-600',     trend: 'bg-red-50 text-red-600' },
    green:   { bg: 'bg-emerald-50', text: 'text-emerald-600', trend: 'bg-emerald-50 text-emerald-600' },
    slate:   { bg: 'bg-slate-50',   text: 'text-slate-600',   trend: 'bg-slate-50 text-slate-600' },
};

const dashboardAlertStyles = {
    danger: {
        shell: 'border-rose-200 bg-rose-50/70',
        iconWrap: 'bg-rose-100 text-rose-700',
        pill: 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
    },
    warning: {
        shell: 'border-amber-200 bg-amber-50/70',
        iconWrap: 'bg-amber-100 text-amber-700',
        pill: 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
    },
    info: {
        shell: 'border-sky-200 bg-sky-50/70',
        iconWrap: 'bg-sky-100 text-sky-700',
        pill: 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>',
    },
    success: {
        shell: 'border-emerald-200 bg-emerald-50/70',
        iconWrap: 'bg-emerald-100 text-emerald-700',
        pill: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    },
};

const dashboardChartOptions = {
    line: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.96)',
                padding: 12,
                cornerRadius: 12,
                displayColors: false,
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: '#64748b',
                    maxRotation: 0,
                },
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(148, 163, 184, 0.16)',
                },
                ticks: {
                    color: '#64748b',
                    precision: 0,
                },
            },
        },
    },
    bar: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.96)',
                padding: 12,
                cornerRadius: 12,
                displayColors: false,
                callbacks: {
                    label: (context) => `${context.dataset.label}: ${context.parsed.y}%`,
                },
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: '#64748b',
                },
            },
            y: {
                beginAtZero: true,
                suggestedMax: 100,
                grid: {
                    color: 'rgba(148, 163, 184, 0.16)',
                },
                ticks: {
                    color: '#64748b',
                    precision: 0,
                },
            },
        },
    },
};

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

const formatNumber = (value, decimals = 0) => {
    const parsed = Number(value ?? 0);
    const safeValue = Number.isFinite(parsed) ? parsed : 0;

    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(safeValue);
};

const formatTimestamp = (value) => {
    if (!value) return '';

    // If value is already a pre-formatted BS date string (not ISO), return as-is
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return String(value);
    }

    const date = new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
    }).format(parsed);
    const time = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(parsed);

    return `${date}, ${time}`;
};

const capitalize = (value) => {
    const text = String(value ?? '');

    if (!text) {
        return '';
    }

    return text.charAt(0).toUpperCase() + text.slice(1);
};

const dashboardEmptyState = ({ title, message, actionHref = '', actionLabel = '' }) => `
    <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-10 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-300 shadow-sm ring-1 ring-slate-200">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h3 class="mt-4 text-sm font-bold text-slate-900">${escapeHtml(title)}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">${escapeHtml(message)}</p>
        ${actionHref ? `<a href="${escapeHtml(actionHref)}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">${escapeHtml(actionLabel || 'Open')}</a>` : ''}
    </div>
`;

const trendIconMarkup = (direction) => {
    if (direction === 'down') {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>';
    }

    if (direction === 'flat') {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>';
    }

    return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M12 4v16"/>';
};

const parseDashboardState = (stateText) => {
    if (!stateText) {
        return null;
    }

    try {
        // Primary path: state is base64 encoded JSON from Blade.
        return JSON.parse(window.atob(stateText));
    } catch {
        try {
            // Backward compatibility fallback for plain JSON payload.
            return JSON.parse(stateText);
        } catch (error) {
            console.warn('[Dashboard] Invalid state payload:', error);
            return null;
        }
    }
};

const initializePrincipalDashboard = () => {
    const root = document.querySelector('[data-principal-dashboard]');

    if (!root) {
        return;
    }

    const endpoint = root.dataset.dashboardEndpoint || window.location.href;
    const sessionDisplay = root.querySelector('[data-dashboard-session-display]');
    const rangeDisplay = root.querySelector('[data-dashboard-range-display]');
    const updatedDisplay = root.querySelector('[data-dashboard-updated-display]');
    const alertList = root.querySelector('[data-dashboard-alert-list]');
    const highlightContainer = root.querySelector('[data-dashboard-highlight]');
    const noticeList = root.querySelector('[data-dashboard-notice-list]');
    const applicationList = root.querySelector('[data-dashboard-application-list]');
    const kpiCards = Array.from(root.querySelectorAll('[data-kpi-card]'));
    const enrollmentCanvas = root.querySelector('[data-principal-chart="enrollment"]');
    const departmentCanvas = root.querySelector('[data-principal-chart="department"]');
    const chartsSection = root.querySelector('#main-insights');

    const initialState = parseDashboardState(root.dataset.dashboardState) || {};
    let currentState = initialState;
    let loadingRequest = null;
    let chartsVisible = false;
    let chartsInitialized = false;
    const charts = {
        enrollment: null,
        department: null,
    };

    const setLoading = (isLoading) => {
        root.classList.toggle('opacity-70', isLoading);
        root.classList.toggle('pointer-events-none', isLoading);
    };

    const syncHeaderState = (state) => {
        if (sessionDisplay) {
            sessionDisplay.textContent = state.sessionLabel || 'Current session';
        }

        if (rangeDisplay) {
            rangeDisplay.textContent = state.rangeLabel || '';
        }

        if (updatedDisplay) {
            updatedDisplay.textContent = `Updated ${formatTimestamp(state.updatedAt) || ''}`;
        }
    };

    const syncKpis = (state) => {
        const cards = Array.isArray(state.kpis) ? state.kpis : [];

        cards.forEach((card) => {
            const element = root.querySelector(`[data-kpi-card="${card.key}"]`);

            if (!element) {
                return;
            }

            const valueNode = element.querySelector('[data-kpi-value]');
            const trendNode = element.querySelector('[data-kpi-trend]');
            const noteNode = element.querySelector('[data-kpi-note]');
            const trendStyle = dashboardKpiStyles[card.tone] || dashboardKpiStyles.blue;

            if (valueNode) {
                valueNode.textContent = card.value;
            }

            if (trendNode) {
                trendNode.className = `inline-flex items-center gap-1 rounded-md ${trendStyle.trend} px-1.5 py-0.5 text-[10px] font-semibold`;
                trendNode.innerHTML = `
                    ${card.trendDirection === 'up' ? '<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l5-5 5 5M7 7l5 5 5-5"/></svg>' : ''}
                    ${card.trendDirection === 'down' ? '<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-5 5-5-5m0 10l5-5 5 5"/></svg>' : ''}
                    ${escapeHtml(card.trend)}
                `;
            }

            if (noteNode) {
                noteNode.textContent = card.note || '';
            }
        });
    };

    const renderAlertList = (state) => {
        if (!alertList) {
            return;
        }

        const alerts = Array.isArray(state.alerts) ? state.alerts : [];

        if (!alerts.length) {
            alertList.innerHTML = '<div class="py-8 text-center"><p class="text-xs text-slate-400">No alerts for this period.</p></div>';
            return;
        }

        const dotColors = {
            danger: 'bg-rose-500',
            warning: 'bg-amber-500',
            success: 'bg-emerald-500',
            info: 'bg-sky-500',
        };

        alertList.innerHTML = alerts.map((alert) => {
            const dot = dotColors[alert.tone] || dotColors.info;
            return `
                <div class="flex items-start gap-3 py-3.5">
                    <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full ${dot}"></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900">${escapeHtml(alert.title)}</p>
                        <p class="mt-0.5 text-xs text-slate-500">${escapeHtml(alert.message)}</p>
                    </div>
                    ${alert.actionHref ? `<a href="${escapeHtml(alert.actionHref)}" class="shrink-0 rounded-md border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">${escapeHtml(alert.actionLabel || 'View')}</a>` : ''}
                </div>
            `;
        }).join('');
    };

    const renderHighlight = (state) => {
        if (!highlightContainer) {
            return;
        }

        const highlight = state.highlight;

        if (!highlight) {
            highlightContainer.innerHTML = `
                <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8">
                    <div class="text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                        <p class="mt-2 text-xs font-medium text-slate-500">No highlight data yet</p>
                        <p class="text-[11px] text-slate-400">Add attendance and results to see top department.</p>
                    </div>
                </div>
            `;
            return;
        }

        highlightContainer.innerHTML = `
            <div class="overflow-hidden rounded-xl border border-slate-200/80 shadow-sm">
                <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 px-5 py-5 text-white">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/50">Top Department</p>
                    <h3 class="mt-2 text-xl font-bold tracking-tight">${escapeHtml(highlight.name)}</h3>
                    <p class="mt-1 text-xs text-white/70">${escapeHtml(highlight.summary)}</p>
                    <div class="mt-4 flex items-end justify-between">
                        <div>
                            <span class="text-3xl font-bold">${formatNumber(highlight.score, 1)}%</span>
                            <p class="text-[10px] uppercase tracking-wider text-white/50">Performance</p>
                        </div>
                        <div class="rounded-lg bg-white/10 px-3 py-2 text-right">
                            <p class="text-[10px] text-white/50">Students</p>
                            <p class="text-sm font-bold">${formatNumber(highlight.students, 0)}</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 divide-x divide-slate-100 bg-white">
                    <div class="px-3 py-3 text-center">
                        <p class="text-sm font-bold text-slate-900">${formatNumber(highlight.attendance_rate ?? 0, 1)}%</p>
                        <p class="text-[10px] text-slate-500">Attendance</p>
                    </div>
                    <div class="px-3 py-3 text-center">
                        <p class="text-sm font-bold text-slate-900">${formatNumber(highlight.pass_rate ?? 0, 1)}%</p>
                        <p class="text-[10px] text-slate-500">Pass Rate</p>
                    </div>
                    <div class="px-3 py-3 text-center">
                        <p class="text-sm font-bold text-slate-900">${formatNumber(highlight.score, 1)}%</p>
                        <p class="text-[10px] text-slate-500">Score</p>
                    </div>
                </div>
            </div>
        `;
    };

    const renderNoticeList = (state) => {
        if (!noticeList) {
            return;
        }

        const notices = Array.isArray(state.recentNotices) ? state.recentNotices : [];

        if (!notices.length) {
            noticeList.innerHTML = '<div class="py-8 text-center"><p class="text-xs text-slate-400">No recent notices.</p></div>';
            return;
        }

        noticeList.innerHTML = notices.map((notice) => {
            const dateParts = String(notice.date || '').split(' ');
            const day = dateParts[0] || '';
            const month = dateParts[1] || '';
            return `
                <a href="${escapeHtml(notice.href)}" class="flex gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <span class="text-[9px] font-semibold uppercase leading-none">${escapeHtml(month)}</span>
                        <span class="text-sm font-bold leading-none">${escapeHtml(day)}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">${escapeHtml(notice.title)}</p>
                        <p class="mt-0.5 text-xs text-slate-500">${escapeHtml(notice.date || '')} · ${escapeHtml(notice.author || 'System')}</p>
                    </div>
                    <span class="shrink-0 self-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">${escapeHtml(notice.type || 'Notice')}</span>
                </a>
            `;
        }).join('');
    };

    const renderApplicationList = (state) => {
        if (!applicationList) {
            return;
        }

        const applications = Array.isArray(state.recentApplications) ? state.recentApplications : [];

        if (!applications.length) {
            applicationList.innerHTML = '<div class="py-8 text-center"><p class="text-xs text-slate-400">No applications yet.</p></div>';
            return;
        }

        applicationList.innerHTML = applications.map((application) => `
            <a href="${escapeHtml(application.href)}" class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-slate-900">${escapeHtml(application.full_name)}</p>
                    <p class="mt-0.5 text-xs text-slate-500">${escapeHtml(application.department || 'General')} · ${escapeHtml(application.date || '')}</p>
                </div>
                <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold ${escapeHtml(application.statusClass || 'bg-amber-100 text-amber-700')}">${escapeHtml(application.statusLabel || 'Pending')}</span>
            </a>
        `).join('');
    };

    const buildLineChart = (chartState) => {
        if (!enrollmentCanvas) {
            return null;
        }

        const context = enrollmentCanvas.getContext('2d');
        const gradient = context.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.34)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.03)');

        return new Chart(enrollmentCanvas, {
            type: 'line',
            data: {
                labels: chartState.labels || [],
                datasets: [{
                    label: 'Admissions',
                    data: chartState.values || [],
                    borderColor: '#2563EB',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.38,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                }],
            },
            options: dashboardChartOptions.line,
        });
    };

    const buildBarChart = (chartState) => {
        if (!departmentCanvas) {
            return null;
        }

        const palette = [
            'rgba(59, 130, 246, 0.88)',
            'rgba(16, 185, 129, 0.88)',
            'rgba(245, 158, 11, 0.88)',
            'rgba(99, 102, 241, 0.88)',
            'rgba(236, 72, 153, 0.88)',
            'rgba(239, 68, 68, 0.88)',
        ];
        const values = chartState.values || [];
        const colors = values.map((_, index) => palette[index % palette.length]);

        return new Chart(departmentCanvas, {
            type: 'bar',
            data: {
                labels: chartState.labels || [],
                datasets: [{
                    label: 'Performance score',
                    data: values,
                    borderRadius: 14,
                    borderSkipped: false,
                    backgroundColor: colors,
                    hoverBackgroundColor: colors,
                    maxBarThickness: 30,
                }],
            },
            options: dashboardChartOptions.bar,
        });
    };

    const syncCharts = (state) => {
        if (!chartsVisible) {
            return;
        }

        const chartData = state.charts || {};

        if (enrollmentCanvas) {
            if (!charts.enrollment) {
                charts.enrollment = buildLineChart(chartData.enrollment || {});
            } else {
                const dataset = chartData.enrollment || {};
                const gradient = charts.enrollment.ctx.createLinearGradient(0, 0, 0, 320);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.34)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.03)');

                charts.enrollment.data.labels = dataset.labels || [];
                charts.enrollment.data.datasets[0].data = dataset.values || [];
                charts.enrollment.data.datasets[0].backgroundColor = gradient;
                charts.enrollment.data.datasets[0].borderColor = '#2563EB';
                charts.enrollment.data.datasets[0].pointBackgroundColor = '#2563EB';
                charts.enrollment.update();
            }
        }

        if (departmentCanvas) {
            if (!charts.department) {
                charts.department = buildBarChart(chartData.departmentPerformance || {});
            } else {
                const dataset = chartData.departmentPerformance || {};
                const palette = [
                    'rgba(59, 130, 246, 0.88)',
                    'rgba(16, 185, 129, 0.88)',
                    'rgba(245, 158, 11, 0.88)',
                    'rgba(99, 102, 241, 0.88)',
                    'rgba(236, 72, 153, 0.88)',
                    'rgba(239, 68, 68, 0.88)',
                ];
                const values = dataset.values || [];
                const colors = values.map((_, index) => palette[index % palette.length]);

                charts.department.data.labels = dataset.labels || [];
                charts.department.data.datasets[0].data = values;
                charts.department.data.datasets[0].backgroundColor = colors;
                charts.department.data.datasets[0].hoverBackgroundColor = colors;
                charts.department.update();
            }
        }

        chartsInitialized = true;
    };

    const applyState = (nextState) => {
        currentState = nextState;
        root.dataset.dashboardState = JSON.stringify(nextState);

        syncHeaderState(nextState);
        syncKpis(nextState);
        renderAlertList(nextState);
        renderHighlight(nextState);
        renderNoticeList(nextState);
        renderApplicationList(nextState);
        syncCharts(nextState);
    };

    const buildRequestUrl = (state, overrides = {}) => {
        const url = new URL(endpoint, window.location.origin);
        const period = overrides.period ?? state.period ?? 'month';
        const sessionId = overrides.session_id ?? state.sessionId;

        url.searchParams.set('period', period);

        if (period === 'session' && sessionId !== undefined && sessionId !== null && sessionId !== '') {
            url.searchParams.set('session_id', sessionId);
        } else {
            url.searchParams.delete('session_id');
        }

        return url;
    };

    const fetchState = async (overrides = {}) => {
        if (loadingRequest) {
            loadingRequest.abort();
        }

        const requestController = new AbortController();
        loadingRequest = requestController;
        setLoading(true);

        try {
            const requestUrl = buildRequestUrl(currentState, overrides);
            const response = await fetch(requestUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Dashboard request failed with status ${response.status}`);
            }

            const nextState = await response.json();
            applyState(nextState);
            window.history.replaceState({}, '', requestUrl.toString());
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('[Dashboard] Refresh failed:', error);
            }
        } finally {
            if (loadingRequest === requestController) {
                setLoading(false);
                loadingRequest = null;
            }
        }
    };

    if (chartsSection && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                chartsVisible = true;
                syncCharts(currentState);
                observer.disconnect();
            }
        }, {
            threshold: 0.15,
        });

        observer.observe(chartsSection);
    } else {
        chartsVisible = true;
        syncCharts(currentState);
    }

    applyState(currentState);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePrincipalDashboard, { once: true });
} else {
    initializePrincipalDashboard();
}

Alpine.start();

