import { Link, router, useForm } from '@inertiajs/react';
import {
  Filter,
  Plus,
  RotateCcw,
  Search,
  Shield,
  ShieldAlert,
  UserPlus,
  Users,
  X,
} from 'lucide-react';
import { FormEvent, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

type UserItem = {
  id: number;
  name: string;
  username: string;
  email: string;
  peran: string;
  peranLabel: string;
  statusAktif: boolean;
  statusLabel: string;
  googleTerhubung: boolean;
  lastLoginAt: string | null;
  createdAt: string | null;
  isCurrentUser: boolean;
  canBeManaged: boolean;
  updateUrl: string;
};

type Option = { value: string; label: string };
type Paginated<T> = {
  data: T[];
  current_page: number;
  last_page: number;
  from: number | null;
  to: number | null;
  total: number;
  links: { url: string | null; label: string; active: boolean }[];
};

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
  dateStyle: 'medium',
  timeStyle: 'short',
});

function formatDate(value: string | null, fallback = 'Belum pernah') {
  return value ? dateFormatter.format(new Date(value)) : fallback;
}

function paginationLabel(label: string) {
  if (label.includes('Previous')) return 'Sebelumnya';
  if (label.includes('Next')) return 'Berikutnya';
  return label.replace(/&laquo;|&raquo;/g, '').trim();
}

function AccountActions({ user }: { user: UserItem }) {
  const [processing, setProcessing] = useState(false);
  const [error, setError] = useState('');

  const update = (action: 'suspend' | 'reactivate') => {
    const message = action === 'suspend'
      ? `Nonaktifkan akses ${user.name}? Pengguna akan langsung kehilangan akses ke panel SPMB.`
      : `Aktifkan kembali akses ${user.name}?`;

    if (!window.confirm(message)) return;

    router.patch(user.updateUrl, { action }, {
      preserveScroll: true,
      onStart: () => {
        setError('');
        setProcessing(true);
      },
      onFinish: () => setProcessing(false),
      onError: (errors) => setError(String(errors.action ?? 'Perubahan tidak dapat disimpan.')),
    });
  };

  if (!user.canBeManaged) {
    return (
      <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
        <Shield className="h-3.5 w-3.5" />
        {user.isCurrentUser ? 'Akun Anda' : 'Akun utama'}
      </span>
    );
  }

  return (
    <div>
      <button
        type="button"
        disabled={processing}
        onClick={() => update(user.statusAktif ? 'suspend' : 'reactivate')}
        className={user.statusAktif
          ? 'inline-flex h-9 items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-bold text-amber-800 transition hover:bg-amber-100 disabled:opacity-50'
          : 'inline-flex h-9 items-center gap-1.5 rounded-lg bg-primary px-3 text-xs font-bold text-white transition hover:bg-primary-dark disabled:opacity-50'}
      >
        {user.statusAktif
          ? <><ShieldAlert className="h-3.5 w-3.5" />Nonaktifkan</>
          : <><RotateCcw className="h-3.5 w-3.5" />Aktifkan kembali</>}
      </button>
      {error && <p className="mt-1 text-xs font-medium text-red-600">{error}</p>}
    </div>
  );
}

