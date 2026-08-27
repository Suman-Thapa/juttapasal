<?php

require_once 'connection.php';

$active   = 'products';
$q        = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

// Build product query
$sql    = "SELECT * FROM product WHERE 1";
$types  = '';
$params = [];

if ($q !== '') {
    $sql .= " AND (Brand LIKE ? OR Description LIKE ?)";

    $types .= 'ss';

    $like     = "%$q%";
    $params[] = $like;
    $params[] = $like;
}

if ($category !== '') {
    $sql .= " AND Category = ?";

    $types   .= 's';
    $params[] = $category;
}

$sql .= " ORDER BY Product_id DESC";

// Prepare statement
$stmt = mysqli_prepare($con, $sql);

if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$products = mysqli_stmt_get_result($stmt);


// Get categories
$cats = [];

$cr = mysqli_query(
    $con,
    "SELECT DISTINCT Category 
     FROM product 
     ORDER BY Category"
);

while ($cr && $c = mysqli_fetch_assoc($cr)) {
    $cats[] = $c['Category'];
}

?>

<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Products — JuttaPasal</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>

<body>

    <?php include 'admin_sidebar.php'; ?>


    <main class="admin-main">

        <!-- Top Bar -->
        <div class="admin-topbar">

            <div>

                <div class="eyebrow">

                    <span class="dot"></span>

                    CATALOG

                </div>

                <h1>Products</h1>

                <p>
                    Manage shoe prices, stock, discounts and product details.
                </p>

            </div>


            <a
                class="btn-jp"
                href="product_form.php"
            >
                <?= icon('plus') ?>

                Add shoe
            </a>

        </div>


        <!-- Product Panel -->
        <div class="jp-panel">

            <!-- Search / Filter -->
            <form
                class="jp-toolbar"
                method="get"
            >

                <input
                    class="jp-input"
                    type="text"
                    name="q"
                    placeholder="Search brand or shoe..."
                    value="<?= h($q) ?>"
                >


                <select
                    class="jp-select"
                    name="category"
                >

                    <option value="">
                        All categories
                    </option>

                    <?php foreach ($cats as $c): ?>

                        <option
                            value="<?= h($c) ?>"
                            <?= $category === $c ? 'selected' : '' ?>
                        >
                            <?= h($c) ?>
                        </option>

                    <?php endforeach; ?>

                </select>


                <button
                    class="btn-jp secondary"
                    type="submit"
                >
                    <?= icon('search') ?>

                    Search
                </button>

            </form>


            <!-- Products Table -->
            <div style="overflow-x: auto">

                <table class="jp-table">

                    <thead>

                        <tr>

                            <th>Shoe</th>

                            <th>Category</th>

                            <th>Price</th>

                            <th>Discount</th>

                            <th>Stock</th>

                            <th>Rating</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (mysqli_num_rows($products) === 0): ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="jp-empty"
                                >
                                    No shoes found.
                                </td>

                            </tr>

                        <?php else: ?>

                            <?php while ($p = mysqli_fetch_assoc($products)): ?>

                                <tr>

                                    <!-- Shoe -->
                                    <td>

                                        <div class="shoe-cell">

                                            <div class="shoe-thumb">

                                                <?php if ($p['Image_url']): ?>

                                                    <img
                                                        src="../<?= h($p['Image_url']) ?>"
                                                        alt="<?= h($p['Description']) ?>"
                                                    >

                                                <?php else: ?>

                                                    <?= icon('shoe') ?>

                                                <?php endif; ?>

                                            </div>


                                            <div>

                                                <b>
                                                    <?= h($p['Description']) ?>
                                                </b>

                                                <span>
                                                    #<?= h($p['Product_id']) ?>
                                                    ·
                                                    <?= h($p['Brand']) ?>
                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Category -->
                                    <td>
                                        <?= h($p['Category']) ?>
                                    </td>


                                    <!-- Price -->
                                    <td>

                                        <b>
                                            Rs. <?= number_format($p['Price']) ?>
                                        </b>

                                    </td>


                                    <!-- Discount -->
                                    <td>
                                        <?= $p['Discount'] ?>%
                                    </td>


                                    <!-- Stock -->
                                    <td>

                                        <span
                                            class="<?= $p['Quantity'] <= 5
                                                ? 'stock-low'
                                                : 'stock-good' ?>"
                                            style="font-weight: 700"
                                        >
                                            <?= h($p['Quantity']) ?>
                                        </span>

                                    </td>


                                    <!-- Rating -->
                                    <td>
                                        <?= h($p['Rating']) ?>
                                    </td>


                                    <!-- Actions -->
                                    <td>

                                        <div class="product-actions">

                                            <!-- Edit -->
                                            <a
                                                class="btn-jp secondary"
                                                href="product_form.php?id=<?= h($p['Product_id']) ?>"
                                            >
                                                <?= icon('edit') ?>
                                            </a>


                                            <!-- Delete -->
                                            <form
                                                method="post"
                                                action="product_delete.php"
                                                onsubmit="return confirm('Delete this shoe?')"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= h($p['Product_id']) ?>"
                                                >

                                                <button
                                                    class="btn-jp danger"
                                                    type="submit"
                                                >
                                                    <?= icon('trash') ?>
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</body>

</html>