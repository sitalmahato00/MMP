import type { ReactNode } from 'react';
import { clsx } from 'clsx';

interface Props {
  title?: string;
  description?: string;
  actions?: ReactNode;
  children: ReactNode;
  className?: string;
  noPadding?: boolean;
}

export function Card({ title, description, actions, children, className, noPadding }: Props) {
  return (
    <div
      className={clsx(className)}
      style={{
        background: '#ffffff',
        border: '1px solid #DCE3EB',
        borderRadius: '4px',
        padding: '1.25rem 1.5rem',
        boxShadow: '0 1px 3px rgba(11,46,107,0.06)',
      }}
    >
      {(title || actions) && (
        <div className="mb-4 flex items-center justify-between">
          <div>
            {title && <h3 className="text-base font-semibold" style={{ color: '#1A2B45' }}>{title}</h3>}
            {description && <p className="mt-0.5 text-sm" style={{ color: '#6B7A8D' }}>{description}</p>}
          </div>
          {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
      )}
      <div className={noPadding ? '-mx-6 -mb-6' : ''}>{children}</div>
    </div>
  );
}
