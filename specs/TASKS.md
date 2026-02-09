# Implementation Tasks

Status legend: `[ ]` pending · `[~]` in progress · `[x]` done

---

## Summary

| Group | Focus | Tasks | Stories | User can... |
|-------|-------|------:|---------|-------------|
| 1 | Panel Foundation | 5 | US-01 | Log in, switch locations — prerequisite for everything |
| 2 | Global Resources (Admin) | 7 | US-10–14, US-03 | Set up categories, products, sizes, locations, users |
| 3 | Tenant Stock Resources | 5 | US-20, US-26, US-30, US-35 | Create and view bulk + package inventory per location |
| 4 | Bulk Inventory Actions | 6 | US-21–25 | Purchase, package, sell, transfer, adjust raw stock |
| 5 | Package Inventory Actions | 5 | US-31–34 | Sell, transfer, adjust packages, update prices |
| 6 | Movement History | 3 | US-40–41 | Browse full audit trail of all inventory changes |
| 7 | Dashboard & Alerts | 7 | US-02, US-50–51 | See at-a-glance overview, low stock alerts, activity feed |
| 8 | Reports | 4 | US-60–62 | Run purchase/sales/valuation reports with CSV export |

After groups 1–3 the user has a working system for setup and inventory tracking.
After groups 4–5 all day-to-day operations are live. Groups 6–8 add visibility and analytics.

---

## Group 1: Panel Foundation

Sets up Filament multi-tenancy, access control, and location switching before any resources are built.

**Stories:** US-01 (Switch Location)

- [x] **1.1** Configure `AdminPanelProvider` with `Location` as tenant (`->tenant(Location::class)`)
- [x] **1.2** Add panel access gate — only `is_admin` or `is_staff` users can access
- [x] **1.3** Scope tenant list — admins see all locations, staff see assigned locations only
- [x] **1.4** Create `CHANGELOG.md`
- [x] **1.5** Tests: panel access (admin ✓, staff ✓, customer ✗), tenant switching, location scoping

---

## Group 2: Global Resources (Admin-Only)

CRUD for shared lookup tables. Admin-only access, no tenant scoping.

**Stories:** US-10, US-11, US-12, US-13, US-14

- [x] **2.1** `CategoryResource` — table (name, active), view page, form (name, description, active), policy delete guard with `authorizationTooltip()`, activity page
- [ ] **2.2** `ProductResource` — table (image, name, category, type, active), view page, form (name, description, category, type, sku, image, active), filters (category, type, active), policy delete guard with `authorizationTooltip()`, activity page
- [ ] **2.3** `PackageSizeResource` — table (name, weight, sort order, active), view page, form fields, policy delete guard with `authorizationTooltip()`, activity page
- [ ] **2.4** `LocationResource` — table (name, address, active), view page, form fields, policy delete guard with `authorizationTooltip()`, activity page
- [ ] **2.5** `UserResource` — table (name, email, role badge, assigned locations), view page, form (name, email, password, is_admin, is_staff, location assignments), activity page (no delete)
- [ ] **2.6** Policies for all 5 global resources — `viewAny` requires `is_admin`
- [ ] **2.7** Tests: CRUD operations, delete guards, access control (staff cannot access)

---

## Group 3: Tenant Stock Resources

View and create stock records scoped to current location.

**Stories:** US-20, US-26, US-30, US-35

- [ ] **3.1** `BulkStockResource` — table (product, quantity in kg, low stock badge), filters (product, category, low stock), search by product name, create form (product, initial qty, threshold, default sale price), activity tab
- [ ] **3.2** `PackageStockResource` — table (product, package size, quantity, price, low stock badge), filters (product, size, category, low stock), search by product name, create form (product, size, initial qty, price, threshold), editable price field, activity tab
- [ ] **3.3** Unique constraint validation — bulk: location+product, package: location+product+size
- [ ] **3.4** Initial movement creation — when stock created with qty > 0, create `initial` movement
- [ ] **3.5** Tests: list/create/edit, tenant scoping (can't see other location's stock), unique constraints, initial movements