function CreateUserModal({ open, onClose, storeUrl }: { open: boolean; onClose: () => void; storeUrl: string }) {
  const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    peran: 'admin_spmb',
  });

  if (!open) return null;

  const submit = (event: FormEvent) => {
    event.preventDefault();
    form.post(storeUrl, {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        onClose();
      },
    });
  };

  return (
    <div className="fixed inset-0 z-[70] grid place-items-center bg-slate-950/45 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="create-user-title">
      <div className="spmb-panel max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto p-6 shadow-2xl">
        <div className="flex items-center justify-between border-b border-slate-100 pb-4">
          <div className="flex items-center gap-3">
            <span className="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-primary"><UserPlus className="h-5 w-5" /></span>
            <div>
              <h2 id="create-user-title" className="font-bold text-ink">Tambah akun pengguna</h2>
              <p className="text-xs text-slate-500">Buat akses baru untuk pengelola SPMB.</p>
            </div>
          </div>
          <button type="button" onClick={onClose} className="grid h-9 w-9 place-items-center rounded-lg text-slate-400 hover:bg-slate-100" aria-label="Tutup"><X className="h-4 w-4" /></button>
        </div>

        <form onSubmit={submit} className="mt-5 grid gap-4 sm:grid-cols-2">
          <label className="sm:col-span-2"><span className="spmb-label">Nama lengkap</span><input required className="spmb-input" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />{form.errors.name && <p className="spmb-error">{form.errors.name}</p>}</label>
          <label><span className="spmb-label">Username</span><input required autoComplete="off" className="spmb-input" value={form.data.username} onChange={(event) => form.setData('username', event.target.value)} />{form.errors.username && <p className="spmb-error">{form.errors.username}</p>}</label>
          <label><span className="spmb-label">Peran</span><input disabled className="spmb-input" value="Admin SPMB" /></label>
          <label className="sm:col-span-2"><span className="spmb-label">Email</span><input required type="email" className="spmb-input" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />{form.errors.email && <p className="spmb-error">{form.errors.email}</p>}</label>
          <label><span className="spmb-label">Password</span><input required type="password" minLength={8} className="spmb-input" value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} />{form.errors.password && <p className="spmb-error">{form.errors.password}</p>}</label>
          <label><span className="spmb-label">Konfirmasi password</span><input required type="password" minLength={8} className="spmb-input" value={form.data.password_confirmation} onChange={(event) => form.setData('password_confirmation', event.target.value)} /></label>
          <div className="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2">
            <button type="button" onClick={onClose} className="spmb-button-secondary">Batal</button>
            <button type="submit" disabled={form.processing} className="spmb-button-primary"><Plus className="h-4 w-4" />{form.processing ? 'Membuat…' : 'Buat akun'}</button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function UsersIndex({
  users,
  filters,
  options,
  storeUrl,
}: {
  users: Paginated<UserItem>;
  filters: { search: string; status: string; peran: string };
  options: { peran: Option[]; status: Option[] };
  storeUrl: string;
}) {
  const [search, setSearch] = useState(filters.search);
  const [status, setStatus] = useState(filters.status);
  const [peran, setPeran] = useState(filters.peran);
  const [modalOpen, setModalOpen] = useState(false);

  const applyFilters = (event: FormEvent) => {
    event.preventDefault();
    router.get('/admin/users', { search, status, peran }, { preserveState: true, replace: true });
  };

  return (
    <AdminLayout title="Kelola Pengguna" description="Buat akun baru dan atur akses pengelola SPMB.">
      <div className="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center">
        <form onSubmit={applyFilters} className="spmb-panel grid flex-1 gap-2.5 p-3 sm:grid-cols-2 lg:grid-cols-[minmax(220px,1fr)_160px_170px_auto]">
          <label className="relative sm:col-span-2 lg:col-span-1"><span className="sr-only">Cari pengguna</span><Search className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input type="search" className="spmb-input pl-10" placeholder="Cari nama, username, atau email…" value={search} onChange={(event) => setSearch(event.target.value)} /></label>
          <label><span className="sr-only">Filter status</span><select className="spmb-input" value={status} onChange={(event) => setStatus(event.target.value)}><option value="">Semua status</option>{options.status.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select></label>
          <label><span className="sr-only">Filter peran</span><select className="spmb-input" value={peran} onChange={(event) => setPeran(event.target.value)}><option value="">Semua peran</option>{options.peran.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select></label>
          <button type="submit" className="spmb-button-primary"><Filter className="h-4 w-4" />Terapkan</button>
        </form>
        <button type="button" onClick={() => setModalOpen(true)} className="spmb-button-primary shrink-0"><Plus className="h-4 w-4" />Tambah akun</button>
      </div>

      <section className="spmb-panel overflow-hidden">
        <div className="divide-y divide-slate-100 lg:hidden">
          {users.data.map((user) => <article key={user.id} className="space-y-3 p-4"><div className="flex items-start gap-3"><span className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-50 font-bold text-primary">{user.name.slice(0, 1).toUpperCase()}</span><div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><h2 className="font-bold text-ink">{user.name}</h2>{user.isCurrentUser && <span className="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-primary">Anda</span>}</div><p className="text-xs font-semibold text-primary">@{user.username}</p><p className="truncate text-xs text-slate-500">{user.email}</p></div><span className={`spmb-badge ${user.statusAktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>{user.statusLabel}</span></div><dl className="grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-3 text-xs"><div><dt className="text-slate-400">Peran</dt><dd className="font-bold">{user.peranLabel}</dd></div><div><dt className="text-slate-400">Google</dt><dd className="font-bold">{user.googleTerhubung ? 'Terhubung' : 'Belum'}</dd></div><div className="col-span-2"><dt className="text-slate-400">Login terakhir</dt><dd className="font-medium">{formatDate(user.lastLoginAt)}</dd></div></dl><AccountActions user={user} /></article>)}
        </div>

        <div className="hidden overflow-x-auto lg:block">
          <table className="spmb-table min-w-[900px]"><thead className="bg-slate-50/80"><tr><th>Pengguna</th><th>Peran</th><th>Status</th><th>Google</th><th>Login terakhir</th><th className="text-right">Tindakan</th></tr></thead><tbody>{users.data.map((user) => <tr key={user.id}><td><div className="flex items-center gap-3"><span className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-50 font-bold text-primary">{user.name.slice(0, 1).toUpperCase()}</span><div><div className="flex items-center gap-2"><p className="font-bold text-ink">{user.name}</p>{user.isCurrentUser && <span className="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-primary">Anda</span>}</div><p className="text-xs font-semibold text-primary">@{user.username}</p><p className="text-xs text-slate-500">{user.email}</p></div></div></td><td><span className="spmb-badge bg-indigo-50 text-indigo-700">{user.peranLabel}</span></td><td><span className={`spmb-badge ${user.statusAktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>{user.statusLabel}</span></td><td className="text-xs font-medium">{user.googleTerhubung ? 'Terhubung' : 'Belum terhubung'}</td><td className="whitespace-nowrap text-xs">{formatDate(user.lastLoginAt)}</td><td><div className="flex justify-end"><AccountActions user={user} /></div></td></tr>)}</tbody></table>
        </div>

        {users.data.length === 0 && <div className="px-5 py-16 text-center"><Users className="mx-auto h-10 w-10 text-slate-300" /><p className="mt-3 font-bold text-ink">Pengguna tidak ditemukan</p><p className="mt-1 text-xs text-slate-500">Ubah pencarian atau filter yang digunakan.</p></div>}

        {users.last_page > 1 && <nav className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4"><p className="text-xs font-medium text-slate-500">Menampilkan {users.from}–{users.to} dari {users.total} pengguna</p><div className="flex flex-wrap gap-1.5">{users.links.map((link, index) => link.url ? <Link key={`${link.label}-${index}`} href={link.url} preserveScroll className={`inline-flex h-9 min-w-9 items-center justify-center rounded-xl border px-3 text-xs font-bold ${link.active ? 'border-primary bg-primary text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'}`}>{paginationLabel(link.label)}</Link> : <span key={`${link.label}-${index}`} className="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 px-3 text-xs text-slate-300">{paginationLabel(link.label)}</span>)}</div></nav>}
      </section>

      <CreateUserModal open={modalOpen} onClose={() => setModalOpen(false)} storeUrl={storeUrl} />
    </AdminLayout>
  );
}
