# Changelog

All notable changes to the Coffee & Tea Inventory System.

## [Unreleased]

### Added
- Complete data model for managing bulk (raw beans/leaves) and packaged (retail bags) inventory
- 8 core models: Categories, Products, Package Sizes, Locations, Bulk Stock, Package Stock, Bulk Movements, Package Movements
- User roles system with admin, staff, and customer flags
- Full activity logging and audit trail for all inventory changes
- Support for all inventory actions: purchases, packaging, sales, transfers, adjustments, and damage tracking
- Location-based multi-tenancy — each shop manages its own inventory independently
- Product descriptions for product-specific details
- Implementation task breakdown covering 28 user stories across 8 delivery milestones

### Changed
- Products now have their own description field (previously relied on category description only)
- Simplified the link between bulk and package movements for a cleaner audit trail
- Locations and users cannot be deleted while they have associated stock or movements (safety constraint)
