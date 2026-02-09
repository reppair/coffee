# User Stories

## Overview

Stories organized by feature area. Each story describes what the user wants to do and how it appears in Filament.

**Key Packages:**
- `spatie/laravel-activitylog` — Logs all model changes
- `pxlrbt/filament-activity-log` — Shows activity on each resource
- Filament Multi-tenancy — Location as tenant

---

## Location Context Stories

### US-01: Switch Location

> As a user, I can switch between locations to work with different inventory.

**Location Switcher (Filament Tenant Switcher)**

- Dropdown in header showing available locations
- Selecting a location changes the entire context
- All tenant-scoped data filters to selected location
- New records automatically belong to selected location

---

### US-02: View Activity Feed

> As a user, I can see recent actions at the current location.

**Dashboard Widget: Activity Feed**

- Shows recent activity for current location
- Includes: sales, purchases, packaging, transfers, adjustments
- Shows: action icon, description, user, time ago
- Click to view related record

---

### US-03: View Record History

> As a user, I can see the change history of any record.

**Activity Page on Each Resource (via filament-activity-log)**

- Shows all changes with old → new values in a table
- Shows who made each change
- Shows timestamp
- Restore action disabled — activities are audit-only

---

## Setup Stories (Global Resources — Admin Only)

### US-10: Manage Categories

> As an admin, I can organize products into categories.

**Resource: CategoryResource (Global — admin only)**

- Table: name, active status
- Table actions: view, edit, delete
- Form: name, description, active toggle
- View page actions: activities, edit, delete (disabled with policy tooltip when products exist)
- Activity tab: shows change history (restore disabled)

---

### US-11: Manage Products

> As an admin, I can add coffee and tea products.

**Resource: ProductResource (Global — admin only)**

- Table: image, name, category, type (coffee/tea), active
- Table actions: view, edit, delete
- Table filters: by category, by type, active only
- Form: name, description, category, type, SKU, image, active toggle
- View page actions: activities, edit, delete (disabled with policy tooltip when stock exists)
- Activity tab: shows change history (restore disabled)

---

### US-12: Manage Package Sizes

> As an admin, I can define available package sizes.

**Resource: PackageSizeResource (Global — admin only)**

- Table: name, weight (grams), sort order, active status
- Table actions: view, edit, delete
- Form: name, weight_grams, sort_order, is_active toggle
- View page actions: activities, edit, delete (disabled with policy tooltip when used by stock)
- Activity tab: shows change history (restore disabled)

---

### US-13: Manage Locations

> As an admin, I can add and configure locations.

**Resource: LocationResource (Global — admin only)**

- Table: name, address, active status
- Table actions: view, edit, delete
- Form: name, address, phone, active toggle
- View page actions: activities, edit, delete (disabled with policy tooltip when stock exists)
- Activity tab: shows change history (restore disabled)

---

### US-14: Manage Users

> As an admin, I can manage user accounts and roles.

**Resource: UserResource (Global — admin only)**

- Table: name, email, role badge (Admin/Staff/Customer), assigned locations
- Table actions: view, edit
- Form: name, email, password, is_admin (toggle), is_staff (toggle), location assignments
- View page actions: activities, edit
- Activity tab: shows change history (restore disabled)

**Role Logic:**
- `is_admin = true` → Admin (full access)
- `is_staff = true` → Staff (tenant access only)
- Both `false` → Customer (no panel access, selectable as buyer)

---

## Bulk Inventory Stories (Tenant-Scoped)

### US-20: View Bulk Stock

> As a user, I can see raw inventory at the current location.

**Resource: BulkStockResource (Tenant-scoped)**

- Automatically filtered to current location
- Table: product, quantity (kg), low stock status
- Filters: by product, by category, low stock only
- Search: by product name
- Display: quantity shown in kg with grams precision (e.g., "15.5 kg")
- Activity tab: shows change history

---

### US-21: Purchase Bulk (Receive Delivery)

> As a user, I can record when bulk inventory arrives from a supplier.

**Action: "Purchase" on BulkStockResource row**

Modal fields:
- Quantity (in kg, converted to grams internally)
- Cost per kg
- Supplier name (optional)
- Notes (optional)

Result:
- Bulk stock quantity increases
- Movement created (type: purchase) with cost recorded
- Activity logged: "Purchased X kg of [Product] @ €Y/kg"

If bulk stock doesn't exist for this product, offer to create it first.

---

### US-22: Package Bulk into Retail Products

> As a user, I can convert raw beans into packaged products.

**Action: "Package" on BulkStockResource row**

