# NetX Template - Database Migration Guide

## Migration & Seeder Files

Proyek ini menggunakan sistem migration dan seeder yang dikombinasi dalam file tunggal untuk memudahkan pengelolaan database.

### File yang Dibuat:

1. **Migration**: `CreateCompleteNetxTemplateDatabase.php`
   - Membuat semua 24 tabel database sesuai struktur SQL asli
   - Foreign key constraints dinonaktifkan sementara (untuk stabilitas)

2. **Seeder**: `CompleteNetxTemplateSeeder.php` 
   - Berisi semua data sample dari SQL template
   - Data lengkap untuk 18 tabel utama

## ✅ Status Deployment (28 Agustus 2025)

**MIGRATION & SEEDER BERHASIL DIJALANKAN!**

- Migration batch: 1
- Tanggal: 2025-08-28 17:50:05
- Status: SELESAI ✅

## Cara Menjalankan

### 1. Reset Database (Optional)
```bash
php spark migrate:rollback
```

### 2. Jalankan Migration
```bash
php spark migrate
```

### 3. Jalankan Seeder
```bash
php spark db:seed CompleteNetxTemplateSeeder
```

### Atau Jalankan Sekaligus:
```bash
php spark migrate:refresh --seed
```

## Struktur Database yang Dibuat (24 Tabel)

### Tabel Utama:
✅ categories - Kategori konten  
✅ hero - Slider banner utama  
✅ konfigurasi - Pengaturan website  
✅ landing_views - Tracking views  
✅ layanan - Layanan/services  
✅ layout - Layout page builder  
✅ menus - Menu admin  
✅ mitra - Partner/mitra  
✅ motifs - Data motif  
✅ navbar - Menu navigasi  
✅ otoritas - Permissions  
✅ page_builder - Page builder  
✅ pages - Halaman statis  
✅ page_views - Tracking page views  
✅ password_resets - Reset password  
✅ pengumuman - Announcements  
✅ posts - Artikel/berita  
✅ post_views - Tracking post views  
✅ roles - User roles  
✅ section_templates - Template sections  
✅ sosmed - Social media links  
✅ team - Team members  
✅ users - User accounts  
✅ visitor - Visitor tracking  

### Data Sample yang Di-insert:
✅ **3 roles**: Admin, User, Super Admin  
✅ **3 users**: admin/admin, user/user, superadmin/superadmin  
✅ **2 categories**: Umum, dokumen A  
✅ **4 hero sliders**: Ecomel banners  
✅ **Konfigurasi profil**: Netx Template lengkap  
✅ **5 layanan**: Produk ramah lingkungan, dll  
✅ **19 menu items**: Dashboard, berita, pengaturan, dll  
✅ **10 mitra/partner**: BIMA, LPPM, dll  
✅ **1 motif**: Dragon  
✅ **8 navbar items**: News, YouTube, layanan, dll  
✅ **44 otoritas/permissions**: Role-based access  
✅ **2 page builder pages**: Homepage, fdffdsf  
✅ **3 pengumuman**: Maintenance, keterlambatan, promo  
✅ **2 posts**: Ecomel launch, tidur cukup  
✅ **2 section templates**: Hero, Custom HTML  
✅ **2 sosial media**: Facebook, Instagram  
✅ **1 team member**: dr. Iskandar  
✅ **6 layout sections**: Hero, layanan, team, dll  

## Fixes Applied

### Masalah yang Diperbaiki:
1. ✅ **DATETIME fields**: Mengubah `viewed_at` dari DATETIME ke TIMESTAMP
2. ✅ **Invalid default values**: Menghilangkan `'0000-00-00 00:00:00'` dan `'CURRENT_TIMESTAMP'` 
3. ✅ **TIMESTAMP issues**: Mengubah ke DATETIME dengan default null
4. ✅ **Foreign key constraints**: Dinonaktifkan sementara untuk stabilitas
5. ✅ **Data type matching**: Menambahkan `unsigned` pada foreign key fields

## User Login Default

Setelah seeding, tersedia user default:

| Username | Password | Role | 
|----------|----------|------|
| admin | admin | Admin |
| user | user | User |
| superadmin | superadmin | Super Admin |

## Database Connection

Database name: `db_template`  
Charset: `utf8mb4_unicode_ci`  
Engine: `InnoDB`  

## Validasi

✅ **Migration**: Berhasil membuat 24 tabel  
✅ **Seeder**: Berhasil insert data sample  
✅ **Status**: Migration batch 1 completed  
✅ **Date**: 2025-08-28 17:50:05  

## Troubleshooting

Jika ada error saat migration:
1. Pastikan database kosong atau backup dulu
2. Cek koneksi database di `.env`
3. Jalankan `php spark migrate:status` untuk cek status
4. Gunakan `php spark migrate:rollback` jika perlu reset

Jika ada error saat seeding:
1. Pastikan migration sudah berhasil
2. Cek apakah ada data duplicate
3. Gunakan `php spark db:seed --class=CompleteNetxTemplateSeeder` dengan spesifik class

## Notes

- Foreign key constraints sementara dinonaktifkan untuk stabilitas
- Semua timestamp fields menggunakan DATETIME dengan default null
- Database structure sesuai dengan SQL template asli
- Ready untuk development dan testing