---

## Group 4: Bulk Inventory Actions

Row actions on `BulkStockResource` that create movements and update quantities.

**Stories:** US-21, US-22, US-23, US-24, US-25

- [ ] **4.1** Purchase action — modal (qty kg, cost/kg, supplier, notes), increases stock, creates `purchase` movement
- [ ] **4.2** Package action — modal (package size, qty or grams, notes), validates enough bulk, decreases bulk, increases package stock, creates linked `packaging`/`packaged` movements
- [ ] **4.3** Sell Bulk action — modal (qty kg, price/kg prefilled from default, customer dropdown, notes), decreases stock, creates `sale` movement
- [ ] **4.4** Transfer action — modal (destination location, qty kg, notes), validates enough stock, creates/finds destination bulk stock, creates linked `transfer_out`/`transfer_in` movements at both locations
- [ ] **4.5** Adjust action — modal (actual qty kg, reason dropdown, notes required), sets stock to new qty, creates `adjustment` or `damaged` movement
- [ ] **4.6** Tests: each action's happy path, validation (insufficient stock, etc.), movement records, stock quantity changes, linked movements

---

## Group 5: Package Inventory Actions

Row actions on `PackageStockResource` that create movements and update quantities.

**Stories:** US-31, US-32, US-33, US-34

- [ ] **5.1** Sell action — modal (qty units, customer dropdown, notes), decreases stock, creates `sale` movement with captured `sale_price`
- [ ] **5.2** Transfer action — modal (destination, qty units, notes), validates enough stock, creates/finds destination package stock, creates linked `transfer_out`/`transfer_in` movements
- [ ] **5.3** Adjust action — modal (actual qty, reason, notes required), sets stock, creates `adjustment`/`damaged` movement
- [ ] **5.4** Price change logging — price edit on PackageStock triggers activity log with old → new
- [ ] **5.5** Tests: each action, validation, movements, linked movements, price change audit

---

## Group 6: Movement History (Read-Only)

Browse all inventory changes at current location. No create/edit — movements are created by actions.

**Stories:** US-40, US-41

- [ ] **6.1** `BulkMovementResource` — read-only table (date, user, product, type badge, change in kg, cost/price, supplier, notes), filters (product, type, user, date range), search by notes
- [ ] **6.2** `PackageMovementResource` — read-only table (date, user, product, size, type badge, change, sale price, notes), filters (product, size, type, user, date range)
- [ ] **6.3** Tests: tenant scoping, read-only (no create/edit/delete), filters

---

## Group 7: Dashboard & Alerts

Widgets on the tenant-scoped dashboard.

**Stories:** US-02, US-50, US-51

- [ ] **7.1** Bulk Stock Summary widget — total kg at location, breakdown by product
- [ ] **7.2** Package Stock Summary widget — total units at location, breakdown by product+size
- [ ] **7.3** Low Stock Alerts widget — bulk and packages below threshold, links to records
- [ ] **7.4** Activity Feed widget — recent activity at location (from spatie activity log), icon + description + user + time ago
- [ ] **7.5** Quick Stats widget — purchases this month (kg, cost), sales this month (units, revenue)
- [ ] **7.6** Navigation badge — low stock count on BulkStock/PackageStock nav items
- [ ] **7.7** Tests: widgets render correct data, scoped to current location

---

## Group 8: Reports

Dedicated report pages with date filtering and CSV export.

**Stories:** US-60, US-61, US-62

- [ ] **8.1** Purchase Cost Report page — date range filter, purchases at location with costs, totals by product, CSV export
- [ ] **8.2** Sales Report page — date range, bulk + package sales, totals and breakdowns, CSV export
- [ ] **8.3** Inventory Valuation page — package inventory × price, bulk inventory × avg cost, location totals
- [ ] **8.4** Tests: report data accuracy, date filtering, tenant scoping
