# ETMS — Paket Implementasi Awal

Paket ini berisi implementasi **Database + Model + Service inti + Controller Training Session**
sesuai hasil analisis & desain di Tahap 1–3. Cara integrasi ke project Laravel 12 Anda:

## 0. Package Tambahan yang Dibutuhkan

Modul import Excel (Data Karyawan) butuh **Laravel Excel**, dan modul Report
butuh **Laravel Excel** (export) + **DomPDF** (export PDF). Jalankan di project Anda:

```bash
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

(Package ini tidak bisa saya install langsung di sandbox saya karena jaringan sandbox
dibatasi hanya ke beberapa domain tertentu — silakan jalankan perintah di atas di
lingkungan Laragon Anda. Anda sudah pernah pakai DomPDF sebelumnya di project WO,
jadi setup-nya seharusnya familiar.)

## 1. Copy File

```
database/migrations/*.php     → project/database/migrations/
app/Models/*.php               → project/app/Models/
app/Services/*.php              → project/app/Services/
app/Http/Requests/*.php        → project/app/Http/Requests/
app/Http/Controllers/*.php     → project/app/Http/Controllers/
```

## 2. Jalankan Migration

```bash
php artisan migrate
```

## 3. Tambahkan Route (routes/web.php)

```php
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingModuleController;

Route::resource('training-sessions', TrainingSessionController::class)
    ->only(['index', 'create', 'store', 'show']);

// PENTING: route 'data' harus didaftarkan SEBELUM Route::resource,
// supaya 'data' tidak tertangkap sebagai {training_module} (route model binding).
Route::get('training-modules/data', [TrainingModuleController::class, 'data'])
    ->name('training-modules.data');

Route::resource('training-modules', TrainingModuleController::class)
    ->except(['show']); // tidak perlu halaman detail terpisah untuk master data sederhana ini

// Data Karyawan
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeContractController;

Route::get('employees/data', [EmployeeController::class, 'data'])
    ->name('employees.data'); // didaftarkan sebelum resource, sama seperti training-modules/data

Route::get('employees/import', [EmployeeController::class, 'showImportForm'])
    ->name('employees.import.form');
Route::post('employees/import', [EmployeeController::class, 'import'])
    ->name('employees.import');

Route::get('employees/export/master', [EmployeeController::class, 'exportMaster'])
    ->name('employees.export.master');

Route::resource('employees', EmployeeController::class);
// Catatan: 'show' TIDAK di-except lagi seperti sebelumnya — sekarang dipakai
// untuk halaman Detail Karyawan (riwayat training + mandatory yang belum/perlu dilakukan).

Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])
    ->name('employees.contracts.store');
Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])
    ->name('employees.contracts.destroy');

// Dashboard
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index']); // arahkan root ke dashboard

// Report
use App\Http\Controllers\ReportController;

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
```

## 3b. Jalankan Seeder Master Training & Departemen

```bash
php artisan db:seed --class=Database\\Seeders\\DepartmentSeeder
php artisan db:seed --class=Database\\Seeders\\TrainingModuleSeeder
```

Jalankan `DepartmentSeeder` **terlebih dahulu** karena form Data Karyawan butuh
data departemen untuk dropdown. Ini akan mengisi 12 departemen umum hotel dan
30 modul training contoh (12 mandatory, 18 non-mandatory) — silakan sesuaikan
dengan struktur aktual Harris Hotel Seminyak.

## 4. Yang Sudah Bisa Langsung Dipakai

- **Migration lengkap** untuk 6 tabel (departments, employees, training_modules,
  training_sessions, training_participants, training_histories) sesuai ERD Tahap 3.
- **Model + relasi** lengkap dengan scope (`active()`, `mandatory()`, `expiringSoon()`, `expired()`).
- **`TrainingSessionService`** — inti dari sistem ini. Method `createWithParticipants()`
  otomatis menangani: simpan session → daftarkan peserta → generate riwayat (snapshot)
  dengan perhitungan `expired_at` otomatis. Ini menjawab requirement utama Anda:
  *"HRD tidak perlu menginput histori training satu per satu."*
- **Validasi** (`StoreTrainingSessionRequest`) termasuk pengecekan jam selesai > jam mulai
  dan minimal 1 peserta.
- **Controller** contoh pemakaian service di atas.

## 5. Update — Modul Master Training Sudah Ditambahkan ✅

- CRUD lengkap (index dengan DataTables server-side, create, edit, delete/nonaktifkan).
- `destroy()` otomatis menonaktifkan modul (bukan gagal error) jika modul sudah
  pernah dipakai di Training Session — dilindungi FK `restrictOnDelete` di database.
- Seeder **30 modul training** bertema hospitality (12 mandatory, 18 non-mandatory) —
  silakan sesuaikan kode/nama/kategori dengan daftar training aktual di hotel Anda.
- Layout dasar Bootstrap 5 (`layouts/app.blade.php`) dipakai semua view — nav
  memakai `Route::has()` supaya tidak error sebelum modul lain (Dashboard, Data
  Karyawan, Report) selesai dibuat.

## 7. Update — Modul Data Karyawan Sudah Ditambahkan ✅

- **CRUD lengkap** (index dengan DataTables server-side + filter departemen & status).
- **Import Excel** (`EmployeesImport`) — matching berdasarkan NIK (`updateOrCreate`),
  jadi re-upload file yang sama akan meng-update data, bukan membuat duplikat.
  Baris yang gagal validasi ditampilkan kembali ke HRD (bukan silent-fail).
- **"Nonaktifkan"** memakai `employment_status = inactive`, bukan hard delete —
  supaya riwayat training karyawan tersebut tetap utuh untuk laporan historis.
- Seeder `DepartmentSeeder` (12 departemen umum hotel) untuk mendukung dropdown
  di form karyawan.

## 9. Update — Modul Dashboard Sudah Ditambahkan ✅

Semua metrik di FR-08 sudah dihitung di `DashboardController`:

- Kartu ringkasan: Total Karyawan, Total Modul, Total Session, Total Mandatory,
  Training Hari Ini, Training Bulan Ini, Akan Expired, Sudah Expired.
- **Mandatory Training Completion** — dihitung sebagai (pasangan employee×modul
  mandatory yang punya riwayat masih valid) ÷ (karyawan aktif × modul mandatory
  aktif). Ini murni dihitung dari data real-time, tidak ada angka yang di-hardcode.
- **Chart per Departemen** (bar) dan **per Bulan** (line) pakai Chart.js.
- Tabel quick-glance 10 data "akan expired" dan "sudah expired" terdekat.

**Catatan performa:** semua query dihitung on-the-fly setiap load dashboard.
Untuk ±30 modul dan ratusan karyawan ini masih sangat ringan. Kalau nanti data
riwayat sudah puluhan ribu baris dan dashboard terasa lambat, opsi peningkatan:
cache hasil query 5–10 menit (`Cache::remember`), bukan mengubah logikanya.

## 11. Update — View Blade Training Session Sudah Ditambahkan ✅

Sistem sekarang **bisa dites end-to-end**:

- **`index`** — daftar session + jumlah peserta.
- **`create`** — form detail session + **pemilihan peserta** dengan search nama/NIK,
  filter departemen, tombol "Pilih Semua (Terfilter)" dan "Kosongkan". Filter berjalan
  murni di browser (JS) karena data karyawan aktif sudah dikirim sekaligus dari server
  — cukup ringan untuk skala ratusan karyawan.
- **`show`** — detail session + daftar peserta beserta status kehadiran (otomatis
  terisi `present` saat dibuat lewat `TrainingSessionService`).

Alur pengujian yang disarankan:
1. Seed Department & Training Module.
2. Tambah beberapa Data Karyawan (manual atau import Excel).
3. Buka **Training Session → Buat Training Session**, pilih modul, isi detail,
   pilih beberapa peserta, simpan.
4. Cek halaman **Dashboard** — angka "Total Session", "Training Bulan Ini", dan
   "Mandatory Training Completion" akan otomatis ter-update karena `TrainingHistory`
   sudah ter-generate otomatis oleh service.

## 13. Update — Modul Report Sudah Ditambahkan ✅ (Modul Terakhir dari Requirement Awal)

- **Filter lengkap** sesuai FR-09: Periode (dari–sampai), Nama Karyawan, Departemen,
  Jabatan, Modul Training, Mandatory/Non Mandatory.
- **Kartu ringkasan** hasil filter: Total Riwayat, Mandatory, Akan Expired, Sudah Expired.
- **Export Excel & PDF** (FR-10) — keduanya memakai query filter yang **sama persis**
  dengan tabel di layar (`buildQuery()` dipakai bersama oleh index, export Excel,
  dan export PDF), supaya hasil export selalu konsisten dengan apa yang dilihat HRD.
- Karena basis datanya adalah `TrainingHistory` (bukan tabel terpisah per jenis laporan),
  satu layar ini otomatis mencakup use case: riwayat per karyawan (filter nama),
  rekap per departemen (filter departemen), rekap per periode (filter tanggal),
  dan mandatory completion (filter mandatory + lihat kartu ringkasan) — sesuai
  prinsip Anda untuk menghindari duplikasi logika.

## 14. Status Keseluruhan Sistem

Seluruh modul dari **Master Prompt ETMS** sudah terimplementasi:

| Modul | Status |
|---|---|
| Master Training | ✅ |
| Data Karyawan | ✅ |
| Training Session + auto-generate riwayat | ✅ |
| Mandatory Training Monitoring | ✅ (terintegrasi di Dashboard & Report) |
| Dashboard | ✅ |
| Report + Export | ✅ |

## 15. Yang Masih Perlu Dikerjakan Sebelum Go-Live

- Middleware login sederhana (`laravel/breeze`, tanpa role/permission).
- Testing menyeluruh dengan data riil Harris Hotel Seminyak.
- Review keamanan dasar (validasi upload file, rate limiting form).
- Opsional: caching dashboard jika data sudah sangat besar.

## 6. Catatan Penting

- `TrainingHistory` **tidak pernah diupdate manual** — hanya dibuat via
  `TrainingSessionService`. Kalau butuh koreksi data lama, sebaiknya buat method
  terpisah (`correctHistorySnapshot()`) daripada edit langsung, supaya jejak
  perubahan (audit) tetap jelas.
- Field `status` pada `TrainingHistory` **tidak disimpan di database** — dihitung
  dinamis lewat accessor `getStatusAttribute()` (valid / expiring_soon / expired /
  no_expiry), supaya tidak butuh cron job untuk sinkronisasi.

## 17. Update Besar — Selaras dengan Data HR Excel Riil (Multi-Sheet) ✅

Setelah melihat struktur Excel HR asli Anda (sheet Staff dengan ~70+ kolom, plus
sheet DW/Casual/Training/Outsourcing), berikut perubahan yang dilakukan:

### a. Kolom baru: `employee_type`
Migration tambahan `add_employee_type_to_employees_table` — enum
`staff | dw | casual | trainee | outsourcing`. Ditambahkan di model, request,
form, filter index, dan endpoint DataTables.

**Jalankan migration baru ini:**
```bash
php artisan migrate
```

### b. Sertifikasi eksternal jadi Training Module, bukan kolom statis
2 modul baru ditambahkan ke `TrainingModuleSeeder`: `CERT-FOOD` (Sertifikasi
Penjamah Makanan) dan `CERT-KOMP` (Sertifikasi Kompetensi). **Re-jalankan seeder**
untuk mendapatkan modul ini:
```bash
php artisan db:seed --class=Database\\Seeders\\TrainingModuleSeeder
```

### c. Import Excel sekarang multi-sheet
`EmployeesImport` diubah total — sekarang implementasi `WithMultipleSheets`,
otomatis membaca sheet **Staff, DW, Casual, Training, Outsourcing** dari satu
file yang sama (sheet lain diabaikan otomatis, tidak error). Tiap sheet dipetakan
ke `employee_type` yang sesuai (lihat komentar di `EmployeesImport.php` untuk
asumsi pemetaan — khususnya sheet "Training" saya asumsikan berisi karyawan
trainee, BUKAN data training).

Untuk tiap baris, sistem otomatis:
1. Membuat/update `Employee` (matching by NIK/ID No.).
2. Kalau ada nilai di kolom sertifikasi (Penjamah Makanan / Kompetensi),
   otomatis membuat `TrainingHistory` — **tanpa melalui Training Session**
   (`training_participant_id = null`, sesuai desain nullable yang sudah
   disiapkan sejak Tahap 3 untuk kasus persis seperti ini).
3. Kolom dicari dengan **fragment matching** (bukan nama kolom persis) supaya
   toleran terhadap typo di header asli (`Sertifikasi Kompentensi` vs
   `Kompetensi`, dll). Kalau ada kolom penting yang tidak terbaca, beri tahu
   saya nama kolom persisnya dan saya sesuaikan fragment-nya.

### d. Kolom yang SENGAJA TIDAK diimpor (di luar scope ETMS)
NPWP, BPJS, rekening bank, kontrak 1–5 + tanggal berakhir, data keluarga
(pasangan/anak), alamat detail, gologan darah, pendidikan, kontak darurat, dll.
Ini murni data HRIS/payroll — kalau suatu saat dibutuhkan, sebaiknya jadi
sistem terpisah, bukan menumpuk di ETMS yang fokus untuk training.

### e. Limit ukuran file upload dinaikkan
Dari 5MB → 10MB (`max:10240`) karena file HR multi-sheet biasanya lebih besar.

## 19. Update — Widget Dashboard Diganti: Pengingat Per Modul Training ✅

Sesuai masukan Anda, 2 tabel quick-glance lama ("Akan Expired 10 terdekat" /
"Sudah Expired 10 terbaru" — yang list per baris karyawan) diganti dengan satu
widget **"Training yang Perlu Dijadwalkan Tahun Ini"**, dikelompokkan **per
Modul Training** — lebih cocok untuk perencanaan HRD ("training apa yang perlu
diadakan tahun ini"), bukan sekadar daftar siapa yang expired.

**Definisi "perlu training tahun ini"** (sengaja dibuat lebih luas dari sekadar
"akan expired ≤30 hari"): karyawan aktif yang riwayatnya untuk modul tersebut
TIDAK bertahan sampai 31 Desember tahun berjalan. Ini otomatis mencakup 3 kondisi
sekaligus — belum pernah ikut, sudah expired, atau akan expired sebelum akhir
tahun meski saat ini masih valid.

Tiap baris juga menampilkan **jumlah Training Session yang sudah dibuat tahun
ini** untuk modul tersebut (supaya HRD tahu progress penjadwalan), dan tombol
**"Buat Session"** yang langsung mengarah ke form Training Session dengan modul
tersebut sudah ter-pilih otomatis (lewat query string `?training_module_id=`).

Kartu ringkasan "Akan Expired" / "Sudah Expired" di bagian atas dashboard
tetap dipertahankan sebagai angka cepat — hanya tabel detail di bawahnya yang
diganti.

## 20. Update — Perbaikan Portabilitas Database (SQLite-safe) ✅

Anda memakai SQLite (default Laravel 12 saat `php artisan install` tanpa pilih
driver lain), sementara kode dashboard sebelumnya pakai `MONTH(session_date)`
yang merupakan fungsi **khusus MySQL** — makanya error saat dijalankan.

**Sudah diperbaiki di `DashboardController.php`**: statistik per bulan sekarang
memakai `whereYear()` (method bawaan Laravel yang otomatis diterjemahkan ke
sintaks yang benar sesuai driver database aktif) lalu pengelompokan per bulan
dilakukan di PHP — bukan lewat `DB::raw()` yang terikat ke satu jenis database.

Saya juga sudah cek **seluruh file lain di project ini** — tidak ada raw SQL
sejenis di tempat lain, jadi ini satu-satunya titik yang perlu diperbaiki.
Ke depannya, setiap kode yang saya berikan akan saya tulis database-agnostic
dari awal supaya Anda tidak perlu edit manual lagi setiap kali menumpuk file baru.

## 21. Update — Halaman Detail Karyawan (Riwayat Training Sudah/Belum Dilakukan) ✅

Klik tombol **"Detail"** di baris karyawan (Data Karyawan → Detail) sekarang
menampilkan:

- **Info karyawan** — NIK, departemen, jabatan, kategori pekerja, status, kontak.
- **Mandatory Training yang Belum/Perlu Dilakukan** — daftar modul mandatory
  yang belum pernah diikuti ATAU riwayatnya sudah expired (pakai method
  `Employee::missingMandatoryModules()` yang sekaligus saya perbaiki di update
  ini — sebelumnya hanya mengecek "belum pernah ikut", sekarang juga menghitung
  yang sudah expired sebagai "perlu diulang", konsisten dengan logika Dashboard).
- **Riwayat Training** — seluruh `TrainingHistory` karyawan tsb, dengan badge
  status (Valid/Akan Expired/Expired/Tanpa Masa Berlaku), diurutkan terbaru dulu.

**Route yang perlu diperhatikan**: `Route::resource('employees', ...)` sekarang
TIDAK lagi meng-except `show` seperti sebelumnya (lihat bagian 3 di atas yang
sudah saya update) — pastikan route Anda memakai versi terbaru.

## 22. Update Besar — Profil HR Lengkap (atas konfirmasi Anda: semua kategori) ✅

Field-field berikut ditambahkan ke `employees` (migration
`add_hr_profile_fields_to_employees_table`): demografi personal, keluarga,
finansial/legal, dan pendidikan — sesuai konfirmasi Anda sebelumnya.

**Jalankan 2 migration baru:**
```bash
php artisan migrate
```

### Keputusan desain penting
- **`nik_ktp`** (NIK KTP asli) adalah kolom **BARU dan TERPISAH** dari kolom
  `nik` yang sudah ada sejak awal (yang sebenarnya "ID No." badge karyawan,
  bukan NIK KTP — lihat diskusi sebelumnya). Jangan sampai tertukar.
- **"Age" tidak disimpan** — dihitung otomatis dari `date_of_birth` lewat
  accessor `$employee->age`, supaya tidak pernah basi (prinsip yang sama
  dengan status expired di Training History).
- **Riwayat kontrak (Contract 1–5, Last Contract, Permanen) dinormalisasi**
  jadi tabel terpisah `employee_contracts`, bukan belasan kolom pipih —
  supaya fleksibel (tidak terbatas 5 kontrak) dan bisa dikelola dari halaman
  Detail Karyawan (tambah/hapus riwayat kontrak).
- **Import Excel diperbarui** untuk mengisi semua field baru ini otomatis,
  termasuk penanganan khusus untuk kolom **"End Contract" yang muncul
  berulang 5x dengan nama persis sama** di file asli Anda — ini di-parsing
  berdasarkan **posisi kolom** (bukan nama), supaya tidak ada nilai yang
  tertimpa/hilang.
- Kolom "Permanen" (tanggal jadi karyawan tetap) ikut ter-import sebagai
  salah satu baris di `employee_contracts` dengan `type = permanent`.

### Export "Data Karyawan Lengkap" (jawaban untuk permintaan Report Anda)
Karena field sekarang sangat banyak (30+), menambahkannya ke Report Training
yang sudah ada akan membuatnya sangat lebar & berulang per baris training.
Sebagai gantinya, saya buat **export terpisah** di halaman **Data Karyawan**
(tombol "Export Data Lengkap") — **satu baris per karyawan**, mencakup semua
field HR di atas. Report Training (halaman Report) tetap fokus ke riwayat
training seperti sebelumnya.

### ⚠️ Catatan keamanan data
File export "Data Karyawan Lengkap" memuat data sensitif (NPWP, NIK KTP,
rekening bank, BPJS). Karena sistem ini belum punya role/permission (sesuai
keputusan awal untuk MVP), **siapa pun yang bisa akses ETMS bisa download
data ini**. Kalau ini jadi perhatian, opsi ke depannya: tambah login+role
sederhana khusus untuk membatasi menu ini ke HRD Manager saja.

## 23. Fitur Baru — Portal Karyawan: Materi Training Bisa Diakses & Didownload ✅

### a. Migration baru
```bash
php artisan migrate
```
Menambahkan kolom `password`+`remember_token` di `employees`, dan tabel baru
`training_materials`.

### b. ⚠️ WAJIB: Tambahkan guard 'employee' di `config/auth.php`

Saya TIDAK menimpa file `config/auth.php` Anda (berisiko menghapus konfigurasi
lain yang mungkin sudah ada). Tambahkan manual 2 blok ini:

```php
// Di dalam array 'guards' =>
'employee' => [
    'driver' => 'session',
    'provider' => 'employees',
],

// Di dalam array 'providers' =>
'employees' => [
    'driver' => 'eloquent',
    'model' => App\Models\Employee::class,
],
```

### c. Tambahkan Route (routes/web.php)

```php
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\TrainingMaterialController;

// Login Portal Karyawan (guest — belum login)
Route::middleware('guest:employee')->group(function () {
    Route::get('/portal/login', [EmployeeAuthController::class, 'showLoginForm'])->name('portal.login');
    Route::post('/portal/login', [EmployeeAuthController::class, 'login'])->name('portal.login.submit');
});

// Halaman Portal (WAJIB login sebagai employee)
Route::middleware('auth:employee')->group(function () {
    Route::get('/portal', [EmployeePortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/materials/{material}/download', [EmployeePortalController::class, 'download'])
        ->name('portal.materials.download');
    Route::post('/portal/logout', [EmployeeAuthController::class, 'logout'])->name('portal.logout');
});

// Upload/hapus materi (sisi HR — di halaman Edit Master Training)
Route::post('training-modules/{training_module}/materials', [TrainingMaterialController::class, 'store'])
    ->name('training-modules.materials.store');
Route::delete('training-modules/{training_module}/materials/{material}', [TrainingMaterialController::class, 'destroy'])
    ->name('training-modules.materials.destroy');
```

### d. Cara HRD Memberi Akses Karyawan
Buka **Data Karyawan → Edit** karyawan yang bersangkutan, isi field **"Password
Login Portal"** di seksi paling bawah. Karyawan login pakai **ID No.** (bukan
NIK KTP — sama seperti field identifier lain di sistem ini) + password itu.
Kosongkan field ini saat edit = password tidak berubah.

### e. Cara HRD Upload Materi
Buka **Master Training → Edit** modul yang bersangkutan → scroll ke bawah ke
card **"Materi Training"** → isi judul + pilih file → Upload. Mendukung semua
jenis file (PDF, PPT, Word, video, dll), maksimal 50MB per file.

### f. Keputusan Desain Penting
- **File disimpan di disk `local` (privat)**, BUKAN disk `public` — jadi tidak
  ada URL langsung ke file. Karyawan HARUS login dan lewat route
  `portal.materials.download` (dilindungi `auth:employee`) untuk bisa
  mengunduh. Ini sengaja lebih aman daripada sekadar taruh di folder public.
- **Guard `employee` TERPISAH TOTAL dari sesi HRD** — Employee model
  extends `Authenticatable` sendiri, beda dari `User` (HRD). Karyawan yang
  login TIDAK bisa mengakses halaman admin HRD sama sekali (memang belum
  ada middleware apapun di halaman admin — lihat catatan di bawah).
- **Login otomatis ditolak untuk karyawan resign/nonaktif** — kondisi
  `employment_status = active` ikut jadi syarat saat `Auth::attempt()`,
  jadi tidak perlu pengecekan status manual terpisah.
- **Employee::missingMandatoryModules()** yang sudah ada dipakai ulang di
  Portal, supaya karyawan juga bisa lihat sendiri mandatory training apa
  yang masih perlu dilakukan.

### ⚠️ Catatan penting yang perlu Anda sadari
Sisi **admin/HRD (Dashboard, Data Karyawan, Master Training, dll) masih
TANPA LOGIN SAMA SEKALI** — sesuai keputusan awal MVP. Dengan fitur ini,
artinya: **siapa pun yang bisa akses jaringan internal bisa upload/hapus
materi training dan mengatur password login karyawan lain**, tapi karyawan
sendiri tidak bisa masuk ke sisi admin. Kalau ini jadi perhatian keamanan,
beri tahu saya — bisa saya tambahkan login sederhana untuk HRD juga
(terpisah dari guard `employee` ini, pakai guard `web` default Laravel).

## 18. Update — Tampilan Direstyle (Sidebar Admin Panel Style) ✅

Atas permintaan Anda, seluruh tampilan direstyle mengikuti gaya admin panel yang
Anda tunjukkan (mirip screenshot Admin Panel WO-IT Anda): sidebar gelap fixed di
kiri, konten dengan card putih rounded, topbar berisi judul halaman + subtitle +
tombol aksi di kanan atas.

**Yang berubah:**
- `layouts/app.blade.php` — struktur baru: `.app-sidebar` (gelap, nav dengan
  highlight otomatis untuk menu aktif via `request()->routeIs()`) + `.app-main`
  berisi `.app-topbar` (judul/subtitle/aksi) dan `.app-content`.
- Section baru yang dipakai tiap halaman: `@section('page-title', ...)`,
  `@section('page-subtitle', ...)`, `@section('page-actions')` — menggantikan
  header manual `<h4>` + `d-flex` yang sebelumnya diulang di tiap view.
- Semua `.card` Bootstrap polos diganti `.stat-card` (untuk kartu angka statistik)
  dan `.content-card` (untuk tabel/form) — keduanya di-style rounded-16px,
  border tipis, shadow halus, sesuai referensi Anda.
- Tetap 100% Bootstrap 5 + Blade — tidak ada framework CSS baru yang ditambahkan,
  jadi tidak perlu install apapun tambahan untuk update tampilan ini.

**Catatan:** karena logo "Harris Hotel Seminyak" di sidebar saya hardcode di
`layouts/app.blade.php`, kalau nanti nama hotel/cabang bisa berubah per instalasi,
sebaiknya dipindah ke config atau `.env` — beri tahu saya kalau mau saya buatkan.

---

Semua modul dari requirement awal Anda sudah selesai, dengan tampilan yang sudah
di-restyle. Beri tahu saya kalau ingin saya bantu setup **login sederhana
(laravel/breeze)**, buat **seeder data dummy** untuk testing, atau ada bagian
tampilan/fungsional lain yang ingin direvisi.
