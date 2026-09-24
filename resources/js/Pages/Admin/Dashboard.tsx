import { Head, Link, router } from '@inertiajs/react';
import { CalendarRange, CheckCircle2, History, Layers } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { cn } from '@/lib/utils';

interface PeriodeListItem {
  id: number;
  nama: string;
  tahun_ajaran: string;
  status_aktif: boolean;
}

interface DashboardProps {
  ringkasan: {
    total: number;
    perluPerbaikan: number;
    diajukan: number;
    diterima: number;
  };
  terbaru: Array<{
    id: number;
    nama_lengkap: string;
    status: string;
    jenjang?: {
      nama: string;
    };
    periode?: {
      tahun_ajaran: string;
    };
  }>;
  periodeList?: PeriodeListItem[];
  periodeTerpilih?: string;
  periodeAktif?: PeriodeListItem | null;
}

export default function Dashboard({
  ringkasan,
  terbaru,
  periodeList = [],
  periodeTerpilih = 'all',
  periodeAktif,
}: DashboardProps) {
  const cards = [
    { label: 'Total pendaftar', value: ringkasan.total, color: 'text-ink' },
    { label: 'Perlu perbaikan', value: ringkasan.perluPerbaikan, color: 'text-amber-600' },
    { label: 'Menunggu verifikasi', value: ringkasan.diajukan, color: 'text-primary' },
    { label: 'Diterima', value: ringkasan.diterima, color: 'text-success' },
  ];

  const gantiPeriode = (val: string) => {
    router.get('/admin', { periode: val }, { preserveState: true });
  };

  const selectedPeriodeObj =
    periodeTerpilih !== 'all'
      ? periodeList.find((p) => String(p.id) === String(periodeTerpilih))
      : null;

  return (
    <AdminLayout title="Dashboard">
      <Head title="Dashboard" />

      {/* Bar Pengalih Sesi Periode */}
      <div className="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between shadow-2xs">
        <div className="flex flex-wrap items-center gap-3">
          <div className="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-400">
            <CalendarRange className="h-4 w-4 text-primary" />
            <span>Sesi Periode:</span>
          </div>

          <select
            value={periodeTerpilih}
            onChange={(e) => gantiPeriode(e.target.value)}
            className="spmb-input h-9 min-h-0 py-1.5 px-3 text-xs font-bold text-ink w-auto max-w-xs border-slate-300"
          >
            {periodeAktif && (
              <option value={String(periodeAktif.id)}>
                {periodeAktif.tahun_ajaran} (Periode Aktif)
              </option>
            )}

            {periodeList
              .filter((p) => !p.status_aktif)
              .map((p) => (
                <option key={p.id} value={String(p.id)}>
                  Periode {p.tahun_ajaran} - {p.nama}
                </option>
              ))}

            <option value="all">Semua Periode (Gabungan)</option>
            <option value="tanpa_periode">Tanpa Periode (PAUD)</option>
          </select>

          {selectedPeriodeObj?.status_aktif ? (
            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
              <CheckCircle2 className="h-3 w-3 text-emerald-600" />
              Sesi Aktif
            </span>
          ) : selectedPeriodeObj ? (
            <span className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
              <History className="h-3 w-3 text-amber-600" />
              Arsip Periode {selectedPeriodeObj.tahun_ajaran}
            </span>
          ) : periodeTerpilih === 'tanpa_periode' ? (
            <span className="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-0.5 text-[11px] font-bold text-purple-800">
              <Layers className="h-3 w-3 text-purple-600" />
              PAUD (Sepanjang Tahun)
            </span>
          ) : (
            <span className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-800">
              <Layers className="h-3 w-3 text-primary" />
              Semua Periode
            </span>
          )}
        </div>

        <Link
          href="/admin/periode"
          className="text-xs font-bold text-primary hover:text-primary-dark transition"
        >
          Kelola Periode SPMB →
        </Link>
      </div>

      {/* Grid Kartu Ringkasan */}
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {cards.map((card) => (
          <article
            key={card.label}
            className="spmb-panel p-5 shadow-2xs transition hover:shadow-xs"
          >
            <p className="text-xs font-bold uppercase tracking-wider text-slate-500">
              {card.label}
            </p>
            <p className={`mt-2 text-3xl font-extrabold tracking-tight ${card.color}`}>
              {card.value}
            </p>
          </article>
        ))}
      </div>

      {/* Tabel Pendaftaran Terbaru */}
      <section className="spmb-panel mt-6 overflow-hidden shadow-2xs">
        <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <div className="flex items-center gap-2">
            <h2 className="text-base font-bold text-ink">Pendaftaran terbaru</h2>
            {selectedPeriodeObj && (
              <span className="text-xs font-semibold text-slate-400">
                ({selectedPeriodeObj.tahun_ajaran})
              </span>
            )}
          </div>
          <Link
            href={
              periodeTerpilih !== 'all'
                ? `/admin/pendaftaran?periode=${periodeTerpilih}`
                : '/admin/pendaftaran'
            }
            className="text-xs font-bold text-primary hover:text-primary-dark transition"
          >
            Lihat semua di pendaftaran →
          </Link>
        </div>

        <div className="overflow-x-auto">
          <table className="spmb-table">
            <thead>
              <tr>
                <th>Pendaftar</th>
                <th>Jenjang</th>
                <th>Periode</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              {terbaru.map((item) => (
                <tr key={item.id} className="hover:bg-slate-50/80 transition">
                  <td>
                    <Link
                      href={`/admin/pendaftaran/${item.id}`}
                      className="font-bold text-ink hover:text-primary transition"
                    >
                      {item.nama_lengkap}
                      <span className="mt-0.5 block text-xs font-medium text-slate-400">
                      </span>
                    </Link>
                  </td>
                  <td className="font-medium text-slate-600">
                    {item.jenjang?.nama || '-'}
                  </td>
                  <td>
                    <span className="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                      {item.periode?.tahun_ajaran ?? (item.jenjang?.nama?.toLowerCase().includes('paud') || !item.periode ? 'PAUD' : 'Tanpa Periode')}
                    </span>
                  </td>
                  <td>
                    <span className="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-slate-700">
                      {item.status.replaceAll('_', ' ')}
                    </span>
                  </td>
                </tr>
              ))}
              {!terbaru.length && (
                <tr>
                  <td colSpan={4} className="py-12 text-center text-sm font-medium text-slate-400">
                    Belum ada pendaftaran di sesi periode ini.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </section>
    </AdminLayout>
  );
}