Modal fields:
- Package size (dropdown from PackageSize model)
- Either:
  - Number of packages to create (system calculates grams needed), OR
  - Grams to use (system calculates packages created)
- Notes (optional)

Validation:
- Enough bulk must exist
- Package stock record must exist (or offer to create with price)

Result:
- Bulk stock decreases by grams used
- Package stock increases by units created
- Bulk movement created (type: packaging)
- Package movement created (type: packaged, linked to bulk movement)
- Activity logged: "Packaged X kg into Y × [Size] bags"

---

### US-23: Sell Bulk (Unpackaged Sale)

> As a user, I can sell raw beans directly to a customer.

**Action: "Sell Bulk" on BulkStockResource row**

Modal fields:
- Quantity (in kg)
- Price per kg (pre-filled with default if set)
- Customer (optional, searchable dropdown of customers)
- Notes (optional)

Result:
- Bulk stock decreases
- Movement created (type: sale) with price and customer recorded
- Activity logged: "Sold X kg of [Product] @ €Y/kg" (to [Customer] if selected)

---

### US-24: Transfer Bulk Between Locations

> As a user, I can move raw inventory to another location.

**Action: "Transfer" on BulkStockResource row**

Modal fields:
- Destination location (dropdown, excludes current)
- Quantity (in kg)
- Notes (optional)

Validation:
- Enough bulk must exist at source
- If product doesn't exist at destination, offer to create bulk stock there

Result:
- Source bulk stock decreases
- Destination bulk stock increases (created if needed)
- Two movements created (transfer_out and transfer_in, linked)
- Activity logged at source: "Transferred X kg to [Location]"
- Activity logged at destination: "Received X kg from [Location]"

---

### US-25: Adjust Bulk Stock

> As a user, I can correct bulk quantities when counts are wrong.

**Action: "Adjust" on BulkStockResource row**

Modal fields:
- Actual quantity (in kg)
- Reason (dropdown: miscounted, damaged, other)
- Notes (required)

Result:
- Bulk stock set to new quantity
- Movement created (type: adjustment or damaged) showing difference
- Activity logged: "Adjusted [Product] by ±X kg (reason)"

---

### US-26: Set Up Bulk Stock

> As a user, I can add a product to the current location's bulk inventory.

**Create form on BulkStockResource**

Form fields:
- Product (location auto-set from tenant context)
- Initial quantity (in kg, can be 0)
- Low stock threshold (in kg)
- Default sale price per kg (optional)

Validation:
- Unique combination of location + product

Result:
- Bulk stock record created
- If quantity > 0, initial movement created
- Activity logged: "Created bulk stock for [Product]"

---

## Package Inventory Stories (Tenant-Scoped)

### US-30: View Package Stock

> As a user, I can see package inventory at the current location.

**Resource: PackageStockResource (Tenant-scoped)**

- Automatically filtered to current location
- Table: product, package size, quantity, price, low stock status
- Filters: by product, by package size, by category, low stock only
- Search: by product name
- Activity tab: shows change history

---

### US-31: Sell Packages

> As a user, I can record package sales to customers.

**Action: "Sell" on PackageStockResource row**

Modal fields:
- Quantity (number of units)
- Customer (optional, searchable dropdown of customers)
- Notes (optional)

Result:
- Package stock decreases
- Movement created (type: sale) with current price captured as `sale_price`
- Activity logged: "Sold X × [Product] [Size] @ €Y" (to [Customer] if selected)

---

### US-32: Transfer Packages Between Locations

> As a user, I can move packages to another location.

**Action: "Transfer" on PackageStockResource row**

Modal fields:
- Destination location
- Quantity (units)
- Notes (optional)

Validation:
- Enough packages must exist at source
- If product/size doesn't exist at destination, offer to create with same price

Result:
- Source package stock decreases
- Destination package stock increases
- Two movements created (linked)
- Activity logged at both locations

---

### US-33: Adjust Package Stock

> As a user, I can correct package quantities.

**Action: "Adjust" on PackageStockResource row**

Modal fields:
- Actual quantity
- Reason (miscounted, damaged, other)
- Notes (required)

Result:
- Package stock set to new quantity
- Movement created showing difference
- Activity logged: "Adjusted [Product] [Size] by ±X (reason)"

---

### US-34: Change Package Price

> As a user, I can update retail prices.

**Edit action on PackageStockResource**

When price field changes:
- Old and new price automatically logged via activity log
- New price takes effect immediately
- Activity logged: "Updated [Product] [Size] price: €X → €Y"

---

### US-35: Set Up Package Stock

> As a user, I can add a package type to the current location's inventory.

**Create form on PackageStockResource**

