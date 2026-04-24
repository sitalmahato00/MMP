# MMP Mobile PWA Blueprint

This document defines the production target for an installable, mobile-first MMP academic system that behaves like a native app instead of a traditional website.

It is grounded in the current repository:

- Backend: Laravel 12
- Frontend today: Blade + Tailwind 4 + Alpine
- Existing PWA files: `public/manifest.json`, `public/sw.js`
- Existing notification layer: Laravel database notifications

## Product Stance

The mobile app must reject common web-first mistakes:

- No desktop sidebar as the primary navigation on mobile.
- No cardifying tables that are naturally tabular.
- No mixed-scope feeds where unrelated notices or resources leak into the wrong role.
- No full-page spinner walls; use skeletons.
- No multi-column forms on 360px screens.
- No navigation that requires precision taps in the top corners for core tasks.

## Recommended Architecture

The cleanest production shape is:

- Public marketing pages can remain web-oriented.
- The installable role-based experience should live in a dedicated mobile app shell.
- Keep Laravel as the system backend and source of truth.
- Build the mobile shell as an SPA with API-backed screens so navigation feels native.

Recommended stack:

- Laravel 12 API + Sanctum session auth
- React + TypeScript + Vite for the PWA shell
- Tailwind 4 with CSS variables for light/dark tokens
- React Router for app navigation
- TanStack Query for cache + revalidation
- Laravel queues for notification fan-out
- Laravel Reverb or WebSockets for real-time in-app notification delivery
- Web Push with VAPID for background push when the app is closed

If the team wants a smaller migration from the current codebase, use the same backend but introduce the mobile shell under `/app` while leaving the current Blade public site and desktop-heavy admin views intact.

## Global Mobile Rules

- Design for 360px to 430px widths first.
- Safe areas must be respected on iPhone and Android devices.
- Every primary tap target must be at least 44px.
- Content width must never require horizontal scrolling except for intentionally scrollable dense tables.
- Top app bar height: 56px.
- Bottom navigation height: 64px to 72px including safe area padding.
- Motion: 220ms to 280ms, ease-out for screen transitions, spring for bottom sheets.

## Navigation Model

Authenticated navigation is always a fixed bottom bar plus a top app bar.

Public:

- Home
- Notices
- About
- Login

Admin:

- Dashboard
- Users
- Notices
- Resources
- Settings

Teacher:

- Dashboard
- Classes
- Notices
- Resources
- Profile

Student:

- Home
- Notices
- Resources
- Profile

Parent:

- Dashboard
- Children
- Notices
- Profile

Shared top bar actions:

- Notification bell with unread badge
- Search entry on list-heavy screens
- Context menu or avatar

## Screen Blueprints By Role

### Public

Landing:

- Minimal hero with college identity, install CTA, notice summary, login CTA.
- One primary action: `Login`.
- One secondary action: `View Notices`.

Notices:

- Dense list with sticky filter chips: `All`, `College`, `Department`, `Exam`, `Result`.
- Each row shows icon, scope label, title, age, and chevron.

About:

- Short institutional blocks, accreditation, contact, campus facts.
- Avoid long paragraphs without section anchors.

Login/Register:

- Single-column, thumb-friendly form.
- Password reveal toggle.
- Device trust copy kept short.

### Admin

Dashboard:

- KPI strip: users, unpublished notices, pending resources, system alerts.
- Urgent queue list: exam updates, failed jobs, unread high-priority alerts.
- Recent activity list with compact rows, not large widgets.

Users:

- Compact table with columns: `Name`, `Role`, `Dept`, `State`.
- Row tap opens detail sheet.
- Trailing icon actions: view, edit, suspend.

Notices:

- Columns: `Scope`, `Title`, `Status`, `Age`.
- FAB: `New Notice`.
- Publish toggle must be explicit; do not auto-publish on save.

Resources:

- Columns: `File`, `Scope`, `By`, `State`.
- FAB: `Upload`.
- Filter sheet: department, program, semester, category, uploader.

Settings:

- Notification channels
- Theme mode
- Branding
- Role permissions
- Audit shortcuts

