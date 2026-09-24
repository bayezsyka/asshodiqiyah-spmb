<?php

namespace App\Http\Requests\Pendaftaran;

use App\Models\JenjangPendaftaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class SimpanPendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenjang_pendaftaran_id' => ['required', 'integer', 'exists:jenjang_pendaftaran,id'],
            'periode_ppdb_id' => ['nullable', 'integer', 'exists:periode_ppdb,id'],
            'nisn' => ['nullable', 'string', 'digits:10', 'unique:pendaftaran,nisn'],
            'nik' => ['nullable', 'string', 'digits:16', 'unique:pendaftaran,nik'],

            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nama_panggilan' => ['required', 'string', 'max:100'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'agama_calon' => ['required', 'string', 'max:64'],
            'suku_bangsa_calon' => ['nullable', 'string', 'max:100'],
            'kewarganegaraan' => ['required', 'in:WNI,WNA,Keturunan/Campuran'],
            'anak_ke' => ['nullable', 'integer', 'min:1', 'max:99'],
            'jumlah_saudara_kandung' => ['nullable', 'integer', 'min:0', 'max:99'],
            'jumlah_saudara_tiri' => ['nullable', 'integer', 'min:0', 'max:99'],
            'jumlah_saudara_angkat' => ['nullable', 'integer', 'min:0', 'max:99'],
            'jumlah_bersaudara' => ['nullable', 'integer', 'min:1', 'max:99'],
            'alamat_domisili' => ['required', 'string', 'max:2000'],
            'nomor_telepon_calon' => ['required', 'string', 'max:24'],
            'tinggal_bersama' => ['nullable', 'in:orang_tua,famili,lain_lain'],
            'jarak_rumah_km' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'jarak_rumah_meter' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'waktu_tempuh_jam' => ['nullable', 'integer', 'min:0', 'max:99'],
            'waktu_tempuh_menit' => ['nullable', 'integer', 'min:0', 'max:59'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'alamat_sekolah_asal' => ['nullable', 'string', 'max:2000'],
            'nomor_telepon_darurat_1' => ['nullable', 'string', 'max:24'],
            'nomor_telepon_darurat_2' => ['nullable', 'string', 'max:24'],

            'nama_ayah' => ['required', 'string', 'max:150'],
            'tempat_lahir_ayah' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir_ayah' => ['nullable', 'date'],
            'agama_ayah' => ['nullable', 'string', 'max:64'],
            'suku_bangsa_ayah' => ['nullable', 'string', 'max:100'],
            'pendidikan_ayah' => ['nullable', 'string', 'max:100'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:150'],
            'alamat_ayah' => ['nullable', 'string', 'max:2000'],
            'nomor_telepon_ayah' => ['required', 'string', 'max:24'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:100'],

            'nama_ibu' => ['required', 'string', 'max:150'],
            'tempat_lahir_ibu' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir_ibu' => ['nullable', 'date'],
            'agama_ibu' => ['nullable', 'string', 'max:64'],
            'suku_bangsa_ibu' => ['nullable', 'string', 'max:100'],
            'pendidikan_ibu' => ['nullable', 'string', 'max:100'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:150'],
            'alamat_ibu' => ['nullable', 'string', 'max:2000'],
            'nomor_telepon_ibu' => ['required', 'string', 'max:24'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:100'],

            'berkas' => ['required', 'array', 'max:20'],
            'berkas.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $jenjang = JenjangPendaftaran::find($this->integer('jenjang_pendaftaran_id'));

            if ($jenjang && blank($this->input('nisn'))) {
                $validator->errors()->add('nisn', 'NISN wajib diisi untuk jenjang SD, SMP, dan SMA.');
            }

            // ASVS V5.2: Pas foto wajib berupa file gambar
            foreach ((array) $this->file('berkas') as $kode => $file) {
                if ($file instanceof UploadedFile && in_array($kode, ['pas_foto_calon', 'foto_ayah', 'foto_ibu'], true)) {
                    $mime = (string) $file->getMimeType();
                    $ext = strtolower((string) $file->getClientOriginalExtension());
                    if ($mime === 'application/pdf' || $ext === 'pdf') {
                        $validator->errors()->add("berkas.{$kode}", 'Pas foto harus berupa file gambar (JPG, PNG, atau WEBP).');
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $jenjang = JenjangPendaftaran::find($this->integer('jenjang_pendaftaran_id'));

        $this->merge([
            'nisn' => $this->angka($this->input('nisn')),
            'nik' => $this->angka($this->input('nik')),
            'periode_ppdb_id' => $this->input('periode_ppdb_id') ?: null,
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => 'Pilihan :attribute tidak valid.',
            'exists' => ':attribute yang dipilih tidak tersedia.',
            'array' => ':attribute tidak valid.',
            'file' => ':attribute harus berupa file.',
            'mimes' => ':attribute harus berformat PDF, JPG, atau PNG.',
            'mimetypes' => ':attribute harus berformat PDF, JPG, atau PNG.',
            'max' => ':attribute melebihi batas yang diperbolehkan.',
            'max.file' => 'Ukuran :attribute maksimal 5 MB.',
            'min' => ':attribute belum memenuhi nilai minimum.',
        ];
    }

    public function attributes(): array
    {
        return array_map(static fn (string $attribute) => Str::ucfirst($attribute), [
            'jenjang_pendaftaran_id' => 'jenjang pendidikan', 'periode_ppdb_id' => 'periode SPMB',
            'nisn' => 'NISN', 'nik' => 'NIK',
            'nama_lengkap' => 'nama lengkap', 'nama_panggilan' => 'nama panggilan',
            'tempat_lahir' => 'tempat lahir', 'tanggal_lahir' => 'tanggal lahir', 'jenis_kelamin' => 'jenis kelamin', 'agama_calon' => 'agama', 'suku_bangsa_calon' => 'suku bangsa', 'kewarganegaraan' => 'kewarganegaraan',
            'anak_ke' => 'anak ke', 'jumlah_bersaudara' => 'jumlah bersaudara', 'jumlah_saudara_kandung' => 'jumlah saudara kandung', 'jumlah_saudara_tiri' => 'jumlah saudara tiri', 'jumlah_saudara_angkat' => 'jumlah saudara angkat',
            'alamat_domisili' => 'alamat domisili', 'nomor_telepon_calon' => 'nomor telepon calon peserta', 'tinggal_bersama' => 'keterangan tempat tinggal',
            'jarak_rumah_km' => 'jarak rumah (km)', 'jarak_rumah_meter' => 'jarak rumah (meter)', 'waktu_tempuh_jam' => 'waktu tempuh (jam)', 'waktu_tempuh_menit' => 'waktu tempuh (menit)', 'asal_sekolah' => 'asal sekolah', 'alamat_sekolah_asal' => 'alamat sekolah asal',
            'nomor_telepon_darurat_1' => 'nomor telepon darurat 1', 'nomor_telepon_darurat_2' => 'nomor telepon darurat 2',
            'nama_ayah' => 'nama ayah', 'tempat_lahir_ayah' => 'tempat lahir ayah', 'tanggal_lahir_ayah' => 'tanggal lahir ayah', 'agama_ayah' => 'agama ayah', 'suku_bangsa_ayah' => 'suku bangsa ayah', 'pendidikan_ayah' => 'pendidikan terakhir ayah', 'pekerjaan_ayah' => 'pekerjaan ayah', 'alamat_ayah' => 'alamat ayah', 'nomor_telepon_ayah' => 'nomor telepon ayah', 'penghasilan_ayah' => 'penghasilan ayah per bulan',
            'nama_ibu' => 'nama ibu', 'tempat_lahir_ibu' => 'tempat lahir ibu', 'tanggal_lahir_ibu' => 'tanggal lahir ibu', 'agama_ibu' => 'agama ibu', 'suku_bangsa_ibu' => 'suku bangsa ibu', 'pendidikan_ibu' => 'pendidikan terakhir ibu', 'pekerjaan_ibu' => 'pekerjaan ibu', 'alamat_ibu' => 'alamat ibu', 'nomor_telepon_ibu' => 'nomor telepon ibu', 'penghasilan_ibu' => 'penghasilan ibu per bulan',
            'berkas' => 'berkas persyaratan', 'berkas.*' => 'berkas persyaratan',
        ]);
    }

    private function angka(mixed $nilai): ?string
    {
        $angka = preg_replace('/\D/', '', (string) $nilai) ?: null;

        return $angka;
    }
}
