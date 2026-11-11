# BKPM - WORKSHOP SISTEM INFORMASI WEB FRAMEWORK

**Pokok Bahasan**: Laravel Print & Payment Gateway - Midtrans
**Acara Praktikum**: Minggu 11/1 - 11/3
**Tempat**: Daring/Luring Politeknik Negeri Jember
**Alokasi Waktu**: 100 Menit / Acara

---

## ACARA 35. Laravel Print

### a. Capaian Pembelajaran Mata Kuliah (CPMK)
1. Mahasiswa mampu menjelaskan konsep dasar Laravel Print
2. Mahasiswa mampu mengimplementasikan konfigurasi Laravel print
3. Mahasiswa mampu memahami proses Laravel print
4. Mahasiswa mampu mengimplementasikan Library Mike 42 / ESC-POS

### b. Penilaian Bertahap BNSP (Skill Passport)
**Nama Skema Sertifikasi:** Pengembangan Web/Web Developer (SKM-496-028)

| No. | Kode Unit | Nama Unit Kompetensi | Elemen Kompetensi |
|---|---|---|---|
| 1 | J.620100.001.01 | Menganalisis Tools | • Mengidentifikasi tools yang akan digunakan<br>• Menggunakan tools perangkat lunak |
| 2 | J.620100.002.01 | Menganalisis Skalabilitas Perangkat Lunak | • Menggunakan tools perangkat lunak<br>• Menganalisis kebutuhan skalabilitas |

### c. Indikator Penilaian
1. Mahasiswa mampu menjelaskan konsep dasar Laravel Print
2. Mahasiswa mampu mengimplementasikan konfigurasi Laravel print
3. Mahasiswa mampu memahami proses Laravel print
4. Mahasiswa mampu mengimplementasikan Library Mike 42 / ESC-POS

### d. Dasar Teori
Pada Framework Laravel Terdapat beberapa Library yang menyediakan fasilitas untuk koneksi ke Print salah satunya adalah **Library Mike42 ESC/POS PHP** yang merupakan sebuah library open-source berbasis PHP yang dirancang khusus untuk memfasilitasi komunikasi antara aplikasi dan printer thermal menggunakan protokol standar ESC/POS (Epson Standard Code for Point of Sale). Protokol ini umum digunakan oleh berbagai merek printer struk seperti Epson, XPrinter, POS-58, dan lainnya. 

Library ini memungkinkan pengembang untuk dengan mudah mencetak teks, mengatur format cetakan (seperti teks tebal, miring, atau perataan tengah), mencetak barcode maupun QR code, serta melakukan pemotongan kertas secara otomatis. Fungsi-fungsi ini sangat berguna dalam pengembangan sistem kasir (POS), pencetakan struk belanja, nota penjualan, sistem antrean, dan berbagai aplikasi lainnya yang memerlukan keluaran cetak secara langsung ke printer thermal.

**Fungsi Utama:**
* Mencetak teks biasa (misal: nama toko, item belanja, total).
* Format teks: tebal, miring, rata kiri/kanan/tengah.
* Memotong kertas otomatis.
* Mencetak barcode dan QR Code.
* Mengatur ukuran huruf dan gaya huruf.
* Dapat digunakan dengan berbagai koneksi: USB, jaringan, port serial, atau file.

**Kelebihan:**
* Ringan dan cepat.
* Tidak butuh driver khusus (langsung kirim ke printer).
* Bisa digunakan di berbagai OS (Linux, Windows, macOS).

### e. Alat dan Bahan
1. PC/Laptop
2. Windows 10
3. RAM 4 GB (Minimal) / 8 GB (Rekomendasi)
4. SSD 512GB
5. Kertas A4 / Folio Bergaris
6. Pulpen

### f. Prosedur Kerja

**1) Instalasi Library**
Laravel menyediakan perintah Artisan untuk menginstal library. Jalankan perintah berikut:
```bash
composer require mike42/escpos-php
```

