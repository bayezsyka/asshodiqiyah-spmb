import { Link } from '@inertiajs/react';
import { ArrowRight, Check, ClipboardCheck, FileCheck2, Search, ShieldCheck } from 'lucide-react';
import PublicLayout from '@/Layouts/PublicLayout';
import type { Jenjang } from '@/types';

const alurPendaftaran = [
  { icon: ClipboardCheck, text: 'Isi formulir sesuai dokumen asli' },
  { icon: FileCheck2, text: 'Unggah berkas persyaratan' },
  { icon: ShieldCheck, text: 'Pantau informasi dari panitia' },
];

export default function Home({ jenjang, periodeAktif }: { jenjang: Jenjang[]; periodeAktif: { nama: string; tahun_ajaran: string; informasi?: string } | null }) {
  return <PublicLayout title="SPMB Online">
    <section className="home-hero home-hero-pattern relative isolate overflow-hidden pb-16 pt-36 text-white sm:pb-24 sm:pt-44 lg:pb-28">
      <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_90%_15%,rgba(0,119,200,.24),transparent_32rem)]"/>
      <div className="spmb-container grid gap-12 lg:grid-cols-[1.08fr_.72fr] lg:items-end lg:gap-24">
        <div><p className="text-xs font-bold uppercase tracking-[0.14em] text-decorative">SPMB Lenterahati IBS</p><h1 className="public-display mt-5 max-w-4xl text-[clamp(3rem,6vw,5.75rem)] leading-[1.01]">Pendaftaran yang <span className="text-decorative">lebih jelas,</span> untuk langkah awal terbaik.</h1><p className="mt-7 max-w-2xl text-base leading-8 text-white/70 sm:text-lg">Pilih jenjang, isi data sesuai dokumen asli, unggah berkas, lalu pantau setiap perkembangan pendaftaran dengan identitas calon peserta.</p><div className="mt-9 flex flex-wrap gap-3"><Link href="/daftar" className="spmb-button bg-decorative text-ink hover:bg-yellow-300">Mulai pendaftaran <ArrowRight className="h-4 w-4"/></Link><Link href="/cek-status" className="spmb-button border border-white/25 text-white hover:bg-white/10"><Search className="h-4 w-4"/>Cek status</Link></div></div>
        <aside className="border-t border-white/20 pt-6 lg:mb-3"><p className="text-[0.66rem] font-bold uppercase tracking-[0.12em] text-white/45">Informasi saat ini</p><h2 className="public-display mt-4 text-3xl text-white">{periodeAktif?.nama ?? 'Pendaftaran PAUD & SPMB'}</h2><p className="mt-3 text-sm font-semibold text-decorative">{periodeAktif ? `Periode ${periodeAktif.tahun_ajaran}` : 'PAUD dibuka sepanjang tahun (tanpa periode)'}</p><p className="mt-5 max-w-md text-sm leading-7 text-white/65">{periodeAktif?.informasi ?? 'Jenjang formal mengikuti periode yang ditetapkan panitia. Pendaftaran PAUD dibuka fleksibel sepanjang tahun tanpa batasan periode.'}</p></aside>
      </div>
    </section>
    <section id="informasi" className="bg-[var(--public-paper)] py-20 sm:py-24"><div className="spmb-container"><div className="grid gap-8 lg:grid-cols-[.82fr_1.18fr] lg:items-end lg:gap-24"><header><h2 className="public-display max-w-xl text-[clamp(2.5rem,4.8vw,4.25rem)] text-ink">Pilih jenjang tujuan.</h2></header><p className="max-w-2xl text-base leading-8 text-slate-600">Setiap jenjang memiliki informasi dan persyaratan yang dikelola panitia. Formulir akan menyesuaikan kebutuhan pendaftaran yang dipilih.</p></div><div className="mt-12 grid border-y border-slate-300/75 sm:grid-cols-2 lg:grid-cols-4">{jenjang.map((j, index) => <Link key={j.id} href={`/daftar?jenjang=${j.id}`} className="group border-slate-300/75 py-7 transition hover:bg-white sm:px-7 lg:border-l lg:py-10 first:lg:border-l-0"><span className="text-[0.68rem] font-bold uppercase tracking-[0.12em] text-primary">{j.kelompok === 'paud' ? 'Pendaftaran sepanjang tahun (tanpa periode)' : 'Pendaftaran periode reguler'}</span><h3 className="public-display mt-4 text-2xl text-ink">{j.nama}</h3><span className="mt-6 inline-flex items-center gap-2 text-sm font-bold text-primary">Isi formulir <ArrowRight className="h-4 w-4 transition group-hover:translate-x-1"/></span></Link>)}</div></div></section>
    <section className="bg-white py-20 sm:py-24"><div className="spmb-container grid gap-12 lg:grid-cols-[.88fr_1.12fr] lg:gap-24"><div><h2 className="public-display text-[clamp(2.5rem,4.8vw,4.25rem)] text-ink">Proses yang mudah dipantau.</h2><p className="mt-6 max-w-lg leading-8 text-slate-600">Tidak perlu akun wali santri. Status dapat diperiksa dengan NISN atau NIK, tanggal lahir, dan nama ibu kandung.</p></div><div className="border-t border-slate-300/80">{alurPendaftaran.map(({ icon: Icon, text }) => <div key={text} className="flex items-center gap-5 border-b border-slate-300/80 py-5"><span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-primary"><Icon className="h-5 w-5"/></span><p className="font-bold text-ink">{text}</p><Check className="ml-auto h-5 w-5 text-success"/></div>)}</div></div></section>
  </PublicLayout>;
}
