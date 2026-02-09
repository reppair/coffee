# Database Schema

## Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          GLOBAL TABLES                                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   categories ◄───── products                                                 │
│                                                                              │
│   package_sizes                                                              │
│                                                                              │
│   locations ◄────────► location_user ◄────────► users                       │
│       │                                                                      │
│       │ (tenant)                                                             │
│       ▼                                                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│                       TENANT-SCOPED TABLES                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   bulk_stocks ─────► bulk_movements                                          │
│       │                   │                                                  │
│       │                   │                                                  │
│   package_stocks ──► package_movements                                       │
│       │                                                                      │
│       └── belongsTo package_sizes                                            │
│                                                                              │
│   All above have location_id for tenant scoping                             │
│                                                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                         ACTIVITY LOG                                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   activity_log (spatie/laravel-activitylog)                                 │
│   - Tracks all model changes (dirty attributes)                             │
│   - Tracks manual action logs (purchases, sales, etc.)                      │
│   - Tracks changes via polymorphic subject                                 │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Key Packages:**
- `spatie/laravel-activitylog` — Core activity logging
- `pxlrbt/filament-activity-log` — Display activity in Filament resources

---

## Tables

### categories

Groups products for organization.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| name | string | Required |
| slug | string | Unique, URL-friendly |
| description | text | Nullable |
| is_active | boolean | Default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### products

Coffee and tea items.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| category_id | bigint | Foreign key, nullable |
| name | string | Required |
| slug | string | Unique, URL-friendly |
| type | enum | 'coffee', 'tea' |
| sku | string | Nullable, unique if set |
| image | string | Nullable, file path |
| is_active | boolean | Default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### package_sizes

Available packaging options.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| name | string | Display name ("200g", "1kg") |
| weight_grams | integer | Actual weight (200, 1000) |
| sort_order | integer | For display ordering, default: 0 |
| is_active | boolean | Default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### locations

Physical shop locations.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| name | string | Required |
| address | text | Nullable |
| phone | string | Nullable |
| is_active | boolean | Default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### users

User accounts with role flags.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| name | string | Required |
| email | string | Unique |
| email_verified_at | timestamp | Nullable |
| password | string | Hashed |
| is_admin | boolean | Default: false, full access to global + all locations |
| is_staff | boolean | Default: false, tenant resources only |
| is_active | boolean | Default: true |
| remember_token | string | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**Role Logic:**
- `is_admin = true` → Admin
- `is_staff = true` → Staff  
- Both `false` → Customer (no panel access, selectable as buyer)

---

### location_user

Pivot table: which users work at which locations.

| Column | Type | Notes |
|--------|------|-------|
| location_id | bigint | Foreign key |
| user_id | bigint | Foreign key |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique constraint: (location_id, user_id)

---

### bulk_stocks

Raw inventory at each location.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| location_id | bigint | Foreign key |
| product_id | bigint | Foreign key |
| quantity_grams | integer | Current stock in grams |
| low_stock_threshold_grams | integer | Alert threshold, default: 5000 (5kg) |
| default_sale_price_per_kg | decimal(10,2) | Nullable, for quick bulk sales |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique constraint: (location_id, product_id)

---

### bulk_movements

All changes to bulk inventory. Tenant-scoped via location_id.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| location_id | bigint | Foreign key (tenant) |
| bulk_stock_id | bigint | Foreign key |
| user_id | bigint | Foreign key (who made the change) |
| customer_id | bigint | Nullable, FK to users (buyer for sales) |
| type | enum | See types below |
| quantity_grams_change | integer | Signed (+/−) |
| quantity_grams_before | integer | Snapshot |
| quantity_grams_after | integer | Snapshot |
| cost_per_kg | decimal(10,2) | Nullable, for purchases |
| sale_price_per_kg | decimal(10,2) | Nullable, for sales |
| supplier | string | Nullable, for purchases |
| related_movement_id | bigint | Nullable, FK to self, for transfers |
| notes | text | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**Movement Types:**
- `initial` — First stock setup
- `purchase` — Bought from supplier
- `sale` — Sold unpackaged to customer
- `packaging` — Converted to packages (links to package_movement)
- `transfer_out` — Sent to another location
- `transfer_in` — Received from another location
- `adjustment` — Count correction
- `damaged` — Lost or damaged

