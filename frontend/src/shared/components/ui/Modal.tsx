import type { ReactNode } from 'react';
import { X } from 'lucide-react';
import { clsx } from 'clsx';

interface Props {
  open: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  footer?: ReactNode;
}

const sizeMap = {
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
};

export function Modal({ open, onClose, title, children, size = 'md', footer }: Props) {
  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
    >
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />

      {/* Panel */}
      <div
        className={clsx('relative z-10 w-full bg-white', sizeMap[size])}
        style={{
          borderRadius: '4px',
          boxShadow: '0 4px 16px rgba(11,46,107,0.12)',
        }}
      >
        {/* Header */}
        <div
          className="flex items-center justify-between px-6 py-4"
          style={{ borderBottom: '1px solid #DCE3EB' }}
        >
          <h2 className="text-base font-semibold" style={{ color: '#1A2B45' }}>{title}</h2>
          <button
            onClick={onClose}
            className="rounded p-1 transition"
            style={{ color: '#6B7A8D' }}
            onMouseEnter={e => {
              (e.currentTarget as HTMLButtonElement).style.background = '#F4F7FB';
              (e.currentTarget as HTMLButtonElement).style.color = '#1A2B45';
            }}
            onMouseLeave={e => {
              (e.currentTarget as HTMLButtonElement).style.background = 'transparent';
              (e.currentTarget as HTMLButtonElement).style.color = '#6B7A8D';
            }}
            aria-label="Close"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Body */}
        <div className="px-6 py-5">{children}</div>

        {/* Footer */}
        {footer && (
          <div
            className="flex justify-end gap-3 px-6 py-4"
            style={{ borderTop: '1px solid #DCE3EB' }}
          >
            {footer}
          </div>
        )}
      </div>
    </div>
  );
}
