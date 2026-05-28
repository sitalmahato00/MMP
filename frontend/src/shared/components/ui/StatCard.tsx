import type { ReactNode } from 'react';
import { clsx } from 'clsx';

interface Props {
  title: string;
  value: string | number;
  icon: ReactNode;
  trend?: { value: number; label: string };
  color?: 'blue' | 'green' | 'yellow' | 'red' | 'purple';
}

const colorMap = {
  blue:   'bg-blue-50 text-blue-600',
  green:  'bg-green-50 text-green-600',
  yellow: 'bg-yellow-50 text-yellow-600',
  red:    'bg-red-50 text-red-600',
  purple: 'bg-purple-50 text-purple-600',
};

export function StatCard({ title, value, icon, trend, color = 'blue' }: Props) {
  return (
    <div
      className="flex items-start gap-4"
      style={{
        background: '#ffffff',
        border: '1px solid #DCE3EB',
        borderRadius: '4px',
        padding: '1.25rem 1.5rem',
        boxShadow: '0 1px 3px rgba(11,46,107,0.06)',
      }}
    >
      <div className={clsx('rounded p-3', colorMap[color])}>
        {icon}
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-sm truncate" style={{ color: '#6B7A8D' }}>{title}</p>
        <p className="mt-1 text-2xl font-bold" style={{ color: '#1A2B45' }}>{value}</p>
        {trend && (
          <p className={`mt-1 text-xs font-medium ${trend.value >= 0 ? 'text-green-600' : 'text-red-600'}`}>
            {trend.value >= 0 ? '▲' : '▼'} {Math.abs(trend.value)}% {trend.label}
          </p>
        )}
      </div>
    </div>
  );
}
