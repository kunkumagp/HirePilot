import React from 'react';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string;
  error?: string;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, id, className = '', ...props }, ref) => {
    const inputId = id || label.toLowerCase().replace(/\s+/g, '-');
    return (
      <div className="space-y-1.5">
        <label htmlFor={inputId} className="block text-sm font-medium text-slate-300">
          {label}
        </label>
        <input
          ref={ref}
          id={inputId}
          className={`flex h-10 w-full rounded-lg border bg-slate-900 px-3 py-2 text-sm text-slate-100 
            placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900
            ${error ? 'border-red-500 focus:ring-red-500' : 'border-slate-700 focus:ring-cyan-500'}
            disabled:cursor-not-allowed disabled:opacity-50 ${className}`}
          aria-invalid={!!error}
          aria-describedby={error ? `${inputId}-error` : undefined}
          {...props}
        />
        {error && (
          <p id={`${inputId}-error`} className="text-sm text-red-400" role="alert">
            {error}
          </p>
        )}
      </div>
    );
  },
);

Input.displayName = 'Input';
