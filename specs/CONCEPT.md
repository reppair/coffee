# Coffee & Tea Inventory System

## What Is This?

A system for coffee shop owners to:
- Buy raw coffee beans in bulk (organized by category)
- Package beans into various sizes for sale
- Track both raw (bulk) and packaged inventory at each location
- Sell packages or unpackaged beans directly
- Move stock between locations
- See total inventory across all shops

---

## The Two Types of Inventory

This system tracks **two distinct inventories** that are connected:

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                     │
│   BULK INVENTORY              ───►        PACKAGE INVENTORY         │
│   (Raw beans/leaves)          packaging   (Finished products)       │
│                                                                     │
│   Measured in GRAMS                       Measured in UNITS         │
│   "We have 15kg of                        "We have 45 bags of       │
│    Ethiopian beans"                        Ethiopian 200g"          │
│                                                                     │
│   Inherits category from                  Uses defined package      │
│   product (Single Origin,                 sizes (200g, 500g, 1kg)   │
│   Blends, Black Tea, etc.)                                          │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### Bulk Inventory (Raw Material)
- Purchased from suppliers in kilograms
- Organized by product category (Single Origin, Blends, etc.)
- Stored as raw beans/leaves
- Tracked in grams for precision
- Gets consumed when packaging
- Can also be sold directly (unpackaged bulk sales)

### Package Inventory (Finished Product)
- Created by packaging bulk inventory
- Uses defined package sizes (200g, 500g, 1kg, etc.)
- Stored as individual units (bags, tins)
- Ready for retail sale
- Has a set retail price

---

## How It All Flows

```
                    ┌─────────────┐
                    │  SUPPLIER   │
                    └──────┬──────┘
                           │
                           │ Purchase (10kg @ €25/kg)
                           ▼
┌──────────────────────────────────────────────────────────────────────┐
│                        BULK INVENTORY                                │
│                                                                      │
│   Location: Main Street                                              │
│   Product: Ethiopian Yirgacheffe                                     │
│   Quantity: 15,000g (15kg)                                          │
│                                                                      │
└─────────────┬────────────────────────────────────────────────────────┘
              │                                │
              │ Package                        │ Bulk Sale
              │ (use 2kg to make              │ (sell 5kg to
              │  10 × 200g bags)               │  restaurant)
              ▼                                ▼
┌─────────────────────────────┐    ┌─────────────────────────────┐
│    PACKAGE INVENTORY        │    │      BULK SALE              │
│                             │    │                             │
│    10 × Ethiopian 200g      │    │    5kg @ €35/kg             │
│    @ €8.50 each             │    │    Customer: Local Café     │
│                             │    │                             │
└──────────────┬──────────────┘    └─────────────────────────────┘
               │
               │ Retail Sale
               │ (sell 2 bags)
               ▼
┌─────────────────────────────┐
│      PACKAGE SALE           │
│                             │
│      2 bags @ €8.50         │
│                             │
└─────────────────────────────┘
```

---

## Location Independence

Each location manages its own inventory. They can buy, package, sell, and transfer independently.

```
┌─────────────────────────────────┐      ┌─────────────────────────────────┐
│         MAIN STREET             │      │         AIRPORT KIOSK           │
├─────────────────────────────────┤      ├─────────────────────────────────┤
│                                 │      │                                 │
│  BULK:                          │      │  BULK:                          │
│  • Ethiopian: 15kg              │      │  • Ethiopian: 3kg               │
│  • House Blend: 8kg             │      │  • House Blend: 2kg             │
│                                 │      │                                 │
│  PACKAGES:                      │      │  PACKAGES:                      │
│  • Ethiopian 200g: 45 units     │      │  • Ethiopian 200g: 12 units     │
│  • Ethiopian 1kg: 10 units      │      │  • Ethiopian 1kg: 3 units       │
│  • House Blend 200g: 30 units   │      │  • House Blend 200g: 8 units    │
│                                 │      │                                 │
└─────────────────────────────────┘      └─────────────────────────────────┘
                    │                                    ▲
                    │         Transfer                   │
                    │    (5kg bulk + 10 packages)        │
                    └────────────────────────────────────┘
```

---

## Core Concepts

### 1. Products & Categories

Products are your coffee and tea varieties. Each belongs to a category. Each product can exist as bulk AND as packages.

```
Category: Single Origin
└── Product: Ethiopian Yirgacheffe
    ├── Bulk form: raw beans (tracked in grams)
    └── Package forms: 200g, 500g, 1kg bags (tracked in units)

Category: Black Tea  
└── Product: Earl Grey
    ├── Bulk form: loose leaf (tracked in grams)
    └── Package forms: 200g, 500g tins (tracked in units)
```