**2) Buat Controller untuk Cetak Struk**
Setelah instalasi, silahkan lakukan pengecekan apakah library sudah terinstall, kemudian buat controller di laravel. Berikut adalah contoh controller `ReceiptController` yang akan dbuat:

```php
use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ReceiptController extends Controller
{
    public function print()
    {
        try {
            // Ganti 'POS-58' dengan nama printer tujuan (cek di Control Panel > Printers)
            $connector = new WindowsPrintConnector("POS-58");
            $printer = new Printer($connector);
            
            $printer->text("TOKO CONTOH\n");
            $printer->text("Jl. Mawar No. 123\n");
            $printer->text("========================\n");
            $printer->text("Barang A      2 x 5.000\n");
            $printer->text("Barang B      1 x 10.000\n");
            $printer->text("------------------------\n");
            $printer->text("Total:        Rp20.000\n");
            $printer->text("========================\n");
            $printer->text("Terima kasih!\n");
            
            $printer->cut();
            $printer->close();
            
            return response()->json(['message' => 'Struk berhasil dicetak']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```
*Catatan:*
* Hanya akan berfungsi di OS Windows/Linux yang memiliki printer langsung terhubung.
* Untuk yang menggunakan OS Linux bisa gunakan `CupsPrintConnector`.

**3) Menambahkan Route**
Tambahkan route berikut di `routes/web.php`:
```php
Route::get('/print-receipt', [ReceiptController::class, 'print']);
```

**4) Testing Print**
a. Pastikan printer thermal sudah terkoneksi dan bisa digunakan.
b. Jika menggunakan Windows, pastikan nama printer sesuai.
c. Lakukan Testing melalui local host test `http://localhost/print-receipt`.

**5) Custom Formating**
Selain beberapa kriteria diatas library Mike42 memiliki dukungan untuk beberapa fitur sebagai berikut:
* Bold
* Underline
* Align left/right/center
* QR Code dan Barcode
* Image Logo (jika printer support)

Contoh implementasi untuk menambahkan bold dan center:
```php
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text("TOKO CONTOH\n");
$printer->setEmphasis(false);
```

**6) Setelan Tambahan untuk OS Linux (Optional)**
```php
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
$connector = new CupsPrintConnector("POS-58");
```

Kemudian, di dalam file `app/Events/MessageSent.php`, implementasikan antarmuka `ShouldBroadcastNow` dan tentukan channel tempat event akan di broadcast:
```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\Channel;

class MessageSent implements ShouldBroadcastNow
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('chat');
    }
}
```

### g. Hasil dan Pembahasan
**Format Laporan:**
* Kertas A4
* Format *.pdf
* Struktur Laporan: Cover, Pendahuluan, Hasil Praktik, Kesimpulan dan Daftar Pustaka
* Berikan Identitas Diri: NIM, Nama, Golongan, Tugas Minggu Ke-X
* Penamaan File: `ACARA-X_GOL_NIM_NAMA.pdf` (Contoh: `ACARA-33_A_E1234356_BUDI.pdf`)
* Kumpulkan pada https://elearning-jti.polije.ac.id/

### h. Kesimpulan
Mike42 ESC/POS PHP adalah solusi pencetakan yang efisien dan fleksibel untuk aplikasi berbasis PHP, khususnya dalam sistem Point of Sale (POS) atau pencetakan struk otomatis. Dengan dukungan terhadap berbagai jenis printer thermal dan protokol ESC/POS, library ini memungkinkan pengembang untuk mencetak teks, barcode, QR code, serta melakukan pemformatan dan pemotongan kertas secara langsung dari aplikasi.

### i. Rubrik Penilaian
| No | Indikator Penilaian | 1 | 2 | 3 | 4 |
|---|---|---|---|---|---|
| 1 | Ketepatan waktu dan ketepatan Langkah kerja dalam mengerjakan | | | | V |
| 2 | Ketepatan waktu dan Langkah kerja kurang tepat dalam mengerjakan | | | V | |
| 3 | Keterlambatan waktu dan Langkah kerja yang dilakukan kurang tepat | | V | | |
| 4 | Keterlambatan waktu pengumpulan dan Langkah kerja tidak sesuai | V | | | |
| | **Total Skor*** | | | | **Skor x 25** |

