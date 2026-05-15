import { clsx } from 'clsx';
import type { ReactNode } from 'react';

type Variant = 'green' | 'red' | 'yellow' | 'blue' | 'gray';

interface Props { variant: Variant; children: ReactNode; className?: string; }

export function Badge({ variant, children, className }: Props) {
  return (
    <span className={clsx(`badge-${variant}`, className)}>{children}</span>
  );
}

export function StatusBadge({ status }: { status: string }) {
  const map: Record<string, Variant> = {
    active:      'green',
    inactive:    'gray',
    graduated:   'blue',
    suspended:   'red',
    transferred: 'yellow',
    published:   'green',
    draft:       'gray',
    completed:   'blue',
    present:     'green',
    absent:      'red',
    late:        'yellow',
  };
  const variant = map[status] ?? 'gray';
  return <Badge variant={variant}>{status.replace('_', ' ')}</Badge>;
}
