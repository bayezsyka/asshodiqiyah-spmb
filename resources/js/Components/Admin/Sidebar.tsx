import { Link, router } from '@inertiajs/react';
import {
  ExternalLink,
  LogOut,
  LucideIcon,
  Menu,
  X,
} from 'lucide-react';
import { useState } from 'react';
import { BrandLogo } from '@/Components/BrandLogo';
import { cn } from '@/lib/utils';

export interface NavItem {
  label: string;
  href: string;
  icon: LucideIcon;
  active?: boolean;
}

export interface NavGroup {
  title: string;
  items: NavItem[];
}

interface SidebarProps {
  open: boolean;
  onClose: () => void;
  collapsed?: boolean;
  onToggleCollapse: () => void;
  groups: NavGroup[];
  user?: {
    name?: string;
    email?: string;
    role?: string;
  } | null;
}

export default function Sidebar({
  open,
  onClose,
  collapsed = false,
  onToggleCollapse,
  groups,
  user,
}: SidebarProps) {
  const [isHovered, setIsHovered] = useState(false);
  const [collapsedGroups, setCollapsedGroups] = useState<Record<string, boolean>>({});

  const visuallyCollapsed = collapsed && !isHovered;

  const toggleGroup = (title: string) => {
    setCollapsedGroups((prev) => ({ ...prev, [title]: !prev[title] }));
  };

  const handleLogout = () => {
    router.post('/admin/logout');
  };

  return (
    <>
      {/* Backdrop Layar Sentuh / Mobile */}
      <div
        aria-hidden="true"
        onClick={onClose}
        className={cn(
          'fixed inset-0 z-40 bg-ink/50 backdrop-blur-xs transition-opacity duration-300 lg:hidden',
          open ? 'opacity-100 pointer-events-auto' : 'pointer-events-none opacity-0'
        )}
      />

      {/* Kontainer Sidebar Fixed */}
      <aside
        onMouseEnter={() => collapsed && setIsHovered(true)}
        onMouseLeave={() => collapsed && setIsHovered(false)}
        className={cn(
          'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200 bg-white text-ink transition-all duration-300 ease-in-out lg:translate-x-0',
          open ? 'translate-x-0 shadow-2xl' : '-translate-x-full',
          visuallyCollapsed ? 'w-16' : 'w-64',
          collapsed && isHovered ? 'shadow-2xl ring-1 ring-black/5' : ''
        )}
      >
        {/* 1. Header / Logo Brand & Tombol Toggle */}
        <div className="flex h-16 shrink-0 items-center border-b border-slate-200 px-2 bg-white">
          <div className="flex h-full w-full items-center">
            <Link
              href="/admin"
              className={cn(
                'flex items-center gap-2.5 overflow-hidden whitespace-nowrap pl-1 transition-all duration-300 ease-in-out',
                visuallyCollapsed ? 'max-w-0 opacity-0 pointer-events-none' : 'max-w-[170px] opacity-100'
              )}
            >
              <div className="flex items-center gap-1 shrink-0">
                <BrandLogo jenis="yayasan" className="h-7 w-7" />
                <BrandLogo jenis="sekolah" className="h-7 w-7" />
              </div>
              <div className="min-w-0 flex flex-col">
                <span className="truncate text-sm font-extrabold leading-tight text-ink">
                  Lenterahati IBS
                </span>
                <span className="text-[10px] font-semibold text-slate-500">
                  Panel SPMB
                </span>
              </div>
            </Link>

            <div
              className={cn(
                'flex items-center',
                visuallyCollapsed ? 'w-12 justify-center' : 'ml-auto'
              )}
            >
              <button
                type="button"
                onClick={onToggleCollapse}
                className="hidden h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-2xs transition hover:bg-slate-100 hover:text-ink lg:flex"
                title={collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'}
              >
                <Menu className="h-4.5 w-4.5" />
              </button>
              <button
                type="button"
                onClick={onClose}
                className="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-2xs transition hover:bg-slate-100 hover:text-ink lg:hidden"
                aria-label="Tutup navigasi"
              >
                <X className="h-4.5 w-4.5" />
              </button>
            </div>
          </div>
        </div>

        {/* 2. Daftar Navigasi Mandiri (Independent Scroll) */}
        <div className="flex-1 overflow-y-auto overflow-x-hidden px-2 py-3.5 space-y-4">
          {groups.map((group) => {
            const isCollapsed = collapsedGroups[group.title];
            return (
              <section key={group.title}>
                {/* Header Grup Sub-navigasi Accordion */}
                <button
                  type="button"
                  onClick={() => toggleGroup(group.title)}
                  className="mb-1 flex h-5 w-full items-center px-1.5 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-600 transition"
                >
                  <span
                    className={cn(
                      'overflow-hidden whitespace-nowrap transition-all duration-300 ease-in-out',
                      visuallyCollapsed
                        ? 'max-w-0 opacity-0 pointer-events-none'
                        : 'max-w-[160px] opacity-100'
                    )}
                  >
                    {group.title}
                  </span>
                  {!visuallyCollapsed && (
                    <span className="ml-auto text-xs font-mono text-slate-400">
                      {isCollapsed ? '+' : '−'}
                    </span>
                  )}
                </button>

                {/* Item Menu */}
                {!isCollapsed && (
                  <div className="space-y-1">
                    {group.items.map((item) => {
                      const Icon = item.icon;
                      return (
                        <Link
                          key={item.href}
                          href={item.href}
                          className={cn(
                            'group relative flex h-10 items-center rounded-xl transition',
                            item.active
                              ? 'bg-primary text-white font-bold shadow-xs shadow-blue-950/20'
                              : 'text-slate-600 hover:bg-slate-100 hover:text-ink font-semibold'
                          )}
                          title={visuallyCollapsed ? item.label : undefined}
                        >
                          {/* Fixed Icon Box (48px) */}
                          <span className="flex h-10 w-12 shrink-0 items-center justify-center">
                            <Icon className="h-4.5 w-4.5" />
                          </span>

                          {/* Sliding Label */}
                          <span
                            className={cn(
                              'overflow-hidden whitespace-nowrap text-sm transition-all duration-300 ease-in-out pr-2.5',
                              visuallyCollapsed
                                ? 'max-w-0 opacity-0 pointer-events-none'
                                : 'max-w-[160px] opacity-100'
                            )}
                          >
                            {item.label}
                          </span>
                        </Link>
                      );
                    })}
                  </div>
                )}
              </section>
            );
          })}
        </div>

        {/* 3. Footer Utilitas Terpadu (Pusat Utilitas di Bawah) */}
        <div className="shrink-0 border-t border-slate-200 bg-slate-50/70 p-2 space-y-1.5">
          {/* Profil Pengguna */}
          <Link
            href="/admin/profil"
            className="group flex h-11 items-center rounded-xl border border-slate-200 bg-white px-3 transition hover:border-primary/50 hover:bg-blue-50/30"
            title={visuallyCollapsed ? user?.name || 'Profil Akun' : undefined}
          >
            <div
              className={cn(
                'overflow-hidden whitespace-nowrap transition-all duration-300 ease-in-out flex flex-col justify-center text-left min-w-0',
                visuallyCollapsed
                  ? 'max-w-0 opacity-0 pointer-events-none'
                  : 'max-w-[220px] opacity-100'
              )}
            >
              <span className="text-xs font-bold leading-tight truncate text-ink">
                {user?.name || 'Admin Lentera Hati'}
              </span>
              <span className="text-[10px] text-slate-500 leading-tight truncate">
                {user?.email || 'admin@spmb.lenterahatiibs.com'}
              </span>
            </div>
          </Link>

          {/* Lihat Portal SPMB */}
          <a
            href="/"
            target="_blank"
            rel="noreferrer"
            className="flex h-10 items-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:border-slate-300 hover:bg-slate-100 hover:text-ink"
            title={visuallyCollapsed ? 'Lihat Portal SPMB' : undefined}
          >
            <span className="flex h-10 w-12 shrink-0 items-center justify-center text-slate-500">
              <ExternalLink className="h-4 w-4" />
            </span>
            <span
              className={cn(
                'overflow-hidden whitespace-nowrap transition-all duration-300 ease-in-out pr-2.5 text-xs font-bold text-slate-700',
                visuallyCollapsed
                  ? 'max-w-0 opacity-0 pointer-events-none'
                  : 'max-w-[160px] opacity-100'
              )}
            >
              Portal SPMB
            </span>
          </a>

          {/* Tombol Keluar (Logout) */}
          <button
            type="button"
            onClick={handleLogout}
            className="flex h-10 w-full items-center rounded-xl border border-rose-200 bg-rose-50/60 text-rose-600 transition hover:bg-rose-100 hover:border-rose-300"
            title={visuallyCollapsed ? 'Keluar' : undefined}
          >
            <span className="flex h-10 w-12 shrink-0 items-center justify-center">
              <LogOut className="h-4 w-4" />
            </span>
            <span
              className={cn(
                'overflow-hidden whitespace-nowrap transition-all duration-300 ease-in-out pr-2.5 text-xs font-bold',
                visuallyCollapsed
                  ? 'max-w-0 opacity-0 pointer-events-none'
                  : 'max-w-[160px] opacity-100'
              )}
            >
              Keluar
            </span>
          </button>
        </div>
      </aside>
    </>
  );
}