### 2. Package Sizes

Define what package sizes you offer. Managed by admins.

```
Package Sizes
├── 200g (200 grams)
├── 500g (500 grams)
└── 1kg (1000 grams)
```

New sizes can be added as needed (e.g., 250g, 100g sample packs).

### 3. Locations (Shops)

Physical locations that maintain their own inventory.

```
Locations
├── Main Street Shop (primary, does most packaging)
├── Airport Kiosk
└── Farmers Market Stand
```

### 4. Bulk Stock

Raw inventory at a specific location.

```
Bulk Stock Record
├── Location: Main Street
├── Product: Ethiopian Yirgacheffe
├── Quantity: 15,000 grams (15kg)
├── Low stock alert: when below 5,000g
└── Default bulk sale price: €35/kg
```

### 5. Package Stock

Finished package inventory at a specific location.

```
Package Stock Record
├── Location: Main Street
├── Product: Ethiopian Yirgacheffe
├── Package Size: 200g
├── Quantity: 45 units
├── Price: €8.50 per unit
└── Low stock alert: when below 10 units
```

---

## All The Actions

### Bulk Inventory Actions

| Action | What Happens | Example |
|--------|--------------|---------|
| **Purchase** | Add bulk, record cost | Buy 10kg @ €25/kg |
| **Package** | Convert bulk to packages | Use 2kg to make 10 × 200g bags |
| **Bulk Sale** | Sell unpackaged to customer | Sell 5kg @ €35/kg to café |
| **Transfer Out** | Send bulk to another location | Send 3kg to Airport |
| **Transfer In** | Receive bulk from another location | Receive 3kg from Main St |
| **Adjustment** | Correct quantity | Fix counting error |
| **Damaged** | Record loss | 500g spilled |

### Package Inventory Actions

| Action | What Happens | Example |
|--------|--------------|---------|
| **Packaged** | Created from bulk | 10 bags created from 2kg |
| **Sale** | Sell to customer | Sell 2 bags @ €8.50 |
| **Transfer Out** | Send to another location | Send 5 bags to Airport |
| **Transfer In** | Receive from another location | Receive 5 bags from Main |
| **Adjustment** | Correct quantity | Fix counting error |
| **Damaged** | Record loss | 1 bag damaged |

---

## The Packaging Process

When you package bulk inventory into retail packages:

```
BEFORE PACKAGING
────────────────
Bulk: Ethiopian @ Main Street
      15,000g available

Packages: Ethiopian 200g @ Main Street  
          35 units available


ACTION: Package 2,000g into 200g bags
────────────────────────────────────
Uses: 2,000g of bulk
Creates: 10 × 200g packages


AFTER PACKAGING
───────────────
Bulk: Ethiopian @ Main Street
      13,000g available (−2,000g)

Packages: Ethiopian 200g @ Main Street
          45 units available (+10)
```

The system automatically:
- Validates enough bulk exists
- Calculates how many packages can be made
- Reduces bulk stock
- Increases package stock
- Links both movements together for audit trail

---

## Transfers Between Locations

You can transfer both bulk and packages between locations.

### Bulk Transfer
```
Transfer 3kg Ethiopian from Main Street to Airport

Main Street Bulk: 15,000g → 12,000g (−3,000g, transfer_out)
Airport Bulk:      3,000g →  6,000g (+3,000g, transfer_in)

Both movements linked together
```

### Package Transfer
```
Transfer 10 Ethiopian 200g bags from Main Street to Airport

Main Street Packages: 45 → 35 (−10, transfer_out)
Airport Packages:     12 → 22 (+10, transfer_in)

Both movements linked together
```

---

## Cost & Price Tracking

### Bulk Costs
Each purchase records cost per kilogram for analysis:
- "Bought 10kg Ethiopian @ €25/kg on Jan 10"
- "Bought 15kg Ethiopian @ €27/kg on Feb 5" (price went up)

### Package Prices
Each package stock has a retail price:
- "Ethiopian 200g sells for €8.50"
- Price changes are logged in history

### Bulk Sale Prices
When selling unpackaged bulk, price is entered at sale time:
- Can have a default price on bulk stock
- Can override per sale

---

## Dashboard Metrics

