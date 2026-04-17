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
    red: {
        trend: 'bg-red-50 text-[#8B0000] ring-1 ring-red-100',
    },
    amber: {
        trend: 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
    },
    green: {
        trend: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
    },
    slate: {
        trend: 'bg-slate-50 text-slate-700 ring-1 ring-slate-200',
    },
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
    const parsed = new Date(value);

    if (Number.isNaN(parsed.getTime())) {
        return '';
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
        return JSON.parse(stateText);
    } catch (error) {
        console.warn('[Dashboard] Invalid state payload:', error);
        return null;
    }
};

const initializePrincipalDashboard = () => {
    const root = document.querySelector('[data-principal-dashboard]');

    if (!root) {
        return;
    }

    const endpoint = root.dataset.dashboardEndpoint || window.location.href;
    const loadingBadge = root.querySelector('[data-dashboard-loading]');
    const periodDisplays = Array.from(root.querySelectorAll('[data-dashboard-period-display]'));
    const periodLabel = root.querySelector('[data-dashboard-period-label]');
    const sessionDisplay = root.querySelector('[data-dashboard-session-display]');
    const heroSession = root.querySelector('[data-dashboard-hero-session]');
    const rangeDisplay = root.querySelector('[data-dashboard-range-display]');
    const updatedDisplay = root.querySelector('[data-dashboard-updated-display]');
    const sessionPanel = root.querySelector('[data-dashboard-session-panel]');
    const sessionSelect = root.querySelector('[data-dashboard-session-select]');
    const sessionApply = root.querySelector('[data-dashboard-session-apply]');
    const alertList = root.querySelector('[data-dashboard-alert-list]');
    const highlightContainer = root.querySelector('[data-dashboard-highlight]');
    const noticeList = root.querySelector('[data-dashboard-notice-list]');
    const applicationList = root.querySelector('[data-dashboard-application-list]');
    const kpiCards = Array.from(root.querySelectorAll('[data-kpi-card]'));
    const periodButtons = Array.from(root.querySelectorAll('[data-dashboard-period]'));
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
        if (loadingBadge) {
            loadingBadge.classList.toggle('hidden', !isLoading);
        }

        periodButtons.forEach((button) => {
            button.disabled = isLoading;
        });

        if (sessionApply) {
            sessionApply.disabled = isLoading;
            sessionApply.classList.toggle('opacity-70', isLoading);
            sessionApply.classList.toggle('cursor-wait', isLoading);
        }
    };

    const setPeriodButtonState = (activePeriod) => {
        periodButtons.forEach((button) => {
            const isActive = button.dataset.dashboardPeriod === activePeriod;
            button.setAttribute('aria-pressed', String(isActive));
            button.classList.toggle('border-[#8B0000]', isActive);
            button.classList.toggle('bg-white', isActive);
            button.classList.toggle('shadow-sm', isActive);
            button.classList.toggle('border-slate-200', !isActive);
            button.classList.toggle('bg-white/70', !isActive);
            button.classList.toggle('hover:border-red-200', !isActive);
            button.classList.toggle('hover:bg-white', !isActive);
        });
    };

    const syncHeaderState = (state) => {
        periodDisplays.forEach((element) => {
            element.textContent = `${capitalize(state.period)} view`;
        });

        if (periodLabel) {
            periodLabel.textContent = state.periodLabel || '';
        }

        if (sessionDisplay) {
            sessionDisplay.textContent = state.sessionLabel || 'Current session';
        }

        if (heroSession) {
            heroSession.textContent = state.sessionLabel || 'Current session';
        }

        if (rangeDisplay) {
            rangeDisplay.textContent = state.rangeLabel || '';
        }

        if (updatedDisplay) {
            updatedDisplay.textContent = formatTimestamp(state.updatedAt) || '';
        }

        if (sessionSelect && state.sessionId !== undefined && state.sessionId !== null) {
            sessionSelect.value = String(state.sessionId);
        }

        if (sessionPanel) {
            sessionPanel.classList.toggle('hidden', state.period !== 'session');
        }

        setPeriodButtonState(state.period || 'month');
    };

    const syncKpis = (state) => {
        const cards = Array.isArray(state.kpis) ? state.kpis : [];

        cards.forEach((card) => {
            const element = root.querySelector(`[data-kpi-card="${card.key}"]`);

            if (!element) {
                return;
            }

            if (card.href) {
                element.setAttribute('href', card.href);
            }

            const valueNode = element.querySelector('[data-kpi-value]');
            const trendNode = element.querySelector('[data-kpi-trend]');
            const noteNode = element.querySelector('[data-kpi-note]');
            const trendStyle = dashboardKpiStyles[card.tone] || dashboardKpiStyles.slate;

            if (valueNode) {
                valueNode.textContent = card.value;
            }

            if (trendNode) {
                trendNode.className = `inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold ${trendStyle.trend}`;
                trendNode.innerHTML = `
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        ${trendIconMarkup(card.trendDirection)}
                    </svg>
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
            alertList.innerHTML = dashboardEmptyState({
                title: 'No alerts',
                message: 'The campus looks stable for the selected period.',
            });
            return;
        }

        alertList.innerHTML = alerts.map((alert) => {
            const style = dashboardAlertStyles[alert.tone] || dashboardAlertStyles.info;

            return `
                <div class="flex items-start gap-4 rounded-3xl border px-4 py-4 ${style.shell}">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ${style.iconWrap}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">${style.icon}</svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-950">${escapeHtml(alert.title)}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">${escapeHtml(alert.message)}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] ${style.pill}">${escapeHtml(capitalize(alert.tone))}</span>
                        ${alert.actionHref ? `<a href="${escapeHtml(alert.actionHref)}" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:border-[#8B0000] hover:text-[#8B0000]">${escapeHtml(alert.actionLabel || 'Open')}</a>` : ''}
                    </div>
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
            highlightContainer.innerHTML = dashboardEmptyState({
                title: 'No highlight yet',
                message: 'Add attendance and result data to reveal the strongest department.',
            });
            return;
        }

        highlightContainer.innerHTML = `
            <div class="rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-[#8B0000] p-5 text-white shadow-[0_24px_60px_rgba(15,23,42,0.24)]">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-white/70">Top Department</p>
                <h3 class="mt-3 text-2xl font-black tracking-tight">${escapeHtml(highlight.name)}</h3>
                <p class="mt-2 text-sm leading-6 text-white/80">${escapeHtml(highlight.summary)}</p>
                <div class="mt-5 flex items-end justify-between gap-4">
                    <div>
                        <span class="text-4xl font-black">${formatNumber(highlight.score, 1)}%</span>
                        <p class="text-xs uppercase tracking-[0.18em] text-white/60">Performance score</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3 text-right">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/60">Students</p>
                        <p class="mt-1 text-lg font-bold">${formatNumber(highlight.students, 0)}</p>
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-2xl bg-white/10 px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Attendance</p>
                        <p class="mt-1 text-sm font-bold">${formatNumber(highlight.attendance_rate ?? 0, 1)}%</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Pass rate</p>
                        <p class="mt-1 text-sm font-bold">${formatNumber(highlight.pass_rate ?? 0, 1)}%</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Score</p>
                        <p class="mt-1 text-sm font-bold">${formatNumber(highlight.score, 1)}%</p>
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
            noticeList.innerHTML = dashboardEmptyState({
                title: 'No notices',
                message: 'No notices have been published recently.',
                actionHref: '/admin/notices/create',
                actionLabel: 'Post Notice',
            });
            return;
        }

        noticeList.innerHTML = notices.map((notice) => `
            <a href="${escapeHtml(notice.href)}" class="group flex gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-2xl bg-red-50 text-[#8B0000]">
                    <span class="text-[10px] font-bold uppercase leading-none">${escapeHtml(String(notice.date || '').split(' ')[1] || '')}</span>
                    <span class="mt-1 text-lg font-black leading-none">${escapeHtml(String(notice.date || '').split(' ')[0] || '')}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="truncate text-sm font-bold text-slate-950">${escapeHtml(notice.title)}</p>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600">${escapeHtml(notice.type || 'Notice')}</span>
                    </div>
                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600">${escapeHtml(notice.excerpt || '')}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span>${escapeHtml(notice.date || '')}</span>
                        <span>•</span>
                        <span>${escapeHtml(notice.author || 'System')}</span>
                    </div>
                </div>
            </a>
        `).join('');
    };

    const renderApplicationList = (state) => {
        if (!applicationList) {
            return;
        }

        const applications = Array.isArray(state.recentApplications) ? state.recentApplications : [];

        if (!applications.length) {
            applicationList.innerHTML = dashboardEmptyState({
                title: 'No applications',
                message: 'Applications will appear here as soon as students start applying.',
                actionHref: '/apply',
                actionLabel: 'Open Apply Page',
            });
            return;
        }

        applicationList.innerHTML = applications.map((application) => `
            <a href="${escapeHtml(application.href)}" class="group flex gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h6M9 3h6a2 2 0 012 2v14l-5-3-5 3V5a2 2 0 012-2z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="truncate text-sm font-bold text-slate-950">${escapeHtml(application.full_name)}</p>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] ${escapeHtml(application.statusClass || '')}">${escapeHtml(application.statusLabel || 'Pending')}</span>
                    </div>
                    <p class="mt-1 text-sm leading-6 text-slate-600">${escapeHtml(application.department || 'General intake')} · ${escapeHtml(application.phone || '')}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span>${escapeHtml(application.date || '')}</span>
                        <span>•</span>
                        <span>${escapeHtml(application.email || '')}</span>
                    </div>
                </div>
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

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const selectedPeriod = button.dataset.dashboardPeriod;

            if (!selectedPeriod || selectedPeriod === currentState.period) {
                return;
            }

            fetchState({
                period: selectedPeriod,
                session_id: sessionSelect?.value || currentState.sessionId,
            });
        });
    });

    if (sessionApply) {
        sessionApply.addEventListener('click', () => {
            fetchState({
                period: 'session',
                session_id: sessionSelect?.value || currentState.sessionId,
            });
        });
    }

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

