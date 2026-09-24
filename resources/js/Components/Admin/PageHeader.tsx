import { PropsWithChildren, ReactNode } from 'react';

interface PageHeaderProps extends PropsWithChildren {
  title: string;
  actions?: ReactNode;
}

export default function PageHeader({ title, actions, children }: PageHeaderProps) {
  return (
    <div className="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
      <div>
        <h1 className="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
          {title}
        </h1>
      </div>
      {(actions || children) && (
        <div className="flex shrink-0 items-center gap-2.5">
          {actions}
          {children}
        </div>
      )}
    </div>
  );
}