### Global Overview (All Locations)
```
┌────────────────────────────────────────────────────────────────┐
│ COMPANY TOTALS                                                 │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Total Bulk Inventory          Total Package Inventory         │
│  ┌──────────────────┐          ┌──────────────────┐           │
│  │    87.5 kg       │          │    342 units     │           │
│  │    across all    │          │    across all    │           │
│  │    products      │          │    products      │           │
│  └──────────────────┘          └──────────────────┘           │
│                                                                │
│  By Product:                   By Product:                     │
│  • Ethiopian: 25kg             • Ethiopian 200g: 120           │
│  • House Blend: 18kg           • Ethiopian 1kg: 35             │
│  • Colombian: 22kg             • House Blend 200g: 95          │
│  • Earl Grey: 12kg             • House Blend 1kg: 42           │
│  • Chamomile: 10.5kg           • ...                           │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

### Per-Location Dashboard
```
┌────────────────────────────────────────────────────────────────┐
│ MAIN STREET SHOP                                               │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Bulk Stock              Packages              Alerts          │
│  ┌────────────┐         ┌────────────┐        ┌────────────┐  │
│  │   45 kg    │         │  215 units │        │  3 low     │  │
│  └────────────┘         └────────────┘        └────────────┘  │
│                                                                │
│  ⚠️ Low Stock:                                                 │
│  • Ethiopian bulk: 4.2kg (threshold: 5kg)                      │
│  • House Blend 200g: 8 units (threshold: 10)                   │
│  • Earl Grey 200g: 5 units (threshold: 10)                     │
│                                                                │
│  Recent Activity:                                              │
│  • Maria sold 3 × Ethiopian 200g                               │
│  • Admin packaged 5kg House Blend → 25 bags                    │
│  • Jan sold 2kg bulk to Local Café                             │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

## Users & Roles

Users have simple boolean flags for access control.

| Flags | Role | Access |
|-------|------|--------|
| `is_admin = true` | Admin | Global resources + all locations |
| `is_staff = true` | Staff | Tenant resources only, assigned locations |
| Both `false` | Customer | No panel access, selectable as buyer |

```
Users
├── Owner (is_admin: true) — manages everything
├── Maria (is_staff: true) — works at Main Street
├── Jan (is_staff: true) — works at Main Street + Airport
└── Local Café (both false) — bulk buyer, selectable in sales
```

---

## Location Context (Multi-tenancy)

The system uses **location-based multi-tenancy**. Users switch between locations using a location switcher in the UI.

```
┌─────────────────────────────────────────────────────────┐
│  ☕ Coffee Inventory          [Main Street ▼]  [Maria]  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  When "Main Street" is selected:                        │
│  • See only Main Street's bulk stock                    │
│  • See only Main Street's package stock                 │
│  • See only Main Street's activity                      │
│  • All new records created belong to Main Street        │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### What's Scoped to Location (Tenant)
- Bulk Stock
- Package Stock
- Bulk Movements
- Package Movements
- Activity Feed

### What's Global (Shared)
- Categories
- Products
- Users
- Package Sizes (enum)

---

## Activity Logging

Every action in the system is logged for full audit trail.

### Automatic Logging
All model changes are automatically tracked:
- What changed (old value → new value)
- Who changed it
- When it changed

### Manual Action Logging
Business actions are explicitly logged:
- "Purchased 10kg of Ethiopian @ €25/kg"
- "Packaged 2kg into 10 × 200g bags"
- "Sold 5 bags to walk-in customer"
- "Transferred 3kg to Airport"

### Activity Feed
The dashboard shows recent activity:

```
┌─────────────────────────────────────────────────────────┐
│  Recent Activity @ Main Street                          │
├─────────────────────────────────────────────────────────┤
│  🛒 Maria sold 3 × Ethiopian 200g              2 min ago│
│  📦 Jan packaged 5kg House Blend → 25 bags    15 min ago│
│  🚚 Maria transferred 2kg to Airport          30 min ago│
│  💰 Admin purchased 10kg Ethiopian @ €25/kg    1 hr ago │
│  ✏️ Admin updated Ethiopian price: €8→€8.50    2 hr ago │
└─────────────────────────────────────────────────────────┘
```

---

## Glossary

| Term | Meaning |
|------|---------|
| **Bulk** | Raw, unpackaged inventory (beans, leaves) measured in grams |
| **Package** | Finished retail product measured in units |
| **Purchase** | Buying bulk from supplier |
| **Packaging** | Converting bulk into packages |
| **Bulk Sale** | Selling unpackaged product (e.g., 5kg to a restaurant) |
| **Package Sale** | Selling retail packages to customers |
| **Transfer** | Moving inventory between locations |
| **Location/Shop** | A physical place with its own inventory |
