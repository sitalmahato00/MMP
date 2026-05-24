import { useLogo } from '@hooks/useLogo';

interface LogoBadgeProps {
  size?: number; // px, default 64
  className?: string;
}

/**
 * Circular college logo badge used on auth pages and anywhere
 * a standalone logo is needed. Falls back to "MMP" text if the
 * image fails to load.
 */
export function LogoBadge({ size = 64, className = '' }: LogoBadgeProps) {
  const logoUrl = useLogo();

  return (
    <div
      className={`mx-auto mb-4 flex shrink-0 items-center justify-center overflow-hidden rounded-full shadow-md ${className}`}
      style={{
        width: size,
        height: size,
        border: '3px solid #DAA520',
        background: 'radial-gradient(circle, #003D82, #001F4D)',
      }}
    >
      <img
        src={logoUrl}
        alt="MMP Logo"
        className="h-full w-full object-contain"
        onError={(e) => {
          const t = e.currentTarget;
          t.style.display = 'none';
          const fb = t.nextElementSibling as HTMLElement | null;
          if (fb) fb.style.display = 'flex';
        }}
      />
      {/* Fallback text shown only when image fails */}
      <span
        className="hidden h-full w-full items-center justify-center text-xl font-bold text-white"
        aria-hidden="true"
      >
        MMP
      </span>
    </div>
  );
}
