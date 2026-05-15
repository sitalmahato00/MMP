import axios, {
  type AxiosInstance,
  type AxiosRequestConfig,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios';
import toast from 'react-hot-toast';

const BASE_URL: string = import.meta.env.VITE_API_URL ?? '/api';

const api: AxiosInstance = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: false,
});

// ─── Request Interceptor — attach Bearer token ────────────────────────────────
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = localStorage.getItem('erp_token');
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// ─── Response Interceptor — global error handling ────────────────────────────
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  (error) => {
    if (error.response) {
      const { status, data } = error.response;

      if (status === 401) {
        localStorage.removeItem('erp_token');
        localStorage.removeItem('erp_user');
        window.location.href = '/login';
        return Promise.reject(error);
      }

      if (status === 403) {
        toast.error('You do not have permission to perform this action.');
        return Promise.reject(error);
      }

      if (status === 422) {
        // Validation errors — let individual handlers deal with them
        return Promise.reject(error);
      }

      if (status === 429) {
        toast.error('Too many requests. Please wait and try again.');
        return Promise.reject(error);
      }

      if (status >= 500) {
        toast.error('Server error. Please try again later.');
        return Promise.reject(error);
      }

      // Surface any message from backend
      if (data?.message) {
        toast.error(data.message);
      }
    } else if (error.request) {
      toast.error('Network error. Please check your connection.');
    }

    return Promise.reject(error);
  }
);

export default api;

// ─── Typed wrappers ───────────────────────────────────────────────────────────

export function get<T>(url: string, config?: AxiosRequestConfig) {
  return api.get<T>(url, config).then((r) => r.data);
}

export function post<T>(url: string, data?: unknown, config?: AxiosRequestConfig) {
  return api.post<T>(url, data, config).then((r) => r.data);
}

export function put<T>(url: string, data?: unknown, config?: AxiosRequestConfig) {
  return api.put<T>(url, data, config).then((r) => r.data);
}

export function patch<T>(url: string, data?: unknown, config?: AxiosRequestConfig) {
  return api.patch<T>(url, data, config).then((r) => r.data);
}

export function del<T>(url: string, config?: AxiosRequestConfig) {
  return api.delete<T>(url, config).then((r) => r.data);
}
