import { Head, usePage } from '@inertiajs/react';
import {
  CalendarRange,
  ClipboardList,
  LayoutDashboard,
  UserRoundCog,
  Users,
} from 'lucide-react';
import { PropsWithChildren, ReactNode, useMemo, useState } from 'react';
import MobileTopbar from '@/Components/Admin/MobileTopbar';
import PageHeader from '@/Components/Admin/PageHeader';
import Sidebar, { NavGroup } from '@/Components/Admin/Sidebar';
import { cn } from '@/lib/utils';

interface AdminLayoutProps extends PropsWithChildren {
  title: string;
  description?: string;
  actions?: ReactNode;
  hideHeader?: boolean;
}

export default function AdminLayout({
  title,
  actions,
  hideHeader = false,
  children,
}: AdminLayoutProps) {
  const page = usePage<{
    auth: { user: { name: string; email?: string; role?: string } | null };
    flash: { success?: string; error?: string };
  }>();

  const [sidebarOpen, setSidebarOpen] = useState(false);

  // Inisialisasi sinkron dari localStorage (Bebas Flicker)
  const [sidebarCollapsed, setSidebarCollapsed] = useState(() => {
    if (typeof window !== 'undefined') {
      return window.localStorage.getItem('app.sidebar.collapsed') === 'true';
    }
    return false;
  });

  const toggleSidebarCollapse = () => {
    setSidebarCollapsed((prev) => {
      const next = !prev;
      if (typeof window !== 'undefined') {
        window.localStorage.setItem('app.sidebar.collapsed', String(next));
      }
      return next;
    });
  };

  const currentUrl = page.url;
  const isActive = (href: string) =>
    href === '/admin' ? currentUrl === href : currentUrl.startsWith(href);

  const groups: NavGroup[] = useMemo(
    () => [
      {
        title: 'Penerimaan Siswa',
        items: [
          {
            label: 'Dashboard',
            href: '/admin',
            icon: LayoutDashboard,
            active: isActive('/admin'),
          },
          {
            label: 'Pendaftaran',
            href: '/admin/pendaftaran',
            icon: ClipboardList,
            active: isActive('/admin/pendaftaran'),
          },
          {
            label: 'Kelola Periode',
            href: '/admin/periode',
            icon: CalendarRange,
            active: isActive('/admin/periode'),
          },
        ],
      },
      {
        title: 'Pengaturan & Akun',
        items: [
          ...(page.props.auth.user?.role === 'superadmin' ? [{
            label: 'Kelola Pengguna',
            href: '/admin/users',
            icon: Users,
            active: isActive('/admin/users'),
          }] : []),
          {
            label: 'Profil Akun',
            href: '/admin/profil',
            icon: UserRoundCog,
            active: isActive('/admin/profil'),
          },
        ],
      },
    ],
    [currentUrl, page.props.auth.user?.role]
  );

  return (
    <>
      <Head title={title} />
      <div className="admin-portal min-h-screen bg-[#f4f7fa] text-ink antialiased">
        {/* Sidebar Terpadu */}
        <Sidebar
          open={sidebarOpen}
          onClose={() => setSidebarOpen(false)}
          collapsed={sidebarCollapsed}
          onToggleCollapse={toggleSidebarCollapse}
          groups={groups}
          user={page.props.auth.user}
        />

        {/* Area Konten Utama (Desktop Topbar-Less Canvas) */}
        <div
          className={cn(
            'flex min-h-screen flex-col transition-[padding] duration-300 ease-in-out',
            sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'
          )}
        >
          {/* Mobile Topbar (Hanya tampil di layar seluler < lg) */}
          <MobileTopbar title={title} onMenuClick={() => setSidebarOpen(true)} />

          {/* Konten Halaman */}
          <main className="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div className="mx-auto max-w-7xl">
              {!hideHeader && <PageHeader title={title} actions={actions} />}

              {/* Flash Alerts */}
              {page.props.flash?.success && (
                <div className="fixed bottom-5 right-5 z-50 rounded-xl bg-success px-4 py-3 text-sm font-bold text-white shadow-xl shadow-emerald-950/20">
                  {page.props.flash.success}
                </div>
              )}
              {page.props.flash?.error && (
                <div className="fixed bottom-5 right-5 z-50 rounded-xl bg-danger px-4 py-3 text-sm font-bold text-white shadow-xl shadow-red-950/20">
                  {page.props.flash.error}
                </div>
              )}

              {children}
            </div>
          </main>
        </div>
      </div>
    </>
  );
}
