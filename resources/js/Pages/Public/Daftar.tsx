import { Link, useForm, usePage } from '@inertiajs/react';
import { ArrowUpRight, Check, ChevronLeft, ChevronRight, Info, RotateCcw, Save } from 'lucide-react';
import { ChangeEvent, useEffect, useMemo, useRef, useState } from 'react';
import PendaftaranBerkasInput, { type BerkasLokal } from '@/Components/PendaftaranBerkasInput';
import PublicLayout from '@/Layouts/PublicLayout';
import { ambilBerkasDraf, ambilDrafPendaftaran, hapusBerkasDraf, hapusSeluruhDrafPendaftaran, simpanBerkasDraf, simpanDrafPendaftaran } from '@/lib/penyimpananDrafPendaftaran';
import type { Jenjang, Persyaratan } from '@/types';

const langkah = ['Pilih jenjang', 'Data calon peserta', 'Data orang tua', 'Berkas & kirim'];
const dataCalon = [
  ['nama_lengkap', 'Nama lengkap'], ['nama_panggilan', 'Nama panggilan'], ['nik', 'NIK calon peserta'], ['nisn', 'NISN'], ['tempat_lahir', 'Tempat lahir'], ['tanggal_lahir', 'Tanggal lahir'], ['agama_calon', 'Agama'], ['suku_bangsa_calon', 'Suku bangsa'], ['anak_ke', 'Anak ke'], ['jumlah_bersaudara', 'Jumlah bersaudara'], ['jumlah_saudara_kandung', 'Saudara kandung'], ['jumlah_saudara_tiri', 'Saudara tiri'], ['jumlah_saudara_angkat', 'Saudara angkat'], ['nomor_telepon_calon', 'Nomor telepon/HP'], ['alamat_domisili', 'Alamat lengkap'], ['asal_sekolah', 'Asal sekolah'], ['alamat_sekolah_asal', 'Alamat sekolah asal'], ['jarak_rumah_km', 'Jarak rumah (km)'], ['jarak_rumah_meter', 'Jarak rumah (meter)'], ['waktu_tempuh_jam', 'Waktu tempuh (jam)'], ['waktu_tempuh_menit', 'Waktu tempuh (menit)'],
];
const dataKeluarga = [
  ['nama_ayah', 'Nama lengkap ayah'], ['tempat_lahir_ayah', 'Tempat lahir ayah'], ['tanggal_lahir_ayah', 'Tanggal lahir ayah'], ['agama_ayah', 'Agama ayah'], ['suku_bangsa_ayah', 'Suku bangsa ayah'], ['pendidikan_ayah', 'Pendidikan terakhir ayah'], ['pekerjaan_ayah', 'Pekerjaan ayah'], ['nomor_telepon_ayah', 'Nomor telepon/HP ayah'], ['penghasilan_ayah', 'Penghasilan ayah per bulan'], ['alamat_ayah', 'Alamat lengkap ayah'],
  ['nama_ibu', 'Nama lengkap ibu'], ['tempat_lahir_ibu', 'Tempat lahir ibu'], ['tanggal_lahir_ibu', 'Tanggal lahir ibu'], ['agama_ibu', 'Agama ibu'], ['suku_bangsa_ibu', 'Suku bangsa ibu'], ['pendidikan_ibu', 'Pendidikan terakhir ibu'], ['pekerjaan_ibu', 'Pekerjaan ibu'], ['nomor_telepon_ibu', 'Nomor telepon/HP ibu'], ['penghasilan_ibu', 'Penghasilan ibu per bulan'], ['alamat_ibu', 'Alamat lengkap ibu'],
  ['nomor_telepon_darurat_1', 'No. telepon darurat 1'], ['nomor_telepon_darurat_2', 'No. telepon darurat 2'],
];
const bidangCalon = new Set([...dataCalon.map(([nama]) => nama), 'jenis_kelamin', 'kewarganegaraan', 'tinggal_bersama']);
const bidangKeluarga = new Set(dataKeluarga.map(([nama]) => nama));
const labelBidang: Record<string, string> = Object.fromEntries([...dataCalon, ...dataKeluarga, ['jenis_kelamin', 'Jenis kelamin'], ['kewarganegaraan', 'Kewarganegaraan'], ['tinggal_bersama', 'Keterangan tempat tinggal'], ['jenjang_pendaftaran_id', 'Jenjang pendidikan'], ['periode_ppdb_id', 'Periode SPMB']]);
const bidangAngkaBulat = new Set(['anak_ke', 'jumlah_bersaudara', 'jumlah_saudara_kandung', 'jumlah_saudara_tiri', 'jumlah_saudara_angkat', 'jarak_rumah_meter', 'waktu_tempuh_jam', 'waktu_tempuh_menit']);
const bidangAngkaDesimal = new Set(['jarak_rumah_km']);
const bidangDigit = new Set(['nik', 'nisn', 'nomor_telepon_calon', 'nomor_telepon_darurat_1', 'nomor_telepon_darurat_2', 'nomor_telepon_ayah', 'nomor_telepon_ibu']);
const bidangWajib = new Set(['nama_lengkap', 'nama_panggilan', 'tempat_lahir', 'tanggal_lahir', 'agama_calon', 'anak_ke', 'jumlah_bersaudara', 'jumlah_saudara_kandung', 'jumlah_saudara_tiri', 'jumlah_saudara_angkat', 'alamat_domisili', 'nomor_telepon_calon', 'asal_sekolah', 'alamat_sekolah_asal', 'jarak_rumah_km', 'jarak_rumah_meter', 'waktu_tempuh_jam', 'waktu_tempuh_menit', 'nama_ayah', 'nomor_telepon_ayah', 'nama_ibu', 'nomor_telepon_ibu']);

