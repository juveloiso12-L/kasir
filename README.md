# POS Kopi Kita

Aplikasi POS Laravel untuk menyimpan transaksi, mencetak struk dari backend, dan menerima pembayaran Midtrans.

## Menjalankan aplikasi

1. Jalankan `composer install`.
2. Salin `.env.example` menjadi `.env`, lalu isi `APP_KEY` (jalankan `php artisan key:generate`).
3. Jalankan migrasi dengan `php artisan migrate`.
4. Jalankan aplikasi dengan `php artisan serve`, lalu buka URL yang ditampilkan Artisan.

Menu awal dan harganya disalin dari `config/pos.php` ke katalog database melalui `ProductCatalogSeeder`. Jalankan `php artisan db:seed --class=ProductCatalogSeeder` setelah migrasi untuk mengisi menu awal. Setelah itu, kategori dan produk dikelola melalui panel Filament di `/admin`, dan perubahan produk akan tampil pada POS. Harga transaksi selalu dihitung server dari katalog database; harga tidak diambil dari browser. Detail nama dan harga produk tetap disimpan sebagai snapshot pada transaksi.

Untuk membuat akun administrator panel, jalankan `php artisan make:filament-user` dan ikuti prompt lokal. Akun baru belum mendapat akses admin secara otomatis. Setelah migrasi `is_admin` diterapkan, operator tepercaya dapat memberikan akses dengan `php artisan app:grant-filament-admin email@contoh.com`. User biasa tetap tidak dapat membuka `/admin`.

## Struk

Default `RECEIPT_DRIVER=pdf` membuat struk PDF di backend (tanpa mencetak dari browser), tersimpan pada `storage/app/private/receipts`. Endpoint struk transaksi juga tersedia pada tautan Unduh struk PDF.

Untuk printer thermal, set `RECEIPT_DRIVER=escpos` dan:

- Windows: set `RECEIPT_CONNECTOR=windows` serta `RECEIPT_PRINTER_NAME` sesuai nama printer di Windows.
- Simulasi file ESC/POS: set `RECEIPT_CONNECTOR=file`; data mentah printer ditulis di folder struk.

Pastikan proses PHP dapat mengakses printer yang dipilih. Transaksi kasir tetap disimpan jika pencetakan gagal, dan kegagalan dilaporkan sebagai error.

## Midtrans Sandbox

Isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` dengan kunci Sandbox. Nilai production default-nya `false`; aktifkan `MIDTRANS_IS_PRODUCTION=true` hanya saat menggunakan kredensial Production. Arahkan URL notifikasi Midtrans ke `/midtrans/notification`. Status order dan pencetakan struk pembayaran online diperbarui dari notifikasi yang signature dan nominalnya diverifikasi.
