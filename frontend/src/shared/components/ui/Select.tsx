import { forwardRef, type SelectHTMLAttributes } from 'react';
import { clsx } from 'clsx';

interface Option { value: string | number; label: string; }

interface Props extends SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options: Option[];
  placeholder?: string;
}

const baseSelectStyle: React.CSSProperties = {
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
  appearance: 'auto',
};

const errorSelectStyle: React.CSSProperties = {
  ...baseSelectStyle,
  borderColor: '#DC2626',
};

export const Select = forwardRef<HTMLSelectElement, Props>(
  ({ label, error, options, placeholder, className, id, style, onFocus, onBlur, ...props }, ref) => {
    const selectId = id ?? label?.toLowerCase().replace(/\s+/g, '_');

    function handleFocus(e: React.FocusEvent<HTMLSelectElement>) {
      e.currentTarget.style.borderColor = error ? '#DC2626' : '#1D4ED8';
      e.currentTarget.style.boxShadow = error
        ? '0 0 0 2px rgba(220,38,38,0.15)'
        : '0 0 0 2px rgba(29,78,216,0.15)';
      onFocus?.(e);
    }

    function handleBlur(e: React.FocusEvent<HTMLSelectElement>) {
      e.currentTarget.style.borderColor = error ? '#DC2626' : '#DCE3EB';
      e.currentTarget.style.boxShadow = 'none';
      onBlur?.(e);
    }

    return (
      <div className="w-full">
        {label && (
          <label htmlFor={selectId} className="form-label">
            {label}
            {props.required && <span className="ml-0.5 text-red-500">*</span>}
          </label>
        )}
        <select
          ref={ref}
          id={selectId}
          className={clsx(className)}
          style={{ ...(error ? errorSelectStyle : baseSelectStyle), ...style }}
          onFocus={handleFocus}
          onBlur={handleBlur}
          {...props}
        >
          {placeholder && <option value="">{placeholder}</option>}
          {options.map((o) => (
            <option key={o.value} value={o.value}>
              {o.label}
            </option>
          ))}
        </select>
        {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
      </div>
    );
  }
);

Select.displayName = 'Select';
