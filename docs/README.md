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
1. **URL aplikasi ter-deploy** yang dapat diakses juri.
2. **Repository** dengan *commit history* sepanjang 24 jam.
3. **README:** cara menjalankan, teknologi, daftar asumsi, **akun demo tiap peran**.
4. **Data dummy** memadai agar seluruh fitur (termasuk peta/dashboard/AI) dapat didemonstrasikan.
5. **Presentasi/demo** singkat di hadapan juri.

### **M. Ruang Lingkup (MVP vs Bonus)**
* **Wajib (MVP)**
  Ke-8 fitur utama (termasuk *face recognition*).
* **Bonus (pembeda)**
  *Anomaly detection* absensi · notifikasi status · ekspor slip gaji/rekap (PDF/Excel) · grafik tren · audit log.

*\*Referensi inspirasi: Talenta, Gadjian, KaryaONE, Hadirr, BambooHR — sebagai pembanding kategori, bukan untuk ditiru tampilannya.*