---

### package_stocks

Packaged inventory at each location.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| location_id | bigint | Foreign key (tenant) |
| product_id | bigint | Foreign key |
| package_size_id | bigint | Foreign key |
| quantity | integer | Current stock in units |
| price | decimal(10,2) | Retail price per unit |
| low_stock_threshold | integer | Alert threshold, default: 10 |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique constraint: (location_id, product_id, package_size_id)

---

### package_movements

All changes to package inventory. Tenant-scoped via location_id.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| location_id | bigint | Foreign key (tenant) |
| package_stock_id | bigint | Foreign key |
| user_id | bigint | Foreign key |
| customer_id | bigint | Nullable, FK to users (buyer for sales) |
| type | enum | See types below |
| quantity_change | integer | Signed (+/−) |
| quantity_before | integer | Snapshot |
| quantity_after | integer | Snapshot |
| sale_price | decimal(10,2) | Nullable, price per unit at time of sale |
| related_movement_id | bigint | Nullable, FK to self, for transfers |
| bulk_movement_id | bigint | Nullable, FK, for packaging link |
| notes | text | Nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**Movement Types:**
- `initial` — First stock setup
- `packaged` — Created from bulk (links to bulk_movement)
- `sale` — Sold to customer
- `transfer_out` — Sent to another location
- `transfer_in` — Received from another location
- `adjustment` — Count correction
- `damaged` — Lost or damaged

---

### activity_log

Spatie activity log table (auto-created by package).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| log_name | string | Log channel name |
| description | string | Human-readable description |
| subject_type | string | Model class being logged |
| subject_id | bigint | Model ID |
| causer_type | string | Usually "App\Models\User" |
| causer_id | bigint | User who caused the activity |
| properties | json | Old/new values, custom data |
| batch_uuid | uuid | Nullable, groups related activities |
| event | string | created, updated, deleted, custom |
| created_at | timestamp | |
| updated_at | timestamp | |

**Custom Properties Example:**
```json
{
  "old": { "quantity": 100 },
  "new": { "quantity": 95 },
  "action": "sale",
  "details": "Sold 5 bags to walk-in customer"
}
```

---

## Relationships

### Category
- hasMany → Product

### Product
- belongsTo → Category
- hasMany → BulkStock
- hasMany → PackageStock

### PackageSize
- hasMany → PackageStock

### Location (Tenant)
- hasMany → BulkStock
- hasMany → PackageStock
- hasMany → BulkMovement
- hasMany → PackageMovement
- belongsToMany → User (pivot: location_user)

### User
- belongsToMany → Location
- hasMany → BulkMovement (as performer)
- hasMany → BulkMovement (as customer)
- hasMany → PackageMovement (as performer)
- hasMany → PackageMovement (as customer)
- hasMany → Activity (as causer)

### BulkStock
- belongsTo → Location
- belongsTo → Product
- hasMany → BulkMovement

### BulkMovement
- belongsTo → Location
- belongsTo → BulkStock
- belongsTo → User (performer)
- belongsTo → User (customer, nullable)
- belongsTo → BulkMovement (related_movement_id, for transfers)
- hasOne → PackageMovement (via bulk_movement_id on package_movements)

### PackageStock
- belongsTo → Location
- belongsTo → Product
- belongsTo → PackageSize
- hasMany → PackageMovement

### PackageMovement
- belongsTo → Location
- belongsTo → PackageStock
- belongsTo → User (performer)
- belongsTo → User (customer, nullable)
- belongsTo → PackageMovement (related_movement_id, for transfers)
- belongsTo → BulkMovement (bulk_movement_id, for packaging)

### Activity (Spatie)
- morphTo → subject (any model)
- belongsTo → User (causer)

---

## Visual: Packaging Link

When packaging happens, two movements are created and linked:

```
bulk_movements                          package_movements
┌────────────────────────┐              ┌────────────────────────┐
│ id: 45                 │              │ id: 78                 │
│ type: packaging        │◄─────────────│ type: packaged         │
│ quantity: −2000g       │              │ quantity: +10          │
│                        │              │ bulk_movement_id: 45   │
└────────────────────────┘              └────────────────────────┘
```

