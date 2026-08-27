<?php

require_once 'connection.php';

$active = 'dashboard';


// Dashboard statistics
$totalProducts = (int) (
    mysqli_fetch_assoc(
        mysqli_query(
            $con,
            "SELECT COUNT(*) c FROM product"
        )
    )['c'] ?? 0
);


$totalUsers = (int) (
    mysqli_fetch_assoc(
        mysqli_query(
            $con,
            "SELECT COUNT(*) c
             FROM Users
             WHERE Role = 'user'"
        )
    )['c'] ?? 0
);


$totalOrders = (int) (
    mysqli_fetch_assoc(
        mysqli_query(
            $con,
            "SELECT COUNT(*) c FROM orders"
        )
    )['c'] ?? 0
);


// Revenue
$revenueRow = mysqli_fetch_assoc(
    mysqli_query(
        $con,
        "SELECT COALESCE(SUM(Total_Amount), 0) total
         FROM orders
         WHERE Payment_Status = 'paid'
         AND Status <> 'cancelled'"
    )
);

$revenue = (float) ($revenueRow['total'] ?? 0);


// Low stock products
$lowStock = (int) (
    mysqli_fetch_assoc(
        mysqli_query(
            $con,
            "SELECT COUNT(*) c
             FROM product
             WHERE Quantity <= 5"
        )
    )['c'] ?? 0
);


// Recent orders
$recentOrders = [];

$r = mysqli_query(
    $con,
    "SELECT o.*, u.First_Name, u.Last_Name
     FROM orders o
     JOIN Users u ON u.ID = o.User_id
     ORDER BY o.Created_At DESC
     LIMIT 7"
);

if ($r) {

    while ($row = mysqli_fetch_assoc($r)) {
        $recentOrders[] = $row;
    }
}


// Top products
$topProducts = [];

$r = mysqli_query(
    $con,
    "SELECT
        Product_id,
        Brand,
        Description,
        Price,
        Quantity,
        Discount,
        Image_url
     FROM product
     ORDER BY Product_id DESC
     LIMIT 5"
);

if ($r) {

    while ($row = mysqli_fetch_assoc($r)) {
        $topProducts[] = $row;
    }
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

    <title>
        JuttaPasal — Admin Dashboard
    </title>

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

                    JUTTAPASAL MANAGEMENT

                </div>

                <h1>
                    Dashboard
                </h1>

                <p>
                    Manage your shoes, prices, stock and customer orders.
                </p>

            </div>


            <div class="admin-actions">

                <a
                    class="btn-jp"
                    href="product_form.php"
                >
                    <?= icon('plus') ?>

                    Add shoe
                </a>

            </div>

        </div>


        <!-- Statistics -->
        <div class="jp-stat-grid">

            <!-- Total Products -->
            <div class="jp-stat">

                <div class="stat-icon">
                    <?= icon('box') ?>
                </div>

                <small>
                    Total shoes
                </small>

                <strong>
                    <?= number_format($totalProducts) ?>
                </strong>

                <em>
                    Products in catalog
                </em>

            </div>


            <!-- Total Orders -->
            <div class="jp-stat">

                <div class="stat-icon">
                    <?= icon('orders') ?>
                </div>

                <small>
                    Total orders
                </small>

                <strong>
                    <?= number_format($totalOrders) ?>
                </strong>

                <em>
                    All customer orders
                </em>

            </div>


            <!-- Customers -->
            <div class="jp-stat">

                <div class="stat-icon">
                    <?= icon('users') ?>
                </div>

                <small>
                    Customers
                </small>

                <strong>
                    <?= number_format($totalUsers) ?>
                </strong>

                <em>
                    Registered users
                </em>

            </div>


            <!-- Revenue -->
            <div class="jp-stat">

                <div class="stat-icon">
                    <?= icon('shoe') ?>
                </div>

                <small>
                    Paid revenue
                </small>

                <strong>
                    Rs. <?= number_format($revenue, 2) ?>
                </strong>

                <em>
                    <?= $lowStock ?> low-stock shoes
                </em>

            </div>

        </div>


        <!-- Dashboard Grid -->
        <div class="jp-grid">


            <!-- Recent Orders -->
            <section
                class="jp-panel"
                id="recent-orders"
            >

                <div class="jp-panel-head">

                    <h2>
                        Recent orders
                    </h2>

                    <a
                        class="jp-link"
                        href="dashboard.php#recent-orders"
                    >
                        View all orders →
                    </a>

                </div>


                <div style="overflow-x: auto">

                    <table class="jp-table">

                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Payment
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!$recentOrders): ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="jp-empty"
                                    >
                                        No orders found.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($recentOrders as $o): ?>

                                    <tr>

                                        <!-- Order -->
                                        <td>

                                            <b>
                                                #<?= h($o['Order_id']) ?>
                                            </b>

                                            <br>

                                            <small>
                                                <?= h(
                                                    date(
                                                        'M d, Y',
                                                        strtotime($o['Created_At'])
                                                    )
                                                ) ?>
                                            </small>

                                        </td>


                                        <!-- Customer -->
                                        <td>
                                            <?= h($o['Receiver_Name']) ?>
                                        </td>


                                        <!-- Total -->
                                        <td>

                                            <b>
                                                Rs.
                                                <?= number_format(
                                                    $o['Total_Amount'],
                                                    2
                                                ) ?>
                                            </b>

                                        </td>


                                        <!-- Payment -->
                                        <td
                                            class="<?= $o['Payment_Status'] === 'paid'
                                                ? 'payment-paid'
                                                : 'payment-unpaid' ?>"
                                        >

                                            <?= h(
                                                strtoupper(
                                                    $o['Payment_Method']
                                                )
                                            ) ?>

                                            <br>

                                            <small>
                                                <?= h(
                                                    $o['Payment_Status']
                                                ) ?>
                                            </small>

                                        </td>


                                        <!-- Status -->
                                        <td>

                                            <span
                                                class="status-pill status-<?= h(
                                                    $o['Status']
                                                ) ?>"
                                            >
                                                <?= h($o['Status']) ?>
                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- Catalog Snapshot -->
            <section class="jp-panel">

                <div class="jp-panel-head">

                    <h2>
                        Catalog snapshot
                    </h2>

                    <a
                        class="jp-link"
                        href="products.php"
                    >
                        Manage →
                    </a>

                </div>


                <div class="mini-list">

                    <?php foreach ($topProducts as $p): ?>

                        <div class="mini-item">


                            <!-- Product Information -->
                            <div class="mini-item-left">

                                <div class="shoe-thumb">

                                    <?php if ($p['Image_url']): ?>

                                        <img
                                            src="../<?= h($p['Image_url']) ?>"
                                            alt=""
                                        >

                                    <?php else: ?>

                                        <?= icon('shoe') ?>

                                    <?php endif; ?>

                                </div>


                                <div>

                                    <b>
                                        <?= h($p['Description']) ?>
                                    </b>

                                    <div
                                        style="
                                            font-size: 11px;
                                            color: #8b95a7;
                                        "
                                    >
                                        <?= h($p['Brand']) ?>

                                        ·

                                        Rs.
                                        <?= number_format($p['Price']) ?>

                                    </div>

                                </div>

                            </div>


                            <!-- Stock -->
                            <span
                                class="<?= $p['Quantity'] <= 5
                                    ? 'stock-low'
                                    : 'stock-good' ?>"
                                style="
                                    font-size: 11px;
                                    font-weight: 700;
                                "
                            >
                                <?= h($p['Quantity']) ?> left
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        </div>

    </main>

</body>

</html>