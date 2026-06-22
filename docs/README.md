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

### **L. Deliverables (Wajib)**

#### 1. URL Aplikasi Ter-Deploy
| Panel | URL |
| :--- | :--- |
| **Pegawai (default)** | `https://<domain>/` |
| **HRD** | `https://<domain>/hrd/login` |
| **Keuangan** | `https://<domain>/accounting/login` |

> *URL akan diupdate setelah deployment ke server publik.*

#### 2. Repository
- Repository GitHub: **G4CENeiz/surya-komponen-nusantara-internal**
- *Commit history* tercatat sepanjang 24 jam pengerjaan.

#### 3. Teknologi yang Digunakan
| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | PHP 8.3+, Laravel 13 |
| **Frontend Admin Panel** | Filament v5 (3 panel: Employee, HRD, Accounting) |
| **UI Styling** | Tailwind CSS v4, Livewire v4 |
| **RBAC** | Spatie Permission + Filament Shield |
| **Absensi & Face Recognition** | Face API.js / TensorFlow.js (client-side) + Geofencing |
| **PDF Export** | Laravel DomPDF |
| **Media Library** | Spatie Media Library |
| **Database** | MySQL / SQLite |
| **Testing** | Pest PHP v4, PHPUnit v12 |
| **Build Tool** | Vite v8 + Bun |

#### 4. Cara Menjalankan
```bash
# 1. Clone repository
git clone https://github.com/G4CENeiz/surya-komponen-nusantara-internal.git
cd surya-komponen-nusantara-internal

# 2. Jalankan setup otomatis (install, migrate, seed, build)
composer run setup

# 3. Jalankan server development
php artisan serve

# 4. Buka browser
# Pegawai  → http://localhost:8000
# HRD      → http://localhost:8000/hrd/login
# Keuangan → http://localhost:8000/accounting/login
```

#### 5. Akun Demo tiap Peran

| Peran | Email | Password | URL Login |
| :--- | :--- | :--- | :--- |
| **Pegawai** | `employee@example.com` | `password` | `http://localhost:8000` |
| **HRD** | `hr@example.com` | `password` | `http://localhost:8000/hrd/login` |
| **Keuangan (Accounting)** | `accounting@example.com` | `password` | `http://localhost:8000/accounting/login` |

> *Selain 3 akun demo utama, terdapat **1.200 data pegawai dummy** yang dibuat otomatis oleh seeder.*

#### 6. Data Dummy
Data dummy yang disediakan agar seluruh fitur dapat didemonstrasikan:

| Data | Jumlah | Keterangan |
| :--- | :--- | :--- |
| **Pegawai** | 1.200 | Lengkap dengan NIK, nama, foto wajah referensi, departemen, kelas jabatan, lokasi kerja |
| **Departemen** | 8+ | Produksi, HRD, Keuangan, IT, Marketing, dll. |
| **Kelas Jabatan** | 6+ | Staff, Supervisor, Manager, GM, Direktur, dengan rentang gaji |
| **Lokasi Kerja (Workplace)** | 3+ | Pabrik Utama, Kantor Pusat, Cabang — dilengkapi koordinat geofencing |
| **Pengumuman** | 10+ | Beragam topik, beberapa dengan lampiran PDF |
| **Penugasan** | 5+ | Tugas luar kota (Jakarta, Surabaya, Bandung, dll.) |
| **Absensi & Log** | Terisi otomatis | Data presensi dengan koordinat, status hadir/terlambat/tugas luar |
| **Pengajuan (Time Off, Lembur, Sakit)** | 20+ | Campuran status: pending, approved, rejected |
| **Penggajian (Payroll Settings, Payslip)** | Per periode | Gaji pokok, honor lembur, potongan (BPJS, PPh 21, dll.) |
| **Reimbursement** | 10+ | Berbagai jenis klaim pengeluaran |

#### 7. Daftar Asumsi
- **Honor lembur:** menggunakan rumus lazim Indonesia `upah/jam = 1/173 × gaji pokok bulanan`, dengan pengali 1,5× (hari biasa) dan 2× (hari libur).
- **Radius geofencing:** 100 meter dari titik koordinat lokasi kerja.
- **Ambang keterlambatan:** toleransi 15 menit dari jam masuk (08:00 WIB).
- **Face recognition:** menggunakan `face-api.js` (TensorFlow.js) dengan *cosine similarity* ≥ 0.6 sebagai ambang batas kecocokan wajah.
- **Potongan gaji:** BPJS Kesehatan (1% gaji + 4% employer), BPJS Ketenagakerjaan (JKK 0,24%, JKM 0,30%), PPh 21 menggunakan tarif progresif, potongan keterlambatan Rp 50.000/kejadian.
- **Jam kerja standar:** 08:00–17:00 WIB (8 jam kerja, 1 jam istirahat).
- **Status keterlambatan:** `Terlambat` jika clock-in setelah 08:15, `Tugas Luar` jika disetujui HRD, `Izin/Cuti` jika disetujui HRD.

#### 8. Presentasi/Demo
Presentasi singkat mencakup:
1. Login sebagai 3 peran berbeda (Pegawai → HRD → Keuangan)
2. Absensi dengan verifikasi lokasi + face recognition
3. Pengajuan cuti/sakit/lembur → verifikasi HRD
4. Perhitungan gaji otomatis → cetak slip gaji
5. Dashboard HRD (grafik kehadiran) & Dashboard Keuangan (grafik beban gaji)

### **M. Ruang Lingkup (MVP vs Bonus)**
* **Wajib (MVP)**
  Ke-8 fitur utama (termasuk *face recognition*).
* **Bonus (pembeda)**
  *Anomaly detection* absensi · notifikasi status · ekspor slip gaji/rekap (PDF/Excel) · grafik tren · audit log.

*\*Referensi inspirasi: Talenta, Gadjian, KaryaONE, Hadirr, BambooHR — sebagai pembanding kategori, bukan untuk ditiru tampilannya.*