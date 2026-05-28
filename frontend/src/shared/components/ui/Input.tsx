import { forwardRef, type InputHTMLAttributes } from 'react';
import { clsx } from 'clsx';

interface Props extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  hint?: string;
}

const baseInputStyle: React.CSSProperties = {
  display: 'block',
  width: '100%',
  border: '1px solid #DCE3EB',
  borderRadius: '3px',
  padding: '0.4375rem 0.75rem',
  fontSize: '0.8125rem',
  lineHeight: '1.5',
  background: '#ffffff',
  color: '#1A2B45',
  outline: 'none',
  transition: 'border-color 0.15s, box-shadow 0.15s',
};

const errorInputStyle: React.CSSProperties = {
  ...baseInputStyle,
  borderColor: '#DC2626',
};

export const Input = forwardRef<HTMLInputElement, Props>(
  ({ label, error, hint, className, id, style, onFocus, onBlur, ...props }, ref) => {
    const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '_');

    function handleFocus(e: React.FocusEvent<HTMLInputElement>) {
      e.currentTarget.style.borderColor = error ? '#DC2626' : '#1D4ED8';
      e.currentTarget.style.boxShadow = error
        ? '0 0 0 2px rgba(220,38,38,0.15)'
        : '0 0 0 2px rgba(29,78,216,0.15)';
      onFocus?.(e);
    }

    function handleBlur(e: React.FocusEvent<HTMLInputElement>) {
      e.currentTarget.style.borderColor = error ? '#DC2626' : '#DCE3EB';
      e.currentTarget.style.boxShadow = 'none';
      onBlur?.(e);
    }

    return (
      <div className="w-full">
        {label && (
          <label
            htmlFor={inputId}
            className="form-label"
          >
            {label}
            {props.required && <span className="ml-0.5 text-red-500">*</span>}
          </label>
        )}
        <input
          ref={ref}
          id={inputId}
          className={clsx(className)}
          style={{ ...(error ? errorInputStyle : baseInputStyle), ...style }}
          onFocus={handleFocus}
          onBlur={handleBlur}
          {...props}
        />
        {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        {hint && !error && <p className="mt-1 text-xs" style={{ color: '#6B7A8D' }}>{hint}</p>}
      </div>
    );
  }
);

Input.displayName = 'Input';
