# BKPM - WORKSHOP PENGEMBANGAN WEBSITE

---

## ACARA 33. Accelerating Admin Panel Development

**Materi Pembelajaran**: Accelerating Admin Panel Development
**Acara Praktikum/Praktik**: Pengenalan & Instalasi Dasar FilamentPHP
**Tempat**: Lab Jurusan Teknologi Informasi
**Alokasi Waktu**: 4 x 100 Menit

### a. Capaian Pembelajaran Mata Kuliah (CPMK)
1. Mahasiswa mampu memahami konsep Rapid Application Development (RAD) dan arsitektur TALL Stack dalam membangun panel admin menggunakan Filament.
2. Mahasiswa mampu melakukan instalasi package Filament, konfigurasi panel, serta pembuatan akun administrator melalui perintah Artisan.
3. Mahasiswa mampu melakukan generate modul CRUD otomatis (Filament Resource) berdasarkan Model Eloquent dan skema basis data yang tersedia.
4. Mahasiswa mampu mengonfigurasi komponen Form (formulir input) dan Table (tabel data) dasar pada Filament Resource.

### b. Penilaian Bertahap BNSP (Skill Passport)
**Nama Skema Sertifikasi:** Pengembangan Web/Web Developer (SKM-496-028)

| No. | Kode Unit | Nama Unit Kompetensi | Elemen Kompetensi |
|---|---|---|---|
| 1 | J.620100.001.01 | Menganalisis Tools | • Mengidentifikasi tools yang akan digunakan<br>• Menggunakan tools perangkat lunak |
| 2 | J.620100.002.01 | Menganalisis Skalabilitas Perangkat Lunak | • Menggunakan tools perangkat lunak<br>• Menganalisis kebutuhan skalabilitas |

### c. Indikator Penilaian
1. Mahasiswa mampu menginstal dan mengonfigurasi panel admin Filament pada proyek Laravel.
2. Mahasiswa mampu membuat akun user admin dan mengakses halaman dashboard Filament (`/admin`).
3. Mahasiswa mampu men-generate Filament Resource untuk entitas data master (contoh: `Category` dan `Product`).
4. Mahasiswa mampu menyusun skema Form dan kolom Table untuk operasi Create, Read, Update, dan Delete (CRUD).

### d. Alat dan Bahan
1. Web Server PC / Laptop
2. Windows 10
3. RAM 4 GB (Minimal) / 8 GB (Rekomendasi)
4. SSD 512GB
5. Composer
6. Visual Studio Code
7. Kertas A4 / Folio Bergaris
8. Pulpen

### e. Dasar Teori
Setiap kali membuat aplikasi web, bagian halaman admin (back-office) hampir selalu mengulang pekerjaan yang sama: membuat fitur tambah-ubah-hapus data (CRUD), halaman login, kolom pencarian, hingga validasi formulir. **Accelerating Admin Panel Development** hadir sebagai solusi jalan pintas daripada menghabiskan waktu menulis kode HTML/CSS dan logika dasar berulang-ulang dari nol, kita memanfaatkan kerangka kerja (framework) admin panel siap pakai agar aplikasi jauh lebih cepat selesai.

Di ekosistem Laravel, salah satu andalan utamanya adalah **Filament**. FilamentPHP adalah koleksi paket Laravel yang menyediakan alat-alat untuk membangun panel admin, form builders, table builders, notification systems, dan lain-lain. Filosofi utamanya adalah *"convention over configuration"* yang dapat diartikan sebagai membuat fitur-fitur kompleks dengan kode yang minimal.

Kekuatan Filament berasal dari perpaduan empat teknologi yang disebut **TALL Stack**:
* **Tailwind CSS** yang bertugas mengatur tata letak dan tampilan antarmuka agar selalu rapi dan responsif di berbagai ukuran layar.
* **Alpine.js** sebagai penggerak JavaScript ringan agar elemen-elemen di browser terasa interaktif.
* **Laravel** yang bekerja di balik layar sebagai fondasi utama untuk urusan backend, autentikasi login, dan pengelolaan database.
* **Livewire** yang menjadi jembatan penghubung antara tampilan dan server secara reaktif, sehingga halaman bisa terasa dinamis tanpa membuat kita pusing menulis kode JavaScript kompleks secara manual.

