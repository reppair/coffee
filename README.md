# Coffee & Tea Inventory System

A multi-location inventory management system for coffee roasters and tea shops. Track bulk beans from purchase through packaging to sale.

## Core Concept

```
Purchase Bulk → Store at Location → Package or Sell → Track Everything
```

**Two inventory types:**
- **Bulk** — Raw beans/leaves in grams (purchased from suppliers)
- **Packages** — Retail products in units (created from bulk)

## Key Features

- **Multi-location** — Each shop maintains its own stock
- **Bulk → Package workflow** — Convert raw inventory into retail products
- **Full audit trail** — Every change logged with who, what, when
- **Customer tracking** — Link sales to registered customers
- **Activity feed** — Real-time dashboard of location activity

## Tech Stack

- Laravel 12
- Filament PHP (admin panel)
- Livewire 4
- `spatie/laravel-activitylog` (audit trail)
- `pxlrbt/filament-activity-log` (Filament integration)

## Architecture

### Multi-tenancy

Location-based tenancy via Filament. Users switch locations via header dropdown.

| Scope | Resources |
|-------|-----------|
| **Global** (admin only) | Categories, Products, PackageSizes, Locations, Users |
| **Tenant** (per location) | BulkStock, PackageStock, Movements |

### User Roles

| is_admin | is_staff | Role | Access |
|----------|----------|------|--------|
| ✓ | — | Admin | Everything |
| ✗ | ✓ | Staff | Tenant resources only |
| ✗ | ✗ | Customer | No panel (selectable as buyer) |

## Main Actions

| Action | What happens |
|--------|--------------|
| **Purchase** | Add bulk from supplier, record cost |
| **Package** | Convert bulk → retail packages |
| **Sell** | Record sale (bulk or packages), optional customer |
| **Transfer** | Move stock between locations |
| **Adjust** | Correct quantities (damaged, miscount) |

## Data Model

```
Categories → Products → BulkStock (grams)
                    ↘ PackageStock (units) ← PackageSizes

Location (tenant) → BulkStock, PackageStock, Movements, Activity

User → performs actions, or is customer on sales
```

## Specs & Documentation

| File | Contents |
|------|----------|
| [CHANGELOG.md](CHANGELOG.md) | What's new, changed, and fixed |
| [CONCEPT.md](specs/CONCEPT.md) | Full system explanation |
| [USER_STORIES.md](specs/USER_STORIES.md) | All features with Filament implementation |
| [TASKS.md](specs/TASKS.md) | Implementation task breakdown |
| [DATABASE_SCHEMA.md](specs/DATABASE_SCHEMA.md) | Tables, relationships, indexes |
| [DECISIONS.md](specs/DECISIONS.md) | Technical decision log |

## Quick Start

```bash
composer install
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve
```

## License

MIT
