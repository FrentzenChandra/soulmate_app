# Graph Report - soulmate_app  (2026-09-13)

## Corpus Check
- 106 files · ~69,496 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 565 nodes · 912 edges · 58 communities (27 shown, 17 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Master Data Kategori
- POS Kasir & Checkout
- Autentikasi & Pengguna
- POS Kasir & Checkout
- Modul package.json
- POS Kasir & Checkout
- Autentikasi & Pengguna
- Master Data Kategori
- Master Data Kategori
- Modul Index
- Master Data Kategori
- Manajemen Multi-Cabang
- POS Kasir & Checkout
- Autentikasi & Pengguna
- Modul Index
- Master Data Kategori
- Modul require
- Master Data Kategori
- POS Kasir & Checkout
- Master Data Kategori
- Modul require-dev
- Frontend UI & Blade Views
- Modul pestphp/pest-plugin
- Master Data Kategori
- Master Data Produk
- Modul app.php
- POS Kasir & Checkout
- Autentikasi & Pengguna
- Frontend UI & Blade Views
- Master Data Kategori
- Modul AppServiceProvider.php
- Database & Migration
- Manajemen Multi-Cabang
- Modul logging.php
- Sistem Notifikasi & Alert
- Automated Test Suite
- Automated Test Suite
- Modul Illuminate\Foundation\Inspiring
- Manajemen Multi-Cabang
- Manajemen Multi-Cabang
- Routing & Controllers
- Autentikasi & Pengguna
- Modul Illuminate\Support\Facades\Broadcast
- Frontend UI & Blade Views

## God Nodes (most connected - your core abstractions)
1. `Cabang` - 53 edges
2. `Produk` - 40 edges
3. `Index` - 37 edges
4. `User` - 35 edges
5. `Index` - 28 edges
6. `KategoriProduk` - 25 edges
7. `Stok` - 25 edges
8. `Index` - 20 edges
9. `KasirPosTest` - 20 edges
10. `Penjualan` - 19 edges

## Surprising Connections (you probably didn't know these)
- `KasirPosTest` --references--> `Cabang`  [EXTRACTED]
  tests/Feature/KasirPosTest.php → app/Models/Cabang.php
- `OwnerMasterTest` --references--> `Cabang`  [EXTRACTED]
  tests/Feature/OwnerMasterTest.php → app/Models/Cabang.php
- `ProductAndStockTest` --references--> `Cabang`  [EXTRACTED]
  tests/Feature/ProductAndStockTest.php → app/Models/Cabang.php
- `ProductAndStockTest` --references--> `KategoriProduk`  [EXTRACTED]
  tests/Feature/ProductAndStockTest.php → app/Models/KategoriProduk.php
- `KasirPosTest` --references--> `MasterPpn`  [EXTRACTED]
  tests/Feature/KasirPosTest.php → app/Models/MasterPpn.php

## Import Cycles
- None detected.

## Communities (58 total, 17 thin omitted)

### Community 0 - "Master Data Kategori"
Cohesion: 0.07
Nodes (14): Customer, NomorUrutTransaksi, Penjualan, PenjualanDetail, ServisDetail, ServisMasuk, ServisStatusLog, CustomerSeeder (+6 more)

### Community 1 - "POS Kasir & Checkout"
Cohesion: 0.05
Nodes (17): User, DatabaseSeeder, RoleAndUserSeeder, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash (+9 more)

### Community 2 - "Autentikasi & Pengguna"
Cohesion: 0.06
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 3 - "POS Kasir & Checkout"
Cohesion: 0.07
Nodes (3): Index, MasterPpn, MasterPpnSeeder

### Community 4 - "Modul package.json"
Cohesion: 0.07
Nodes (27): dependencies, apexcharts, html5-qrcode, devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin (+19 more)

### Community 5 - "POS Kasir & Checkout"
Cohesion: 0.10
Nodes (19): addToCart({{ $p->id }}), adjustQty({{ $item[, clearCart, closeScanModal, newTransaction, openNewCustomerModal, openPaymentModal, openScanModal( (+11 more)

### Community 6 - "Autentikasi & Pengguna"
Cohesion: 0.26
Nodes (10): WithSorting, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\DB, Illuminate\Validation\Rule, Illuminate\Validation\ValidationException, Livewire\Attributes\Layout, Livewire\Attributes\Title, Livewire\Component (+2 more)

### Community 7 - "Master Data Kategori"
Cohesion: 0.13
Nodes (3): Index, NotificationBell, Stok

### Community 10 - "Master Data Kategori"
Cohesion: 0.13
Nodes (14): cancelEditKategori, deleteProduk({{ $p->id }}), generateBarcode, openEditModal({{ $p->id }}), openKategoriModal, cancelUploadFoto, deleteKategori({{ $k->id }}), loadMore (+6 more)

### Community 11 - "Manajemen Multi-Cabang"
Cohesion: 0.18
Nodes (3): BranchSwitcher, Cabang, CabangSeeder

### Community 13 - "Autentikasi & Pengguna"
Cohesion: 0.17
Nodes (3): Login, Index, Illuminate\Support\Facades\Route

### Community 16 - "Modul require"
Cohesion: 0.18
Nodes (11): require, barryvdh/laravel-dompdf, intervention/image, laravel/framework, laravel/reverb, laravel/tinker, livewire/livewire, maatwebsite/excel (+3 more)

### Community 18 - "POS Kasir & Checkout"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 20 - "Modul require-dev"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 21 - "Frontend UI & Blade Views"
Cohesion: 0.25
Nodes (7): openEditModal({{ $u->id }}), cancelUploadFoto, closeModal, loadMore, openCreateModal, removeExistingFoto, toggleStatus({{ $u->id }})

### Community 22 - "Modul pestphp/pest-plugin"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 24 - "Master Data Produk"
Cohesion: 0.29
Nodes (6): openAdjustModal({{ $item->id }}), quickDelta(-1), quickDelta(10), quickDelta(-5), loadMore, $set(

### Community 25 - "Modul app.php"
Cohesion: 0.33
Nodes (4): Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Request

### Community 26 - "POS Kasir & Checkout"
Cohesion: 0.33
Nodes (6): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd

### Community 27 - "Autentikasi & Pengguna"
Cohesion: 0.47
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 28 - "Frontend UI & Blade Views"
Cohesion: 0.33
Nodes (5): openEditModal({{ $c->id }}), closeModal, loadMore, openCreateModal, toggleStatus({{ $c->id }})

### Community 29 - "Master Data Kategori"
Cohesion: 0.33
Nodes (5): openEditModal({{ $k->id }}), closeModal, deleteKategori({{ $k->id }}), loadMore, openCreateModal

### Community 31 - "Database & Migration"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 32 - "Manajemen Multi-Cabang"
Cohesion: 0.40
Nodes (5): dev-master, extra, branch-alias, laravel, dont-discover

### Community 33 - "Modul logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 34 - "Sistem Notifikasi & Alert"
Cohesion: 0.50
Nodes (3): components.branch-switcher, components.notification-bell, $dispatch(

### Community 36 - "Automated Test Suite"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

## Knowledge Gaps
- **123 isolated node(s):** `Controller`, `$schema`, `name`, `type`, `description` (+118 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 324 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **17 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Cabang` connect `Manajemen Multi-Cabang` to `Master Data Kategori`, `POS Kasir & Checkout`, `POS Kasir & Checkout`, `Autentikasi & Pengguna`, `Master Data Kategori`, `Master Data Kategori`, `Modul Index`, `POS Kasir & Checkout`, `Autentikasi & Pengguna`, `Modul Index`, `Master Data Kategori`, `Master Data Kategori`, `Master Data Kategori`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Why does `Index` connect `POS Kasir & Checkout` to `Master Data Kategori`, `POS Kasir & Checkout`, `Autentikasi & Pengguna`, `Autentikasi & Pengguna`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `Produk` connect `POS Kasir & Checkout` to `Master Data Kategori`, `POS Kasir & Checkout`, `POS Kasir & Checkout`, `Autentikasi & Pengguna`, `Master Data Kategori`, `Master Data Kategori`, `Master Data Kategori`, `Master Data Kategori`, `Master Data Kategori`, `Master Data Kategori`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **What connects `Controller`, `$schema`, `name` to the rest of the system?**
  _123 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Master Data Kategori` be split into smaller, more focused modules?**
  _Cohesion score 0.06829573934837092 - nodes in this community are weakly interconnected._
- **Should `POS Kasir & Checkout` be split into smaller, more focused modules?**
  _Cohesion score 0.05380852550663871 - nodes in this community are weakly interconnected._
- **Should `Autentikasi & Pengguna` be split into smaller, more focused modules?**
  _Cohesion score 0.0593990216631726 - nodes in this community are weakly interconnected._