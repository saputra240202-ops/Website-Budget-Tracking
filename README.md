💰 FinanceApp - Budget Tracking Website

FinanceApp adalah aplikasi pengelolaan keuangan modern yang dirancang untuk membantu pengguna melacak anggaran, tabungan, dan pengeluaran harian secara real-time. Dengan antarmuka yang interaktif, mengelola finansial pribadi jadi lebih terukur dan menyenangkan.

✨ Fitur Unggulan
* **Dashboard Interaktif:** Visualisasi data keuangan dengan efek hover yang dinamis untuk melihat detail transaksi.
* **Manajemen Anggaran & Tabungan:** Fitur khusus untuk mengatur target tabungan dan alokasi budget bulanan.
* **Notifikasi Instan:** Sistem *toast notifications* untuk memberikan umpan balik cepat saat menambah atau menghapus data.
* **Responsive Design:** Tampilan yang nyaman diakses baik dari desktop maupun perangkat mobile.

🛠️ Tech Stack
Aplikasi ini dibangun menggunakan arsitektur modern:
* **Backend:** Laravel (API-based)
* **Frontend:** Vue.js
* **Database:** MySQL
* **Styling:** CSS / Tailwind CSS (Opsional: sesuaikan jika kamu menggunakannya)
* **Version Control:** Git & GitHub

🚀 Panduan Instalasi

1. Prasyarat
Pastikan kamu sudah menginstal:
* PHP >= 8.x & Composer
* Node.js & NPM
* Laragon atau XAMPP (MySQL)

2. Setup Backend (Laravel)
# Masuk ke folder backend
cd nama-folder-laravel

# Install dependency
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Migrasi database
php artisan migrate

3. Setup Frontend (Vue.js)
# Masuk ke folder frontend
cd nama-folder-vue

# Install package
npm install

# Jalankan server development
npm run dev

⚙️ Konfigurasi Database
Jangan lupa untuk menyesuaikan file .env di bagian backend agar terhubung ke MySQL lokal kamu

Project ini dikembangkan sebagai solusi praktis untuk literasi keuangan digital.
