import { Head, Link, router, useForm } from '@inertiajs/react';
import {
  AlertCircle,
  CalendarDays,
  Check,
  CheckCircle2,
  Edit2,
  Plus,
  Trash2,
  X,
} from 'lucide-react';
import { FormEvent, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { cn } from '@/lib/utils';

export interface PeriodeItem {
  id: number;
  nama: string;
  tahun_ajaran: string;
  mulai_pada: string | null;
  selesai_pada: string | null;
  status_aktif: boolean;
  informasi: string | null;
  instruksi_pembayaran: string | null;
  pendaftaran_count?: number;
  ringkasan_jenjang?: Array<{ nama: string; total: number }>;
  created_at?: string;
}

export default function Index({ periode, totalPendaftarTanpaPeriode, ringkasanTanpaPeriode }: { periode: PeriodeItem[]; totalPendaftarTanpaPeriode: number; ringkasanTanpaPeriode?: Array<{ nama: string; total: number }> }) {
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<PeriodeItem | null>(null);

  const form = useForm({
    nama: '',
    tahun_ajaran: '',
    mulai_pada: '',
    selesai_pada: '',
    status_aktif: false,
    informasi: '',
    instruksi_pembayaran: '',
  });

  const bukaModalTambah = () => {
    setEditingItem(null);
    form.reset();
    form.clearErrors();
    form.setData({
      nama: '',
      tahun_ajaran: '',
      mulai_pada: '',
      selesai_pada: '',
      status_aktif: periode.length === 0,
      informasi: '',
      instruksi_pembayaran: '',
    });
    setModalOpen(true);
  };

  const bukaModalEdit = (item: PeriodeItem) => {
    setEditingItem(item);
    form.clearErrors();
    form.setData({
      nama: item.nama || '',
      tahun_ajaran: item.tahun_ajaran || '',
      mulai_pada: item.mulai_pada ? item.mulai_pada.split('T')[0] : '',
      selesai_pada: item.selesai_pada ? item.selesai_pada.split('T')[0] : '',
      status_aktif: item.status_aktif,
      informasi: item.informasi || '',
      instruksi_pembayaran: item.instruksi_pembayaran || '',
    });
    setModalOpen(true);
  };

  const submitForm = (e: FormEvent) => {
    e.preventDefault();
    if (editingItem) {
      form.put(`/admin/periode/${editingItem.id}`, {
        onSuccess: () => {
          setModalOpen(false);
          form.reset();
        },
      });
    } else {
      form.post('/admin/periode', {
        onSuccess: () => {
          setModalOpen(false);
          form.reset();
        },
      });
    }
  };

  const jadikanAktif = (item: PeriodeItem) => {
    if (confirm(`Aktifkan periode ${item.tahun_ajaran}? Periode lain akan dinonaktifkan dan pendaftaran baru akan masuk ke periode ini.`)) {
      router.put(`/admin/periode/${item.id}/aktifkan`);
    }
  };

  const hapusPeriode = (item: PeriodeItem) => {
    if (item.status_aktif) {
      alert('Periode yang sedang aktif tidak dapat dihapus.');
      return;
    }
    if ((item.pendaftaran_count ?? 0) > 0) {
      alert(`Periode tidak dapat dihapus karena memiliki ${item.pendaftaran_count} pendaftar.`);
      return;
    }
    if (confirm(`Hapus periode "${item.nama} (${item.tahun_ajaran})"?`)) {
      router.delete(`/admin/periode/${item.id}`);
    }
  };

  const formatTanggal = (tgl: string | null) => {
    if (!tgl) return '-';
    return new Date(tgl).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  };

  return (
    <AdminLayout
      title="Kelola Periode SPMB"
      actions={
        <button
          type="button"
          onClick={bukaModalTambah}
          className="spmb-button-primary text-xs"
        >
          <Plus className="h-4 w-4" />
          Tambah Periode Baru
        </button>
      }
    >
      <Head title="Kelola Periode SPMB" />

      {/* Info Card */}
      <div className="mb-6 rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-950">
        <div className="flex items-start gap-3">
          <CalendarDays className="h-5 w-5 shrink-0 text-primary mt-0.5" />
          <div>
            <p className="font-bold">Sistem Periode SPMB &amp; Ketentuan Jenjang</p>
            <p className="mt-0.5 text-xs leading-relaxed text-blue-900/80">
              Periode yang <strong>Aktif</strong> menjadi tujuan pendaftaran peserta didik jenjang formal (SD, SMP, SMA). Pendaftaran <strong>PAUD dibuka sepanjang tahun</strong> tanpa terikat batasan periode pendaftaran. Data seluruh pendaftar tetap tersimpan dan dapat disaring melalui filter periode di Dashboard maupun menu Pendaftaran.
            </p>
          </div>
        </div>
      </div>

      {/* Tabel Daftar Periode */}
      <div className="spmb-panel overflow-hidden shadow-2xs">
        <div className="overflow-x-auto">
          <table className="spmb-table">
            <thead>
              <tr>
                <th>Status</th>
                <th>Tahun Ajaran</th>
                <th>Nama Periode</th>
                <th>Jadwal Pendaftaran</th>
                <th className="text-center">Total Pendaftar</th>
                <th className="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {periode.map((item) => (
                <tr key={item.id} className={cn(item.status_aktif ? 'bg-emerald-50/30' : '')}>
                  <td>
                    {item.status_aktif ? (
                      <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                        <Check className="h-3.5 w-3.5 text-emerald-600" />
                        Periode Aktif
                      </span>
                    ) : (
                      <span className="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                        Arsip / Selesai
                      </span>
                    )}
                  </td>
                  <td>
                    <span className="font-extrabold text-ink text-sm">
                      {item.tahun_ajaran}
                    </span>
                  </td>
                  <td>
                    <div>
                      <p className="font-bold text-ink">{item.nama}</p>
                      {item.informasi && (
                        <p className="mt-0.5 line-clamp-1 text-xs text-slate-500 max-w-sm">
                          {item.informasi}
                        </p>
                      )}
                    </div>
                  </td>
                  <td>
                    <span className="text-xs font-medium text-slate-600">
                      {formatTanggal(item.mulai_pada)} — {formatTanggal(item.selesai_pada)}
                    </span>
                  </td>
                  <td className="text-center">
                    <div className="grid justify-items-center gap-1.5"><span className="inline-flex min-w-[2rem] items-center justify-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-ink">{item.pendaftaran_count ?? 0}</span>{item.ringkasan_jenjang && item.ringkasan_jenjang.length > 0 && <span className="max-w-40 text-center text-[11px] leading-4 text-slate-500">{item.ringkasan_jenjang.map((jenjang) => `${jenjang.nama} ${jenjang.total}`).join(' · ')}</span>}</div>
                  </td>
                  <td>
                    <div className="flex items-center justify-end gap-1.5">
                      {!item.status_aktif && (
                        <button
                          type="button"
                          onClick={() => jadikanAktif(item)}
                          className="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                          title="Jadikan periode aktif untuk penerimaan baru"
                        >
                          <CheckCircle2 className="h-3.5 w-3.5" />
                          Aktifkan
                        </button>
                      )}

                      <button
                        type="button"
                        onClick={() => bukaModalEdit(item)}
                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-primary hover:text-primary"
                        title="Edit periode"
                      >
                        <Edit2 className="h-3.5 w-3.5" />
                      </button>

                      {!item.status_aktif && (
                        <button
                          type="button"
                          onClick={() => hapusPeriode(item)}
                          disabled={(item.pendaftaran_count ?? 0) > 0}
                          className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                          title={(item.pendaftaran_count ?? 0) > 0 ? 'Tidak bisa dihapus karena ada data pendaftar' : 'Hapus periode'}
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}

              {!periode.length && (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-sm font-medium text-slate-400">
                    Belum ada periode pendaftaran. Klik tombol "+ Tambah Periode Baru" di atas untuk membuat.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {totalPendaftarTanpaPeriode > 0 && (
        <div className="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-700 shadow-2xs">
          <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p className="font-bold text-ink">Pendaftaran Tanpa Periode (PAUD / Sepanjang Tahun)</p>
              <p className="text-xs text-slate-500">
                Terdapat <strong>{totalPendaftarTanpaPeriode} pendaftar</strong> tercatat tanpa ikatan periode (pendaftaran sepanjang tahun).
                {ringkasanTanpaPeriode && ringkasanTanpaPeriode.length > 0 && (
                  <span> ({ringkasanTanpaPeriode.map((j) => `${j.nama}: ${j.total}`).join(' · ')})</span>
                )}
              </p>
            </div>
            <Link
              href="/admin/pendaftaran?periode=tanpa_periode"
              className="inline-flex items-center text-xs font-bold text-primary hover:underline shrink-0"
            >
              Lihat di Pendaftaran →
            </Link>
          </div>
        </div>
      )}

      {/* Modal Dialog Form Tambah / Edit */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink/60 backdrop-blur-xs">
          <div
            className="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-200"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-slate-200 px-6 py-4">
              <h2 className="text-lg font-bold text-ink">
                {editingItem ? 'Edit Periode SPMB' : 'Tambah Periode SPMB Baru'}
              </h2>
              <button
                type="button"
                onClick={() => setModalOpen(false)}
                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-ink transition"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Modal Form */}
            <form onSubmit={submitForm} className="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
              <div className="grid gap-4 sm:grid-cols-2">
                <label className="block sm:col-span-2">
                  <span className="spmb-label">Nama Periode</span>
                  <input
                    type="text"
                    required
                    className="spmb-input"
                    value={form.data.nama}
                    onChange={(e) => form.setData('nama', e.target.value)}
                    placeholder="Contoh: Pendaftaran 2026/2027"
                  />
                  {form.errors.nama && <p className="spmb-error">{form.errors.nama}</p>}
                </label>

                <label className="block sm:col-span-2">
                  <span className="spmb-label">Tahun Ajaran</span>
                  <input
                    type="text"
                    required
                    className="spmb-input"
                    value={form.data.tahun_ajaran}
                    onChange={(e) => form.setData('tahun_ajaran', e.target.value)}
                    placeholder="Contoh: 2026/2027"
                  />
                  {form.errors.tahun_ajaran && (
                    <p className="spmb-error">{form.errors.tahun_ajaran}</p>
                  )}
                </label>

                <label className="block">
                  <span className="spmb-label">Tanggal Mulai (Opsional)</span>
                  <input
                    type="date"
                    className="spmb-input"
                    value={form.data.mulai_pada}
                    onChange={(e) => form.setData('mulai_pada', e.target.value)}
                  />
                  {form.errors.mulai_pada && (
                    <p className="spmb-error">{form.errors.mulai_pada}</p>
                  )}
                </label>

                <label className="block">
                  <span className="spmb-label">Tanggal Selesai (Opsional)</span>
                  <input
                    type="date"
                    className="spmb-input"
                    value={form.data.selesai_pada}
                    onChange={(e) => form.setData('selesai_pada', e.target.value)}
                  />
                  {form.errors.selesai_pada && (
                    <p className="spmb-error">{form.errors.selesai_pada}</p>
                  )}
                </label>
              </div>

              <label className="block">
                <span className="spmb-label">Informasi / Keterangan Tambahan</span>
                <textarea
                  className="spmb-input min-h-20"
                  value={form.data.informasi}
                  onChange={(e) => form.setData('informasi', e.target.value)}
                  placeholder="Informasi jalur pendaftaran, kuota, atau pengumuman khusus..."
                />
                {form.errors.informasi && (
                  <p className="spmb-error">{form.errors.informasi}</p>
                )}
              </label>

              <label className="block">
                <span className="spmb-label">Instruksi Pembayaran (Opsional)</span>
                <textarea
                  className="spmb-input min-h-20"
                  value={form.data.instruksi_pembayaran}
                  onChange={(e) => form.setData('instruksi_pembayaran', e.target.value)}
                  placeholder="Informasi nomor rekening yayasan / biaya formulir jika ada..."
                />
                {form.errors.instruksi_pembayaran && (
                  <p className="spmb-error">{form.errors.instruksi_pembayaran}</p>
                )}
              </label>

              <div className="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
                <label className="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    className="mt-0.5 h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                    checked={form.data.status_aktif}
                    onChange={(e) => form.setData('status_aktif', e.target.checked)}
                  />
                  <div>
                    <span className="text-xs font-bold text-ink block">
                      Jadikan sebagai Periode Utama yang Aktif
                    </span>
                    <span className="text-[11px] text-slate-500 block mt-0.5 leading-snug">
                      Jika dicentang, semua formulir pendaftaran baru di web publik akan otomatis dikaitkan ke periode ini.
                    </span>
                  </div>
                </label>
              </div>

              {/* Action Buttons */}
              <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="spmb-button-secondary text-xs"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={form.processing}
                  className="spmb-button-primary text-xs"
                >
                  {form.processing
                    ? 'Menyimpan...'
                    : editingItem
                    ? 'Simpan Perubahan'
                    : 'Tambah Periode'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
