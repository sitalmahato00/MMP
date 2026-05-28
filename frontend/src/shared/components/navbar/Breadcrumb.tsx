import { Link, useLocation } from 'react-router-dom';
import { Home } from 'lucide-react';

/**
 * Builds a human-readable label from a URL segment.
 * e.g. "news-events" → "News Events", "create" → "Create"
 */
function toLabel(segment: string): string {
  return segment
    .replace(/-/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

/**
 * Maps known path segments to nicer display names.
 */
const LABEL_MAP: Record<string, string> = {
  admin:        'Dashboard',
  dashboard:    'Dashboard',
  students:     'Students',
  teachers:     'Teachers',
  parents:      'Parents',
  alumni:       'Alumni',
  staff:        'Staff',
  hods:         'HODs',
  executives:   'Executives',
  users:        'System Users',
  attendance:   'Attendance',
  exams:        'Examinations',
  results:      'Results',
  programs:     'Programs',
  departments:  'Departments',
  sessions:     'Academic Sessions',
  notices:      'Notices & Circulars',
  'news-events':'News & Events',
  reports:      'Reports',
  cms:          'Web Pages',
  media:        'Media Library',
  downloads:    'File Repository',
  banners:      'Banner Management',
  settings:     'Account Settings',
  roles:        'Access Control',
  'audit-logs': 'Activity Logs',
  create:       'New Record',
  edit:         'Edit',
  documents:    'Documents',
};

export function Breadcrumb() {
  const { pathname } = useLocation();

  // Split path and remove empty segments
  const segments = pathname.split('/').filter(Boolean);

  // Build cumulative crumbs, skip pure numeric IDs in label but keep in path
  const crumbs: { label: string; path: string }[] = [];
  let cumPath = '';

  for (let i = 0; i < segments.length; i++) {
    const seg = segments[i];
    cumPath += '/' + seg;

    // Skip the bare "admin" prefix — we show "Dashboard" for /admin/dashboard
    if (seg === 'admin' && i === 0) continue;

    const label = LABEL_MAP[seg] ?? (
      // If it's a numeric ID, label it as "Details"
      /^\d+$/.test(seg) ? 'Details' : toLabel(seg)
    );

    crumbs.push({ label, path: cumPath });
  }

  // Always prepend a Home crumb pointing to /admin/dashboard
  const allCrumbs = [
    { label: 'Dashboard', path: '/admin/dashboard', isHome: true },
    ...crumbs.filter((c) => c.path !== '/admin/dashboard'),
  ];

  return (
    <div
      className="flex h-12 shrink-0 items-center gap-1.5 px-5 text-[12px]"
      style={{
        backgroundColor: '#ffffff',
        borderBottom: '1px solid #dce3eb',
        minHeight: 48,
        maxHeight: 48,
      }}
    >
      {allCrumbs.map((crumb, idx) => {
        const isLast = idx === allCrumbs.length - 1;
        return (
          <span key={crumb.path} className="flex items-center gap-1.5">
            {/* Separator */}
            {idx > 0 && (
              <span style={{ color: '#9baec8' }}>/</span>
            )}

            {/* Home icon on first crumb */}
            {idx === 0 && (
              <Home className="h-3.5 w-3.5 shrink-0" style={{ color: '#4a6a9a' }} />
            )}

            {isLast ? (
              <span className="font-semibold" style={{ color: '#002366' }}>
                {crumb.label}
              </span>
            ) : (
              <Link
                to={crumb.path}
                className="transition-colors hover:underline"
                style={{ color: '#4a6a9a' }}
              >
                {crumb.label}
              </Link>
            )}
          </span>
        );
      })}
    </div>
  );
}
