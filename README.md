# HRIS PT Surya Komponen Nusantara

Sistem Manajemen Sumber Daya Manusia berbasis web dengan verifikasi wajah, geofencing, dan penggajian otomatis.

---

### **A. Latar Belakang**
PT Surya Komponen Nusantara (SKN) adalah perusahaan manufaktur komponen otomotif & elektronik dengan ±1.200 karyawan: kelompok produksi (sistem shift, sering lembur) dan kantoran.

Pengelolaan SDM masih manual: absensi *fingerprint* yang rawan titip absen, pengajuan cuti/sakit berbasis kertas, penggajian dihitung manual di *spreadsheet* (rawan salah pada honor lembur & potongan), serta pengumuman/penugasan tersebar di grup chat. Manajemen ingin satu **portal/superapp HRIS berbasis web** dengan hak akses berbasis peran.

### **B. Tantangan**
Bangun **aplikasi web HRIS** yang menyatukan kehadiran, pengajuan, penggajian, penugasan, pengumuman, dan kelas jabatan dalam satu sistem ber-RBAC, dengan **verifikasi wajah** pada absensi, lalu **di-deploy dan dapat diakses publik** di akhir 24 jam.

### **C. Aktor & Hak Akses**
| Aktor | Deskripsi | Hak Akses Inti |
| :--- | :--- | :--- |
| **Pegawai** | Seluruh karyawan | Absensi (*geotag + face recognition*), lihat komponen gaji, ajukan cuti/sakit/lembur, lihat penugasan & pengumuman |
| **Departemen HRD** | Pengelola SDM (termasuk Manajer HRD) | CRUD pegawai, kelas jabatan, penugasan, sakit, lembur, absensi; verifikasi pengajuan; pengumuman; Dashboard HRD |
| **Departemen Keuangan** | Pengelola penggajian | CRUD gaji pokok, honor lembur, potongan; Dashboard Keuangan |

### **D. Alur Proses Bisnis**
1. **HRD** mendaftarkan **Pegawai + Kelas Jabatan**
2. **Pegawai** login -> **Absensi** harian (*geotag + face recognition*) -> status Hadir/Terlambat/Tugas Luar
3. **Pegawai** mengajukan **Cuti / Surat Sakit / Lembur**
4. **HRD memverifikasi** (setuju/tolak) -> lembur disetujui & rekap kehadiran diteruskan ke Keuangan
5. **Keuangan menghitung gaji** = Gaji Pokok + Honor Lembur - Potongan -> **Take-Home Pay**
6. **Dashboard HRD** (masuk/izin/cuti/tugas luar) & **Dashboard Keuangan** (gaji pokok, honor lembur) — *real-time*

### **E. Fitur Utama & Wajib (8)**
| No. | Fitur | Aktor Utama |
| :--- | :--- | :--- |
| 1 | Autentikasi & RBAC (3 peran) | Semua |
| 2 | CRUD data master: data pegawai + kelas jabatan | HRD |
| 3 | Absensi berbasis lokasi (*geotag/geofencing*) | Pegawai |
| 4 | **Face recognition** untuk verifikasi absensi (*AI/ML wajib*) | Pegawai |
| 5 | Pengajuan & verifikasi (cuti, surat sakit, lembur) | Pegawai -> HRD |
| 6 | Penggajian (gaji pokok, honor lembur, potongan, *take-home pay*) | Keuangan |
| 7 | Dashboard HRD + Dashboard Keuangan | HRD, Keuangan |
| 8 | Deploy publik | — |

*\*Komponen AI/ML wajib (satu di tiap soal).*

### **F. Keterkaitan Antar-Fitur (rantai 3-lapis)**
* **Kelas jabatan -> Gaji pokok:** nominal gaji pokok mengacu kelas jabatan pegawai.
* **Absensi -> Dashboard HRD:** rekap hadir/izin/cuti/tugas luar mengisi dashboard.
* **Lembur (disetujui HRD) -> Honor lembur (Keuangan):** hanya lembur tervalidasi yang dihitung.
* **Keterlambatan -> Potongan:** data absensi memengaruhi potongan gaji.
* **Penggajian -> Dashboard Keuangan:** total gaji pokok & honor lembur teragregasi + prediksi.