### Teacher

Dashboard:

- Today classes
- Pending uploads
- Exam reminders
- Latest notices

Classes:

- Columns: `Subject`, `Sem`, `Section`, `Next`.
- Tapping opens roster and class actions.

Notices:

- Teacher can view scoped notices and post teacher-visible notices.
- Columns: `Scope`, `Title`, `Status`, `Age`.

Resources:

- Columns: `Subject`, `File`, `Sem`, `Updated`.
- Trailing actions: preview, edit, delete.
- FAB: `Upload Resource`.

Profile:

- Contact info
- Assigned subjects
- Theme and notification preferences

### Student

Home:

- Identity strip: program, semester, section.
- Alerts strip: upcoming exam, result published, unread notices.
- Two dense modules: newest notices and newest resources.

Notices:

- Tabs or chips: `All`, `College`, `Department`, `Program`, `Semester`.
- Each row shows scope icon, title, short message, published time.
- Full details open in a dedicated detail page or bottom sheet.

Resources:

- Columns: `Subject`, `File`, `Type`, `Size`.
- Only show items matching department, program, semester, and subject context.

Profile:

- Personal info
- Enrollment summary
- Notification settings
- Theme mode

### Parent

Dashboard:

- Child switcher at top.
- Attendance summary, exam alert, latest notices for selected child.
- No mixed-child feed by default.

Children:

- Columns: `Child`, `Program`, `Sem`, `Status`.
- Tap switches active child context.

Notices:

- Notices are always filtered through the selected child context.
- High-priority result alerts and exam alerts pin to top.

Profile:

- Contact info
- Linked children
- Notification and theme preferences

## Compact Table And List System

Do not convert real lists into bulky cards. Use dense mobile tables.

List shell rules:

- 3 to 4 visible columns maximum
- Body text: 14px to 15px
- Header text: 11px to 12px uppercase
- Row height: 48px to 60px depending on density
- Horizontal padding: 8px to 10px
- Sticky header on scroll
- Divider only, no heavy borders
- Ellipsis for long text
- Secondary data goes in expandable rows, bottom sheets, or detail pages

Recommended patterns:

- `CompactDataList`
- `CompactDataRow`
- `ExpandableMetaRow`
- `ActionIconGroup`
- `FilterSheet`

Example column sets:

| Screen | Visible Columns |
| --- | --- |
| Admin users | Name, Role, Dept, Status |
| Teacher classes | Subject, Sem, Section, Next |
| Student resources | Subject, File, Type, Size |
| Parent children | Child, Program, Sem, Status |
| Notification center | Icon, Title, Message, Time |

## Strict Filtering Rules

Before any entity is shown, resolve viewer context first:

- role
- department_id
- program_id
- semester
- child context for parent

Then apply filters in this order:

1. Role access gate
2. Department gate
3. Program gate
4. Semester gate
5. Optional subject gate

Critical rule:

- Scope filters must compose with `AND`.
- Never add an `OR` escape hatch after the main filters, because that leaks data.

Safe filtering example:

```php
$resources = Download::query()
    ->where(function ($query) use ($student) {
        $query->whereNull('department_id')
            ->orWhere('department_id', $student->department_id);
    })
    ->where(function ($query) use ($student) {
        $query->whereNull('program_id')
            ->orWhere('program_id', $student->program_id);
    })
    ->where(function ($query) use ($student) {
        $query->whereNull('semester')
            ->orWhere('semester', $student->current_semester);
    })
    ->where(function ($query) use ($student) {
        $query->whereNull('subject_id')
            ->orWhereHas('subject', function ($subjectQuery) use ($student) {
                $subjectQuery
                    ->where('program_id', $student->program_id)
                    ->where('semester', $student->current_semester);
            });
    });
```

## Notification System

Notification types:

- notices
- exams
- results
- resources
- system alerts

Priority model:

| Type | Priority | Delivery |
| --- | --- | --- |
| Result | High | Instant push + in-app + alert styling |
| Exam | High | Instant push + in-app + alert styling |
| Notice | Medium | In-app immediately, push if relevant and enabled |
| Resource | Low | Silent push or grouped digest + in-app |
| System | Medium or High | Depends on admin-selected severity |

Flow:

1. Domain event fires.
2. Recipient resolver finds eligible users.
3. `createNotification(users, payload)` writes rows in batches.
4. Real-time event is broadcast to online clients.
5. Push jobs deliver browser push to subscribed devices.

## Required Notification Methods

```php
public function sendNoticeNotification(Notice $notice): int;
public function sendExamNotification(Exam $exam): int;
public function sendResultNotification(Result $result): int;
public function sendResourceNotification(Download $resource): int;
public function sendSystemNotification(array $data): int;
public function createNotification(Collection $users, array $payload): int;
public function sendPushNotification(User $user, array $payload): void;
public function markAsRead(string $notificationId): void;
public function markAllAsRead(int $userId): int;
public function getUserNotifications(int $userId): LengthAwarePaginator;
public function getUnreadCount(int $userId): int;
```

Laravel-oriented service shape:

```php
final class MobileNotificationService
{
    public function sendNoticeNotification(Notice $notice): int
    {
        $users = User::query()
            ->active()
            ->whereIn('role', $this->rolesForNotice($notice))
            ->when($notice->department_id, fn ($q) => $q->where('department_id', $notice->department_id))
            ->when($notice->program_id, fn ($q) => $q->where('program_id', $notice->program_id))
            ->when($notice->semester, fn ($q) => $q->where('semester', $notice->semester))
            ->get();

        return $this->createNotification($users, [
            'title' => $notice->title,
            'message' => str($notice->content)->stripTags()->limit(120)->toString(),
            'type' => 'notice',
            'priority' => 'medium',
            'target_url' => "/notices/{$notice->id}",
        ]);
    }

    public function sendExamNotification(Exam $exam): int
    {
        $users = $this->resolveExamUsers($exam);

        return $this->createNotification($users, [
            'title' => $exam->name,
            'message' => 'Exam schedule or status has been updated.',
            'type' => 'exam',
            'priority' => 'high',
            'target_url' => '/exams',
        ]);
    }

    public function sendResultNotification(Result $result): int
    {
        $users = collect([$result->student->user, $result->student->parents->pluck('user')])
            ->flatten()
            ->filter()
            ->unique('id')
            ->values();

        return $this->createNotification($users, [
            'title' => 'Result published',
            'message' => 'A new result is now available.',
            'type' => 'result',
            'priority' => 'high',
            'target_url' => '/results',
        ]);
    }

    public function sendResourceNotification(Download $resource): int
    {
        $users = $this->resolveResourceUsers($resource);

        return $this->createNotification($users, [
            'title' => $resource->title,
            'message' => 'A new learning resource is available.',
            'type' => 'resource',
            'priority' => 'low',
            'target_url' => '/resources',
        ]);
    }

    public function sendSystemNotification(array $data): int
    {
        $users = $this->resolveSystemUsers($data);

        return $this->createNotification($users, [
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => 'system',
            'priority' => $data['priority'] ?? 'medium',
            'target_url' => $data['target_url'] ?? '/dashboard',
        ]);
    }

    public function createNotification(Collection $users, array $payload): int
    {
        $now = now();
        $rows = $users->unique('id')->map(fn ($user) => [
            'user_id' => $user->id,
            'title' => $payload['title'],
            'message' => $payload['message'],
            'type' => $payload['type'],
            'target_url' => $payload['target_url'],
            'is_read' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($rows->chunk(500) as $chunk) {
            NotificationRecord::query()->insert($chunk->all());
            dispatch(new SendPushBatchJob($chunk->pluck('user_id')->all(), $payload));
        }

        return $rows->count();
    }
}
```

## Notification Database Shape