---

## ACARA 36. Laravel Print (2)

### c. Indikator Penilaian
1. Mahasiswa mampu menjelaskan konsep dasar Laravel Print
2. Mahasiswa mampu mengimplementasikan konfigurasi Laravel print
3. Mahasiswa mampu memahami proses Laravel print
4. Mahasiswa mampu mengimplementasikan Library Mike 42 / ESC-POS

### f. Prosedur Kerja
*"Bikin Kelompok 4-5 Orang kemudian kerjakan Studi kasus Dibawah ini"*

**Studi Kasus Implementatif: Sistem Cetak Struk Otomatis dengan ESC/POS**

**Kasus:**
Sebuah toko bernama Kopi Kita telah memiliki sistem POS berbasis web sederhana yang mencatat transaksi pembelian menggunakan Framework Laravel. Sistem tersebut berjalan di browser komputer kasir dan dapat mencatat data transaksi dengan baik. Saat ini, sistem belum memiliki fitur untuk mencetak struk transaksi secara otomatis ke printer thermal USB. Pemilik toko meminta Anda untuk menambahkan fitur pencetakan otomatis setelah transaksi selesai. Printer yang digunakan mendukung protokol ESC/POS, dan tim pengembang telah menyarankan penggunaan library open-source Mike42 `escpos-php`.

**Tugas:**
Implementasikan sistem pencetakan struk otomatis dengan ketentuan sebagai berikut:
1. Format struk minimal harus mencakup:
   * Nama toko
   * Tanggal dan waktu
   * Daftar item, kuantitas, harga
   * Total pembayaran
   * Diskon

**Ketentuan Teknis:**
* Gunakan library `escpos-php` dari Mike42.
* Printer diakses menggunakan konektor USB atau `FilePrintConnector`, disesuaikan dengan sistem operasi Anda.
* Jika tidak tersedia printer fisik, gunakan printer PDF untuk simulasi.
* Sistem tidak boleh mencetak melalui browser (harus dari backend PHP).

**Output yang Diharapkan:**
* POS web berjalan normal dan menyimpan transaksi.
* Struk tercetak otomatis setelah transaksi masuk.
* Dokumentasi singkat cara menjalankan sistem.

---

## ACARA 37. Payment Gateway - Midtrans

### a. Capaian Pembelajaran Mata Kuliah (CPMK)
1. Mahasiswa mampu menjelaskan konsep dasar Payment Gateway
2. Mahasiswa mampu mengimplementasikan konfigurasi Payment Gateway
3. Mahasiswa mampu memahami proses Payment Gateway - Midtrans
4. Mahasiswa mampu mengimplementasikan Payment Gateway - Midtrans

### d. Dasar Teori
Dalam era digital saat ini, integrasi sistem pembayaran online menjadi salah satu komponen penting dalam pengembangan aplikasi web, khususnya e-commerce dan layanan berbasis langganan. Laravel, sebagai salah satu framework PHP paling populer dan powerful, menyediakan kemudahan dalam mengintegrasikan berbagai payment gateway seperti Midtrans, Xendit, Stripe, PayPal, dan lainnya.

**Proses Umum Integrasi Payment Gateway si Laravel adalah sebagai berikut:**
1. Instalasi package SDK dari provider (via Composer).
2. Konfigurasi API key dan environment (production/sandbox) di `.env`.
3. Pembuatan route dan controller untuk mengelola request pembayaran.
4. Implementasi callback dan webhook untuk menangani notifikasi pembayaran dari provider.
5. Verifikasi dan update status transaksi ke database aplikasi.

**Payment Gateway - Midtrans**
Midtrans adalah layanan payment gateway Indonesia yang memfasilitasi berbagai metode pembayaran seperti:
* Kartu kredit/debit
* Transfer bank (Virtual Account)
* E-wallet (GoPay, ShopeePay, dll)
* Gerai retail (Indomaret, Alfamart)