*\*Tidak ada fitur yang berdiri sendiri: harga & volume mengalir berjenjang dari masyarakat hingga stakeholder.*

### **G. Rincian Kebutuhan Fungsional per Peran**
* **Pegawai**
  Absen masuk/pulang (validasi lokasi dalam radius + verifikasi wajah; status Tepat Waktu/Terlambat/Tugas Luar); ajukan cuti (jenis, tanggal, alasan), surat sakit (unggah lampiran), lembur (jam mulai–selesai); lihat riwayat & status pengajuan; lihat penugasan & pengumuman; lihat komponen gajinya.
* **Departemen HRD**
  CRUD data pegawai (NIK, nama, **foto referensi wajah**, departemen, kelas jabatan, lokasi kerja, status); CRUD kelas jabatan (rentang gaji/tunjangan); CRUD penugasan & pengumuman; verifikasi/koreksi absensi; setuju/tolak cuti/sakit/lembur.
* **Departemen Keuangan**
  CRUD gaji pokok per pegawai/kelas jabatan; CRUD honor lembur (tarif/jam atau hasil kalkulasi); CRUD potongan (BPJS Kesehatan, BPJS Ketenagakerjaan, PPh 21, keterlambatan, pinjaman); hitung *take-home pay*.

### **H. Komponen AI/ML (Wajib)**
**Face Recognition (Computer Vision / Deep Learning)**
* **Fungsi:** verifikasi identitas pegawai saat absensi dengan mencocokkan wajah *live* (kamera perangkat) terhadap foto referensi pegawai.
* **Layak 24 jam:** boleh memakai **model pra-terlatih** (mis. `face-api.js` / `TensorFlow.js`: deteksi + *embedding* wajah) lalu bandingkan *embedding* dengan *cosine similarity* + ambang batas. Tidak perlu melatih dari nol; akurasi sempurna **bukan** syarat — yang dinilai keberadaan & kewajaran alurnya.
* **Bonus AI/ML:** *anomaly detection* pada pola absensi (lokasi di luar radius atau jam tak wajar).

### **I. Dashboard & Visualisasi**
* **Dashboard Manajer HRD:** jumlah karyawan **masuk, izin, cuti, tugas luar** (filter per tanggal/periode); disarankan kartu ringkasan + grafik tren.
* **Dashboard Keuangan:** **total gaji pokok & total honor lembur** periode berjalan; disarankan grafik komposisi beban gaji.

### **J. Ketentuan Teknis & Asumsi**
Peserta **bebas menetapkan asumsi** selama dituliskan di README. Acuan:
* Honor lembur boleh mengacu rumus lazim Indonesia **upah/jam = 1/173 × upah sebulan** (pengali 1,5x/2x), atau disederhanakan jadi tarif/jam yang dapat di-*setting* Keuangan.
* Ambang keterlambatan & radius *geofencing* ditentukan peserta.

