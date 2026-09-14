# Deploy ke Vercel

Proyek ini memakai Laravel 10 dan runtime PHP komunitas `vercel-php@0.7.4` (PHP 8.3). Vercel tidak menyediakan runtime PHP resmi. Import repository ke Vercel dengan root directory proyek ini; `vercel.json` menyiapkan entrypoint dan routing aset Metronic di `public/assets`.

Atur environment variables berikut di Vercel untuk Production (dan Preview jika digunakan):

```dotenv
APP_NAME=EasyKan
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...          # hasil php artisan key:generate --show
APP_URL=https://domain-anda.vercel.app
LOG_CHANNEL=stderr
CACHE_DRIVER=array
SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync
DB_CONNECTION=mysql
DB_HOST=...               # database MySQL eksternal, bukan 127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

Jika fitur email dipakai, tambahkan variabel `MAIL_*` yang sesuai. Jangan menyalin `.env` lokal ke repository atau dashboard tanpa mengganti nilai development dan kredensialnya. Gunakan `APP_KEY` yang sama pada seluruh deployment agar cookie sesi tetap dapat dibaca.

Jalankan migrasi terhadap database eksternal dari mesin atau CI yang memiliki akses database:

```bash
php artisan migrate --force
```

Seeder admin hanya dijalankan bila akun awal diperlukan. `DatabaseSeeder` saat ini menetapkan password statis `12345678`, jadi ubah password segera setelah login dan jangan menjalankan ulang seeder pada akun produksi.

Vercel tidak mempertahankan file yang ditulis fungsi. Entrypoint menggunakan `/tmp` untuk Blade, cache sementara, dan file sementara Excel/PDF. `SESSION_DRIVER=cookie` menjaga sesi antar-invocation tanpa disk persisten. Upload foto profil ke `storage/app/public` tidak akan bertahan; sebelum memakai fitur upload di produksi, pindahkan file ke object storage persisten dan sesuaikan URL publiknya. Database harus layanan eksternal yang dapat diakses dari Vercel. Proses panjang seperti pengiriman email massal dan ekspor besar juga dapat terkena batas waktu fungsi Vercel.
