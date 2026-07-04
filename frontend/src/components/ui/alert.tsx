type AlertVariant = 'info' | 'success' | 'warning' | 'error';

interface AlertProps {
  variant?: AlertVariant;
  message: string;
  className?: string;
  onDismiss?: () => void;
}

const variantStyles: Record<AlertVariant, string> = {
  info: 'border-cyan-800 bg-cyan-950/50 text-cyan-300',
  success: 'border-green-800 bg-green-950/50 text-green-300',
  warning: 'border-amber-800 bg-amber-950/50 text-amber-300',
  error: 'border-red-800 bg-red-950/50 text-red-300',
};

export function Alert({ variant = 'info', message, className = '', onDismiss }: AlertProps) {
  return (
    <div className={`flex items-start gap-2 rounded-lg border p-3 text-sm ${variantStyles[variant]} ${className}`} role="alert">
      <span className="flex-1">{message}</span>
      {onDismiss && (
        <button onClick={onDismiss} className="text-current opacity-70 hover:opacity-100" aria-label="Dismiss">
          ×
        </button>
      )}
    </div>
  );
}
