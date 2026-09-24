import { useForm } from '@inertiajs/react';
import { ArrowRight, ContactRound, LockKeyhole } from 'lucide-react';
import PublicLayout from '@/Layouts/PublicLayout';

export default function CekStatus() {
  const form = useForm({ identitas: '', tanggal_lahir: '', nama_ibu: '' });
  return <PublicLayout title="Cek Status Pendaftaran">
    <section className="flex flex-1 items-center py-12 sm:py-20 lg:py-24"><div className="spmb-container">
      <div className="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[.85fr_1.15fr] lg:items-start lg:gap-16">
        <header className="pt-2">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary">
            Portal Pendaftaran SPMB
          </span>
          <h1 className="public-display mt-4 text-3xl font-bold text-ink sm:text-4xl">Cek Status Pendaftaran</h1>
          <p className="mt-4 leading-7 text-slate-600">Gunakan identitas calon peserta dan nama ibu untuk memantau perkembangan seleksi serta instruksi dari panitia.</p>
          <div className="mt-8 space-y-4 border-t border-slate-200 pt-6 text-sm text-slate-600">
            <p className="flex items-start gap-3"><ContactRound className="mt-0.5 h-4 w-4 shrink-0 text-primary" />Masukkan NISN atau NIK calon peserta sesuai data yang dikirim saat pendaftaran.</p>
            <p className="flex items-start gap-3"><LockKeyhole className="mt-0.5 h-4 w-4 shrink-0 text-primary" />Sistem menjaga keamanan data pendaftaran wali santri secara privat.</p>
          </div>
        </header>
        <form className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" onSubmit={(event) => { event.preventDefault(); form.post('/cek-status'); }}>
          <h2 className="text-xl font-bold text-ink">Data Verifikasi Status</h2>
          <p className="mt-1 text-sm text-slate-600">Isi data di bawah ini sesuai data calon santri yang didaftarkan.</p>
          <label className="mt-6 block"><span className="spmb-label">NISN atau NIK Calon Peserta</span><input className="spmb-input font-semibold tracking-wide" value={form.data.identitas} onChange={(event) => form.setData('identitas', event.target.value.replace(/\D/g, ''))} placeholder="10 digit NISN atau 16 digit NIK" autoComplete="off" inputMode="numeric" maxLength={16} required />{form.errors.identitas && <p className="spmb-error">{form.errors.identitas}</p>}</label>
          <label className="mt-5 block"><span className="spmb-label">Tanggal Lahir Calon Santri</span><input className="spmb-input" type="date" required value={form.data.tanggal_lahir} onChange={(event) => form.setData('tanggal_lahir', event.target.value)} />{form.errors.tanggal_lahir && <p className="spmb-error">{form.errors.tanggal_lahir}</p>}</label>
          <label className="mt-5 block"><span className="spmb-label">Nama Ibu Kandung</span><input className="spmb-input" required value={form.data.nama_ibu} onChange={(event) => form.setData('nama_ibu', event.target.value)} autoComplete="name" />{form.errors.nama_ibu && <p className="spmb-error">{form.errors.nama_ibu}</p>}</label>
          <button className="spmb-button-primary mt-7 w-full" disabled={form.processing}>{form.processing ? 'Memeriksa Data…' : <>Periksa Status Pendaftaran <ArrowRight className="h-4 w-4" /></>}</button>
        </form>
      </div>
    </div></section>
  </PublicLayout>;
}
