import type { PaginationMeta } from '@/types';
import { Button } from './Button';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface Props {
  meta: PaginationMeta;
  onPageChange: (page: number) => void;
}

export function Pagination({ meta, onPageChange }: Props) {
  const { current_page, last_page, from, to, total } = meta;

  return (
    <div
      className="flex items-center justify-between px-4 py-3"
      style={{ borderTop: '1px solid #DCE3EB' }}
    >
      <p className="text-sm" style={{ color: '#6B7A8D' }}>
        Showing <span className="font-medium" style={{ color: '#1A2B45' }}>{from}</span>–
        <span className="font-medium" style={{ color: '#1A2B45' }}>{to}</span> of{' '}
        <span className="font-medium" style={{ color: '#1A2B45' }}>{total}</span> results
      </p>
      <div className="flex items-center gap-1">
        <Button
          variant="secondary"
          size="sm"
          onClick={() => onPageChange(current_page - 1)}
          disabled={current_page <= 1}
          aria-label="Previous page"
        >
          <ChevronLeft className="h-4 w-4" />
        </Button>
        {Array.from({ length: Math.min(last_page, 7) }, (_, i) => {
          const page = i + 1;
          return (
            <button
              key={page}
              onClick={() => onPageChange(page)}
              className="rounded px-3 py-1.5 text-sm font-medium transition"
              style={
                page === current_page
                  ? { background: '#1D4ED8', color: '#ffffff' }
                  : { color: '#6B7A8D', background: 'transparent' }
              }
              onMouseEnter={e => {
                if (page !== current_page) {
                  (e.currentTarget as HTMLButtonElement).style.background = '#F4F7FB';
                }
              }}
              onMouseLeave={e => {
                if (page !== current_page) {
                  (e.currentTarget as HTMLButtonElement).style.background = 'transparent';
                }
              }}
            >
              {page}
            </button>
          );
        })}
        <Button
          variant="secondary"
          size="sm"
          onClick={() => onPageChange(current_page + 1)}
          disabled={current_page >= last_page}
          aria-label="Next page"
        >
          <ChevronRight className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}
