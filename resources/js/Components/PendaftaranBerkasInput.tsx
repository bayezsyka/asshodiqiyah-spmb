import { CheckCircle2, Eye, FileText, Image as ImageIcon, LoaderCircle, RefreshCw, Trash2, UploadCloud, X } from 'lucide-react';
import type { ChangeEvent } from 'react';
import type { Persyaratan } from '@/types';

export type BerkasLokal = { file: File; url: string; persentase: number; sedangMenyiapkan: boolean; gagal: boolean };

function formatUkuran(ukuran: number) {
  return ukuran < 1024 * 1024 ? `${Math.max(1, Math.round(ukuran / 1024))} KB` : `${(ukuran / 1024 / 1024).toFixed(1)} MB`;
}

export default function PendaftaranBerkasInput({ persyaratan, berkas, pratinjauAktif, progresKirim, error, onPilih, onHapus, onUbahPratinjau }: { persyaratan: Persyaratan; berkas?: BerkasLokal; pratinjauAktif: boolean; progresKirim?: number; error?: string; onPilih: (event: ChangeEvent<HTMLInputElement>) => void; onHapus: () => void; onUbahPratinjau: () => void }) {
  const id = `berkas-${persyaratan.kode}`;
  const foto = berkas?.file.type.startsWith('image/');
  const sedangMengirim = progresKirim !== undefined;

  return <article className={`overflow-hidden rounded-xl border transition-colors ${error ? 'border-red-300 bg-red-50/30' : berkas ? 'border-primary/25 bg-blue-50/30' : 'border-slate-200 bg-white hover:border-primary/40'}`}>
    <div className="p-4">
      <div className="flex gap-3"><span className={`grid h-10 w-10 shrink-0 place-items-center rounded-xl ${berkas ? 'bg-primary text-white' : 'bg-slate-100 text-primary'}`}>{foto ? <ImageIcon className="h-5 w-5" /> : <FileText className="h-5 w-5" />}</span><div className="min-w-0 flex-1"><div className="flex flex-wrap items-start justify-between gap-x-3 gap-y-1"><p className="text-sm font-bold leading-5 text-ink">{persyaratan.nama}{persyaratan.wajib && <span className="text-red-600"> *</span>}</p>{berkas && <span className="inline-flex items-center gap-1 text-xs font-bold text-success"><CheckCircle2 className="h-3.5 w-3.5" />Siap dikirim</span>}</div>{persyaratan.keterangan && <p className="mt-1 text-xs leading-5 text-slate-500">{persyaratan.keterangan}</p>}</div></div>

      {!berkas ? <label htmlFor={id} className="mt-4 flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm font-bold text-primary transition hover:border-primary hover:bg-blue-50 focus-within:ring-2 focus-within:ring-primary/30"><UploadCloud className="h-4 w-4" />Pilih file<input id={id} className="sr-only" type="file" accept=".pdf,.jpg,.jpeg,.png" onChange={onPilih} /></label> : <><div className="mt-4 flex min-w-0 items-center gap-3 rounded-lg bg-white/80 p-3 ring-1 ring-inset ring-primary/10">{foto ? <img src={berkas.url} alt="Pratinjau berkas yang dipilih" className="h-11 w-11 shrink-0 rounded-md object-cover" /> : <FileText className="h-8 w-8 shrink-0 text-red-500" />}<div className="min-w-0 flex-1"><p className="truncate text-sm font-semibold text-ink">{berkas.file.name}</p><p className="mt-0.5 text-xs text-[#315c80]">{formatUkuran(berkas.file.size)} · {foto ? 'Akan disimpan sebagai WebP' : 'PDF asli'}</p></div></div><div className="mt-3 flex flex-wrap gap-2"><button type="button" className="spmb-button-secondary !px-3 !py-2 !text-xs" onClick={onUbahPratinjau}><Eye className="h-3.5 w-3.5" />{pratinjauAktif ? 'Tutup' : 'Pratinjau'}</button><label htmlFor={id} className="spmb-button-secondary cursor-pointer !px-3 !py-2 !text-xs"><RefreshCw className="h-3.5 w-3.5" />Ganti file<input id={id} className="sr-only" type="file" accept=".pdf,.jpg,.jpeg,.png" onChange={onPilih} /></label><button type="button" className="spmb-button-secondary !border-red-200 !px-3 !py-2 !text-xs !text-red-700 hover:!bg-red-50" onClick={onHapus}><Trash2 className="h-3.5 w-3.5" />Hapus</button></div></>}

      <p className="mt-3 text-xs leading-5 text-slate-500">PDF, JPG, atau PNG · maksimal 5 MB. Foto dikompresi dan dikonversi aman saat dikirim.</p>
      {berkas?.sedangMenyiapkan && <div className="mt-3" aria-live="polite"><div className="flex items-center justify-between text-xs font-semibold text-primary"><span className="inline-flex items-center gap-1.5"><LoaderCircle className="h-3.5 w-3.5 animate-spin" />Menyiapkan pratinjau</span><span>{berkas.persentase}%</span></div><div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-blue-100"><div className="h-full rounded-full bg-primary transition-[width] duration-200" style={{ width: `${berkas.persentase}%` }} /></div></div>}
      {sedangMengirim && <div className="mt-3" aria-live="polite"><div className="flex items-center justify-between text-xs font-semibold text-primary"><span className="inline-flex items-center gap-1.5"><LoaderCircle className="h-3.5 w-3.5 animate-spin" />Mengunggah bersama formulir</span><span>{progresKirim}%</span></div><div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-blue-100"><div className="h-full rounded-full bg-primary transition-[width] duration-200" style={{ width: `${progresKirim}%` }} /></div></div>}
      {error && <p className="mt-3 text-xs font-medium leading-5 text-red-700">{error}</p>}
    </div>
    {berkas && pratinjauAktif && <div className="border-t border-primary/15 bg-slate-50 p-3">{foto ? <img src={berkas.url} alt={`Pratinjau ${persyaratan.nama}`} className="max-h-72 w-full rounded-lg object-contain" /> : <iframe title={`Pratinjau ${persyaratan.nama}`} src={berkas.url} className="h-72 w-full rounded-lg border border-slate-200 bg-white" />}{berkas.gagal && <p className="mt-2 text-xs text-red-700">Pratinjau tidak dapat disiapkan. Pilih file lain.</p>}</div>}
  </article>;
}