### **K. Kebutuhan Non-Fungsional**
Autentikasi & RBAC; geolokasi bila relevan; responsif/*mobile-friendly*; data tersimpan di basis data (bukan *hardcoded*); ter-*deploy* & dapat diakses publik; integritas/konsistensi status berjenjang & dapat dilacak.

### **L. Deploy & Akses**

| Panel | URL |
| :--- | :--- |
| **Pegawai (default)** | `https://playit.byhaqi.my.id/` |
| **HRD** | `https://playit.byhaqi.my.id/hrd/login` |
| **Keuangan** | `https://playit.byhaqi.my.id/accounting/login` |

### **M. Akun Demo**

| Peran | Email | Password | URL Login |
| :--- | :--- | :--- | :--- |
| **Pegawai** | `employee@example.com` | `password` | `https://playit.byhaqi.my.id` |
| **HRD** | `hr@example.com` | `password` | `https://playit.byhaqi.my.id/hrd/login` |
| **Keuangan** | `accounting@example.com` | `password` | `https://playit.byhaqi.my.id/accounting/login` |

> *Selain 3 akun demo utama, terdapat **1.200 data pegawai dummy** yang dibuat otomatis oleh seeder.*

### **N. Data Dummy**

| Data | Jumlah | Keterangan |
| :--- | :--- | :--- |
| **Pegawai** | 1.200 | Lengkap dengan NIK, nama, foto wajah referensi, departemen, kelas jabatan, lokasi kerja |
| **Departemen** | 8+ | Produksi, HRD, Keuangan, IT, Marketing, dll. |
| **Kelas Jabatan** | 6+ | Staff, Supervisor, Manager, GM, Direktur, dengan rentang gaji |
| **Lokasi Kerja** | 3+ | Pabrik Utama, Kantor Pusat, Cabang — dilengkapi koordinat geofencing |
| **Pengumuman** | 5+ | Beragam topik |
| **Penugasan** | 5+ | Tugas luar kota (Jakarta, Surabaya, Bandung, dll.) |
| **Absensi & Log** | Terisi otomatis | Data presensi dengan koordinat, status hadir/terlambat/tugas luar |
| **Pengajuan** | 20+ | Campuran status: pending, approved, rejected |
| **Penggajian** | Per periode | Gaji pokok, honor lembur, potongan (BPJS, PPh 21, dll.) |

### **O. Asumsi Teknis**
* **Honor lembur:** `upah/jam = 1/173 × gaji pokok bulanan`, pengali 1,5× (hari biasa) dan 2× (hari libur).
* **Radius geofencing:** 100 meter dari titik koordinat lokasi kerja.
* **Ambang keterlambatan:** toleransi 15 menit dari jam masuk (08:00 WIB).
* **Face recognition:** DeepFace (server-side) dengan *cosine similarity* ≥ 0.6.
* **Potongan gaji:** BPJS Kesehatan (4%), BPJS Ketenagakerjaan (3,7%), PPh 21 (5%), potongan keterlambatan Rp 50.000/kejadian.
* **Jam kerja standar:** 08:00–17:00 WIB.

### **P. Teknologi yang Digunakan**

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | PHP 8.5+, Laravel 13 |
| **Frontend Admin Panel** | Filament v5 (3 panel: Employee, HRD, Accounting) |
| **UI Styling** | Tailwind CSS v4, Livewire v4 |
| **RBAC** | Spatie Permission + Filament Shield |
| **Face Recognition** | DeepFace (Python, server-side via Docker) |
| **Database** | MySQL 8.4 |
| **Build Tool** | Vite v8 |
| **Testing** | Pest PHP v4, PHPUnit v12 |
| **Deployment** | Laravel Sail (Docker) + Cloudflare Tunnel |

### **Q. Cara Menjalankan (Development)**

```bash
# 1. Clone repository
git clone https://github.com/G4CENeiz/surya-komponen-nusantara-internal.git
cd surya-komponen-nusantara-internal

# 2. Buat file .env
cp .env.example .env

# 3. Jalankan Docker containers (Laravel Sail)
sh sail up -d

# 4. Install dependencies & build assets
sh sail composer install
sh sail npm install
sh sail npm run build

# 5. Generate key, migrate, seed
sh sail artisan key:generate
sh sail artisan migrate --seed
sh sail artisan optimize:clear

# 6. Buka browser
# Pegawai  → http://localhost
# HRD      → http://localhost/hrd/login
# Keuangan → http://localhost/accounting/login
```

### **R. Presentasi/Demo**
Presentasi singkat mencakup:
1. Login sebagai 3 peran berbeda (Pegawai → HRD → Keuangan)
2. Absensi dengan verifikasi lokasi + face recognition
3. Pengajuan cuti/sakit/lembur → verifikasi HRD
4. Perhitungan gaji otomatis → cetak slip gaji
5. Dashboard HRD (grafik kehadiran) & Dashboard Keuangan (grafik beban gaji)

### **S. Ruang Lingkup**
* **Wajib (MVP):** Ke-8 fitur utama (termasuk *face recognition*).
* **Bonus:** *Anomaly detection* absensi · notifikasi status · ekspor slip gaji/rekap (PDF/Excel) · grafik tren · audit log.

---

*\*Referensi inspirasi: Talenta, Gadjian, KaryaONE, Hadirr, BambooHR — sebagai pembanding kategori, bukan untuk ditiru tampilannya.*

*Repository: [G4CENeiz/surya-komponen-nusantara-internal](https://github.com/G4CENeiz/surya-komponen-nusantara-internal)*
