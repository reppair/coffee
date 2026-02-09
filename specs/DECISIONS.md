# Technical Decisions

| # | Date | Decision | Rationale |
|---|------|----------|-----------|
| 001 | 2026-02-08 | ~~Products inherit description from category — no `description` column on products~~ **REVERSED** (2026-02-09) | ~~Avoids duplication; products in same category share context~~ Products now have their own `description` field for product-specific details |
| 002 | 2026-02-08 | No `id` primary key on `location_user` pivot | Unique constraint on `(location_id, user_id)` is sufficient |
| 003 | 2026-02-08 | `restrictOnDelete()` on `location_id` and `user_id` FKs in stock/movement tables | Prevents cascade-deleting audit trail; locations to be archived/soft-deleted later |
| 004 | 2026-02-08 | Enum columns stored as `string` with PHP enum casts | Avoids MySQL enum limitations; Laravel recommended approach |
| 005 | 2026-02-08 | Single FK for packaging link: `bulk_movement_id` on `package_movements` only | Bidirectional FKs were redundant and created circular migration dependency |
| 006 | 2026-02-09 | No `location_id` on `activity_log` table | Redundant — location is derivable from subject model relationships; avoids denormalization overhead |
| 007 | 2026-02-09 | No product count column on CategoryResource table | Unnecessary — delete guard works regardless; simplifies the table |
| 008 | 2026-02-09 | Disable restore action on activity log pages | Prevents accidental data rollback; activities are for audit purposes only |
| 009 | 2026-02-09 | Delete guard pattern: policy `Response::deny()` with `authorizationTooltip()` | Server-side enforcement via policy; Filament disables button and shows deny message as tooltip. Applied to all global resources with delete guards |
| 010 | 2026-02-09 | Product soft deletes & nullable stock `product_id` | Soft deletes preserve FK relationships and audit trail. Nullable `product_id` with `nullOnDelete()` preserves historical stock records when force-deleted. Delete guard checks active inventory (qty > 0), not record existence |
