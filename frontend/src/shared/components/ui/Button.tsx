import { clsx } from 'clsx';
import type { ButtonHTMLAttributes } from 'react';
import { Spinner } from './Spinner';

interface Props extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost' | 'add' | 'edit' | 'delete' | 'view' | 'info' | 'neutral';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
}

const variantMap: Record<string, string> = {
  primary:   'btn-primary',
  secondary: 'btn-secondary',
  danger:    'btn-danger',
  ghost:     'rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 transition',
  add:       'bg-action-add text-white hover:bg-action-addDark rounded-lg px-4 py-2 text-sm font-medium transition',
  edit:      'bg-action-edit text-white hover:bg-action-editDark rounded-lg px-4 py-2 text-sm font-medium transition',
  delete:    'bg-action-delete text-white hover:bg-action-deleteDark rounded-lg px-4 py-2 text-sm font-medium transition',
  view:      'bg-action-view text-white hover:bg-action-viewDark rounded-lg px-4 py-2 text-sm font-medium transition',
  info:      'bg-action-info text-white hover:bg-action-infoDark rounded-lg px-4 py-2 text-sm font-medium transition',
  neutral:   'bg-gray-100 text-gray-800 hover:bg-gray-200 rounded-lg px-4 py-2 text-sm font-medium transition',
  brand:     'bg-brand text-white hover:bg-brand-dark rounded-lg px-4 py-2 text-sm font-medium transition',
};

const sizeMap = {
  sm: 'px-3 py-1.5 text-xs',
  md: '',
  lg: 'px-6 py-3 text-base',
};

export function Button({
  variant = 'primary',
  size = 'md',
  loading = false,
  children,
  disabled,
  className,
  ...props
}: Props) {
  return (
    <button
      {...props}
      disabled={disabled || loading}
      className={clsx(variantMap[variant], sizeMap[size], className)}
    >
      {loading && <Spinner size="sm" />}
      {children}
    </button>
  );
}
