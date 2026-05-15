// Navigation item definitions for each portal role.
// Icons are from lucide-react.

export interface NavItem {
  label: string;
  path: string;
  icon: string;         // lucide icon name — resolved in Sidebar component
  children?: NavItem[];
}

export const adminNavItems: NavItem[] = [
  { label: 'Dashboard',   path: '/admin/dashboard',  icon: 'LayoutDashboard' },
  { label: 'Students',    path: '/admin/students',   icon: 'GraduationCap'   },
  { label: 'Teachers',    path: '/admin/teachers',   icon: 'Users'           },
  { label: 'Academic',    path: '/admin/academic',   icon: 'BookOpen'        },
  { label: 'Attendance',  path: '/admin/attendance', icon: 'CalendarCheck'   },
  { label: 'Exams',       path: '/admin/exams',      icon: 'ClipboardList'   },
  { label: 'Library',     path: '/admin/library',    icon: 'Library'         },
  { label: 'Hostel',      path: '/admin/hostel',     icon: 'Building'        },
  { label: 'Payroll',     path: '/admin/payroll',    icon: 'Banknote'        },
  { label: 'Inventory',   path: '/admin/inventory',  icon: 'Package'         },
  { label: 'Accounts',    path: '/admin/accounts',   icon: 'DollarSign'      },
  { label: 'CMS',         path: '/admin/cms',        icon: 'Globe'           },
  { label: 'Settings',    path: '/admin/settings',   icon: 'Settings'        },
];

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
