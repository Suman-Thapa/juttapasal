# Kicks — Payment + Order Dashboard Integration

This patch adds real eSewa/Khalti/COD checkout, a "My Orders" page, and an
admin Orders & Payments dashboard to Kicks, ported from your Electronics
project's logic and re-styled to match Kicks' orange/black/white look.

## 1. Copy these files into your Kicks project (same paths, will overwrite)

```
sql/schema_updates.sql          <- run this against your DB first
includes/auth.php               <- NEW
server/db_pdo.php               <- NEW
server/controller.php           <- REPLACES existing (bug fixes only, see below)
server/payment_esewa.php        <- NEW
server/payment_khalti.php       <- NEW
server/place_order_cod.php      <- NEW
server/checkout_redirect.php    <- NEW
pages/checkout.php              <- REPLACES existing stub
pages/orders.php                <- NEW ("My Orders")
pages/pay_order.php             <- NEW (repay flow)
admin/dashboard.php             <- REPLACES existing (adds role guard + Orders link)
admin/orders.php                <- NEW (the payment dashboard)
admin/orders_table_rows.php     <- NEW
admin/order_update_status.php   <- NEW
admin/search_orders.php         <- NEW
styles/orders.css               <- NEW
styles/admin_orders.css         <- NEW
```

## 2. Run the SQL migration

```bash
mysql -u <user> -p <your_db> < sql/schema_updates.sql
```

This adds:
- `Users.Role` (`user`/`admin`) and `Users.Status` — needed to gate `/admin`
- `product.Category`, `Discount`, `Rating` — `controller.php` already
  queried these columns; they didn't exist yet, so shop filtering/sorting
  was silently broken. Now fixed.
- `product.Sizes` — optional CSV field (e.g. `"7,8,9,10,11"`) if you want
  to show size options later.

Promote your own account to admin once you've signed up normally:
```sql
UPDATE Users SET Role = 'admin' WHERE Email = 'you@example.com';
```

## 3. What changed and why

**`server/controller.php`** — only bug fixes, nothing structural removed:
- `login()` / `register()` / `manage_users()` / `get_user_info()` were
  querying columns that don't exist in your actual schema (`FirstName`
  instead of `First_Name`, etc.) — fixed to match your real `Users` table.
- Passwords are now hashed with `password_hash()` on signup. `login()`
  still accepts old plaintext rows too, so existing test accounts keep
  working, but every new signup is hashed going forward.
- `login()` now also stores `$_SESSION['role']`, which the new admin
  pages rely on for `require_role('admin')`.
- The old `check_out()` / `place_order()` AJAX actions are left in place
  but unused — the new checkout page uses proper server-side handlers
  instead (needed because redirecting a browser to an external payment
  gateway doesn't work cleanly from an AJAX success callback).

**`pages/checkout.php`** — was a non-functional stub (a `card`/`cash`
dropdown that always saved `"cash"` regardless of choice). Rebuilt with:
- A real delivery form (receiver name, phone, city, address, postal code)
  saved per-order, matching your `orders` table.
- Three payment cards — COD, eSewa, Khalti — styled with Kicks' orange
  gradient, replacing the raw `<select>`.
- COD posts straight to `server/place_order_cod.php`. eSewa/Khalti post to
  `server/checkout_redirect.php`, which stashes the cart + delivery info in
  the session and hands off to the gateway.

**`server/payment_esewa.php` / `payment_khalti.php`** — same two-step
pattern as Electronics (build/redirect to the gateway, then verify the
callback and commit the order), ported to your table/column names:
cart comes from `cart_item` + `product`, and the order lands in your
`orders`/`order_items` tables with `Payment_Method`, `Payment_Status`,
`Ref_Id`, `Transaction_Uuid` filled in. Both use a DB transaction with
`SELECT … FOR UPDATE` so two people can't oversell the last pair of shoes.

**`pages/orders.php`** — "My Orders": paid/unpaid badge, a
processing → packing → shipping → delivered tracker, and a
"Complete payment →" button for unpaid orders that goes to
`pages/pay_order.php` (repay flow, reuses the same two gateway files).

**`admin/orders.php`** — the payment dashboard: stat cards (total
orders, revenue, unpaid count, processing, delivered), filter tabs,
live search, and an inline status dropdown per order that AJAX-PATCHes
`admin/order_update_status.php`. Marking a COD order "delivered"
automatically flips it to `paid`, since that's when cash actually
changes hands. Gated by `require_role('admin')` — non-admins are
redirected home.

## 4. Sandbox payment credentials (already wired in)

- **eSewa**: `EPAYTEST` / secret key `8gBm/:&EnhH.1/q` (official sandbox test
  credentials) against `rc-epay.esewa.com.np`.
- **Khalti**: a sandbox secret key against `dev.khalti.com`. Get your own
  test key at https://test-admin.khalti.com if this one stops working.

Before going live, swap in your real merchant credentials and update the
`success_url` / `failure_url` / `return_url` / `website_url` variables at
the top of `payment_esewa.php` and `payment_khalti.php` to your real
deployed domain (they currently point at `http://localhost/Kicks/...`).

## 5. One tiny manual step

Add a "My Orders" link to your main site header so logged-in users can
find it (I didn't touch every page's `<nav>` to keep this patch minimal —
just add this one `<li>` wherever `cart.php` is linked, in `index.php`,
`pages/shop.php`, `pages/cart.php`, `pages/contacts.php`, `pages/login.php`,
`pages/signup.php`):

```html
<li><a href="./orders.php">My Orders</a></li>
```
(On `index.php` use `pages/orders.php` instead of `./orders.php`.)

## 6. Testing checklist

1. Run the SQL migration, promote your account to admin.
2. Add a product to cart → Checkout → fill delivery form → pick **COD** →
   Place order → check `pages/orders.php` shows it unpaid, processing.
3. Repeat picking **eSewa** — you'll land on eSewa's sandbox login
   (test MSISDN `9806800001`..`9806800005`, password `Nepal@123`, MPIN
   `1122`, OTP `123456`) — after paying you should land back on
   `orders.php` with the order marked **paid**.
4. Visit `/admin/orders.php` as your admin account — confirm both orders
   show up, try the status dropdown, try the filter tabs and search box.
5. Try visiting `/admin/orders.php` while logged in as a normal (non-admin)
   user — you should get redirected to `index.php`.
