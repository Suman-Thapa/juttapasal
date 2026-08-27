# Round 2: Dynamic homepage gallery + login/role redirects

## Files in this zip (copy into your Kicks project, same paths)

```
index.php                  <- REPLACES existing (dynamic 10-product gallery)
styles/home_gallery.css    <- NEW (caption overlay styling)
scripts/app.js             <- REPLACES existing (role redirect + login-required checkout)
pages/login.php            <- REPLACES existing (adds #login-error element)
sql/seed_10_products.sql   <- NEW (inserts your 10 already-uploaded images as products)
```

Requires the schema_updates.sql from the previous round already applied
(needs `product.Category/Discount/Rating` and `Users.Role`).

## What changed

**Homepage gallery is now dynamic.** `index.php` runs one query —
`SELECT ... FROM product ORDER BY Product_id DESC LIMIT 10` — and splits
the results into two 5-tile rows using your exact existing `.shoe-gallery`
grid CSS. There's no separate "featured" flag: whatever the admin most
recently added in `admin/dashboard.php` → Add Products is what shows here,
newest first. Each tile now links to the shop and shows a brand + price
caption on hover (`styles/home_gallery.css`).

Run `sql/seed_10_products.sql` once to turn your 10 existing images
(`n1–n5.avif`, `shoe-gallery-pic1–5.jpg`) into real product rows so the
homepage isn't empty on first load.

**Login now redirects by role.** `app.js`'s `login()` checks the `role`
the server now returns (from the earlier `controller.php` patch) —
admins go to `admin/dashboard.php`, everyone else goes home (or back to
checkout, see below). Added a `#login-error` message spot on the login
form for wrong-password feedback instead of failing silently.

**Checkout now forces login first, with a proper return trip.** The
cart's Checkout button now calls `goToCheckout()`, which checks login
status first:
- Logged in → goes straight to `checkout.php` (which also independently
  enforces `require_login()` server-side — belt and suspenders).
- Not logged in → remembers "send me to checkout after this" and goes to
  `login.php`. After a successful login, they land back on checkout
  instead of the homepage.

## Test it

1. Run `sql/seed_10_products.sql`.
2. Open `index.php` — you should see 10 real product tiles with hover
   captions, pulled from the DB.
3. Log out, add something to cart, hit Checkout → should bounce you to
   login → after logging in, should land back on checkout automatically.
4. Log in as your admin account → should land on `admin/dashboard.php`
   directly instead of the homepage.
