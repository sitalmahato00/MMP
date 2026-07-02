import { ChevronLeft, ChevronRight, Search } from 'lucide-react'
import { t } from '@/i18n'

interface Column<T> {
  key: string
  header: string
  render?: (item: T) => React.ReactNode
  sortable?: boolean
}

interface DataTableProps<T> {
  columns: Column<T>[]
  data: T[]
  isLoading?: boolean
  onRowClick?: (item: T) => void
  currentPage?: number
  lastPage?: number
  onPageChange?: (page: number) => void
  searchPlaceholder?: string
  onSearch?: (query: string) => void
}

export function DataTable<T extends { id?: number }>({
  columns, data, isLoading, onRowClick,
  currentPage, lastPage, onPageChange,
  searchPlaceholder, onSearch,
}: DataTableProps<T>) {
  if (isLoading) {
    return (
      <div className="card">
        <div className="p-6 space-y-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="h-10 animate-pulse rounded bg-gray-100" />
          ))}
        </div>
      </div>
    )
  }

  return (
    <div className="card">
      {onSearch && (
        <div className="border-b px-4 py-3">
          <div className="relative max-w-xs">
            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <input
              type="text"
              placeholder={searchPlaceholder || t('common.search')}
              onChange={(e) => onSearch(e.target.value)}
              className="form-input pl-9"
            />
          </div>
        </div>
      )}

      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b bg-gray-50">
              {columns.map((col) => (
                <th key={col.key} className="px-4 py-3 text-left font-medium text-muted">
                  {col.header}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.length === 0 ? (
              <tr>
                <td colSpan={columns.length} className="px-4 py-12 text-center text-muted">
                  {t('common.noData')}
                </td>
              </tr>
            ) : (
              data.map((item, idx) => {
                const id = 'id' in item ? (item as { id: number }).id : idx
                return (
                  <tr
                    key={id}
                    onClick={() => onRowClick?.(item)}
                    className={'border-b transition-colors' + (onRowClick ? ' cursor-pointer hover:bg-gray-50' : '')}
                  >
                    {columns.map((col) => (
                      <td key={col.key} className="px-4 py-3">
                        {col.render ? col.render(item) : String((item as Record<string, unknown>)[col.key] ?? '')}
                      </td>
                    ))}
                  </tr>
                )
              })
            )}
          </tbody>
        </table>
      </div>

      {currentPage && lastPage && lastPage > 1 && (
        <div className="flex items-center justify-between border-t px-4 py-3">
          <p className="text-sm text-muted">
            Page {currentPage} of {lastPage}
          </p>
          <div className="flex gap-1">
            <button
              onClick={() => onPageChange?.(currentPage - 1)}
              disabled={currentPage <= 1}
              className="btn-secondary px-2 py-1"
            >
              <ChevronLeft className="h-4 w-4" />
            </button>
            <button
              onClick={() => onPageChange?.(currentPage + 1)}
              disabled={currentPage >= lastPage}
              className="btn-secondary px-2 py-1"
            >
              <ChevronRight className="h-4 w-4" />
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
