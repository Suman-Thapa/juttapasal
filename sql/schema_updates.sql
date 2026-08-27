-- ============================================================
-- Kicks schema updates — run this once against your existing DB
-- (safe to run on the DB that already has orders/order_items)
-- ============================================================

-- 1. Admin access: Users table gets Role + Status
ALTER TABLE Users
  ADD COLUMN Role   ENUM('user','admin') NOT NULL DEFAULT 'user'   AFTER Password,
  ADD COLUMN Status ENUM('active','blocked') NOT NULL DEFAULT 'active' AFTER Role;

-- Promote yourself to admin after signing up normally, e.g.:
-- UPDATE Users SET Role = 'admin' WHERE Email = 'you@example.com';

-- 2. Shoe-appropriate product fields
-- (Category/Discount/Rating are already referenced by controller.php's
--  get_products()/manage_products() code — without these columns the
--  shop's filter/sort was failing silently against the old schema.)
ALTER TABLE product
  ADD COLUMN Category VARCHAR(50)  NOT NULL DEFAULT 'Men' AFTER Brand,   -- Men / Women / Kids
  ADD COLUMN Sizes     VARCHAR(255) NULL     AFTER Description,          -- CSV, e.g. "7,8,9,10,11"
  ADD COLUMN Discount  INT NOT NULL DEFAULT 0 AFTER Quantity,            -- percent, 0-100
  ADD COLUMN Rating    DECIMAL(2,1) NOT NULL DEFAULT 0 AFTER Discount;   -- 0.0 - 5.0

-- 3. Rename Quantity's role conceptually to "stock" — no column rename needed,
--    the app code already treats product.Quantity as available stock.

-- Done. Your existing `orders` / `order_items` tables (Payment_Method,
-- Payment_Status, Ref_Id, Transaction_Uuid, Status) are used as-is —
-- no changes needed there.
