import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, ClipboardCheck, FileText, Menu, ShieldCheck, X } from 'lucide-react';
import { PropsWithChildren, useEffect, useState } from 'react';
import { BrandLogo } from '@/Components/BrandLogo';
import { Container } from '@/Components/Public/Container';
import { cn } from '@/lib/utils';

export default function PublicLayout({ title, children }: { title?: string } & PropsWithChildren) {
  const { url, props } = usePage();
  const seoTitle = (props.seo as { title?: string } | undefined)?.title;
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  const isDaftar = url.startsWith('/daftar');
  const isCekStatus = url.startsWith('/cek-status');

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 16);
    onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => { setOpen(false); }, [url]);
  useEffect(() => { if (!open) return; const overflow = document.body.style.overflow; document.body.style.overflow = 'hidden'; return () => { document.body.style.overflow = overflow; }; }, [open]);

  const authUser = (props.auth as { user?: { id: number; name: string } | null } | undefined)?.user;

  return (
    <>
      <Head title={seoTitle ?? (title ? `${title} · SPMB Asshodiqiyah` : 'SPMB Asshodiqiyah')} />
      <div className="public-portal flex min-h-screen flex-col bg-slate-100/70 font-sans text-ink">
        <a href="#main-content" className="skip-link">Lewati ke konten</a>

        {/* Portal Header */}
        <header className={cn('fixed inset-x-0 top-0 z-50 border-b transition-all duration-300', scrolled ? 'border-slate-200 bg-white/95 shadow-sm backdrop-blur-md' : 'border-slate-200/80 bg-white/90 backdrop-blur-md')}>
          <Container className="flex h-16 items-center justify-between gap-4">
            {/* Brand Logo */}
            <Link href="/" className="flex items-center gap-2.5 pr-2 transition hover:opacity-90" aria-label="Portal SPMB Asshodiqiyah">
              <BrandLogo className="h-9 w-9 sm:h-10 sm:w-10" />
              <div>
                <p className="public-display text-lg font-bold leading-tight text-ink">Asshodiqiyah</p>
                <p className="text-[0.62rem] font-bold uppercase tracking-[0.14em] text-primary">Portal SPMB Online</p>
              </div>
            </Link>

            {/* Main Navigation Segmented Control */}
            <nav aria-label="Portal Menu" className="hidden items-center rounded-xl bg-slate-100 p-1 lg:flex">
              <Link
                href="/daftar"
                className={cn(
                  'flex items-center gap-2 rounded-lg px-4 py-1.5 text-xs font-bold transition duration-200',
                  isDaftar
                    ? 'bg-primary text-white shadow-sm'
                    : 'text-slate-600 hover:text-ink'
                )}
              >
                <FileText className="h-3.5 w-3.5" />
                Formulir Pendaftaran
              </Link>
              <Link
                href="/cek-status"
                className={cn(
                  'flex items-center gap-2 rounded-lg px-4 py-1.5 text-xs font-bold transition duration-200',
                  isCekStatus
                    ? 'bg-primary text-white shadow-sm'
                    : 'text-slate-600 hover:text-ink'
                )}
              >
                <ClipboardCheck className="h-3.5 w-3.5" />
                Cek Status
              </Link>
            </nav>

            {/* Right Action Links */}
            <div className="hidden items-center gap-3 lg:flex">
              <Link
                href="/#informasi"
                className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
              >
                Informasi SPMB
                <ArrowUpRight className="h-3.5 w-3.5 text-slate-400" />
              </Link>
              {authUser ? (
                <Link
                  href="/admin"
                  className="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-2 text-xs font-bold text-white transition hover:bg-primary-dark"
                >
                  <ShieldCheck className="h-3.5 w-3.5 text-decorative" />
                  Dashboard Admin
                </Link>
              ) : (
                <Link
                  href="/login"
                  className="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-2 text-xs font-bold text-white transition hover:bg-primary-dark"
                >
                  <ShieldCheck className="h-3.5 w-3.5 text-decorative" />
                  Admin Panitia
                </Link>
              )}
            </div>

            {/* Mobile Toggle */}
            <button
              type="button"
              onClick={() => setOpen((value) => !value)}
              aria-label={open ? 'Tutup menu' : 'Buka menu'}
              aria-expanded={open}
              className="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-ink lg:hidden"
            >
              {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </Container>

          {/* Mobile Menu Dropdown */}
          <div className={cn('absolute inset-x-3 top-full origin-top rounded-2xl border border-slate-200 bg-white p-3 text-ink shadow-xl transition duration-200 lg:hidden', open ? 'visible scale-100 opacity-100' : 'invisible scale-[0.98] opacity-0')}>
            <nav className="grid gap-1">
              <Link
                href="/daftar"
                className={cn('flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition', isDaftar ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-50')}
              >
                <FileText className="h-4 w-4" />
                Formulir Pendaftaran
              </Link>
              <Link
                href="/cek-status"
                className={cn('flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition', isCekStatus ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-50')}
              >
                <ClipboardCheck className="h-4 w-4" />
                Cek Status Pendaftaran
              </Link>
              <Link
                href="/#informasi"
                className="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
              >
                <span>Informasi Lengkap SPMB (Website Utama)</span>
                <ArrowUpRight className="h-4 w-4 text-slate-400" />
              </Link>
            </nav>
            {authUser ? (
              <Link
                href="/admin"
                className="mt-3 flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-4 text-sm font-bold text-white"
              >
                <ShieldCheck className="h-4 w-4 text-decorative" />
                Buka Dashboard Admin
              </Link>
            ) : (
              <Link
                href="/login"
                className="mt-3 flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-4 text-sm font-bold text-white"
              >
                <ShieldCheck className="h-4 w-4 text-decorative" />
                Login Admin SPMB
              </Link>
            )}
          </div>
        </header>

        {/* Main Content Area */}
        <main id="main-content" className="flex flex-1 flex-col pt-16">
          {children}
        </main>

        {/* Portal App Footer */}
        <footer className="border-t border-slate-200/80 bg-white text-slate-600">
          <Container className="py-8">
            <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
              <div className="flex items-center gap-3">
                <BrandLogo className="h-10 w-10 shrink-0" />
                <div>
                  <p className="font-bold text-ink">Pondok Pesantren Asshodiqiyah Kaligawe</p>
                  <p className="text-xs text-slate-500">Portal Penerimaan Santri &amp; Siswa Baru</p>
                </div>
              </div>
              <div className="flex flex-wrap items-center gap-4 text-xs font-semibold">
                <Link href="/#informasi" className="text-primary hover:underline">
                  Info SPMB
                </Link>
                <span className="text-slate-300">•</span>
                <Link href="/cek-status" className="hover:text-ink">
                  Cek Status
                </Link>
                <span className="text-slate-300">•</span>
                <Link href={authUser ? '/admin' : '/login'} className="hover:text-ink">
                  {authUser ? 'Panel Admin' : 'Admin SPMB'}
                </Link>
              </div>
            </div>
            <div className="mt-6 border-t border-slate-100 pt-6 text-center text-xs text-slate-400 sm:flex sm:items-center sm:justify-between sm:text-left">
              <p>© {new Date().getFullYear()} Yayasan Asshodiqiyah Semarang. Seluruh data pendaftaran dilindungi dan dikelola panitia.</p>
              <p className="mt-2 sm:mt-0">Kaligawe, Kota Semarang</p>
            </div>
          </Container>
        </footer>
      </div>
    </>
  );
}