Untuk mempermudah pekerjaan kita, Filament punya konsep bernama **Filament Resource**. Bayangkan Resource ini sebagai paket lengkap yang secara otomatis menyulap satu Model data di Laravel (seperti `Product`) menjadi halaman manajemen data yang utuh. Hanya dengan membuat satu Resource, Filament langsung menyediakan halaman daftar data (`ListRecords`), halaman tambah data (`CreateRecord`), hingga halaman edit data (`EditRecord`). Sebagai pengembang, kita cukup fokus mengatur dua bagian utamanya saja:
* **Bagian form (`Form $form`)** untuk menentukan isian atau komponen input apa saja yang perlu diisi saat menambah maupun mengubah data.
* **Bagian table (`Table $table`)** untuk mengatur kolom apa saja yang ditampilkan pada tabel daftar data, lengkap dengan fitur pencarian, pengurutan (sorting), dan tombol aksinya.

**Konsep Dasar FilamentPHP:**
* **Resources** berperan sebagai jembatan utama antara tabel database (Model Eloquent) dengan tampilan admin. Setiap model seperti `User` atau `Product` dapat direpresentasikan menjadi `UserResource` maupun `ProductResource` yang secara otomatis menghasilkan halaman kelola data (CRUD) lengkap tanpa perlu dibuat manual satu per satu.
* **Pages** digunakan ketika kita membutuhkan halaman kustom yang berdiri sendiri dan tidak terikat langsung dengan model data tertentu, seperti halaman pengaturan (*Settings*), profil aplikasi (*About*), atau Dashboard kustom.
* **Widgets** merupakan komponen-komponen informasi ringkas yang bisa disematkan ke halaman dashboard maupun halaman Resource, misalnya grafik statistik atau daftar item terbaru agar admin langsung memperoleh ringkasan kondisi sistem.
* **Forms** dibangun menggunakan Form Builder bawaan Filament yang memudahkan kita merancang formulir interaktif dengan beragam jenis kolom input secara praktis.
* **Tables** didukung oleh Table Builder bawaan yang menyajikan data dalam bentuk tabel modern, lengkap dengan fitur pencarian, pengurutan (*sorting*), pembagian halaman (*paginasi*), hingga penyaringan (*filter*) yang siap pakai.

### f. Prosedur Kerja

**Langkah 1: Instalasi FilamentPHP**
1. Buka proyek yang sudah dibuat sebelumnya, kemudian jalankan perintah berikut di terminal:
```bash
composer require filament/filament:"^3.0" -W
```
*(Note: `^3.0` berarti versi 3 atau yang lebih baru. `-W` akan mengizinkan package untuk diperbarui jika ada konflik dependensi.)*

2. Setelah instalasi Composer selesai, publikasikan aset-aset Filament:
```bash
php artisan filament:install --panels
```
Perintah ini akan melakukan beberapa hal:
* Membuat file `config/filament.php`.
* Membuat file `app/Providers/Filament/AdminPanelProvider.php`.
* Mendaftarkan service provider panel admin ke `config/app.php`.
* Menginstal aset-aset visual yang dibutuhkan.

**Langkah 2: Membuat Pengguna Administrator**
1. Filament membutuhkan pengguna untuk mengakses panel admin. Anda dapat membuat pengguna baru dengan perintah berikut:
```bash
php artisan make:filament-user
```
2. Ikuti petunjuk di terminal untuk memasukkan nama, alamat email, dan kata sandi. Email dan kata sandi ini akan digunakan untuk login ke panel admin.

**Langkah 3: Akses Panel Admin**
Jalankan server pengembangan Laravel dengan `php artisan serve`, lalu buka browser dan kunjungi `http://127.0.0.1:8000/admin`. Anda akan melihat halaman login Filament. Masuk dengan kredensial yang anda buat di Langkah 2.

**Langkah 4: Persiapan Model dan Migrasi Data Master**
1. Pastikan tabel `categories` (`id`, `name`, `slug`) dan `products` (`id`, `category_id`, `name`, `price`, `stock`, `image`) telah dibuat serta dimigrasi ke basis data (`php artisan migrate`).
2. Pastikan properti `$fillable` pada Model `app/Models/Category.php` dan `app/Models/Product.php` telah disesuaikan dengan kolom tabel masing-masing.

