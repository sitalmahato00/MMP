import { type LucideIcon } from 'lucide-react'

interface StatCardProps {
  title: string
  value: number | string
  icon: LucideIcon
  color?: string
  onClick?: () => void
}

export function StatCard({ title, value, icon: Icon, color = 'text-primary', onClick }: StatCardProps) {
  return (
    <div
      onClick={onClick}
      className="stat-card flex items-center gap-4 cursor-pointer"
    >
      <div className={`flex h-12 w-12 items-center justify-center rounded-lg bg-opacity-10 ${color} bg-current`}>
        <Icon className={`h-6 w-6 ${color}`} />
      </div>
      <div>
        <p className="text-sm text-muted">{title}</p>
        <p className="text-2xl font-bold text-gray-900">{value}</p>
      </div>
    </div>
  )
}
