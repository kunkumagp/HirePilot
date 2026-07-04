import React from 'react';

interface CardProps {
  children: React.ReactNode;
  className?: string;
}

export function Card({ children, className = '' }: CardProps) {
  return (
    <div className={`rounded-xl border border-slate-800 bg-slate-900/50 p-6 backdrop-blur-sm ${className}`}>
      {children}
    </div>
  );
}

export function CardHeader({ children, className = '' }: CardProps) {
  return <div className={`mb-4 ${className}`}>{children}</div>;
}

export function CardTitle({ children, className = '' }: CardProps) {
  return <h2 className={`text-xl font-semibold text-slate-100 ${className}`}>{children}</h2>;
}

export function CardDescription({ children, className = '' }: CardProps) {
  return <p className={`mt-1 text-sm text-slate-400 ${className}`}>{children}</p>;
}

export function CardContent({ children, className = '' }: CardProps) {
  return <div className={className}>{children}</div>;
}

export function CardFooter({ children, className = '' }: CardProps) {
  return <div className={`mt-6 flex items-center justify-end gap-3 ${className}`}>{children}</div>;
}