---

## Visual: Transfer Link

When transferring, two movements of the same type are linked:

```
Bulk Transfer Example:

bulk_movements (source)                 bulk_movements (destination)
┌────────────────────────┐              ┌────────────────────────┐
│ id: 50                 │              │ id: 51                 │
│ type: transfer_out     │◄────────────►│ type: transfer_in      │
│ quantity: −3000g       │              │ quantity: +3000g       │
│ related_movement_id: 51│              │ related_movement_id: 50│
└────────────────────────┘              └────────────────────────┘
```

---

## Indexes

```
-- Bulk stock lookups (tenant-scoped)
bulk_stocks: (location_id)
bulk_stocks: (location_id, product_id) UNIQUE

-- Package stock lookups (tenant-scoped)
package_stocks: (location_id)
package_stocks: (package_size_id)
package_stocks: (location_id, product_id, package_size_id) UNIQUE

-- Movement queries (tenant-scoped)
bulk_movements: (location_id)
bulk_movements: (location_id, created_at)
bulk_movements: (bulk_stock_id)
bulk_movements: (user_id)
bulk_movements: (customer_id)
bulk_movements: (type)

package_movements: (location_id)
package_movements: (location_id, created_at)
package_movements: (package_stock_id)
package_movements: (user_id)
package_movements: (customer_id)
package_movements: (type)

-- Activity log
activity_log: (subject_type, subject_id)
activity_log: (causer_type, causer_id)

-- User location access
location_user: (user_id)
location_user: (location_id, user_id) UNIQUE
```

---

## Enums

### ProductType
- coffee
- tea

### BulkMovementType
- initial
- purchase
- sale
- packaging
- transfer_out
- transfer_in
- adjustment
- damaged

### PackageMovementType
- initial
- packaged
- sale
- transfer_out
- transfer_in
- adjustment
- damaged

---

## Tenant Scoping

Filament multi-tenancy uses Location as the tenant. Tenant-scoped models automatically filter by the current location.

**Tenant-scoped tables (have location_id):**
- bulk_stocks
- package_stocks
- bulk_movements
- package_movements

**Global tables (no location_id, admin only):**
- categories
- products
- package_sizes
- locations
- users
- location_user

**Role-based Access:**
```php
// Admin can see global resources
// Staff cannot see global resources
// Customer (both false) has no panel access

// In resource policy or canViewAny
public function canViewAny(User $user): bool
{
    return $user->is_admin;
}

// Panel access check
->authGuard('web')
->canAccess(fn (User $user) => $user->is_admin || $user->is_staff)
```

**Filament Implementation:**
```php
// In PanelProvider
->tenant(Location::class)
->tenantRoutePrefix('location')

// Global resources (admin only)
CategoryResource::class,
ProductResource::class,
PackageSizeResource::class,
LocationResource::class,
UserResource::class,

// Tenant resources (admin + staff)
BulkStockResource::class,
PackageStockResource::class,
BulkMovementResource::class,
PackageMovementResource::class,
```

---

## Activity Logging

Uses `spatie/laravel-activitylog`. Location is derived from subject model relationships.

**Automatic logging (model changes):**
```php
// In models, use TracksActivity trait
use App\Models\Concerns\TracksActivity;

class BulkStock extends Model
{
    use TracksActivity;
}
```

**Manual logging (business actions):**
```php
activity()
    ->performedOn($packageStock)
    ->causedBy(auth()->user())
    ->withProperties([
        'action' => 'sale',
        'quantity' => 5,
        'sale_price' => 8.50,
        'customer_id' => $customer?->id,
        'customer_name' => $customer?->name,
    ])
    ->log('Sold 5 × Ethiopian 200g @ €8.50 to Local Café');
```

**Customer Selection in Sales:**
```php
// Query customers for sale modal dropdown (users with both flags false)
User::where('is_admin', false)
    ->where('is_staff', false)
    ->where('is_active', true)
    ->pluck('name', 'id');
```

**Filament display:**
```php
// Use pxlrbt/filament-activity-log
use Pxlrbt\FilamentActivityLog\Pages\ListActivities;
```