Required minimal table:

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('message');
    $table->string('type', 32);
    $table->string('target_url')->nullable();
    $table->boolean('is_read')->default(false);
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->nullable();
});
```

Recommended additions for production:

- `priority`
- `group_key`
- `pushed_at`
- `read_at`
- `metadata` JSON

Push subscription table:

```php
Schema::create('push_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('endpoint');
    $table->text('public_key');
    $table->text('auth_token');
    $table->string('device_name')->nullable();
    $table->string('platform', 32)->nullable();
    $table->timestamp('last_seen_at')->nullable();
    $table->timestamps();
});
```

## In-App Notification UI

Notification Center is a dense list, not a stack of cards.

Row contents:

- left icon by type
- title
- short message
- timestamp
- unread dot

Interactions:

- tap row to open `target_url`
- swipe right or trailing icon to mark as read
- batch action: `Mark all read`
- filter chips: `All`, `Unread`, `High priority`

## Push Notification Contract

Payload:

```json
{
  "title": "Exam Schedule Updated",
  "body": "Midterm exam timing for Semester 4 has changed.",
  "icon": "/brand-logo",
  "click_action": "/exams",
  "type": "exam",
  "priority": "high"
}
```

Service worker handling:

```js
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};

  event.waitUntil(
    self.registration.showNotification(data.title || 'MMP', {
      body: data.body || '',
      icon: data.icon || '/brand-logo',
      badge: '/brand-logo',
      data: { click_action: data.click_action || '/' },
      requireInteraction: data.priority === 'high',
      silent: data.priority === 'low',
    })
  );
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  const target = event.notification.data?.click_action || '/';
  event.waitUntil(clients.openWindow(target));
});
```

## Dark Mode

Dark mode is mandatory. It must not be an afterthought.

Behavior:

- Default to OS preference on first launch.
- Persist user choice locally and on the user profile.
- Update `theme-color` metadata for browser chrome.
- All icons and dividers must meet contrast rules in both themes.

Core tokens:

| Token | Light | Dark |
| --- | --- | --- |
| `--bg-app` | `#f5f1ea` | `#07111d` |
| `--bg-surface` | `#ffffff` | `#0f1b2d` |
| `--bg-elevated` | `#fff8f2` | `#16243a` |
| `--text-primary` | `#102033` | `#edf3ff` |
| `--text-secondary` | `#5c6878` | `#9fb0c7` |
| `--border-subtle` | `#d7dde5` | `#24344c` |
| `--brand-primary` | `#8b2332` | `#ff8d6a` |
| `--brand-accent` | `#f0b24f` | `#ffd27c` |
| `--success` | `#147a52` | `#53d6a1` |
| `--warning` | `#a26116` | `#ffbf63` |
| `--danger` | `#b42318` | `#ff7d7d` |

## Component Inventory

- `AppShell`
- `TopBar`
- `BottomNav`
- `CompactDataList`
- `CompactDataRow`
- `SearchFilterBar`
- `FilterSheet`
- `NotificationList`
- `NotificationRow`
- `FabButton`
- `BottomActionBar`
- `SkeletonList`
- `InstallPromptSheet`

## PWA Requirements

- Installable with valid manifest
- Splash-screen-friendly brand colors
- Offline fallback page
- Cached app shell assets
- Cached recently opened pages
- Push notifications when the app is closed
- No full page reload feel inside the app shell

## Suggested API Surface

- `POST /api/mobile/auth/login`
- `POST /api/mobile/auth/logout`
- `GET /api/mobile/me`
- `GET /api/mobile/dashboard`
- `GET /api/mobile/notices`
- `GET /api/mobile/resources`
- `GET /api/mobile/notifications`
- `POST /api/mobile/notifications/read-all`
- `POST /api/mobile/notifications/{id}/read`
- `GET /api/mobile/exams`
- `GET /api/mobile/results`
- `POST /api/mobile/push-subscriptions`
- `DELETE /api/mobile/push-subscriptions/{id}`

## Delivery Recommendation

Build order:

1. Mobile app shell and design tokens
2. Shared compact list system
3. Student and parent read-only flows
4. Teacher resource and notice workflows
5. Admin mobile management flows
6. Real-time + push notification engine
7. Offline and install polish

This order delivers the highest-value user flows first while keeping the architecture clean.