**Langkah 5: Generate Filament Resource Otomatis**
1. Jalankan perintah Artisan berikut untuk membuat Resource bagi Model `Category` dan `Product`:
```bash
php artisan make:filament-resource Category
php artisan make:filament-resource Product
```
2. Periksa struktur berkas yang otomatis terbentuk di dalam folder `app/Filament/Resources/`.

**Langkah 6: Konfigurasi Skema Form dan Table pada `ProductResource.php`**
1. Buka berkas `app/Filament/Resources/ProductResource.php`.
2. Sesuaikan metode `form(Form $form)` untuk mendefinisikan komponen input data produk:
```php
use Filament\Forms;
use Filament\Forms\Form;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Produk')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('price')
                ->label('Harga')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('stock')
                ->label('Stok')
                ->numeric()
                ->default(0)
                ->required(),
            Forms\Components\FileUpload::make('image')
                ->label('Gambar Produk')
                ->image()
                ->directory('products'),
        ]);
}
```
3. Sesuaikan metode `table(Table $table)` untuk menampilkan daftar data produk beserta fitur pencarian dan pengurutan:
```php
use Filament\Tables;
use Filament\Tables\Table;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('image')
                ->label('Gambar'),
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Produk')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('price')
                ->label('Harga')
                ->money('IDR')
                ->sortable(),
            Tables\Columns\TextColumn::make('stock')
                ->label('Stok')
                ->sortable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}
```
4. Lakukan hal serupa pada `CategoryResource.php` untuk kolom `name` dan `slug`, lalu uji fitur tambah, ubah, dan hapus data melalui browser.

### g. Hasil dan Pembahasan
**Format Laporan**
* Kertas A4
* Format *.pdf
* Struktur Laporan: Cover, Pendahuluan, Hasil Praktik, Kesimpulan dan Daftar Pustaka
* Berikan Identitas Diri: NIM, Nama, Golongan, Tugas Minggu Ke-X
* Penamaan File: `ACARA-X_GOL_NIM_NAMA.pdf` (**Contoh:** `ACARA-31_A_E1234356_BUDI.pdf`)
* Kumpulkan pada https://elearning-jti.polije.ac.id/

### h. Kesimpulan
Dengan memahami konsep accelerating admin panel development menggunakan Filament, melakukan instalasi dan pembuatan akun administrator, melakukan generate Filament Resource, serta mengonfigurasi skema Form dan Table untuk operasi CRUD otomatis, mahasiswa dapat melanjutkan proses praktikum ke tahap berikutnya.

### i. Rubrik Penilaian
| No | Indikator | Skor* |
|---|---|---|
| 1 | Ketepatan waktu dan ketepatan dalam menjelaskan dari tugas ditunjang dengan bukti referensi | 1 2 3 4 |
| 2 | Ketepatan waktu dan ketepatan dalam menjelaskan dari tugas | 1 2 3 4 |
| 3 | Ketepatan waktu akan tetapi kurang tepat dalam menjelaskan tugas | 1 2 3 4 |
| 4 | Keterlambatan pengumpulan tugas dan ketidaktepatan dalam menjelaskan tugas | 1 2 3 4 |
| | **Jumlah skor** | |

---

## ACARA 34. Accelerating Admin Panel Development (Custom Lanjutan)

**Materi Pembelajaran**: Accelerating Admin Panel Development
**Acara Praktikum/Praktik**: Kustomisasi Lanjutan Filament: Relasi Data, Filter Tabel, dan Dashboard Widgets
**Tempat**: Lab Jurusan Teknologi Informasi
**Alokasi Waktu**: 4 x 100 Menit

### a. Capaian Pembelajaran Mata Kuliah (CPMK)
1. Mahasiswa mampu memahami pengelolaan relasi antar-entitas (Eloquent Relationship) pada antarmuka formulir dan tabel Filament.
2. Mahasiswa mampu mengimplementasikan komponen relasi (`Select::relationship()`) serta fitur penyaringan data (Table Filters dan Badges).
3. Mahasiswa mampu merancang dan membuat Dashboard Widgets untuk menampilkan ringkasan statistik dan grafik data secara visual.
4. Mahasiswa mampu mengustomisasi navigasi, ikon, dan tata letak ringkasan pada halaman utama dashboard panel admin.

