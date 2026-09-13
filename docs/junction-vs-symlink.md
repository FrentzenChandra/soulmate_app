# 📖 Junction vs Symlink — Penjelasan untuk Pemula

> Ditulis untuk: Frentzen Chandra  
> Konteks: Soulmate App — Docker + Windows

---

## 🧸 Bayangkan Begini (Analogi Bayi)

### Folder biasa = Rumah sungguhan
Kalau kamu masuk ke folder biasa, kamu benar-benar ada di sana. Isinya nyata, ada di situ.

```
📁 storage/
   └── 📁 products/
          └── 🖼️ foto.jpg   ← file beneran ada di sini
```

---

### Shortcut (.lnk di Windows) = Peta menuju rumah
Shortcut itu **bukan rumah**, cuma **kertas petunjuk arah**.
Kalau kamu klik shortcut di Desktop, Windows baca petunjuknya lalu buka rumah yang dimaksud.
Tapi kalau kamu kirim shortcut itu ke orang lain, dia tidak bisa pakai — karena alamat di peta itu cuma berlaku di komputer kamu.

```
🗺️ foto_shortcut.lnk  → "pergi ke C:\Users\frentzen\foto.jpg"
```

---

### Junction (Windows) = Pintu portal rahasia khusus Windows 🪟
Junction adalah **folder** yang kalau kamu masuk ke dalamnya, Windows diam-diam **teleport** kamu ke folder lain.
Dari luar kelihatan seperti folder biasa. Tapi dalamnya adalah tempat lain.

```
📁 public/storage/   ← kelihatan seperti folder biasa
       ↓ (kalau kamu masuk ke dalamnya)
📁 storage/app/public/   ← kamu sebenarnya "diteleport" ke sini
```

**Masalahnya:** Junction ini **hanya dimengerti oleh Windows**.
Linux (sistem operasi yang dipakai Docker) tidak tahu apa itu Junction.
Bagi Linux, Junction itu kelihatannya folder kosong atau tidak ada sama sekali.

---

### Symlink (Linux/Mac) = Pintu portal yang dimengerti Linux 🐧
Symlink itu konsepnya sama — portal ke folder/file lain.
Tapi symlink dibuat oleh Linux, dan **hanya dimengerti oleh Linux**.

```
📁 public/storage → storage/app/public   (Linux symlink ✅)
```

---

## 🎯 Ringkasan Perbedaan

| | Junction | Symlink |
|---|---|---|
| Dibuat oleh | Windows | Linux / Mac |
| Dimengerti oleh | Windows saja | Linux / Mac saja |
| Perintah pembuatnya | `mklink /J` atau `php artisan storage:link` di Windows | `ln -s` atau `php artisan storage:link` di Linux |
| Di Docker (Linux) | ❌ Tidak terbaca | ✅ Terbaca |

---

## 🐳 Kasus Nyata: Soulmate App + Docker

```
Windows Terminal:
php artisan storage:link
→ Membuat JUNCTION (Windows)
→ Docker Linux tidak bisa baca → 403 Forbidden ❌

Docker Terminal:
docker exec soulmate_apps php artisan storage:link
→ Membuat SYMLINK (Linux)
→ Docker Linux bisa baca → gambar tampil ✅
```

---

## ✅ Aturan Emas

> **Selalu jalankan perintah yang menyentuh sistem file**
> **(seperti storage:link) dari DALAM Docker, bukan dari Windows terminal.**

Command:
```bash
docker exec soulmate_apps php artisan storage:link
```

Atau tambahkan otomatis ke `docker-compose.yml` supaya jalan tiap container start:
```yaml
command: sh -c "php artisan storage:link && apache2-foreground"
```

---

## 🔤 Kamus IT (Kata-kata Penting)

| Kata | Artinya (bahasa manusia) |
|---|---|
| **Junction** | Folder "portal" rahasia khusus Windows — teleport ke folder lain |
| **Symlink** | Folder/file "portal" di Linux/Mac — konsep sama, tapi untuk Linux |
| **NTFS** | "Bahasa" yang Windows pakai untuk nyimpan file di hardisk |
| **ext4** | "Bahasa" yang Linux pakai untuk nyimpan file di hardisk |
| **Volume mount** | Docker "meminjam" folder dari komputer Windows, bukan copy |
| **Host** | Komputer aslinya (Windows kamu), bukan yang di dalam Docker |
| **Container** | "Komputer mini dalam komputer" yang dijalankan Docker, pakai Linux |
| **PHP built-in server** | Web server sederhana bawaan PHP, dipakai `php artisan serve` |
| **Apache** | Web server sungguhan, lebih canggih, baca `.htaccess` |