const analyticsMetricStyles = {
    red: {
        idle: 'border-slate-200 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50/60 hover:text-[#8B0000]',
        active: 'border-red-200 bg-red-50 text-[#8B0000] shadow-[0_12px_30px_rgba(139,0,0,0.10)]',
    },
    amber: {
        idle: 'border-slate-200 bg-white text-slate-700 hover:border-amber-200 hover:bg-amber-50/60 hover:text-amber-700',
        active: 'border-amber-200 bg-amber-50 text-amber-700 shadow-[0_12px_30px_rgba(180,83,9,0.10)]',
    },
    slate: {
        idle: 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950',
        active: 'border-slate-300 bg-slate-950 text-white shadow-[0_12px_30px_rgba(15,23,42,0.12)]',
    },
};

const analyticsChartOptions = {
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
            },
        },
    },
};

const initializeAnalyticsPage = () => {
    const root = document.querySelector('[data-analytics-page]');

    if (!root) {
        return;
    }

    const endpoint = root.dataset.analyticsEndpoint || window.location.href;
    const loadingBadge = root.querySelector('[data-analytics-loading]');
    const sessionSelect = root.querySelector('[data-analytics-session-select]');
    const departmentSelect = root.querySelector('[data-analytics-department-select]');
    const programSelect = root.querySelector('[data-analytics-program-select]');
    const metricButtons = Array.from(root.querySelectorAll('[data-analytics-metric-button]'));
    const openDetailsButton = root.querySelector('[data-analytics-open-details]');
    const reportLink = root.querySelector('[data-analytics-report-link]');
    const sessionPill = root.querySelector('[data-analytics-session-pill]');
    const departmentPill = root.querySelector('[data-analytics-department-pill]');
    const programPill = root.querySelector('[data-analytics-program-pill]');
    const mainTitle = root.querySelector('[data-analytics-main-title]');
    const mainSubtitle = root.querySelector('[data-analytics-main-subtitle]');
    const comparisonTitle = root.querySelector('[data-analytics-comparison-title]');
    const comparisonSubtitle = root.querySelector('[data-analytics-comparison-subtitle]');
    const insightPanel = root.querySelector('[data-analytics-insight-panel]');
    const detailSection = root.querySelector('[data-analytics-detail-section]');
    const detailPanel = root.querySelector('[data-analytics-detail-panel]');
    const mainChartCanvas = root.querySelector('[data-analytics-main-chart]');
    const comparisonChartCanvas = root.querySelector('[data-analytics-comparison-chart]');

    const initialState = parseDashboardState(root.dataset.analyticsState) || {};
    const charts = {
        main: null,
        comparison: null,
    };

    let currentState = initialState;
    let activeRequest = null;
    let detailRequest = null;

    const metricButtonBase = 'group rounded-2xl border px-4 py-2.5 text-sm font-bold transition-all duration-200';

    const setLoading = (isLoading) => {
        if (loadingBadge) {
            loadingBadge.classList.toggle('hidden', !isLoading);
        }

        [sessionSelect, departmentSelect, programSelect, openDetailsButton, ...metricButtons].forEach((element) => {
            if (element) {
                element.disabled = isLoading;
            }
        });
    };

    const hideDetailSection = () => {
        if (detailSection) {
            detailSection.hidden = true;
        }

        if (detailPanel) {
            detailPanel.innerHTML = dashboardEmptyState({
                title: 'Open the drill-down',
                message: 'Click View Details to load student rows with marks and attendance for the current filters.',
            });
        }
    };

    const setUrlState = (state) => {
        const url = new URL(window.location.href);

        if (state.metric) {
            url.searchParams.set('metric', state.metric);
        } else {
            url.searchParams.delete('metric');
        }

        if (state.sessionId) {
            url.searchParams.set('session_id', state.sessionId);
        } else {
            url.searchParams.delete('session_id');
        }

        if (state.departmentId) {
            url.searchParams.set('department_id', state.departmentId);
        } else {
            url.searchParams.delete('department_id');
        }

        if (state.programId) {
            url.searchParams.set('program_id', state.programId);
        } else {
            url.searchParams.delete('program_id');
        }

        if (state.detail) {
            url.searchParams.set('detail', '1');
        } else {
            url.searchParams.delete('detail');
        }

        if (state.detail && state.detailPage && state.detailPage > 1) {
            url.searchParams.set('detail_page', state.detailPage);
        } else {
            url.searchParams.delete('detail_page');
        }

        window.history.replaceState({}, '', url.toString());
    };

    const buildRequestUrl = (state, overrides = {}) => {
        const url = new URL(endpoint, window.location.origin);
        const metric = overrides.metric ?? state.selectedMetric ?? '';
        const sessionId = overrides.session_id ?? state.filters?.sessionId ?? '';
        const departmentId = overrides.department_id ?? state.filters?.departmentId ?? '';
        const programId = overrides.program_id ?? state.filters?.programId ?? '';
        const detail = overrides.detail ?? state.filters?.detail ?? false;
        const detailPage = overrides.detail_page ?? state.filters?.detailPage ?? 1;

        if (metric) {
            url.searchParams.set('metric', metric);
        }

        if (sessionId) {
            url.searchParams.set('session_id', sessionId);
        }

        if (departmentId) {
            url.searchParams.set('department_id', departmentId);
        }

        if (programId) {
            url.searchParams.set('program_id', programId);
        }

        if (detail) {
            url.searchParams.set('detail', '1');
        }

        if (detail && detailPage > 1) {
            url.searchParams.set('detail_page', detailPage);
        }

        return url;
    };

    const syncFilters = (state) => {
        if (sessionSelect && state.filters?.sessionId !== undefined && state.filters?.sessionId !== null) {
            sessionSelect.value = String(state.filters.sessionId);
        }

        if (departmentSelect) {
            departmentSelect.value = state.filters?.departmentId ? String(state.filters.departmentId) : '';
        }

        if (programSelect) {
            programSelect.value = state.filters?.programId ? String(state.filters.programId) : '';
        }

        if (sessionPill) {
            sessionPill.textContent = state.selectedSessionLabel || 'Current session';
        }

        if (departmentPill) {
            departmentPill.textContent = state.selectedDepartmentLabel || 'All departments';
        }

        if (programPill) {
            programPill.textContent = state.selectedProgramLabel || 'All programs';
        }

        if (reportLink && state.reportHref) {
            reportLink.href = state.reportHref;
        }
    };

    const syncMetricButtons = (state) => {
        metricButtons.forEach((button) => {
            const tone = button.dataset.metricTone || 'slate';
            const style = analyticsMetricStyles[tone] || analyticsMetricStyles.slate;
            const isActive = button.dataset.metric === state.selectedMetric;

            button.className = `${metricButtonBase} ${isActive ? style.active : style.idle}`;

            const badge = button.querySelector('[data-metric-badge]');
            if (badge) {
                badge.textContent = isActive ? 'Active' : 'Lens';
            }
        });
    };

    const syncHeadings = (state) => {
        if (mainTitle) {
            mainTitle.textContent = state.mainChart?.title || state.selectedMetricLabel || 'Analytics';
        }

        if (mainSubtitle) {
            mainSubtitle.textContent = state.mainChart?.subtitle || state.selectedMetricDescription || '';
        }

        if (comparisonTitle) {
            comparisonTitle.textContent = state.comparisonChart?.title || 'Comparison';
        }

        if (comparisonSubtitle) {
            comparisonSubtitle.textContent = state.comparisonChart?.subtitle || '';
        }
    };

    const renderInsightPanel = (state) => {
        if (!insightPanel) {
            return;
        }

        const insights = Array.isArray(state.insights) ? state.insights : [];
        const items = insights.slice(0, 3);

        if (!items.length) {
            insightPanel.innerHTML = '';
            return;
        }

        insightPanel.innerHTML = items.map((insight) => `
            <li class="text-sm leading-6 text-slate-700">• ${escapeHtml(insight.message || '')}</li>
        `).join('');
    };

    const createChartOptions = (chartState) => {
        const isPercent = chartState.unit === '%';
        const isHorizontal = chartState.indexAxis === 'y';

        const axisTicks = isPercent
            ? {
                callback: (value) => `${value}%`,
            }
            : {
                precision: 0,
            };

        const scales = isHorizontal
            ? {
                x: {
                    beginAtZero: true,
                    suggestedMax: chartState.yMax || undefined,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.16)',
                    },
                    ticks: axisTicks,
                },
                y: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: '#64748b',
                    },
                },
            }
            : {
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
                    suggestedMax: chartState.yMax || undefined,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.16)',
                    },
                    ticks: axisTicks,
                },
            };

        return {
            ...analyticsChartOptions[chartState.type === 'bar' ? 'bar' : 'line'],
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales,
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
                        label: (context) => {
                            const value = chartState.indexAxis === 'y'
                                ? Number(context.parsed.x ?? 0)
                                : Number(context.parsed.y ?? context.parsed.x ?? 0);
                            return `${context.dataset.label}: ${isPercent ? `${formatNumber(value, 1)}%` : formatNumber(value, 0)}`;
                        },
                    },
                },
            },
        };
    };

    const renderChart = (canvas, chartState, existingChart) => {
        if (!canvas) {
            return null;
        }

        if (existingChart) {
            existingChart.destroy();
        }

        const datasets = (chartState.datasets || []).map((dataset) => ({
            ...dataset,
            borderSkipped: dataset.borderSkipped ?? false,
            borderRadius: dataset.borderRadius ?? 12,
            fill: dataset.fill ?? false,
            tension: dataset.tension ?? 0.38,
            pointRadius: dataset.pointRadius ?? 3,
            pointHoverRadius: dataset.pointHoverRadius ?? 6,
        }));

        return new Chart(canvas, {
            type: chartState.type || 'line',
            data: {
                labels: chartState.labels || [],
                datasets,
            },
            options: createChartOptions(chartState),
        });
    };

    const syncCharts = (state) => {
        charts.main = renderChart(mainChartCanvas, state.mainChart || {}, charts.main);
        charts.comparison = renderChart(comparisonChartCanvas, state.comparisonChart || {}, charts.comparison);
    };

    const renderDetailPanel = (detail) => {
        if (!detailPanel) {
            return;
        }

        if (!detail || !Array.isArray(detail.students) || detail.students.length === 0) {
            detailPanel.innerHTML = dashboardEmptyState({
                title: detail?.emptyMessage ? 'No matching students' : 'Open the drill-down',
                message: detail?.emptyMessage || 'Click View Details to load student rows with marks and attendance for the current filters.',
            });
            return;
        }

        const rows = detail.students;

        detailPanel.innerHTML = `
            <div class="space-y-4">
                <div class="rounded-[1.75rem] bg-slate-950 p-5 text-white shadow-[0_16px_45px_rgba(15,23,42,0.12)]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/60">Drill-down report</p>
                            <h4 class="mt-3 text-2xl font-black tracking-tight">${escapeHtml(detail.scopeLabel || 'Selected filters')}</h4>
                            <p class="mt-2 text-sm leading-6 text-white/75">${formatNumber(detail.summary?.students ?? 0, 0)} students loaded for the current filters.</p>
                        </div>
                        <a href="${escapeHtml(detail.reportHref || '#')}" class="rounded-2xl bg-white/10 px-4 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-white/15">Open Full Report</a>
                    </div>
                    <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs text-white/80">
                        <div class="rounded-2xl bg-white/10 px-3 py-3">
                            <p class="font-bold">Students</p>
                            <p class="mt-1 text-sm font-black">${formatNumber(detail.summary?.students ?? 0, 0)}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-3 py-3">
                            <p class="font-bold">Attendance</p>
                            <p class="mt-1 text-sm font-black">${formatNumber(detail.summary?.attendanceRate ?? 0, 1)}%</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-3 py-3">
                            <p class="font-bold">Pass rate</p>
                            <p class="mt-1 text-sm font-black">${formatNumber(detail.summary?.passRate ?? 0, 1)}%</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <p class="text-sm font-bold text-slate-900">Students</p>
                        <p class="mt-1 text-xs text-slate-500">Lazy-loaded rows, paginated for quick review.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        ${rows.map((row) => `
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold text-slate-950">${escapeHtml(row.name)}</p>
                                        <p class="mt-1 text-xs text-slate-500">Roll ${escapeHtml(row.rollNumber)} · ${escapeHtml(row.department)} / ${escapeHtml(row.program)}</p>
                                    </div>
                                    <a href="${escapeHtml(row.href)}" class="rounded-full bg-slate-50 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200 transition hover:text-[#8B0000]">Open</a>
                                </div>
                                <div class="mt-3 grid gap-2 sm:grid-cols-3 text-xs text-slate-500">
                                    <span class="rounded-2xl bg-slate-50 px-3 py-2">Attendance ${formatNumber(row.attendanceRate ?? 0, 1)}%</span>
                                    <span class="rounded-2xl bg-slate-50 px-3 py-2">Marks ${formatNumber(row.averageMarks ?? 0, 1)}%</span>
                                    <span class="rounded-2xl bg-slate-50 px-3 py-2">Pass ${formatNumber(row.passRate ?? 0, 1)}%</span>
                                </div>
                                ${Array.isArray(row.examMarks) && row.examMarks.length ? `
                                    <div class="mt-3 rounded-2xl bg-slate-50 px-4 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Marks per exam</p>
                                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                            ${row.examMarks.map((exam) => `
                                                <div class="rounded-xl bg-white px-3 py-3 ring-1 ring-slate-200">
                                                    <p class="text-xs font-bold text-slate-900">${escapeHtml(exam.exam)}</p>
                                                    <p class="mt-1 text-lg font-black text-slate-950">${formatNumber(exam.score, 1)}%</p>
                                                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">${escapeHtml(exam.status)}</p>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                    ${detail.pagination ? `
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 text-xs text-slate-500">
                            <span>Showing ${detail.pagination.from ?? 0} - ${detail.pagination.to ?? 0} of ${formatNumber(detail.pagination.total ?? 0, 0)} students</span>
                            <div class="flex items-center gap-2">
                                <button type="button" data-analytics-page-prev class="rounded-full bg-slate-50 px-3 py-1.5 font-bold text-slate-700 ring-1 ring-slate-200 transition hover:text-[#8B0000]" ${detail.pagination.currentPage <= 1 ? 'disabled' : ''}>Previous</button>
                                <button type="button" data-analytics-page-next class="rounded-full bg-slate-50 px-3 py-1.5 font-bold text-slate-700 ring-1 ring-slate-200 transition hover:text-[#8B0000]" ${detail.pagination.currentPage >= detail.pagination.lastPage ? 'disabled' : ''}>Next</button>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        const prevButton = detailPanel.querySelector('[data-analytics-page-prev]');
        const nextButton = detailPanel.querySelector('[data-analytics-page-next]');

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                if ((detail.pagination?.currentPage ?? 1) > 1) {
                    fetchDetailPage((detail.pagination.currentPage ?? 2) - 1);
                }
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                if ((detail.pagination?.currentPage ?? 1) < (detail.pagination?.lastPage ?? 1)) {
                    fetchDetailPage((detail.pagination.currentPage ?? 1) + 1);
                }
            });
        }
    };

    const applyState = (nextState) => {
        currentState = nextState;
        root.dataset.analyticsState = JSON.stringify(nextState);

        syncFilters(nextState);
        syncMetricButtons(nextState);
        syncHeadings(nextState);
        renderInsightPanel(nextState);
        syncCharts(nextState);

        if (nextState.detail) {
            if (detailSection) {
                detailSection.hidden = false;
            }

            renderDetailPanel(nextState.detail);
        } else {
            hideDetailSection();
        }

        setUrlState({
            metric: nextState.selectedMetric,
            sessionId: nextState.filters?.sessionId,
            departmentId: nextState.filters?.departmentId,
            programId: nextState.filters?.programId,
            detail: Boolean(nextState.filters?.detail),
            detailPage: nextState.filters?.detailPage,
        });
    };

    const fetchState = async (overrides = {}) => {
        if (activeRequest) {
            activeRequest.abort();
        }

        const controller = new AbortController();
        activeRequest = controller;
        setLoading(true);

        try {
            const requestUrl = buildRequestUrl(currentState, overrides);
            const response = await fetch(requestUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`Analytics request failed with status ${response.status}`);
            }

            const nextState = await response.json();
            applyState(nextState);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('[Analytics] Refresh failed:', error);
            }
        } finally {
            if (activeRequest === controller) {
                activeRequest = null;
                setLoading(false);
            }
        }
    };

    const fetchDetailPage = async (detailPage) => {
        if (detailRequest) {
            detailRequest.abort();
        }

        const controller = new AbortController();
        detailRequest = controller;

        try {
            const requestUrl = buildRequestUrl(currentState, {
                detail: true,
                detail_page: detailPage,
            });

            const response = await fetch(requestUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`Analytics detail request failed with status ${response.status}`);
            }

            const nextState = await response.json();
            applyState(nextState);

            if (detailSection) {
                detailSection.hidden = false;
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('[Analytics] Detail refresh failed:', error);
            }
        } finally {
            if (detailRequest === controller) {
                detailRequest = null;
            }
        }
    };

    const openDetails = () => {
        if (detailSection) {
            detailSection.hidden = false;
            detailSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        fetchDetailPage(1);
    };

    [sessionSelect, departmentSelect, programSelect].forEach((select) => {
        if (!select) {
            return;
        }

        select.addEventListener('change', () => {
            hideDetailSection();
            fetchState({
                metric: currentState.selectedMetric || 'attendance',
                session_id: sessionSelect?.value || '',
                department_id: departmentSelect?.value || '',
                program_id: programSelect?.value || '',
                detail: false,
                detail_page: 1,
            });
        });
    });

    metricButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const metric = button.dataset.metric || 'attendance';
            hideDetailSection();
            fetchState({
                metric,
                session_id: sessionSelect?.value || '',
                department_id: departmentSelect?.value || '',
                program_id: programSelect?.value || '',
                detail: false,
                detail_page: 1,
            });
        });
    });

    if (openDetailsButton) {
        openDetailsButton.addEventListener('click', openDetails);
    }

    hideDetailSection();
    applyState(currentState);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAnalyticsPage, { once: true });
} else {
    initializeAnalyticsPage();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePrincipalDashboard, { once: true });
} else {
    initializePrincipalDashboard();
}

Alpine.start();
