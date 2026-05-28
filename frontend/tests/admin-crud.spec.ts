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

const headers = () => ({ Authorization: `Bearer ${token}` });
const uid = () => Math.random().toString(36).substring(2, 8);

// ─── Programs ────────────────────────────────────────────────────────────────

test.describe('Programs CRUD', () => {
  let createdId: number;

  test('GET /academic/programs — list programs', async ({ request }) => {
    const res = await request.get(`${BASE}/api/v1/academic/programs`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    expect(Array.isArray(body.data)).toBe(true);
  });

  test('POST /academic/programs — create program', async ({ request }) => {
    const deptRes = await request.get(`${BASE}/api/v1/departments`, { headers: headers() });
    expect(deptRes.ok()).toBeTruthy();
    const depts = (await deptRes.json()).data;
    test.skip(!depts?.length, 'No departments exist');
    const deptId = depts[0].id;
    const code = `TP-${uid()}`;

    const res = await request.post(`${BASE}/api/v1/academic/programs`, {
      headers: headers(),
      data: { department_id: deptId, name: `Test Program ${uid()}`, code, is_active: true },
    });
    if (!res.ok()) {
      const body = await res.json();
      throw new Error(`Create failed: ${JSON.stringify(body)}`);
    }
    const body = await res.json();
    expect(body.data?.name).toContain('Test Program');
    createdId = body.data.id;
  });

  test('GET /academic/programs/{id} — show program', async ({ request }) => {
    test.skip(!createdId, 'No program created');
    const res = await request.get(`${BASE}/api/v1/academic/programs/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });

  test('PUT /academic/programs/{id} — update program', async ({ request }) => {
    test.skip(!createdId, 'No program created');
    const deptRes = await request.get(`${BASE}/api/v1/departments`, { headers: headers() });
    const depts = (await deptRes.json()).data;
    const deptId = depts?.[0]?.id ?? 1;
    const res = await request.put(`${BASE}/api/v1/academic/programs/${createdId}`, {
      headers: headers(),
      data: { department_id: deptId, name: `Updated ${uid()}`, code: `TP-${uid()}`, is_active: true },
    });
    expect(res.ok()).toBeTruthy();
  });

  test('DELETE /academic/programs/{id} — delete program', async ({ request }) => {
    test.skip(!createdId, 'No program created');
    const res = await request.delete(`${BASE}/api/v1/academic/programs/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });
});

// ─── Sessions ─────────────────────────────────────────────────────────────────

test.describe('Sessions CRUD', () => {
  let createdId: number;

  test('GET /academic/sessions — list sessions', async ({ request }) => {
    const res = await request.get(`${BASE}/api/v1/academic/sessions`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    expect(Array.isArray(body.data)).toBe(true);
  });

  test('GET /academic/sessions/current — current session', async ({ request }) => {
    const res = await request.get(`${BASE}/api/v1/academic/sessions/current`, { headers: headers() });
    if (res.status() === 404) return;
    expect(res.ok()).toBeTruthy();
  });

  test('POST /academic/sessions — create session', async ({ request }) => {
    const name = `Test Session ${uid()}`;
    const res = await request.post(`${BASE}/api/v1/academic/sessions`, {
      headers: headers(),
      data: { name, is_current: false },
    });
    if (!res.ok()) {
      const body = await res.json();
      throw new Error(`Create failed: ${JSON.stringify(body)}`);
    }
    const body = await res.json();
    createdId = body.data.id;
  });

  test('PUT /academic/sessions/{id} — update session', async ({ request }) => {
    test.skip(!createdId, 'No session created');
    const res = await request.put(`${BASE}/api/v1/academic/sessions/${createdId}`, {
      headers: headers(),
      data: { name: `Updated ${uid()}`, is_current: false },
    });
    expect(res.ok()).toBeTruthy();
  });

  test('DELETE /academic/sessions/{id} — delete session', async ({ request }) => {
    test.skip(!createdId, 'No session created');
    const res = await request.delete(`${BASE}/api/v1/academic/sessions/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });
});

// ─── Departments ─────────────────────────────────────────────────────────────

test.describe('Departments CRUD', () => {
  let createdId: number;

  test('GET /departments — list departments', async ({ request }) => {
    const res = await request.get(`${BASE}/api/v1/departments`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    expect(Array.isArray(body.data)).toBe(true);
  });

  test('POST /departments — create department', async ({ request }) => {
    const code = `TD-${uid()}`;
    const res = await request.post(`${BASE}/api/v1/departments`, {
      headers: headers(),
      data: { name: `Test Dept ${uid()}`, code, is_active: true },
    });
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    expect(body.data?.name).toContain('Test Dept');
    createdId = body.data.id;
  });

  test('GET /departments/{id} — show department', async ({ request }) => {
    test.skip(!createdId, 'No dept created');
    const res = await request.get(`${BASE}/api/v1/departments/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });

  test('PUT /departments/{id} — update department', async ({ request }) => {
    test.skip(!createdId, 'No dept created');
    const res = await request.put(`${BASE}/api/v1/departments/${createdId}`, {
      headers: headers(),
      data: { name: `Updated Dept ${uid()}`, code: `TD-${uid()}`, is_active: false },
    });
    expect(res.ok()).toBeTruthy();
  });

  test('DELETE /departments/{id} — delete department', async ({ request }) => {
    test.skip(!createdId, 'No dept created');
    const res = await request.delete(`${BASE}/api/v1/departments/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });
});

// ─── Notices ─────────────────────────────────────────────────────────────────

test.describe('Notices CRUD', () => {
  let createdId: number;

  test('GET /notices — list notices', async ({ request }) => {
    const res = await request.get(`${BASE}/api/v1/notices`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    // paginated response: body.data is the items array, body.meta has pagination
    expect(Array.isArray(body.data)).toBe(true);
  });

  test('POST /notices — create notice', async ({ request }) => {
    const res = await request.post(`${BASE}/api/v1/notices`, {
      headers: headers(),
      data: { title: `Test Notice ${uid()}`, is_published: true },
    });
    if (!res.ok()) {
      const body = await res.json();
      throw new Error(`Create failed: ${JSON.stringify(body)}`);
    }
    const body = await res.json();
    expect(body.data?.title).toContain('Test Notice');
    createdId = body.data.id;
  });

  test('GET /notices/{id} — show notice', async ({ request }) => {
    test.skip(!createdId, 'No notice created');
    const res = await request.get(`${BASE}/api/v1/notices/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });

  test('PUT /notices/{id} — update notice', async ({ request }) => {
    test.skip(!createdId, 'No notice created');
    const res = await request.put(`${BASE}/api/v1/notices/${createdId}`, {
      headers: headers(),
      data: { title: `Updated Notice ${uid()}`, is_published: false },
    });
    expect(res.ok()).toBeTruthy();
  });

  test('DELETE /notices/{id} — delete notice', async ({ request }) => {
    test.skip(!createdId, 'No notice created');
    const res = await request.delete(`${BASE}/api/v1/notices/${createdId}`, { headers: headers() });
    expect(res.ok()).toBeTruthy();
  });
});
