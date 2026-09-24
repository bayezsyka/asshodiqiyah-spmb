import { Head, Link, router } from '@inertiajs/react';
import { Download, FileArchive, Filter, Search } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { cn } from '@/lib/utils';

export default function Index({
  pendaftaran,
  filter,
  jenjang,
  periode,
  statusPilihan,
}: {
  pendaftaran: any;
  filter: any;
  jenjang: any[];
  periode: any[];
  statusPilihan: any[];
}) {
  const [cari, setCari] = useState(filter.cari || '');
  const jenjangTerpilih = jenjang.find((item) => item.id === Number(filter.jenjang));
  const perluPeriode = jenjangTerpilih?.kelompok !== 'paud';
  const bisaEksporZip = Boolean(jenjangTerpilih) && (!perluPeriode || Boolean(filter.periode));

  const parameterEkspor = useMemo(
    () =>
      new URLSearchParams(
        Object.entries({
          status: filter.status,
          jenjang: filter.jenjang,
          periode: filter.periode,
        })
          .filter(([, nilai]) => nilai !== '' && nilai !== null && nilai !== undefined)
          .map(([nama, nilai]) => [nama, String(nilai)])
      ).toString(),
    [filter]
  );

  const submit = (event: FormEvent) => {
    event.preventDefault();
    router.get('/admin/pendaftaran', { ...filter, cari }, { preserveState: true });
  };

  return (
    <AdminLayout title="Data Pendaftaran">
      <Head title="Data Pendaftaran" />

      {/* Filter Bar */}
      <form
        className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs md:grid-cols-[1fr_repeat(3,11rem)_auto] xl:grid-cols-[1fr_repeat(3,12rem)_auto]"
        onSubmit={submit}
      >
        <label className="relative block">
          <Search className="absolute left-3.5 top-3.5 text-slate-400" size={16} />
          <input
            className="spmb-input pl-10"
            value={cari}
            onChange={(event) => setCari(event.target.value)}
            placeholder="Cari nama, NISN, NIK, atau no. telepon"
          />
        </label>

        <select
          className="spmb-input"
          value={filter.status || ''}
          onChange={(event) =>
            router.get(
              '/admin/pendaftaran',
              { ...filter, status: event.target.value, cari },
              { preserveState: true }
            )
          }
        >
          <option value="">Semua status</option>
          {statusPilihan.map((item) => (
            <option key={item.value} value={item.value}>
              {item.label}
            </option>
          ))}
        </select>

        <select
          className="spmb-input"
          value={filter.jenjang || ''}
          onChange={(event) =>
            router.get(
              '/admin/pendaftaran',
              { ...filter, jenjang: event.target.value, cari },
              { preserveState: true }
            )
          }
        >
          <option value="">Semua jenjang</option>
          {jenjang.map((item) => (
            <option key={item.id} value={item.id}>
              {item.nama}
            </option>
          ))}
        </select>

        <select
          className="spmb-input"
          value={filter.periode || ''}
          onChange={(event) =>
            router.get(
              '/admin/pendaftaran',
              { ...filter, periode: event.target.value, cari },
              { preserveState: true }
            )
          }
        >
          <option value="">Semua periode</option>
          <option value="tanpa_periode">Tanpa Periode (PAUD)</option>
          {periode.map((item) => (
            <option key={item.id} value={item.id}>
              {item.status_aktif ? `${item.tahun_ajaran} (Aktif)` : item.tahun_ajaran}
            </option>
          ))}
        </select>

        <button className="spmb-button-primary">
          <Filter className="h-4 w-4" />
          Terapkan
        </button>
      </form>

      {/* Ekspor & Arsip Section */}
      <section className="spmb-panel mt-5 p-5 shadow-2xs sm:flex sm:items-center sm:justify-between sm:gap-6">
        <div>
          <h2 className="font-bold text-ink">Unduh & Arsip Pendaftaran</h2>
          <p className="mt-1 text-xs leading-relaxed text-slate-500">
            Ekspor rekap Excel atau ZIP berkas lengkap per jenjang dan periode untuk pengarsipan fisik/digital.
          </p>
        </div>
        <div className="mt-4 flex shrink-0 flex-wrap gap-2.5 sm:mt-0">
          <a
            href={`/admin/ekspor/excel${parameterEkspor ? `?${parameterEkspor}` : ''}`}
            className="spmb-button-secondary text-xs"
          >
            <Download className="h-4 w-4" />
            Unduh Excel
          </a>
          {bisaEksporZip ? (
            <a
              href={`/admin/ekspor/zip?${parameterEkspor}`}
              className="spmb-button-primary text-xs"
            >
              <FileArchive className="h-4 w-4" />
              Unduh ZIP Berkas
            </a>
          ) : (
            <span
              className="spmb-button-primary text-xs cursor-not-allowed bg-slate-100 text-slate-400 shadow-none hover:translate-y-0 hover:bg-slate-100"
              title="Pilih jenjang dan periode terlebih dahulu untuk membuat arsip berkas ZIP"
            >
              <FileArchive className="h-4 w-4" />
              Pilih jenjang{perluPeriode ? ' & periode' : ''}
            </span>
          )}
        </div>
      </section>

      {/* Tabel Data Pendaftaran */}
      <section className="spmb-panel mt-5 overflow-hidden shadow-2xs">
        <div className="overflow-x-auto">
          <table className="spmb-table">
            <thead>
              <tr>
                <th>Pendaftar</th>
                <th>Jenjang</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Dikirim</th>
              </tr>
            </thead>
            <tbody>
              {pendaftaran.data.map((item: any) => (
                <tr key={item.id} className="hover:bg-slate-50/80 transition">
                  <td>
                    <Link
                      href={`/admin/pendaftaran/${item.id}`}
                      className="font-bold text-ink hover:text-primary transition block"
                    >
                      {item.nama_lengkap}
                      <span className="block text-xs font-medium text-slate-400 mt-0.5">
                        {item.nisn
                          ? `NISN ${item.nisn}`
                          : item.nik
                          ? `NIK ${item.nik}`
                          : '-'}
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
                  <td className="text-xs text-slate-500 font-medium">
                    {item.submitted_at
                      ? new Date(item.submitted_at).toLocaleDateString('id-ID', {
                          day: 'numeric',
                          month: 'short',
                          year: 'numeric',
                        })
                      : '-'}
                  </td>
                </tr>
              ))}

              {!pendaftaran.data.length && (
                <tr>
                  <td colSpan={5} className="py-14 text-center text-sm font-medium text-slate-400">
                    Tidak ada data pendaftaran yang sesuai dengan filter.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination Links */}
        {pendaftaran.links && pendaftaran.links.length > 3 && (
          <div className="flex items-center justify-between border-t border-slate-100 px-5 py-3.5 bg-slate-50/50">
            <span className="text-xs text-slate-500 font-medium">
              Menampilkan {pendaftaran.from ?? 0} - {pendaftaran.to ?? 0} dari {pendaftaran.total} pendaftar
            </span>
            <div className="flex items-center gap-1">
              {pendaftaran.links.map((link: any, idx: number) => (
                <Link
                  key={idx}
                  href={link.url || '#'}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                  className={cn(
                    'inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs font-bold transition',
                    link.active
                      ? 'bg-primary text-white shadow-2xs'
                      : link.url
                      ? 'text-slate-600 hover:bg-slate-200/70 hover:text-ink'
                      : 'text-slate-300 pointer-events-none'
                  )}
                />
              ))}
            </div>
          </div>
        )}
      </section>
    </AdminLayout>
  );
}
