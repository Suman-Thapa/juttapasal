<?php

require_once 'connection.php';

$active = 'products';

$id     = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$errors = [];


// -------------------------------------------------
// DEFAULT PRODUCT VALUES
// -------------------------------------------------

$p = [
    'Brand'       => '',
    'Category'    => 'Men',
    'Description' => '',
    'Sizes'       => '',
    'Price'       => '',
    'Quantity'    => '',
    'Discount'    => 0,
    'Rating'      => 0,
    'Image_url'   => ''
];


// -------------------------------------------------
// LOAD PRODUCT FOR EDITING
// -------------------------------------------------

if ($isEdit) {

    $st = mysqli_prepare(
        $con,
        "SELECT * FROM product WHERE Product_id = ?"
    );

    mysqli_stmt_bind_param(
        $st,
        'i',
        $id
    );

    mysqli_stmt_execute($st);

    $r = mysqli_stmt_get_result($st);

    $found = mysqli_fetch_assoc($r);

    if (!$found) {
        die('Product not found.');
    }

    $p = $found;
}


// -------------------------------------------------
// HANDLE FORM SUBMISSION
// -------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // Get normal form fields
    foreach ($p as $k => $v) {

        if (isset($_POST[$k])) {

            $p[$k] = trim($_POST[$k]);
        }
    }


    // Convert numeric values
    $p['Price']    = (int) $p['Price'];
    $p['Quantity'] = (int) $p['Quantity'];
    $p['Discount'] = (int) $p['Discount'];
    $p['Rating']   = (float) $p['Rating'];


    // -------------------------------------------------
    // VALIDATION
    // -------------------------------------------------

    if ($p['Brand'] === '') {

        $errors[] = 'Brand is required.';
    }


    if ($p['Description'] === '') {

        $errors[] = 'Shoe name/description is required.';
    }


    if ($p['Price'] < 0) {

        $errors[] = 'Price cannot be negative.';
    }


    if ($p['Quantity'] < 0) {

        $errors[] = 'Quantity cannot be negative.';
    }


    if (
        $p['Discount'] < 0 ||
        $p['Discount'] > 100
    ) {

        $errors[] =
            'Discount must be between 0 and 100.';
    }


    if (
        $p['Rating'] < 0 ||
        $p['Rating'] > 5
    ) {

        $errors[] =
            'Rating must be between 0 and 5.';
    }


    // -------------------------------------------------
    // IMAGE UPLOAD
    // -------------------------------------------------

    if (
        isset($_FILES['Image_file']) &&
        $_FILES['Image_file']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $image = $_FILES['Image_file'];


        // Check upload error
        if ($image['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                'Image upload failed. Error code: ' .
                $image['error'];

        } else {


            // Get original filename
            $fileName = basename($image['name']);


            // Get extension
            $extension = strtolower(
                pathinfo(
                    $fileName,
                    PATHINFO_EXTENSION
                )
            );


            // Allowed image extensions
            $allowedExtensions = [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'webp',
                'svg',
                'bmp',
                'avif'
            ];


            // Validate extension
            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                $errors[] =
                    'Please select a valid image file.';

            } else {


                // -------------------------------------------------
                // IMAGES DIRECTORY
                // -------------------------------------------------

                // product_form.php is inside /admin
                //
                // ../images/ means:
                //
                // JuttaPasal/
                //     images/
                //
                // JuttaPasal/
                //     admin/
                //         product_form.php

                $uploadDirectory =
                    __DIR__ . '/../images/';


                // Check images directory
                if (!is_dir($uploadDirectory)) {

                    $errors[] =
                        'Images folder does not exist: ' .
                        $uploadDirectory;

                } else {


                    // Full physical file path
                    $targetPath =
                        $uploadDirectory . $fileName;


                    // -------------------------------------------------
                    // DELETE OLD IMAGE WHEN EDITING
                    // -------------------------------------------------

                    if (
                        $isEdit &&
                        !empty($p['Image_url'])
                    ) {

                        $oldImagePath =
                            __DIR__ .
                            '/../' .
                            $p['Image_url'];


                        if (
                            file_exists($oldImagePath) &&
                            is_file($oldImagePath)
                        ) {

                            unlink($oldImagePath);
                        }
                    }


                    // -------------------------------------------------
                    // MOVE NEW IMAGE
                    // -------------------------------------------------

                    if (
                        move_uploaded_file(
                            $image['tmp_name'],
                            $targetPath
                        )
                    ) {

                        // Store this in database:
                        //
                        // images/product1.png

                        $p['Image_url'] =
                            'images/' . $fileName;

                    } else {

                        $errors[] =
                            'Could not move the uploaded image.';
                    }
                }
            }
        }
    }


    // -------------------------------------------------
    // SAVE PRODUCT
    // -------------------------------------------------

    if (!$errors) {


        // -------------------------------------------------
        // EDIT PRODUCT
        // -------------------------------------------------

        if ($isEdit) {

            $st = mysqli_prepare(
                $con,
                "UPDATE product
                 SET
                    Brand = ?,
                    Category = ?,
                    Description = ?,
                    Sizes = ?,
                    Price = ?,
                    Quantity = ?,
                    Discount = ?,
                    Rating = ?,
                    Image_url = ?
                 WHERE Product_id = ?"
            );


            mysqli_stmt_bind_param(
                $st,
                'ssssiiidsi',
                $p['Brand'],
                $p['Category'],
                $p['Description'],
                $p['Sizes'],
                $p['Price'],
                $p['Quantity'],
                $p['Discount'],
                $p['Rating'],
                $p['Image_url'],
                $id
            );


        // -------------------------------------------------
        // ADD NEW PRODUCT
        // -------------------------------------------------

        } else {

            $st = mysqli_prepare(
                $con,
                "INSERT INTO product
                (
                    Brand,
                    Category,
                    Description,
                    Sizes,
                    Price,
                    Quantity,
                    Discount,
                    Rating,
                    Image_url
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )"
            );


            mysqli_stmt_bind_param(
                $st,
                'ssssiiids',
                $p['Brand'],
                $p['Category'],
                $p['Description'],
                $p['Sizes'],
                $p['Price'],
                $p['Quantity'],
                $p['Discount'],
                $p['Rating'],
                $p['Image_url']
            );
        }


        // -------------------------------------------------
        // EXECUTE QUERY
        // -------------------------------------------------

        if (mysqli_stmt_execute($st)) {

            redirect('products.php');
        }


        $errors[] =
            'Could not save product: ' .
            mysqli_error($con);
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
        <?= $isEdit ? 'Edit' : 'Add' ?> Shoe — JuttaPasal
    </title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body>


    <?php include 'admin_sidebar.php'; ?>


    <main class="admin-main">


        <!-- =====================================================
             TOP BAR
        ====================================================== -->

        <div class="admin-topbar">

            <div>

                <div class="eyebrow">

                    <span class="dot"></span>

                    CATALOG

                </div>


                <h1>

                    <?= $isEdit
                        ? 'Edit shoe'
                        : 'Add new shoe'
                    ?>

                </h1>


                <p>
                    Keep your JuttaPasal product information
                    up to date.
                </p>

            </div>

        </div>


        <!-- =====================================================
             PRODUCT PANEL
        ====================================================== -->

        <div class="jp-panel">


            <!-- Panel Header -->
            <div class="jp-panel-head">

                <h2>
                    Shoe details
                </h2>


                <!-- Existing Image Preview -->
                <?php if (
                    $isEdit &&
                    !empty($p['Image_url'])
                ): ?>

                    <img
                        class="product-image-preview"
                        src="../<?= h($p['Image_url']) ?>"
                        alt="Preview"
                    >

                <?php endif; ?>

            </div>


            <!-- =================================================
                 ERROR MESSAGES
            ================================================== -->

            <?php if ($errors): ?>

                <div class="jp-alert error">

                    <?php foreach ($errors as $e): ?>

                        <div>
                            <?= h($e) ?>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 PRODUCT FORM
            ================================================== -->

            <form
                method="post"
                enctype="multipart/form-data"
            >


                <div class="jp-form-grid">


                    <!-- =========================================
                         BRAND
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Brand
                        </label>

                        <input
                            type="text"
                            name="Brand"
                            value="<?= h($p['Brand']) ?>"
                            required
                        >

                    </div>


                    <!-- =========================================
                         CATEGORY
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Category
                        </label>


                        <select name="Category">

                            <option
                                value="Men"
                                <?= $p['Category'] === 'Men'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Men
                            </option>


                            <option
                                value="Women"
                                <?= $p['Category'] === 'Women'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Women
                            </option>


                            <option
                                value="Kids"
                                <?= $p['Category'] === 'Kids'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Kids
                            </option>


                            <option
                                value="Unisex"
                                <?= $p['Category'] === 'Unisex'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Unisex
                            </option>

                        </select>

                    </div>


                    <!-- =========================================
                         DESCRIPTION
                    ========================================== -->

                    <div class="jp-form-group full">

                        <label>
                            Shoe name / description
                        </label>


                        <input
                            type="text"
                            name="Description"
                            value="<?= h($p['Description']) ?>"
                            required
                        >

                    </div>


                    <!-- =========================================
                         SIZES
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Sizes
                        </label>


                        <input
                            type="text"
                            name="Sizes"
                            placeholder="e.g. 39,40,41,42"
                            value="<?= h($p['Sizes']) ?>"
                        >

                    </div>


                    <!-- =========================================
                         IMAGE UPLOAD
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Product Image
                        </label>


                        <input
                            type="file"
                            name="Image_file"
                            accept="image/*"
                            <?= !$isEdit
                                ? 'required'
                                : '' ?>
                        >


                        <small>
                            Select an image from your computer.
                        </small>

                    </div>


                    <!-- =========================================
                         PRICE
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Price (Rs.)
                        </label>


                        <input
                            type="number"
                            min="0"
                            name="Price"
                            value="<?= h($p['Price']) ?>"
                            required
                        >

                    </div>


                    <!-- =========================================
                         QUANTITY
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Quantity
                        </label>


                        <input
                            type="number"
                            min="0"
                            name="Quantity"
                            value="<?= h($p['Quantity']) ?>"
                            required
                        >

                    </div>


                    <!-- =========================================
                         DISCOUNT
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Discount (%)
                        </label>


                        <input
                            type="number"
                            min="0"
                            max="100"
                            name="Discount"
                            value="<?= h($p['Discount']) ?>"
                        >

                    </div>


                    <!-- =========================================
                         RATING
                    ========================================== -->

                    <div class="jp-form-group">

                        <label>
                            Rating
                        </label>


                        <input
                            type="number"
                            min="0"
                            max="5"
                            step="0.1"
                            name="Rating"
                            value="<?= h($p['Rating']) ?>"
                        >

                    </div>

                </div>


                <!-- =================================================
                     FORM ACTIONS
                ================================================== -->

                <div class="jp-form-actions">


                    <a
                        class="btn-jp secondary"
                        href="products.php"
                    >
                        Cancel
                    </a>


                    <button
                        class="btn-jp"
                        type="submit"
                    >
                        <?= $isEdit
                            ? 'Update shoe'
                            : 'Save shoe'
                        ?>
                    </button>

                </div>


            </form>

        </div>

    </main>

</body>

</html>