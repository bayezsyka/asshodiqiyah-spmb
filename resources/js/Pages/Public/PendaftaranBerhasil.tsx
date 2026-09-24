import { Link } from '@inertiajs/react';
import { CheckCircle2, ClipboardCheck } from 'lucide-react';
import PublicLayout from '@/Layouts/PublicLayout';

export default function PendaftaranBerhasil() {
  return <PublicLayout title="Pendaftaran Berhasil">
    <section className="flex flex-1 items-center py-16 sm:py-24"><div className="spmb-container"><div className="mx-auto max-w-2xl text-center"><span className="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-100 text-success"><CheckCircle2 className="h-8 w-8" /></span><h1 className="public-display mt-6 text-[clamp(2.3rem,5vw,3.5rem)] text-ink">Pendaftaran berhasil dikirim.</h1><p className="mx-auto mt-4 max-w-xl leading-7 text-slate-600">Panitia akan memeriksa data dan berkas yang Anda kirimkan.</p>
      <section className="mt-8 rounded-2xl border border-primary/25 bg-blue-50 px-5 py-6 text-left sm:px-8"><p className="text-xs font-bold uppercase tracking-[.12em] text-[#174e7a]">Untuk mengecek status</p><p className="mt-3 text-sm leading-6 text-[#174e7a]">Gunakan NISN atau NIK calon peserta, tanggal lahir, dan nama ibu kandung sesuai data formulir.</p></section>
      <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row"><Link href="/cek-status" className="spmb-button-primary"><ClipboardCheck className="h-4 w-4" />Cek status pendaftaran</Link><Link href="/" className="spmb-button-secondary">Kembali ke portal SPMB</Link></div></div></div></section>
  </PublicLayout>;
}
