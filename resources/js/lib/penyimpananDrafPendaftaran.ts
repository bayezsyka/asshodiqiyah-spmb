const KUNCI_DRAF = 'spmb.pendaftaran.draf.v1';
const NAMA_DATABASE = 'spmb-lenterahati';
const NAMA_STORE = 'berkas-draf-pendaftaran';
const MASA_BERLAKU_MS = 24 * 60 * 60 * 1000;

type DrafData = { data: Record<string, unknown>; disimpanPada: number };
type BerkasTersimpan = { kode: string; nama: string; tipe: string; terakhirDiubah: number; blob: Blob; disimpanPada: number };

function bukaDatabase(): Promise<IDBDatabase> {
  return new Promise((resolve, reject) => {
    const permintaan = indexedDB.open(NAMA_DATABASE, 1);
    permintaan.onupgradeneeded = () => {
      if (!permintaan.result.objectStoreNames.contains(NAMA_STORE)) permintaan.result.createObjectStore(NAMA_STORE, { keyPath: 'kode' });
    };
    permintaan.onsuccess = () => resolve(permintaan.result);
    permintaan.onerror = () => reject(permintaan.error);
  });
}

export function ambilDrafPendaftaran(): DrafData | null {
  try {
    const nilai = sessionStorage.getItem(KUNCI_DRAF);
    if (!nilai) return null;
    const draf = JSON.parse(nilai) as DrafData;
    if (!draf.disimpanPada || Date.now() - draf.disimpanPada > MASA_BERLAKU_MS) {
      sessionStorage.removeItem(KUNCI_DRAF);
      return null;
    }
    return draf;
  } catch { return null; }
}

export function simpanDrafPendaftaran(data: Record<string, unknown>): void {
  sessionStorage.setItem(KUNCI_DRAF, JSON.stringify({ data, disimpanPada: Date.now() } satisfies DrafData));
}

export async function simpanBerkasDraf(kode: string, file: File): Promise<void> {
  const database = await bukaDatabase();
  await new Promise<void>((resolve, reject) => {
    const transaksi = database.transaction(NAMA_STORE, 'readwrite');
    transaksi.objectStore(NAMA_STORE).put({ kode, nama: file.name, tipe: file.type, terakhirDiubah: file.lastModified, blob: file, disimpanPada: Date.now() } satisfies BerkasTersimpan);
    transaksi.oncomplete = () => resolve();
    transaksi.onerror = () => reject(transaksi.error);
  });
  database.close();
}

export async function ambilBerkasDraf(): Promise<Record<string, File>> {
  const database = await bukaDatabase();
  const berkas = await new Promise<BerkasTersimpan[]>((resolve, reject) => {
    const transaksi = database.transaction(NAMA_STORE, 'readonly');
    const permintaan = transaksi.objectStore(NAMA_STORE).getAll();
    permintaan.onsuccess = () => resolve(permintaan.result as BerkasTersimpan[]);
    permintaan.onerror = () => reject(permintaan.error);
  });
  database.close();
  const berkasKadaluarsa = berkas.filter((item) => Date.now() - item.disimpanPada > MASA_BERLAKU_MS);

  if (berkasKadaluarsa.length > 0) {
    await hapusBerkasDraf(berkasKadaluarsa.map((item) => item.kode));
  }

  return Object.fromEntries(
    berkas
      .filter((item) => Date.now() - item.disimpanPada <= MASA_BERLAKU_MS)
      .map((item) => [item.kode, new File([item.blob], item.nama, { type: item.tipe, lastModified: item.terakhirDiubah })]),
  );
}

export async function hapusBerkasDraf(kode?: string | string[]): Promise<void> {
  const database = await bukaDatabase();
  await new Promise<void>((resolve, reject) => {
    const transaksi = database.transaction(NAMA_STORE, 'readwrite');
    const store = transaksi.objectStore(NAMA_STORE);
    if (Array.isArray(kode)) {
      kode.forEach((nilai) => store.delete(nilai));
    } else if (kode) {
      store.delete(kode);
    } else {
      store.clear();
    }
    transaksi.oncomplete = () => resolve();
    transaksi.onerror = () => reject(transaksi.error);
  });
  database.close();
}

export async function hapusSeluruhDrafPendaftaran(): Promise<void> {
  sessionStorage.removeItem(KUNCI_DRAF);
  await hapusBerkasDraf();
}
