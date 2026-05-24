/**
 * Returns the URL for the college logo.
 * Uses the static favicon.ico which is always available at the root,
 * both in dev (Vite serves frontend/public/) and production (Laravel serves public/).
 */
export function useLogo(): string {
  return '/favicon.ico';
}