Form fields:
- Product (location auto-set from tenant context)
- Package size (dropdown from PackageSize model)
- Initial quantity (can be 0)
- Price
- Low stock threshold

Validation:
- Unique combination of location + product + package size

Result:
- Package stock record created
- Activity logged: "Created package stock for [Product] [Size]"

---

## Movement History Stories (Tenant-Scoped)

### US-40: View Bulk Movement History

> As a user, I can see all bulk inventory changes at the current location.

**Resource: BulkMovementResource (Tenant-scoped, read-only)**

- Automatically filtered to current location
- Table: date, user, product, type, change (kg), cost/price, notes
- Filters: by product, by type, by user, by date range
- Search: by notes content
- Badges: color-coded by movement type

---

### US-41: View Package Movement History

> As a user, I can see all package inventory changes at the current location.

**Resource: PackageMovementResource (Tenant-scoped, read-only)**

- Automatically filtered to current location
- Table: date, user, product, size, type, change, notes
- Filters: by product, by size, by type, by user, by date range
- Badges: color-coded by movement type

---

## Dashboard Stories

### US-50: Location Dashboard

> As a user, I see inventory status for the current location.

**Dashboard Page (Tenant-scoped)**

Widget: Bulk Stock Summary
- Total bulk at current location (kg)
- Breakdown by product

Widget: Package Stock Summary
- Total packages at current location
- Breakdown by product and size

Widget: Low Stock Alerts
- Bulk and packages below threshold at current location
- Quick action to restock or transfer

Widget: Activity Feed
- Recent activity at current location (from activity log)
- Shows: icon, description, user, time ago
- Click to view related record

Widget: Quick Stats
- Purchases this month (kg, cost)
- Sales this month (units, revenue)

---

### US-51: Low Stock Alerts

> As a user, I get notified about low inventory.

**Implementation**

- Dashboard widgets show low stock items prominently
- Navigation badge shows count of low stock items at current location
- Optional: daily email digest

---

## Reporting Stories

### US-60: Purchase Cost Report

> As a user, I can see purchase costs over time.

**Report Page (Tenant-scoped)**

- Date range filter
- Shows all purchases at current location with costs
- Totals by product
- Export to CSV

---

### US-61: Sales Report

> As a user, I can see sales activity.

**Report Page (Tenant-scoped)**

- Date range filter
- Shows bulk sales and package sales at current location
- Totals and breakdowns
- Export to CSV

---

### US-62: Inventory Valuation

> As a user, I can see the value of current inventory.

**Report Page (Tenant-scoped)**

- Package inventory × price = retail value
- Bulk inventory × average cost = cost value
- Totals for current location

---

## Quick Reference

### Role Logic

| is_admin | is_staff | Role | Panel Access |
|----------|----------|------|--------------|
| true | — | Admin | Full |
| false | true | Staff | Tenant only |
| false | false | Customer | None |

### Resource Access

| Resource | Admin | Staff | Customer |
|----------|-------|-------|----------|
| Categories | ✓ | — | — |
| Products | ✓ | — | — |
| PackageSizes | ✓ | — | — |
| Locations | ✓ | — | — |
| Users | ✓ | — | — |
| BulkStock | ✓ | ✓ (tenant) | — |
| PackageStock | ✓ | ✓ (tenant) | — |
| Movements | ✓ | ✓ (tenant) | — |
| Dashboard | ✓ | ✓ (tenant) | — |

### Resource Scoping

| Resource | Scope | Access |
|----------|-------|--------|
| Categories | Global | Admin only |
| Products | Global | Admin only |
| PackageSizes | Global | Admin only |
| Locations | Global | Admin only |
| Users | Global | Admin only |
| BulkStock | Tenant | Admin + Staff |
| PackageStock | Tenant | Admin + Staff |
| BulkMovement | Tenant | Admin + Staff |
| PackageMovement | Tenant | Admin + Staff |

### Actions by Resource

**BulkStockResource**

| Action | Creates Movement | Activity Logged |
|--------|------------------|-----------------|
| Purchase | purchase (+) | ✓ |
| Package | packaging (−) | ✓ |
| Sell Bulk | sale (−) | ✓ |
| Transfer | transfer_out/in | ✓ (both locations) |
| Adjust | adjustment/damaged | ✓ |

**PackageStockResource**

| Action | Creates Movement | Activity Logged |
|--------|------------------|-----------------|
| Sell | sale (−), captures sale_price | ✓ |
| Transfer | transfer_out/in | ✓ (both locations) |
| Adjust | adjustment/damaged | ✓ |
| Edit | — | ✓ (price changes) |