**Konsep Integrasi Laravel dengan Midtrans:**
1. Frontend - Form checkout atau tombol bayar.
2. Backend - Mengirim data ke Midtrans dan menerima callback (notifikasi pembayaran).
3. Security - Validasi signature dan token.
4. Status update - Update status pesanan berdasarkan callback dari Midtrans.

### f. Prosedur Kerja

**1. Buat akun di Midtrans Sandbox**
* Gunakan sandbox untuk testing
* Catat Server Key dan Client Key dari menu `Settings > Access Keys`

**2. Instalasi Laravel dan Midtrans SDK**
```bash
composer require midtrans/midtrans-php
```

**Konfigurasi di Laravel**
1. Tambahkan konfigurasi Midtrans di `.env`:
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXX
MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXX
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_REDIRECT=true
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

2. Buat file config: `config/midtrans.php`
```php
return [
    'serverKey' => env('MIDTRANS_SERVER_KEY'),
    'clientKey' => env('MIDTRANS_CLIENT_KEY'),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
    'isSanitized' => env('MIDTRANS_SANITIZE', true),
    'is3ds' => env('MIDTRANS_3DS', true),
];
```

3. Konfigurasi Midtrans di laravel
```php
use Midtrans\Config;

Config::$serverKey = config('midtrans.serverKey');
Config::$isProduction = config('midtrans.isProduction');
Config::$isSanitized = config('midtrans.isSanitized');
Config::$is3ds = config('midtrans.is3ds');
```

---

## ACARA 38. Payment Gateway - Midtrans (2)

### f. Prosedur Kerja
**Studi kasus Sistem Pembelian Produk Digital**

**1. Buat Route di `routes/web.php`**
```php
use App\Http\Controllers\MidtransController;

Route::post('/checkout', [MidtransController::class, 'checkout']);
Route::post('/midtrans/notification', [MidtransController::class, 'notification']);
```

**2. Controller: `app/Http/Controllers/MidtransController.php`**
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    public function checkout(Request $request)
    {
        $orderId = 'ORDER-' . time();
        $price = 50000;
        
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => 'John',
                'email' => 'john@example.com',
            ]
        ];

        $snapToken = Snap::getSnapToken($params);
        
        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $orderId
        ]);
    }

    public function notification(Request $request)
    {
        $notif = new \Midtrans\Notification();
        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;

        if ($transaction == 'settlement') {
            // Update status pesanan di database jadi "PAID"
        } elseif ($transaction == 'pending') {
            // Status pending
        } elseif ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            // Gagal bayar
        }

        return response()->json(['message' => 'Notification handled']);
    }
}
```

**3. Frontend Checkout (Vue / Blade / JS)**
```html
<button id="pay-button">Bayar Sekarang</button>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function () {
    fetch('/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        window.snap.pay(data.snap_token, {
            onSuccess: function(result) {
                alert("Pembayaran sukses!");
            },
            onPending: function(result) {
                alert("Menunggu pembayaran...");
            },
            onError: function(result) {
                alert("Pembayaran gagal!");
            }
        });
    });
});
</script>
```

**Catatan:**
**2. Callback / Webhook**
Midtrans akan mengirim notifikasi ke endpoint Laravel setelah pembayaran:
* Pastikan URL `/midtrans/notification` bisa diakses publik
* Gunakan route POST
* Pastikan app Laravel kamu tidak memblokir IP Midtrans

**3. Validasi Keamanan (Opsional Tapi Disarankan)**
Tambahkan verifikasi signature jika perlu validasi tambahan agar callback tidak bisa dipalsukan. Midtrans menyediakan signature key untuk hal tersebut.

**Simpan Status Transaksi**
Buat tabel orders:
```bash
php artisan make:migration create_orders_table
```
Kemudian lakukan perintah ini:
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_id');
    $table->string('status')->default('pending');
    $table->timestamps();
});
```

**Push Notifikasi**
```php
$order = Order::where('order_id', $order_id)->first();
$order->status = $transaction;
$order->save();
```