### b. Penilaian Bertahap BNSP (Skill Passport)
**Nama Skema Sertifikasi:** Pengembangan Web/Web Developer (SKM-496-028)

| No. | Kode Unit | Nama Unit Kompetensi | Elemen Kompetensi |
|---|---|---|---|
| 1 | J.620100.001.01 | Menganalisis Tools | • Mengidentifikasi tools yang akan digunakan<br>• Menggunakan tools perangkat lunak |
| 2 | J.620100.002.01 | Menganalisis Skalabilitas Perangkat Lunak | • Menggunakan tools perangkat lunak<br>• Menganalisis kebutuhan skalabilitas |

### c. Indikator Penilaian
1. Mahasiswa mampu mengintegrasikan relasi `belongsTo` (contoh: Produk terhadap Kategori) ke dalam Form dan Table Filament.
2. Mahasiswa mampu menambahkan Filter pencarian spesifik dan indikator Badge pada kolom tabel.
3. Mahasiswa mampu membuat Stats Overview Widget untuk menampilkan metrik ringkasan data secara real-time.
4. Mahasiswa mampu mengustomisasi pengelompokan menu navigasi dan menampilkan Widget pada halaman dashboard Filament.

### d. Alat dan Bahan
1. Web Server PC / Laptop
2. Windows 10
3. RAM 4 GB (Minimal) / 8 GB (Rekomendasi)
4. SSD 512GB
5. Composer
6. Visual Studio Code
7. Kertas A4 / Folio Bergaris
8. Pulpen

### e. Dasar Teori

**1. Pengelolaan Relasi Data di Filament**
Dalam aplikasi nyata, tabel di database jarang berdiri sendiri—misalnya tabel produk yang terhubung dengan tabel kategori lewat *Foreign Key* dalam relasi One-to-Many. Untungnya, Filament langsung menyatu dengan fitur Eloquent Relationship bawaan Laravel sehingga kita tidak perlu repot melakukan query manual:
* Saat menyusun formulir (`Forms\Components\Select`), kita cukup menambahkan metode `->relationship('namaRelasi', 'kolomTampil')` agar daftar pilihan dropdown dari tabel induk langsung muncul secara otomatis.
* Begitu juga saat menampilkan data di kolom tabel (`Tables\Columns\TextColumn`), data dari tabel relasinya bisa langsung dipanggil menggunakan format titik (*dot notation*), misalnya `category.name`.

**2. Table Filters dan Visual Badges**
Ketika data sudah menumpuk ratusan hingga ribuan baris, administrator tentu membutuhkan cara cepat untuk menyaring dan membaca informasi penting tanpa harus menggulir seluruh daftar. Filament membantu hal ini melalui dua fitur praktis:
* **SelectFilter** memudahkan admin menyaring baris data berdasarkan kategori atau kriteria tertentu saja (misalnya menampilkan produk khusus dari kategori tertentu).
* **Badges** mengubah teks biasa di kolom tabel menjadi label berwarna dinamis sesuai kondisi nilainya misalnya otomatis berwarna merah saat stok menipis dan hijau ketika stok masih aman.

**3. Filament Dashboard Widgets**
Begitu admin membuka halaman utama (dashboard), mereka tentu ingin langsung melihat gambaran besar kondisi sistem tanpa harus membuka satu per satu menu. Di sinilah peran **Widget**, yaitu komponen informasi modular berbasis Livewire yang dapat disematkan di halaman utama dashboard maupun di bagian atas atau bawah halaman Resource. Dua jenis widget utama yang paling sering digunakan adalah:
* **Stats Overview Widget** untuk menampilkan kartu ringkasan angka-angka penting (*Key Performance Indicators*), lengkap dengan ikon, warna indikator, dan keterangan trennya.
* **Chart Widget** untuk menyajikan visualisasi grafik—baik batang, garis, maupun lingkaran dengan memanfaatkan pustaka Chart.js yang sudah tertanam langsung di dalam Filament.

### f. Prosedur Kerja

