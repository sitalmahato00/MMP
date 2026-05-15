import api from '@shared/api/axios';

const BASE = '/v1/public';

// ─── Homepage ──────────────────────────────────────────────────────────────
export const getHomepage = () =>
  api.get(`${BASE}/homepage`).then(r => r.data);

// ─── Site Settings ─────────────────────────────────────────────────────────
export const getSiteSettings = () =>
  api.get(`${BASE}/site-settings`).then(r => r.data);

// ─── Notices ───────────────────────────────────────────────────────────────
export const getNotices = (params?: { type?: string; per_page?: number; page?: number }) =>
  api.get(`${BASE}/notices`, { params }).then(r => r.data);

export const getNoticeBySlug = (slug: string) =>
  api.get(`${BASE}/notices/${slug}`).then(r => r.data);

// ─── Departments ───────────────────────────────────────────────────────────
export const getDepartments = () =>
  api.get(`${BASE}/departments`).then(r => r.data);

export const getDepartmentBySlug = (slug: string) =>
  api.get(`${BASE}/departments/${slug}`).then(r => r.data);

export const getProgramBySlug = (deptSlug: string, programSlug: string) =>
  api.get(`${BASE}/departments/${deptSlug}/programs/${programSlug}`).then(r => r.data);

// ─── Alumni ────────────────────────────────────────────────────────────────
export const getAlumniDirectory = (params?: { search?: string; department_id?: number; year?: string; per_page?: number; page?: number }) =>
  api.get(`${BASE}/alumni-directory`, { params }).then(r => r.data);

export const getAlumniProfile = (id: number) =>
  api.get(`${BASE}/alumni/${id}`).then(r => r.data);

// ─── Downloads ─────────────────────────────────────────────────────────────
export const getDownloads = (params?: { category?: string; department_id?: number; search?: string }) =>
  api.get(`${BASE}/downloads`, { params }).then(r => r.data);

// ─── Pages ─────────────────────────────────────────────────────────────────
export const getPage = (slug: string) =>
  api.get(`${BASE}/pages/${slug}`).then(r => r.data);

// ─── Facilities ────────────────────────────────────────────────────────────
export const getFacilities = (params?: { department?: string }) =>
  api.get(`${BASE}/facilities`, { params }).then(r => r.data);

// ─── Staff ─────────────────────────────────────────────────────────────────
export const getStaff = () =>
  api.get(`${BASE}/staff`).then(r => r.data);

// ─── Leadership ────────────────────────────────────────────────────────────
export const getLeadership = () =>
  api.get(`${BASE}/leadership`).then(r => r.data);

// ─── Gallery ───────────────────────────────────────────────────────────────
export const getGallery = () =>
  api.get(`${BASE}/gallery`).then(r => r.data);

// ─── People ────────────────────────────────────────────────────────────────
export const getPeople = (params?: { department?: string }) =>
  api.get(`${BASE}/people`, { params }).then(r => r.data);

// ─── News & Events ─────────────────────────────────────────────────────────
export const getNewsEvents = (params?: { per_page?: number; page?: number }) =>
  api.get(`${BASE}/news-events`, { params }).then(r => r.data);

export const getNewsEventBySlug = (slug: string) =>
  api.get(`${BASE}/news-events/${slug}`).then(r => r.data);

// ─── Question Bank ─────────────────────────────────────────────────────────
export const getQuestionBank = (params?: { department_id?: number; program_id?: number; search?: string }) =>
  api.get(`${BASE}/question-bank`, { params }).then(r => r.data);

// ─── Result Form ───────────────────────────────────────────────────────────
export const getResultForm = () =>
  api.get(`${BASE}/result`).then(r => r.data);
