import { Menu } from 'lucide-react';

interface MobileTopbarProps {
  title?: string;
  onMenuClick: () => void;
}

export default function MobileTopbar({ title, onMenuClick }: MobileTopbarProps) {
  return (
    <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur-xs lg:hidden">
      <div className="flex h-14 items-center justify-between gap-4 px-4 sm:px-6">
        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={onMenuClick}
            className="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-2xs transition active:scale-95 hover:bg-slate-50 hover:text-ink"
            aria-label="Buka navigasi"
          >
            <Menu className="h-4.5 w-4.5" />
          </button>
          <div className="flex items-center gap-2 text-xs text-slate-500">
            <span className="font-semibold">SPMB Admin</span>
            <span>/</span>
            <span className="max-w-[180px] truncate font-bold text-ink sm:max-w-xs">
              {title || 'Dashboard'}
            </span>
          </div>
        </div>
      </div>
    </header>
  );
}