type Formulir = Record<string, any>;

export default function Daftar({ jenjang, periode, persyaratan }: { jenjang: Jenjang[]; periode: { id: number; nama: string; tahun_ajaran: string; informasi?: string; instruksi_pembayaran?: string } | null; persyaratan: Persyaratan[] }) {
  const { url } = usePage();
  const [langkahAktif, setLangkahAktif] = useState(1);
  const [berkasLokal, setBerkasLokal] = useState<Record<string, BerkasLokal>>({});
  const [pratinjauAktif, setPratinjauAktif] = useState<string | null>(null);
  const [ringkasanKesalahan, setRingkasanKesalahan] = useState<string[]>([]);
  const [drafSiap, setDrafSiap] = useState(false);
  const [drafDipulihkan, setDrafDipulihkan] = useState(false);
  const [drafTersimpan, setDrafTersimpan] = useState(false);
  const urlBerkas = useRef(new Set<string>());
  const fileBerkas = useRef<Record<string, File>>({});
  const idAwal = Number(new URLSearchParams(url.split('?')[1] ?? '').get('jenjang')) || '';
  const pilihanAwal = jenjang.find((item) => item.id === Number(idAwal));
  const form = useForm<Formulir>({
    jenjang_pendaftaran_id: idAwal, periode_ppdb_id: periode?.id ?? '', nama_lengkap: '', nama_panggilan: '', nik: '', nisn: '', tempat_lahir: '', tanggal_lahir: '', jenis_kelamin: '', agama_calon: '', suku_bangsa_calon: '', kewarganegaraan: 'WNI', anak_ke: '', jumlah_saudara_kandung: '', jumlah_saudara_tiri: '', jumlah_saudara_angkat: '', jumlah_bersaudara: '', alamat_domisili: '', nomor_telepon_calon: '', tinggal_bersama: '', jarak_rumah_km: '', jarak_rumah_meter: '', waktu_tempuh_jam: '', waktu_tempuh_menit: '', asal_sekolah: '', alamat_sekolah_asal: '', nomor_telepon_darurat_1: '', nomor_telepon_darurat_2: '', nama_ayah: '', tempat_lahir_ayah: '', tanggal_lahir_ayah: '', agama_ayah: '', suku_bangsa_ayah: '', pendidikan_ayah: '', pekerjaan_ayah: '', alamat_ayah: '', nomor_telepon_ayah: '', penghasilan_ayah: '', nama_ibu: '', tempat_lahir_ibu: '', tanggal_lahir_ibu: '', agama_ibu: '', suku_bangsa_ibu: '', pendidikan_ibu: '', pekerjaan_ibu: '', alamat_ibu: '', nomor_telepon_ibu: '', penghasilan_ibu: '', berkas: {},
  });
  const terpilih = jenjang.find((item) => item.id === Number(form.data.jenjang_pendaftaran_id));
  const berkasDiminta = useMemo(() => {
    const sesuaiKonteks = persyaratan.filter((item) => {
      const sesuaiJenjang = !item.jenjang_pendaftaran_id || item.jenjang_pendaftaran_id === terpilih?.id;
      return sesuaiJenjang && (!item.periode_ppdb_id || item.periode_ppdb_id === periode?.id);
    });
    const terpilihPerKode = new Map<string, Persyaratan>();

    sesuaiKonteks
      .sort((a, b) => ((Boolean(a.jenjang_pendaftaran_id) ? 1 : 0) + (Boolean(a.periode_ppdb_id) ? 1 : 0)) - ((Boolean(b.jenjang_pendaftaran_id) ? 1 : 0) + (Boolean(b.periode_ppdb_id) ? 1 : 0)) || a.urutan - b.urutan || a.id - b.id)
      .forEach((item) => terpilihPerKode.set(item.kode, item));

    return [...terpilihPerKode.values()].sort((a, b) => a.urutan - b.urutan || a.id - b.id);
  }, [persyaratan, terpilih, periode]);
  useEffect(() => () => { urlBerkas.current.forEach((url) => URL.revokeObjectURL(url)); }, []);
  useEffect(() => {
    const pulihkanDraf = async () => {
      const draf = ambilDrafPendaftaran();
      if (!draf) { await hapusBerkasDraf(); return; }
      const fileTersimpan = await ambilBerkasDraf();
      const tampilanBerkas = Object.fromEntries(Object.entries(fileTersimpan).map(([kode, file]) => {
        const url = URL.createObjectURL(file);
        urlBerkas.current.add(url);
        return [kode, { file, url, persentase: 100, sedangMenyiapkan: false, gagal: false } satisfies BerkasLokal];
      }));
      fileBerkas.current = fileTersimpan;
      form.setData({ ...form.data, ...draf.data, berkas: fileTersimpan });
      setBerkasLokal(tampilanBerkas);
      setDrafDipulihkan(true);
      setDrafTersimpan(true);
    };
    void pulihkanDraf().catch(() => undefined).finally(() => setDrafSiap(true));
  }, []);
  useEffect(() => {
    if (!drafSiap || form.processing) return;
    const dataDraf = { ...form.data };
    delete dataDraf.berkas;
    const memilikiIsi = Object.entries(dataDraf).some(([nama, nilai]) => !['kewarganegaraan', 'periode_ppdb_id', 'jenjang_pendaftaran_id'].includes(nama) && nilai !== '');
    if (!memilikiIsi && Object.keys(fileBerkas.current).length === 0) return;
    const timer = window.setTimeout(() => { simpanDrafPendaftaran(dataDraf); setDrafTersimpan(true); }, 500);
    return () => window.clearTimeout(timer);
  }, [drafSiap, form.data, form.processing]);

  const ubahNilai = (nama: string, nilai: string) => form.setData(nama, nilai);
  const field = ([nama, label]: string[]) => {
    if (['alamat_domisili', 'alamat_sekolah_asal', 'alamat_ayah', 'alamat_ibu'].includes(nama)) return <label key={nama} className="sm:col-span-2"><span className="spmb-label">{label}</span><textarea className="spmb-input min-h-28 resize-y" value={form.data[nama] ?? ''} onChange={(event) => ubahNilai(nama, event.target.value)}/>{form.errors[nama] && <p className="spmb-error">{form.errors[nama]}</p>}</label>;
    if (nama === 'pendidikan_ayah' || nama === 'pendidikan_ibu') return <label key={nama}><span className="spmb-label">{label}</span><select className="spmb-input" value={form.data[nama] ?? ''} onChange={(event) => ubahNilai(nama, event.target.value)}><option value="">Pilih pendidikan terakhir</option>{['S3', 'S2', 'S1', 'Diploma', 'SMU', 'SMP', 'SD'].map((pilihan) => <option key={pilihan} value={pilihan}>{pilihan}</option>)}</select>{form.errors[nama] && <p className="spmb-error">{form.errors[nama]}</p>}</label>;
    const angkaBulat = bidangAngkaBulat.has(nama);
    const angkaDesimal = bidangAngkaDesimal.has(nama);
    const hanyaDigit = bidangDigit.has(nama);
    const tipe = nama.startsWith('tanggal_lahir') ? 'date' : angkaBulat || angkaDesimal ? 'number' : hanyaDigit ? 'tel' : 'text';
    const wajib = bidangWajib.has(nama) || nama === 'nisn';
    const batasDigit = nama === 'nisn' ? 10 : nama === 'nik' ? 16 : 24;
    return <label key={nama}><span className="spmb-label">{label}{wajib && <span className="text-red-600"> *</span>}</span><input className="spmb-input" type={tipe} inputMode={angkaBulat || hanyaDigit ? 'numeric' : angkaDesimal ? 'decimal' : undefined} min={angkaBulat || angkaDesimal ? 0 : undefined} step={angkaDesimal ? '0.01' : angkaBulat ? 1 : undefined} maxLength={hanyaDigit ? batasDigit : undefined} required={wajib} value={form.data[nama] ?? ''} onChange={(event) => ubahNilai(nama, hanyaDigit ? event.target.value.replace(/\D/g, '') : event.target.value)}/>{nama === 'nisn' && <p className="spmb-help">NISN 10 digit sesuai data resmi sekolah asal.</p>}{nama === 'nik' && <p className="spmb-help">Diisi bila tersedia.</p>}{form.errors[nama] && <p className="spmb-error">{form.errors[nama]}</p>}</label>;
  };
  const pilihJenjang = (id: string) => {
    const pilihan = jenjang.find((item) => item.id === Number(id));
    form.setData({ ...form.data, jenjang_pendaftaran_id: Number(id), periode_ppdb_id: periode?.id ?? '' });
  };
  const ubahBerkas = (event: ChangeEvent<HTMLInputElement>, kode: string) => {
    const file = event.target.files?.[0];
    event.currentTarget.value = '';
    if (!file) return;

    const sebelumnya = berkasLokal[kode];
    if (sebelumnya) { URL.revokeObjectURL(sebelumnya.url); urlBerkas.current.delete(sebelumnya.url); }
    const urlFile = URL.createObjectURL(file);
    urlBerkas.current.add(urlFile);
    setBerkasLokal((semua) => ({ ...semua, [kode]: { file, url: urlFile, persentase: 0, sedangMenyiapkan: true, gagal: false } }));
    fileBerkas.current[kode] = file;
    form.setData('berkas', { ...fileBerkas.current });
    void simpanBerkasDraf(kode, file).catch(() => undefined);

    const pembaca = new FileReader();
    pembaca.onprogress = (progres) => {
      if (!progres.lengthComputable) return;
      setBerkasLokal((semua) => {
        if (semua[kode]?.url !== urlFile) return semua;
        return { ...semua, [kode]: { ...semua[kode], persentase: Math.max(8, Math.round((progres.loaded / progres.total) * 100)) } };
      });
    };
    pembaca.onload = () => setBerkasLokal((semua) => semua[kode]?.url === urlFile ? { ...semua, [kode]: { ...semua[kode], persentase: 100, sedangMenyiapkan: false } } : semua);
    pembaca.onerror = () => setBerkasLokal((semua) => semua[kode]?.url === urlFile ? { ...semua, [kode]: { ...semua[kode], sedangMenyiapkan: false, gagal: true } } : semua);
    pembaca.readAsArrayBuffer(file);
  };
  const hapusBerkas = (kode: string) => {
    const berkas = berkasLokal[kode];
    if (berkas) { URL.revokeObjectURL(berkas.url); urlBerkas.current.delete(berkas.url); }
    setBerkasLokal((semua) => { const { [kode]: _dihapus, ...sisa } = semua; return sisa; });
    delete fileBerkas.current[kode];
    form.setData('berkas', { ...fileBerkas.current });
    void hapusBerkasDraf(kode).catch(() => undefined);
    if (pratinjauAktif === kode) setPratinjauAktif(null);
  };
  const berikutnya = () => setLangkahAktif((current) => Math.min(4, current + 1));
  const sebelumnya = () => setLangkahAktif((current) => Math.max(1, current - 1));
  const kirimFormulir = () => {
    setRingkasanKesalahan([]);
    form.post('/daftar', {
      forceFormData: true,
      onSuccess: () => { void hapusSeluruhDrafPendaftaran().catch(() => undefined); },
      onError: (errors) => {
        const bidangPertama = Object.keys(errors)[0] ?? '';
        const namaBidang = bidangPertama.replace(/^berkas\./, '');
        const langkahTujuan = bidangPertama === 'jenjang_pendaftaran_id' || bidangPertama === 'periode_ppdb_id' ? 1 : bidangCalon.has(namaBidang) ? 2 : bidangKeluarga.has(namaBidang) ? 3 : 4;
        setLangkahAktif(langkahTujuan);
        setRingkasanKesalahan(Object.entries(errors).slice(0, 4).map(([bidang, pesan]) => `${labelBidang[bidang.replace(/^berkas\./, '')] ?? bidang.replace(/^berkas\./, '')}: ${pesan}`));
        window.requestAnimationFrame(() => document.getElementById('formulir-pendaftaran')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      },
    });
  };
  const mulaiUlang = () => {
    urlBerkas.current.forEach((url) => URL.revokeObjectURL(url));
    urlBerkas.current.clear();
    fileBerkas.current = {};
    setBerkasLokal({});
    setPratinjauAktif(null);
    setRingkasanKesalahan([]);
    setDrafDipulihkan(false);
    setDrafTersimpan(false);
    form.reset();
    void hapusSeluruhDrafPendaftaran().catch(() => undefined);
  };

  return <PublicLayout title="Formulir Pendaftaran">
    <section className="flex-1 py-10 sm:py-16 lg:py-20"><div className="spmb-container">
      <header className="mx-auto max-w-3xl text-center">
        <span className="inline-flex items-center gap-1.5 rounded-full bg-[#e7efe8] px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary">
          Portal Pendaftaran Santri Baru
        </span>
        <h1 className="public-display mt-3 text-3xl font-bold text-ink sm:text-4xl">Formulir Pendaftaran</h1>
        <p className="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
          Lengkapi 4 tahapan data di bawah ini sesuai dokumen resmi. Data &amp; berkas disimpan privat untuk proses seleksi.
        </p>
      </header>
      <div className="mx-auto mt-10 max-w-5xl">
        <ol className="grid grid-cols-2 gap-x-3 gap-y-4 border-y border-slate-300/80 py-5 sm:grid-cols-4" aria-label="Tahapan pendaftaran">{langkah.map((nama, index) => { const nomor = index + 1; const selesai = nomor < langkahAktif; const aktif = nomor === langkahAktif; return <li key={nama} className="flex min-w-0 items-center gap-2"><span className={`grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold ${aktif ? 'bg-primary text-white' : selesai ? 'bg-success text-white' : 'bg-white text-slate-400 ring-1 ring-slate-300'}`}>{selesai ? <Check className="h-4 w-4"/> : nomor}</span><span className={`truncate text-sm font-bold ${aktif ? 'text-ink' : 'text-slate-500'}`}>{nama}</span></li>; })}</ol>
        <form id="formulir-pendaftaran" className="mt-8 rounded-[1.25rem] border border-slate-200 bg-white p-5 shadow-[0_1.25rem_3rem_rgba(3,41,68,.06)] sm:p-8" onSubmit={(event) => { event.preventDefault(); kirimFormulir(); }}>
          {ringkasanKesalahan.length > 0 && <div className="mb-7 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-950" role="alert" aria-live="assertive"><p className="font-bold">Formulir belum terkirim.</p><p className="mt-1 leading-6">Lengkapi atau perbaiki bagian berikut, lalu kirim kembali.</p><ul className="mt-2 list-disc space-y-1 pl-5">{ringkasanKesalahan.map((pesan) => <li key={pesan}>{pesan}</li>)}</ul></div>}
          {langkahAktif === 1 && <div className="mx-auto max-w-2xl"><div className="flex items-start gap-3"><span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-primary"><Info className="h-5 w-5"/></span><div><h2 className="text-xl font-bold text-ink">Pilih unit tujuan</h2><p className="mt-1 text-sm leading-6 text-slate-600">Pendaftaran ditangani panitia unit Asshodiqiyah yang dipilih.</p></div></div><label className="mt-7 block"><span className="spmb-label">Unit pendidikan</span><select className="spmb-input" value={form.data.jenjang_pendaftaran_id} onChange={(event) => pilihJenjang(event.target.value)}><option value="">Pilih unit</option>{jenjang.map((item) => <option key={item.id} value={item.id}>{item.nama}</option>)}</select>{form.errors.jenjang_pendaftaran_id && <p className="spmb-error">{form.errors.jenjang_pendaftaran_id}</p>}</label>{terpilih && <div className="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600"><strong className="text-ink">{terpilih.nama}</strong>{periode ? ` · Periode ${periode.nama} (${periode.tahun_ajaran}).` : ' · Periode pendaftaran belum dibuka oleh panitia.'}</div>}{!periode && terpilih && <p className="mt-3 text-xs font-semibold text-red-600">Periode SPMB belum aktif. Silakan hubungi panitia atau pantau informasi terbaru.</p>}<div className="mt-8 flex justify-end"><button type="button" disabled={!terpilih || !periode} className="spmb-button-primary" onClick={berikutnya}>Lanjutkan <ChevronRight className="h-4 w-4"/></button></div></div>}
          {langkahAktif === 2 && <div><div><h2 className="text-xl font-bold text-ink">Data calon peserta</h2><p className="mt-1 text-sm text-slate-600">Gunakan data yang sama dengan dokumen identitas calon peserta.</p></div><div className="mt-7 grid gap-5 sm:grid-cols-2">{dataCalon.filter(([nama]) => nama !== 'nisn' || terpilih?.kelompok !== 'paud').map(field)}<label><span className="spmb-label">Jenis kelamin <span className="text-red-600">*</span></span><select required className="spmb-input" value={form.data.jenis_kelamin} onChange={(event) => ubahNilai('jenis_kelamin', event.target.value)}><option value="">Pilih jenis kelamin</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select>{form.errors.jenis_kelamin && <p className="spmb-error">{form.errors.jenis_kelamin}</p>}</label><label><span className="spmb-label">Kewarganegaraan <span className="text-red-600">*</span></span><select required className="spmb-input" value={form.data.kewarganegaraan} onChange={(event) => ubahNilai('kewarganegaraan', event.target.value)}><option value="WNI">WNI</option><option value="WNA">WNA</option><option value="Keturunan/Campuran">Keturunan/Campuran</option></select>{form.errors.kewarganegaraan && <p className="spmb-error">{form.errors.kewarganegaraan}</p>}</label><label><span className="spmb-label">Bertempat tinggal dengan</span><select className="spmb-input" value={form.data.tinggal_bersama} onChange={(event) => ubahNilai('tinggal_bersama', event.target.value)}><option value="">Pilih keterangan</option><option value="orang_tua">Orang tua</option><option value="famili">Famili</option><option value="lain_lain">Lain-lain</option></select>{form.errors.tinggal_bersama && <p className="spmb-error">{form.errors.tinggal_bersama}</p>}</label></div></div>}
          {langkahAktif === 3 && <div><div><h2 className="text-xl font-bold text-ink">Data orang tua & kontak</h2><p className="mt-1 text-sm text-slate-600">Data ini dipakai panitia untuk verifikasi dan komunikasi pendaftaran.</p></div><div className="mt-7 grid gap-5 sm:grid-cols-2">{dataKeluarga.map(field)}</div></div>}
          {langkahAktif === 4 && <div><div><h2 className="text-xl font-bold text-ink">Berkas persyaratan</h2><p className="mt-1 text-sm text-[#315c80]">Periksa pratinjau sebelum mengirim. Berkas tidak tersimpan sampai formulir berhasil dikirim.</p></div>{periode?.instruksi_pembayaran && <p className="mt-5 rounded-xl bg-blue-50 px-4 py-3 text-sm leading-6 text-[#174e7a]">{periode.instruksi_pembayaran}</p>}<div className="mt-7 grid gap-4 sm:grid-cols-2">{berkasDiminta.map((item) => <PendaftaranBerkasInput key={item.kode} persyaratan={item} berkas={berkasLokal[item.kode]} pratinjauAktif={pratinjauAktif === item.kode} progresKirim={form.processing ? form.progress?.percentage ?? 0 : undefined} error={form.errors[`berkas.${item.kode}`]} onPilih={(event) => ubahBerkas(event, item.kode)} onHapus={() => hapusBerkas(item.kode)} onUbahPratinjau={() => setPratinjauAktif((aktif) => aktif === item.kode ? null : item.kode)} />)}</div></div>}
          {langkahAktif > 1 && <div className="mt-9 flex items-center justify-between border-t border-slate-100 pt-5"><button type="button" className="spmb-button-secondary" onClick={sebelumnya}><ChevronLeft className="h-4 w-4"/>Kembali</button>{langkahAktif < 4 ? <button type="button" className="spmb-button-primary" onClick={berikutnya}>Lanjutkan <ChevronRight className="h-4 w-4"/></button> : <button disabled={form.processing} className="spmb-button-primary">{form.processing ? 'Mengirim formulir…' : 'Kirim formulir'}</button>}</div>}
          {drafSiap && <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5 text-xs leading-5 text-slate-600" aria-live="polite"><span className="inline-flex items-center gap-2"><Save className="h-4 w-4 text-primary" />{drafDipulihkan ? 'Draf sebelumnya dipulihkan di perangkat ini.' : drafTersimpan ? 'Draf disimpan otomatis di perangkat ini.' : 'Perubahan akan disimpan otomatis di perangkat ini.'}</span><button type="button" className="inline-flex items-center gap-1.5 font-bold text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-primary" onClick={mulaiUlang}><RotateCcw className="h-3.5 w-3.5" />Mulai ulang</button></div>}
        </form>
      </div>
    </div></section>
  </PublicLayout>;
}
