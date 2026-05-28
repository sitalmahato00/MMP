import { test, expect } from '@playwright/test';

const BASE = 'http://localhost:8000';
const EMAIL = process.env.ADMIN_EMAIL ?? 'sitalmahato077@gmail.com';
const PASS = process.env.ADMIN_PASSWORD ?? 'password';

let token: string;

test.beforeAll(async ({ request }) => {
  const res = await request.post(`${BASE}/api/auth/login`, {
    data: { email: EMAIL, password: PASS },
  });
  expect(res.ok()).toBeTruthy();
  const body = await res.json();
  expect(body.success).toBe(true);
  token = body.data.token;
});

test.describe('GET /api/v1/dashboard/admin', () => {
  let dash: any;

  test.beforeAll(async ({ request }) => {
    const res = await request.get(`${BASE}/api/v1/dashboard/admin`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    expect(res.ok()).toBeTruthy();
    dash = await res.json();
  });

  test('returns greeting and session info', () => {
    expect(dash.greeting).toMatch(/Good (morning|afternoon|evening)/);
    expect(dash.sessionLabel).toBeTruthy();
    expect(dash.updatedAt).toBeTruthy();
  });

  test('has four KPIs with correct keys', () => {
    expect(dash.kpis).toHaveLength(4);
    const keys = dash.kpis.map((k: any) => k.key);
    expect(keys).toEqual(['students', 'attendance', 'pass', 'departments']);
    dash.kpis.forEach((k: any) => {
      expect(k.title).toBeTruthy();
      expect(k.value).toBeTruthy();
    });
  });

  test('attendance KPIs have valid numbers', () => {
    const att = dash.kpis.find((k: any) => k.key === 'attendance');
    expect(Number(att.value)).toBeGreaterThanOrEqual(0);
    expect(att.suffix).toBe('%');
  });

  test('pass rate KPIs have valid numbers', () => {
    const pass = dash.kpis.find((k: any) => k.key === 'pass');
    expect(Number(pass.value)).toBeGreaterThanOrEqual(0);
    expect(pass.suffix).toBe('%');
  });

  test('has running semesters array', () => {
    expect(Array.isArray(dash.runningSemesters)).toBe(true);
  });

  test('recent notices is an array', () => {
    expect(Array.isArray(dash.recentNotices)).toBe(true);
  });

  test('alerts is an array', () => {
    expect(Array.isArray(dash.alerts)).toBe(true);
  });

  test('community quickStats are present', () => {
    expect(dash.highlight?.quickStats).toBeDefined();
    expect(typeof dash.highlight.quickStats.teachers).toBe('number');
    expect(typeof dash.highlight.quickStats.parents).toBe('number');
    expect(typeof dash.highlight.quickStats.alumni).toBe('number');
  });

  test('attendance chart data has 7 and 30 day periods', () => {
    expect(dash.attendanceChartData['7']).toBeDefined();
    expect(dash.attendanceChartData['30']).toBeDefined();
    expect(dash.attendanceChartData['7'].labels).toHaveLength(7);
    expect(dash.attendanceChartData['30'].labels).toHaveLength(30);
  });

  test('grade distribution is present', () => {
    expect(dash.gradeDistribution).toBeDefined();
    expect('hasData' in dash.gradeDistribution).toBe(true);
  });

  test('totalTeachers, totalParents, totalAlumni are numbers', () => {
    expect(typeof dash.totalTeachers).toBe('number');
    expect(typeof dash.totalParents).toBe('number');
    expect(typeof dash.totalAlumni).toBe('number');
  });
});
