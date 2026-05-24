export interface NavItem {
  label: string;
  path: string;
  icon: string;
  children?: NavItem[];
}

export interface NavGroup {
  label: string;
  standalone?: boolean;
  items: NavItem[];
}

export const adminNavGroups: NavGroup[] = [
  {
    label: 'Dashboard',
    standalone: true,
    items: [
      { label: 'Overview', path: '/admin/dashboard', icon: 'LayoutDashboard' },
    ],
  },
  {
    label: 'Users',
    items: [
      { label: 'System Users', path: '/admin/users', icon: 'Users' },
      { label: 'HODs', path: '/admin/hods', icon: 'UserCheck' },
      { label: 'Executives', path: '/admin/executives', icon: 'Star' },
      { label: 'Students', path: '/admin/students', icon: 'GraduationCap' },
      { label: 'Teachers', path: '/admin/teachers', icon: 'Briefcase' },
      { label: 'Parents', path: '/admin/parents', icon: 'Heart' },
      { label: 'Alumni', path: '/admin/alumni', icon: 'Trophy' },
      { label: 'Staff', path: '/admin/staff', icon: 'UserPlus' },
    ],
  },
  {
    label: 'Academics',
    items: [
      { label: 'Programs', path: '/admin/programs', icon: 'BookOpen' },
      { label: 'Attendance Tracking', path: '/admin/attendance', icon: 'CalendarCheck' },
      { label: 'Examination & Results', path: '/admin/exams', icon: 'ClipboardList' },
    ],
  },
  {
    label: 'Configurations',
    items: [
      { label: 'Academic Sessions', path: '/admin/sessions', icon: 'Calendar' },
      { label: 'Departments', path: '/admin/departments', icon: 'Building' },
    ],
  },
  {
    label: 'Communications',
    items: [
      { label: 'Notices', path: '/admin/notices', icon: 'Bell' },
      { label: 'News & Events', path: '/admin/news-events', icon: 'Newspaper' },
    ],
  },
  {
    label: 'Web Content',
    items: [
      { label: 'Web Pages', path: '/admin/cms', icon: 'FileText' },
      { label: 'Media Library', path: '/admin/media', icon: 'Image' },
      { label: 'File Repository', path: '/admin/downloads', icon: 'Download' },
      { label: 'Banner Management', path: '/admin/banners', icon: 'Palette' },
    ],
  },
  {
    label: 'System Control',
    items: [
      { label: 'Account Settings', path: '/admin/settings', icon: 'Settings' },
      { label: 'Access Control', path: '/admin/roles', icon: 'Shield' },
      { label: 'Activity Logs', path: '/admin/audit-logs', icon: 'FileText' },
    ],
  },
];

export const adminNavItems: NavItem[] =
  adminNavGroups.flatMap((g) => g.items);

export const teacherNavItems: NavItem[] = [
  { label: 'Dashboard',   path: '/teacher/dashboard',  icon: 'LayoutDashboard' },
  { label: 'Attendance',  path: '/teacher/attendance', icon: 'CalendarCheck'   },
  { label: 'Exams',       path: '/teacher/exams',      icon: 'ClipboardList'   },
  { label: 'Assignments', path: '/teacher/assignments',icon: 'FileText'        },
  { label: 'Students',    path: '/teacher/students',   icon: 'GraduationCap'   },
];

export const studentNavItems: NavItem[] = [
  { label: 'Dashboard',   path: '/student/dashboard',  icon: 'LayoutDashboard' },
  { label: 'Attendance',  path: '/student/attendance', icon: 'CalendarCheck'   },
  { label: 'Results',     path: '/student/results',    icon: 'BarChart2'       },
  { label: 'Assignments', path: '/student/assignments',icon: 'FileText'        },
  { label: 'Library',     path: '/student/library',    icon: 'Library'         },
  { label: 'Hostel',      path: '/student/hostel',     icon: 'Building'        },
];

export const hodNavItems: NavItem[] = [
  { label: 'Dashboard',   path: '/hod/dashboard',   icon: 'LayoutDashboard' },
  { label: 'Students',    path: '/hod/students',    icon: 'GraduationCap'   },
  { label: 'Teachers',    path: '/hod/teachers',    icon: 'Users'           },
  { label: 'Attendance',  path: '/hod/attendance',  icon: 'CalendarCheck'   },
  { label: 'Timetable',   path: '/hod/timetable',   icon: 'Calendar'        },
];
