# Changelog

All notable changes to the Coffee & Tea Inventory System.

## [Unreleased]

### Added
- Product management resource for admins — create, edit, and delete products with full CRUD, image upload, activity log
- Category management resource for admins — create, edit, and delete product categories
- Admin panel multi-tenancy with Location as tenant — users switch between locations
- Panel access control — only admin and staff users can access the admin panel
- Tenant scoping — admins see all locations, staff see only their assigned locations
- Complete data model for managing bulk (raw beans/leaves) and packaged (retail bags) inventory
- 8 core models: Categories, Products, Package Sizes, Locations, Bulk Stock, Package Stock, Bulk Movements, Package Movements
- User roles system with admin, staff, and customer flags
- Full activity logging and audit trail for all inventory changes
- Support for all inventory actions: purchases, packaging, sales, transfers, adjustments, and damage tracking
- Location-based multi-tenancy — each shop manages its own inventory independently
- Product descriptions for product-specific details
- Implementation task breakdown covering 28 user stories across 8 delivery milestones

### Changed
- Product deletion uses soft deletes to preserve historical data and audit trails
- Product list includes trashed filter, restore action on table rows and view page, force delete on view page for soft-deleted products
- Product form restructured to two-column layout — fields in a left section, image upload on the right
- Category form wrapped in a section with active toggle first
- Stock tables use nullable product reference to preserve records when products are permanently deleted
- Product delete guard checks active inventory quantity instead of mere stock record existence
- Products now have their own description field (previously relied on category description only)
- Simplified the link between bulk and package movements for a cleaner audit trail
- Locations and users cannot be deleted while they have associated stock or movements (safety constraint)
