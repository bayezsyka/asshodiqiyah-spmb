export type Jenjang = { id:number; kode:string; nama:string; kelompok:string };
export type Persyaratan = { id:number; jenjang_pendaftaran_id:number|null; periode_ppdb_id:number|null; kode:string; nama:string; keterangan:string|null; wajib:boolean; urutan:number };
export type StatusOption = { value:string; label:string };
