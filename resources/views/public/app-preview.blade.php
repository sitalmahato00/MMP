@extends('layouts.mobile-preview')

@section('title', 'Mobile App Preview')

@push('styles')
<style>
    html[data-preview-theme="light"] {
        color-scheme: light;
        --mmp-bg: #efe8dd;
        --mmp-bg-accent: #f7f1e7;
        --mmp-surface: rgba(255, 253, 248, 0.94);
        --mmp-surface-strong: #ffffff;
        --mmp-surface-soft: #f7f1e8;
        --mmp-ink: #102033;
        --mmp-muted: #627184;
        --mmp-line: #d9d0c4;
        --mmp-brand: #8b2332;
        --mmp-brand-soft: rgba(139, 35, 50, 0.12);
        --mmp-accent: #f0b24f;
        --mmp-success: #15744e;
        --mmp-warning: #a4631d;
        --mmp-danger: #b42318;
        --mmp-shadow: 0 24px 48px rgba(62, 38, 17, 0.12);
        --mmp-phone-shadow: 0 26px 56px rgba(18, 24, 34, 0.2);
    }

    html[data-preview-theme="dark"] {
        color-scheme: dark;
        --mmp-bg: #07111d;
        --mmp-bg-accent: #0f1827;
        --mmp-surface: rgba(17, 28, 44, 0.94);
        --mmp-surface-strong: #122033;
        --mmp-surface-soft: #16243a;
        --mmp-ink: #edf3ff;
        --mmp-muted: #9fb1c8;
        --mmp-line: #223248;
        --mmp-brand: #ff8d6a;
        --mmp-brand-soft: rgba(255, 141, 106, 0.16);
        --mmp-accent: #ffd27c;
        --mmp-success: #53d6a1;
        --mmp-warning: #ffbf63;
        --mmp-danger: #ff7d7d;
        --mmp-shadow: 0 24px 48px rgba(0, 0, 0, 0.28);
        --mmp-phone-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
    }

    body {
        margin: 0;
        background:
            radial-gradient(circle at top left, rgba(240, 178, 79, 0.18), transparent 30%),
            radial-gradient(circle at top right, rgba(139, 35, 50, 0.12), transparent 24%),
            linear-gradient(180deg, var(--mmp-bg-accent), var(--mmp-bg));
        color: var(--mmp-ink);
        font-family: "Segoe UI Variable Text", "Aptos", "SF Pro Display", "Poppins", sans-serif;
    }

    .mmp-preview-page {
        width: min(1260px, calc(100% - 32px));
        margin: 0 auto;
        padding: 28px 0 56px;
    }

    .mmp-preview-hero {
        padding: 24px;
        border: 1px solid var(--mmp-line);
        border-radius: 28px;
        background: linear-gradient(145deg, var(--mmp-surface-strong), var(--mmp-surface));
        box-shadow: var(--mmp-shadow);
    }

    .mmp-preview-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .mmp-preview-eyebrow,
    .mmp-preview-meta-pill,
    .mmp-preview-role-btn,
    .mmp-preview-theme-btn,
    .mmp-preview-link,
    .mmp-preview-chip,
    .mmp-preview-segment,
    .mmp-preview-scope,
    .mmp-preview-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--mmp-line);
        background: var(--mmp-surface-soft);
        font-size: 12px;
        font-weight: 700;
        color: var(--mmp-ink);
    }

    .mmp-preview-eyebrow {
        min-height: 34px;
        background: var(--mmp-brand-soft);
        border-color: transparent;
        color: var(--mmp-brand);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .mmp-preview-theme-btn {
        min-height: 44px;
        padding-inline: 16px;
        cursor: pointer;
    }

    .mmp-preview-hero h1 {
        margin: 18px 0 10px;
        font-family: Georgia, "Iowan Old Style", "Palatino Linotype", serif;
        font-size: clamp(28px, 4vw, 44px);
        line-height: 1.04;
    }

    .mmp-preview-lead {
        margin: 0;
        max-width: 780px;
        font-size: 15px;
        line-height: 1.7;
        color: var(--mmp-muted);
    }

    .mmp-preview-actions,
    .mmp-preview-meta,
    .mmp-preview-role-switch,
    .mmp-preview-row,
    .mmp-phone-chip-row,
    .mmp-phone-segment-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mmp-preview-actions,
    .mmp-preview-meta,
    .mmp-preview-role-switch {
        margin-top: 18px;
    }

    .mmp-preview-link {
        min-height: 44px;
        padding-inline: 16px;
        text-decoration: none;
    }

    .mmp-preview-link--primary {
        background: var(--mmp-brand);
        border-color: transparent;
        color: #fff;
    }

    .mmp-preview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 420px;
        gap: 22px;
        margin-top: 24px;
        align-items: start;
    }

    .mmp-preview-panel {
        padding: 20px;
        border: 1px solid var(--mmp-line);
        border-radius: 24px;
        background: var(--mmp-surface);
        box-shadow: var(--mmp-shadow);
    }

    .mmp-preview-panel h2 {
        margin: 0 0 10px;
        font-size: 18px;
    }

    .mmp-preview-panel p,
    .mmp-preview-panel li {
        margin: 0;
        font-size: 14px;
        line-height: 1.65;
        color: var(--mmp-muted);
    }

    .mmp-preview-notes {
        display: grid;
        gap: 14px;
        margin-top: 18px;
    }

    .mmp-preview-note {
        padding: 16px;
        border: 1px solid var(--mmp-line);
        border-radius: 20px;
        background: linear-gradient(180deg, var(--mmp-surface-strong), var(--mmp-surface));
    }

    .mmp-preview-note h3 {
        margin: 0 0 8px;
        font-size: 15px;
    }

    .mmp-preview-role-btn {
        min-height: 40px;
        padding-inline: 14px;
        cursor: pointer;
        transition: 160ms ease;
    }

    .mmp-preview-role-btn.is-active {
        background: var(--mmp-brand-soft);
        border-color: transparent;
        color: var(--mmp-brand);
        transform: translateY(-1px);
    }

    .mmp-phone {
        width: 100%;
        max-width: 390px;
        margin-left: auto;
        padding: 10px;
        border-radius: 34px;
        background: linear-gradient(180deg, #1f2937, #0b1220);
        box-shadow: var(--mmp-phone-shadow);
    }

    .mmp-phone-screen {
        height: 760px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 26px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background:
            radial-gradient(circle at top right, rgba(240, 178, 79, 0.12), transparent 26%),
            linear-gradient(180deg, var(--mmp-surface-strong), var(--mmp-surface-soft));
    }

    html[data-preview-theme="dark"] .mmp-phone-screen {
        background:
            radial-gradient(circle at top right, rgba(255, 141, 106, 0.12), transparent 26%),
            linear-gradient(180deg, #122033, #0d1727);
    }

    .mmp-phone-status {
        display: flex;
        justify-content: space-between;
        padding: 12px 16px 4px;
        font-size: 12px;
        font-weight: 800;
    }

    .mmp-phone-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 8px 16px 14px;
    }

    .mmp-phone-topbar strong {
        display: block;
        font-size: 18px;
    }

    .mmp-phone-topbar span {
        display: block;
        margin-top: 4px;
        color: var(--mmp-muted);
        font-size: 12px;
    }

    .mmp-phone-icons {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mmp-phone-circle,
    .mmp-phone-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid var(--mmp-line);
        background: var(--mmp-surface);
        font-size: 11px;
        font-weight: 800;
        color: var(--mmp-ink);
    }

    .mmp-phone-circle {
        position: relative;
    }

    .mmp-phone-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        padding: 0 6px;
        border-radius: 999px;
        background: var(--mmp-brand);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        line-height: 18px;
        text-align: center;
    }

    .mmp-phone-body {
        flex: 1;
        padding: 0 14px;
        overflow: auto;
    }

    .mmp-phone-body::-webkit-scrollbar,
    .mmp-phone-list::-webkit-scrollbar,
    .mmp-phone-chip-row::-webkit-scrollbar,
    .mmp-phone-segment-row::-webkit-scrollbar {
        display: none;
    }

    .mmp-phone-hero,
    .mmp-phone-summary,
    .mmp-phone-module,
    .mmp-phone-sheet {
        border: 1px solid var(--mmp-line);
        border-radius: 20px;
        background: var(--mmp-surface);
    }

    .mmp-phone-hero {
        padding: 16px;
        color: #fff8f5;
        background: linear-gradient(135deg, rgba(139, 35, 50, 0.96), rgba(61, 20, 29, 0.98));
    }

    html[data-preview-theme="dark"] .mmp-phone-hero {
        background:
            linear-gradient(135deg, rgba(255, 141, 106, 0.16), rgba(255, 210, 124, 0.08)),
            #16243a;
        color: var(--mmp-ink);
    }

    .mmp-phone-hero h3 {
        margin: 0;
        font-size: 18px;
    }

    .mmp-phone-hero p {
        margin: 10px 0 0;
        font-size: 13px;
        line-height: 1.6;
        color: rgba(255, 248, 245, 0.84);
    }

    html[data-preview-theme="dark"] .mmp-phone-hero p {
        color: var(--mmp-muted);
    }

    .mmp-phone-chip-row,
    .mmp-phone-segment-row {
        overflow: auto;
        flex-wrap: nowrap;
        padding-top: 12px;
        scrollbar-width: none;
    }

    .mmp-phone-chip-row {
        gap: 8px;
    }

    .mmp-preview-chip,
    .mmp-preview-segment,
    .mmp-phone-chip,
    .mmp-phone-segment {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--mmp-line);
        background: var(--mmp-surface-soft);
        color: var(--mmp-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .mmp-preview-chip.is-active,
    .mmp-preview-segment.is-active,
    .mmp-phone-chip.is-active,
    .mmp-phone-segment.is-active {
        border-color: transparent;
        background: var(--mmp-brand-soft);
        color: var(--mmp-brand);
    }

    .mmp-phone-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 12px;
        overflow: hidden;
    }

    .mmp-phone-summary-cell {
        padding: 12px 10px;
        border-right: 1px solid var(--mmp-line);
    }

    .mmp-phone-summary-cell:last-child {
        border-right: 0;
    }

    .mmp-phone-summary-cell strong {
        display: block;
        font-size: 16px;
    }

    .mmp-phone-summary-cell span {
        display: block;
        margin-top: 4px;
        color: var(--mmp-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .mmp-phone-search {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        min-height: 44px;
        padding: 0 12px;
        border: 1px solid var(--mmp-line);
        border-radius: 16px;
        background: var(--mmp-surface);
        color: var(--mmp-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .mmp-phone-module {
        margin-top: 12px;
        overflow: hidden;
    }

    .mmp-phone-module-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 14px 10px;
    }

    .mmp-phone-module-head h4 {
        margin: 0;
        font-size: 14px;
    }

    .mmp-phone-module-head span {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--mmp-muted);
    }

    .mmp-phone-list {
        max-height: 252px;
        overflow: auto;
        border-top: 1px solid var(--mmp-line);
        scrollbar-width: none;
    }

    .mmp-phone-table-head,
    .mmp-phone-table-row {
        display: grid;
        grid-template-columns: .9fr 1.8fr .8fr .8fr;
        gap: 8px;
        align-items: center;
        min-height: 48px;
        padding: 0 12px;
    }

    .mmp-phone-table-head {
        position: sticky;
        top: 0;
        z-index: 2;
        border-bottom: 1px solid var(--mmp-line);
        background: var(--mmp-surface-soft);
        color: var(--mmp-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .mmp-phone-table-row {
        border-bottom: 1px solid var(--mmp-line);
        font-size: 14px;
    }

    .mmp-phone-table-row:last-child {
        border-bottom: 0;
    }

    .mmp-phone-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mmp-phone-muted {
        color: var(--mmp-muted);
    }

    .mmp-preview-scope,
    .mmp-phone-scope {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        background: var(--mmp-surface-soft);
        color: var(--mmp-brand);
    }

    .mmp-phone-scope.is-high {
        color: var(--mmp-danger);
        background: rgba(180, 35, 24, 0.12);
    }

    .mmp-phone-scope.is-low {
        color: var(--mmp-success);
        background: rgba(21, 116, 78, 0.12);
    }

    .mmp-phone-actions {
        display: inline-flex;
        justify-content: flex-end;
        gap: 6px;
    }

    .mmp-phone-action {
        min-width: 28px;
        height: 28px;
        border-radius: 10px;
    }

    .mmp-phone-sheet {
        margin-top: 16px;
        border-radius: 24px 24px 0 0;
        padding: 14px 14px 18px;
    }

    .mmp-phone-sheet-handle {
        width: 52px;
        height: 5px;
        margin: 0 auto 12px;
        border-radius: 999px;
        background: var(--mmp-line);
    }

    .mmp-phone-sheet-grid {
        display: grid;
        gap: 10px;
    }

    .mmp-phone-bottom-nav {
        display: grid;
        gap: 4px;
        padding: 10px 8px calc(14px + env(safe-area-inset-bottom, 0px));
        border-top: 1px solid var(--mmp-line);
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(12px);
    }

    html[data-preview-theme="dark"] .mmp-phone-bottom-nav {
        background: rgba(12, 19, 31, 0.86);
    }

    .mmp-phone-bottom-nav.is-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .mmp-phone-bottom-nav.is-5 {
        grid-template-columns: repeat(5, 1fr);
    }

    .mmp-phone-nav-item {
        display: grid;
        justify-items: center;
        gap: 6px;
        color: var(--mmp-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .mmp-phone-nav-item.is-active {
        color: var(--mmp-brand);
    }

    .mmp-phone-nav-dot {
        width: 26px;
        height: 26px;
        border-radius: 10px;
        border: 1px solid transparent;
        background: transparent;
    }

    .mmp-phone-nav-item.is-active .mmp-phone-nav-dot {
        background: var(--mmp-brand-soft);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .mmp-phone-fab {
        position: absolute;
        right: 24px;
        bottom: 94px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: var(--mmp-brand);
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        box-shadow: 0 18px 28px rgba(139, 35, 50, 0.34);
    }

    .mmp-phone-screen-wrap {
        position: relative;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .mmp-phone-notification-row {
        display: grid;
        grid-template-columns: 28px 1fr auto;
        gap: 10px;
        align-items: start;
        min-height: 58px;
        padding: 12px;
        border-bottom: 1px solid var(--mmp-line);
    }

    .mmp-phone-notification-row:last-child {
        border-bottom: 0;
    }

    .mmp-phone-notif-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 10px;
        background: var(--mmp-brand-soft);
        color: var(--mmp-brand);
        font-size: 11px;
        font-weight: 800;
    }

    .mmp-phone-notif-text strong {
        display: block;
        font-size: 14px;
    }

    .mmp-phone-notif-text p {
        margin: 4px 0 0;
        color: var(--mmp-muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .mmp-phone-notif-time {
        display: grid;
        justify-items: end;
        gap: 8px;
        color: var(--mmp-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .mmp-phone-unread-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--mmp-brand);
    }

    @media (max-width: 1024px) {
        .mmp-preview-grid {
            grid-template-columns: 1fr;
        }

        .mmp-phone {
            margin-inline: auto;
        }
    }

    @media (max-width: 720px) {
        .mmp-preview-page {
            width: min(100% - 20px, 100%);
            padding-top: 18px;
        }

        .mmp-preview-hero,
        .mmp-preview-panel {
            padding: 18px;
            border-radius: 24px;
        }

        .mmp-preview-role-switch {
            overflow: auto;
            flex-wrap: nowrap;
            scrollbar-width: none;
        }
    }
</style>
@endpush

@section('content')
<div
    x-data="{
        role: 'student',
        theme: localStorage.getItem('mmp:app-preview-theme') || ((window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light'),
        init() {
            this.applyTheme(this.theme);
        },
        setRole(nextRole) {
            this.role = nextRole;
        },
        applyTheme(nextTheme) {
            this.theme = nextTheme;
            document.documentElement.setAttribute('data-preview-theme', nextTheme);
            document.body.setAttribute('data-preview-theme', nextTheme);
            localStorage.setItem('mmp:app-preview-theme', nextTheme);

            const meta = document.getElementById('preview-theme-color');
            if (meta) {
                meta.setAttribute('content', nextTheme === 'dark' ? '#07111d' : '#8b2332');
            }
        },
        toggleTheme() {
            this.applyTheme(this.theme === 'dark' ? 'light' : 'dark');
        },
        isRole(name) {
            return this.role === name;
        }
    }"
    x-init="init()"
    class="mmp-preview-page"
>
    <section class="mmp-preview-hero">
        <div class="mmp-preview-topline">
            <div class="mmp-preview-eyebrow">Live mobile app shell preview</div>

            <button type="button" class="mmp-preview-theme-btn" @click="toggleTheme()">
                <span x-show="theme === 'light'">Switch to dark mode</span>
                <span x-show="theme === 'dark'" x-cloak>Switch to light mode</span>
            </button>
        </div>

        <h1>Role-based academic PWA preview inside Laravel, with compact lists and native-style navigation.</h1>
        <p class="mmp-preview-lead">
            This route continues the blueprint work by turning the concept into a reviewable app shell. It keeps the mobile-first rules strict:
            bottom navigation, dense tabular lists instead of bulky cards, dark mode, notification-first behavior, and scoped feeds per role.
        </p>

        <div class="mmp-preview-actions">
            <a href="{{ asset('mockups/mobile-pwa/index.html') }}" class="mmp-preview-link mmp-preview-link--primary" target="_blank" rel="noopener">Open static multi-screen board</a>
            <a href="{{ route('home') }}" class="mmp-preview-link">Back to public site</a>
            <a href="{{ route('login') }}" class="mmp-preview-link">Login flow</a>
        </div>

        <div class="mmp-preview-meta">
            <div class="mmp-preview-meta-pill">360px to 430px first</div>
            <div class="mmp-preview-meta-pill">Bottom nav only</div>
            <div class="mmp-preview-meta-pill">Dark mode included</div>
            <div class="mmp-preview-meta-pill">Notification center included</div>
        </div>
    </section>

    <section class="mmp-preview-grid">
        <div class="mmp-preview-panel">
            <h2>What this page is proving</h2>
            <p>
                The current portal is functional, but its main authenticated shell still leans desktop-first. This preview gives us a concrete path toward a mobile-native PWA
                without pretending that the existing sidebar-driven experience is already mobile-ready.
            </p>

            <div class="mmp-preview-role-switch" role="tablist" aria-label="Preview roles">
                <button type="button" class="mmp-preview-role-btn" :class="{ 'is-active': isRole('public') }" @click="setRole('public')">Public</button>
                <button type="button" class="mmp-preview-role-btn" :class="{ 'is-active': isRole('admin') }" @click="setRole('admin')">Admin</button>
                <button type="button" class="mmp-preview-role-btn" :class="{ 'is-active': isRole('teacher') }" @click="setRole('teacher')">Teacher</button>
                <button type="button" class="mmp-preview-role-btn" :class="{ 'is-active': isRole('student') }" @click="setRole('student')">Student</button>
                <button type="button" class="mmp-preview-role-btn" :class="{ 'is-active': isRole('parent') }" @click="setRole('parent')">Parent</button>
                <button type="button" class="mmp-preview-role-btn" :class="{ 'is-active': isRole('notifications') }" @click="setRole('notifications')">Inbox</button>
            </div>

            <div class="mmp-preview-notes">
                <article class="mmp-preview-note">
                    <h3>Compact table behavior</h3>
                    <p>Each list keeps 3 to 4 visible columns, sticky headers, 14px to 15px text, trailing icon actions, and overflow handled with ellipsis instead of layout breakage.</p>
                </article>

                <article class="mmp-preview-note">
                    <h3>Strict filtering principle</h3>
                    <p>Student and parent feeds are scoped before rendering. Department, program, semester, and child context are not optional filters. They are hard gates.</p>
                </article>

                <article class="mmp-preview-note">
                    <h3>Notification stance</h3>
                    <p>Results and exams interrupt fast. Notices remain timely but calmer. Resource pushes can be grouped or silent. Every row deep-links into a target screen.</p>
                </article>

                <article class="mmp-preview-note">
                    <h3>Next engineering move</h3>
                    <p>The strongest next step after this preview is a dedicated `/app` SPA shell with API-backed screens, keeping the public marketing site separate from the mobile portal experience.</p>
                </article>
            </div>
        </div>

        <div class="mmp-phone" aria-label="Mobile PWA preview device">
            <div class="mmp-phone-screen">
                <div class="mmp-phone-status">
                    <span>9:41</span>
                    <span x-text="theme === 'dark' ? '5G 82%' : 'LTE 91%'">LTE 91%</span>
                </div>

                <div class="mmp-phone-screen-wrap">
                    <section x-show="isRole('public')" x-cloak class="h-full flex flex-col">
                        <div class="mmp-phone-topbar">
                            <div>
                                <strong>MMP App</strong>
                                <span>Guest mode</span>
                            </div>
                            <div class="mmp-phone-icons">
                                <span class="mmp-phone-circle">I</span>
                                <span class="mmp-phone-circle">N</span>
                            </div>
                        </div>

                        <div class="mmp-phone-body">
                            <div class="mmp-phone-hero">
                                <h3>Install the campus app</h3>
                                <p>Get notices, admissions updates, and result alerts with fast app-style navigation even on weak connections.</p>
                                <div class="mmp-phone-chip-row">
                                    <span class="mmp-phone-chip is-active">Install</span>
                                    <span class="mmp-phone-chip">Login</span>
                                    <span class="mmp-phone-chip">View notices</span>
                                </div>
                            </div>

                            <div class="mmp-phone-summary">
                                <div class="mmp-phone-summary-cell"><strong>27</strong><span>Live notices</span></div>
                                <div class="mmp-phone-summary-cell"><strong>4</strong><span>Admissions</span></div>
                                <div class="mmp-phone-summary-cell"><strong>3</strong><span>Quick links</span></div>
                            </div>

                            <div class="mmp-phone-search">Search notices, resources, results</div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Latest notices</h4>
                                    <span>Compact list</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Scope</div>
                                        <div>Title</div>
                                        <div>Age</div>
                                        <div>Open</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">College</span></div>
                                        <div class="mmp-phone-truncate">Scholarship interview schedule</div>
                                        <div class="mmp-phone-muted">2h</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">Exam</span></div>
                                        <div class="mmp-phone-truncate">Entrance admit card download</div>
                                        <div class="mmp-phone-muted">5h</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">Dept</span></div>
                                        <div class="mmp-phone-truncate">Architecture studio orientation</div>
                                        <div class="mmp-phone-muted">1d</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">About</span></div>
                                        <div class="mmp-phone-truncate">Campus visit and contact hours</div>
                                        <div class="mmp-phone-muted">2d</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mmp-phone-sheet">
                                <div class="mmp-phone-sheet-handle"></div>
                                <div class="mmp-phone-sheet-grid">
                                    <span class="mmp-phone-chip">Admissions</span>
                                    <span class="mmp-phone-chip">Programs</span>
                                    <span class="mmp-phone-chip">Result check</span>
                                </div>
                            </div>
                        </div>

                        <div class="mmp-phone-bottom-nav is-4">
                            <div class="mmp-phone-nav-item is-active"><span class="mmp-phone-nav-dot"></span><span>Home</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Notices</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>About</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Login</span></div>
                        </div>
                    </section>

                    <section x-show="isRole('admin')" x-cloak class="h-full flex flex-col">
                        <div class="mmp-phone-topbar">
                            <div>
                                <strong>Admin Dashboard</strong>
                                <span>Today overview</span>
                            </div>
                            <div class="mmp-phone-icons">
                                <span class="mmp-phone-circle">B<span class="mmp-phone-badge">6</span></span>
                                <span class="mmp-phone-circle">A</span>
                            </div>
                        </div>

                        <div class="mmp-phone-body">
                            <div class="mmp-phone-summary">
                                <div class="mmp-phone-summary-cell"><strong>512</strong><span>Users</span></div>
                                <div class="mmp-phone-summary-cell"><strong>9</strong><span>Urgent</span></div>
                                <div class="mmp-phone-summary-cell"><strong>14</strong><span>Drafts</span></div>
                            </div>

                            <div class="mmp-phone-segment-row">
                                <span class="mmp-phone-segment is-active">Users</span>
                                <span class="mmp-phone-segment">Notices</span>
                                <span class="mmp-phone-segment">Resources</span>
                                <span class="mmp-phone-segment">Alerts</span>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>User management</h4>
                                    <span>Dense table</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Name</div>
                                        <div>Role</div>
                                        <div>Dept</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Sita Rai</div>
                                        <div class="mmp-phone-truncate">Teacher</div>
                                        <div class="mmp-phone-muted">IT</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span><span class="mmp-phone-action">E</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Rajan Karki</div>
                                        <div class="mmp-phone-truncate">Student</div>
                                        <div class="mmp-phone-muted">Civil</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span><span class="mmp-phone-action">E</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Asha Sharma</div>
                                        <div class="mmp-phone-truncate">Parent</div>
                                        <div class="mmp-phone-muted">Mech</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span><span class="mmp-phone-action">E</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Nabin Tamang</div>
                                        <div class="mmp-phone-truncate">Admin</div>
                                        <div class="mmp-phone-muted">All</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span><span class="mmp-phone-action">E</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Urgent queue</h4>
                                    <span>Priority first</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Type</div>
                                        <div>Issue</div>
                                        <div>Age</div>
                                        <div>Fix</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope is-high">High</span></div>
                                        <div class="mmp-phone-truncate">Semester 4 result push pending</div>
                                        <div class="mmp-phone-muted">8m</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">Med</span></div>
                                        <div class="mmp-phone-truncate">3 notices waiting for publish review</div>
                                        <div class="mmp-phone-muted">22m</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope is-low">Low</span></div>
                                        <div class="mmp-phone-truncate">Resource tags missing on 5 uploads</div>
                                        <div class="mmp-phone-muted">1h</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mmp-phone-fab">+</div>

                        <div class="mmp-phone-bottom-nav is-5">
                            <div class="mmp-phone-nav-item is-active"><span class="mmp-phone-nav-dot"></span><span>Dash</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Users</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Notices</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Res</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Set</span></div>
                        </div>
                    </section>

                    <section x-show="isRole('teacher')" x-cloak class="h-full flex flex-col">
                        <div class="mmp-phone-topbar">
                            <div>
                                <strong>Teacher Workspace</strong>
                                <span>3 classes today</span>
                            </div>
                            <div class="mmp-phone-icons">
                                <span class="mmp-phone-circle">B<span class="mmp-phone-badge">3</span></span>
                                <span class="mmp-phone-circle">P</span>
                            </div>
                        </div>

                        <div class="mmp-phone-body">
                            <div class="mmp-phone-summary">
                                <div class="mmp-phone-summary-cell"><strong>3</strong><span>Today</span></div>
                                <div class="mmp-phone-summary-cell"><strong>2</strong><span>Upload</span></div>
                                <div class="mmp-phone-summary-cell"><strong>1</strong><span>Exam alert</span></div>
                            </div>

                            <div class="mmp-phone-search">Search subject, notice, student</div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Classes</h4>
                                    <span>Tap row for roster</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Subject</div>
                                        <div>Section</div>
                                        <div>Next</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">DBMS</div>
                                        <div class="mmp-phone-truncate">DIT-4A</div>
                                        <div class="mmp-phone-muted">10:15</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Web Tech</div>
                                        <div class="mmp-phone-truncate">DIT-4B</div>
                                        <div class="mmp-phone-muted">12:30</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Lab</div>
                                        <div class="mmp-phone-truncate">DIT-2A</div>
                                        <div class="mmp-phone-muted">14:00</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Resource queue</h4>
                                    <span>Scoped upload list</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Subj</div>
                                        <div>File</div>
                                        <div>Sem</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">DBMS</div>
                                        <div class="mmp-phone-truncate">Normalization notes</div>
                                        <div class="mmp-phone-muted">4</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">E</span><span class="mmp-phone-action">D</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Web</div>
                                        <div class="mmp-phone-truncate">REST API handout</div>
                                        <div class="mmp-phone-muted">4</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">E</span><span class="mmp-phone-action">D</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Lab</div>
                                        <div class="mmp-phone-truncate">SQL practice sheet</div>
                                        <div class="mmp-phone-muted">2</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">E</span><span class="mmp-phone-action">D</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mmp-phone-fab">+</div>

                        <div class="mmp-phone-bottom-nav is-5">
                            <div class="mmp-phone-nav-item is-active"><span class="mmp-phone-nav-dot"></span><span>Dash</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Class</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Notes</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Res</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Prof</span></div>
                        </div>
                    </section>

                    <section x-show="isRole('student')" x-cloak class="h-full flex flex-col">
                        <div class="mmp-phone-topbar">
                            <div>
                                <strong>Student Home</strong>
                                <span>DIT, Semester 4, Section A</span>
                            </div>
                            <div class="mmp-phone-icons">
                                <span class="mmp-phone-circle">B<span class="mmp-phone-badge">4</span></span>
                                <span class="mmp-phone-circle">S</span>
                            </div>
                        </div>

                        <div class="mmp-phone-body">
                            <div class="mmp-phone-hero">
                                <h3>Filtered to your semester only</h3>
                                <p>College notices still appear, but department, program, and semester items only show when they match your active academic context.</p>
                                <div class="mmp-phone-chip-row">
                                    <span class="mmp-phone-chip is-active">College</span>
                                    <span class="mmp-phone-chip is-active">IT</span>
                                    <span class="mmp-phone-chip is-active">DIT</span>
                                    <span class="mmp-phone-chip is-active">Sem 4</span>
                                </div>
                            </div>

                            <div class="mmp-phone-summary">
                                <div class="mmp-phone-summary-cell"><strong>2</strong><span>Exam alerts</span></div>
                                <div class="mmp-phone-summary-cell"><strong>7</strong><span>Unread</span></div>
                                <div class="mmp-phone-summary-cell"><strong>9</strong><span>Resources</span></div>
                            </div>

                            <div class="mmp-phone-segment-row">
                                <span class="mmp-phone-segment is-active">Notices</span>
                                <span class="mmp-phone-segment">Resources</span>
                                <span class="mmp-phone-segment">Results</span>
                                <span class="mmp-phone-segment">Exams</span>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Notices</h4>
                                    <span>No irrelevant data</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Type</div>
                                        <div>Title</div>
                                        <div>Age</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">College</span></div>
                                        <div class="mmp-phone-truncate">Library timing revised for final prep</div>
                                        <div class="mmp-phone-muted">3h</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">Prog</span></div>
                                        <div class="mmp-phone-truncate">DIT semester 4 project review slots</div>
                                        <div class="mmp-phone-muted">7h</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope is-high">Exam</span></div>
                                        <div class="mmp-phone-truncate">Midterm seating plan updated</div>
                                        <div class="mmp-phone-muted">1d</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Resources</h4>
                                    <span>Program + semester scoped</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Subj</div>
                                        <div>File</div>
                                        <div>Size</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">DBMS</div>
                                        <div class="mmp-phone-truncate">Normalization notes</div>
                                        <div class="mmp-phone-muted">1.4MB</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Web</div>
                                        <div class="mmp-phone-truncate">REST API handout</div>
                                        <div class="mmp-phone-muted">880KB</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">DBMS</div>
                                        <div class="mmp-phone-truncate">Lab worksheet 03</div>
                                        <div class="mmp-phone-muted">540KB</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mmp-phone-bottom-nav is-4">
                            <div class="mmp-phone-nav-item is-active"><span class="mmp-phone-nav-dot"></span><span>Home</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Notes</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Res</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Prof</span></div>
                        </div>
                    </section>

                    <section x-show="isRole('parent')" x-cloak class="h-full flex flex-col">
                        <div class="mmp-phone-topbar">
                            <div>
                                <strong>Parent Dashboard</strong>
                                <span>Child context locked</span>
                            </div>
                            <div class="mmp-phone-icons">
                                <span class="mmp-phone-circle">B<span class="mmp-phone-badge">2</span></span>
                                <span class="mmp-phone-circle">P</span>
                            </div>
                        </div>

                        <div class="mmp-phone-body">
                            <div class="mmp-phone-hero">
                                <h3>Viewing: Anish Rai</h3>
                                <p>Diploma in Information Technology, Semester 4. Parent feeds must always bind to the selected child and never mix siblings by default.</p>
                            </div>

                            <div class="mmp-phone-segment-row">
                                <span class="mmp-phone-segment is-active">Anish Rai</span>
                                <span class="mmp-phone-segment">Mina Rai</span>
                            </div>

                            <div class="mmp-phone-summary">
                                <div class="mmp-phone-summary-cell"><strong>92%</strong><span>Attendance</span></div>
                                <div class="mmp-phone-summary-cell"><strong>1</strong><span>Result</span></div>
                                <div class="mmp-phone-summary-cell"><strong>3</strong><span>Unread</span></div>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Children</h4>
                                    <span>Switch context</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Child</div>
                                        <div>Program</div>
                                        <div>Sem</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Anish Rai</div>
                                        <div class="mmp-phone-truncate">DIT</div>
                                        <div class="mmp-phone-muted">4</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">On</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div class="mmp-phone-truncate">Mina Rai</div>
                                        <div class="mmp-phone-truncate">Civil</div>
                                        <div class="mmp-phone-muted">2</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">Go</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Child notices</h4>
                                    <span>Selected child only</span>
                                </div>
                                <div class="mmp-phone-list">
                                    <div class="mmp-phone-table-head">
                                        <div>Type</div>
                                        <div>Title</div>
                                        <div>Age</div>
                                        <div>Act</div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope is-high">Result</span></div>
                                        <div class="mmp-phone-truncate">Internal result published</div>
                                        <div class="mmp-phone-muted">21m</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">Prog</span></div>
                                        <div class="mmp-phone-truncate">Parent meeting for DIT semester 4</div>
                                        <div class="mmp-phone-muted">5h</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                    <div class="mmp-phone-table-row">
                                        <div><span class="mmp-phone-scope">College</span></div>
                                        <div class="mmp-phone-truncate">Transportation route update</div>
                                        <div class="mmp-phone-muted">1d</div>
                                        <div class="mmp-phone-actions"><span class="mmp-phone-action">V</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mmp-phone-bottom-nav is-4">
                            <div class="mmp-phone-nav-item is-active"><span class="mmp-phone-nav-dot"></span><span>Dash</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Kids</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Notes</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Prof</span></div>
                        </div>
                    </section>

                    <section x-show="isRole('notifications')" x-cloak class="h-full flex flex-col">
                        <div class="mmp-phone-topbar">
                            <div>
                                <strong>Notifications</strong>
                                <span>Unread first, compact rows</span>
                            </div>
                            <div class="mmp-phone-icons">
                                <span class="mmp-phone-circle">All</span>
                                <span class="mmp-phone-circle">U</span>
                            </div>
                        </div>

                        <div class="mmp-phone-body">
                            <div class="mmp-phone-segment-row">
                                <span class="mmp-phone-segment is-active">All</span>
                                <span class="mmp-phone-segment">Unread</span>
                                <span class="mmp-phone-segment">High priority</span>
                            </div>

                            <div class="mmp-phone-module">
                                <div class="mmp-phone-module-head">
                                    <h4>Inbox</h4>
                                    <span>Mark all read</span>
                                </div>
                                <div class="mmp-phone-list" style="max-height: 560px;">
                                    <div class="mmp-phone-notification-row">
                                        <div class="mmp-phone-notif-icon">RS</div>
                                        <div class="mmp-phone-notif-text">
                                            <strong>Result published</strong>
                                            <p>Your semester 4 internal result is now available. Tap to open the result screen.</p>
                                        </div>
                                        <div class="mmp-phone-notif-time"><span>5m</span><span class="mmp-phone-unread-dot"></span></div>
                                    </div>
                                    <div class="mmp-phone-notification-row">
                                        <div class="mmp-phone-notif-icon">EX</div>
                                        <div class="mmp-phone-notif-text">
                                            <strong>Exam schedule updated</strong>
                                            <p>Midterm practical timing changed for the IT department and semester 4 cohort.</p>
                                        </div>
                                        <div class="mmp-phone-notif-time"><span>24m</span><span class="mmp-phone-unread-dot"></span></div>
                                    </div>
                                    <div class="mmp-phone-notification-row">
                                        <div class="mmp-phone-notif-icon">NT</div>
                                        <div class="mmp-phone-notif-text">
                                            <strong>Department notice</strong>
                                            <p>Project review panel list is now published for your program.</p>
                                        </div>
                                        <div class="mmp-phone-notif-time"><span>2h</span><span class="mmp-phone-unread-dot"></span></div>
                                    </div>
                                    <div class="mmp-phone-notification-row">
                                        <div class="mmp-phone-notif-icon">RE</div>
                                        <div class="mmp-phone-notif-text">
                                            <strong>New resource</strong>
                                            <p>REST API handout uploaded to the semester 4 resource list.</p>
                                        </div>
                                        <div class="mmp-phone-notif-time"><span>6h</span><span></span></div>
                                    </div>
                                    <div class="mmp-phone-notification-row">
                                        <div class="mmp-phone-notif-icon">SY</div>
                                        <div class="mmp-phone-notif-text">
                                            <strong>System alert</strong>
                                            <p>Maintenance window tonight from 11:00 PM to 11:30 PM. Low-priority push will be silent.</p>
                                        </div>
                                        <div class="mmp-phone-notif-time"><span>1d</span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mmp-phone-bottom-nav is-4">
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Home</span></div>
                            <div class="mmp-phone-nav-item is-active"><span class="mmp-phone-nav-dot"></span><span>Inbox</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Filter</span></div>
                            <div class="mmp-phone-nav-item"><span class="mmp-phone-nav-dot"></span><span>Profile</span></div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