**Langkah 1: Konfigurasi Relasi Eloquent pada Model**
1. Buka berkas `app/Models/Product.php` dan pastikan metode relasi `belongsTo` ke model `Category` telah didefinisikan:
```php
public function category()
{
    return $this->belongsTo(Category::class);
}
```
2. Buka berkas `app/Models/Category.php` dan definisikan relasi `hasMany` ke model `Product`:
```php
public function products()
{
    return $this->hasMany(Product::class);
}
```

**Langkah 2: Mengintegrasikan Relasi, Badge, dan Filter pada `ProductResource.php`**
1. Buka `app/Filament/Resources/ProductResource.php`, lalu tambahkan komponen `Select` berelasi pada metode `form()`:
```php
Forms\Components\Select::make('category_id')
    ->label('Kategori Produk')
    ->relationship('category', 'name')
    ->searchable()
    ->preload()
    ->required(),
```
2. Perbarui metode `table()` untuk menampilkan nama kategori, badge warna pada kolom stok, serta filter kategori:
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('image')
                ->label('Gambar'),
            Tables\Columns\TextColumn::make('category.name')
                ->label('Kategori')
                ->sortable(),
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Produk')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('price')
                ->label('Harga')
                ->money('IDR')
                ->sortable(),
            Tables\Columns\TextColumn::make('stock')
                ->label('Stok')
                ->badge()
                ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'success')
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('category')
                ->label('Filter Kategori')
                ->relationship('category', 'name'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}
```

**Langkah 3: Membuat Stats Overview Widget untuk Dashboard**
1. Buka terminal dan jalankan perintah Artisan untuk membuat widget statistik:
```bash
php artisan make:filament-widget StatsOverview --stats-overview
```
*(Pilih lokasi penempatan di Panel admin saat diminta oleh terminal).*

2. Buka berkas `app/Filament/Widgets/StatsOverview.php` dan sesuaikan metode `getStats()`:
```php
namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Kategori', Category::count())
                ->description('Kategori produk terdaftar')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
            Stat::make('Total Produk', Product::count())
                ->description('Keseluruhan item aktif')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Stok Menipis (<=5)', Product::where('stock', '<=', 5)->count())
                ->description('Perlu segera dilakukan restock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
```

**Langkah 4: Kustomisasi Navigasi & Pengujian Dashboard**
1. Buka `ProductResource.php` dan `CategoryResource.php`, lalu atur properti ikon dan pengelompokan menu pada bagian atas kelas:
```php
protected static ?string $navigationIcon = 'heroicon-o-cube';
protected static ?string $navigationGroup = 'Manajemen Master';
```
2. Muat ulang halaman `/admin` pada browser. Pastikan kartu statistik tampil pada halaman dashboard, menu terkelompok rapi di sidebar, serta uji pemilihan relasi kategori dan fitur filter pada tabel produk.

### g. Hasil dan Pembahasan
**Format Laporan**
* Kertas A4
* Format *.pdf
* Struktur Laporan: Cover, Pendahuluan, Hasil Praktik, Kesimpulan dan Daftar Pustaka
* Berikan Identitas Diri: NIM, Nama, Golongan, Tugas Minggu Ke-X
* Penamaan File: `ACARA-X_GOL_NIM_NAMA.pdf` (**Contoh:** `ACARA-31_A_E1234356_BUDI.pdf`)
* Kumpulkan pada https://elearning-jti.polije.ac.id/

### h. Kesimpulan
Dengan memahami pengelolaan relasi antar-entitas di Filament, mengimplementasikan komponen relasi dan filter pada tabel, merancang Dashboard Widgets untuk visualisasi statistik, serta mengustomisasi navigasi panel admin, mahasiswa dapat melanjutkan proses praktikum ke tahap berikutnya.

### i. Rubrik Penilaian
| No | Indikator | Skor* |
|---|---|---|
| 1 | Ketepatan waktu dan ketepatan dalam menjelaskan dari tugas ditunjang dengan bukti referensi | 1 2 3 4 |
| 2 | Ketepatan waktu dan ketepatan dalam menjelaskan dari tugas | 1 2 3 4 |
| 3 | Ketepatan waktu akan tetapi kurang tepat dalam menjelaskan tugas | 1 2 3 4 |
| 4 | Keterlambatan pengumpulan tugas dan ketidaktepatan dalam menjelaskan tugas | 1 2 3 4 |
| | **Jumlah skor** | |