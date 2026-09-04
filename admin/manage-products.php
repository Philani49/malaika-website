<?php
require_once '../includes/config.php';
requireRole('Admin');

$pageTitle = 'Manage Products';

// Handle Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM products WHERE product_id = ?")
        ->execute([$_GET['delete']]);

    setFlash('success', 'Product deleted successfully.');
    header("Location: manage-products.php");
    exit();
}

// Handle Add/Edit
$error = '';
$edit_product = null;

// Get product for editing
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = $_POST['product_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    $size = trim($_POST['size'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_status = $_POST['stock_status'] ?? 'Available';
    $description = trim($_POST['description'] ?? '');

    // Keep existing image when editing
    $image_url = $edit_product['image_url'] ?? '';

    // Validate required fields
    if (empty($name) || empty($category) || $price <= 0) {

        $error = 'Please fill in all required fields (Name, Category, Price).';

    } else {

        /*
        ==========================================
        IMAGE UPLOAD
        ==========================================
        */

        if (
            isset($_FILES['product_image']) &&
            $_FILES['product_image']['error'] === UPLOAD_ERR_OK
        ) {

            $uploadDir = '../images/products/';

            // Create folder if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Get file extension
            $fileExtension = strtolower(
                pathinfo(
                    $_FILES['product_image']['name'],
                    PATHINFO_EXTENSION
                )
            );

            // Allowed image types
            $allowedTypes = [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'webp'
            ];

            // Check file type
            if (!in_array($fileExtension, $allowedTypes)) {

                $error = 'Please upload a valid image (JPG, JPEG, PNG, GIF or WEBP).';

            } else {

                // Check file size (5MB maximum)
                if ($_FILES['product_image']['size'] > 5 * 1024 * 1024) {

                    $error = 'Image is too large. Maximum size is 5MB.';

                } else {

                    // Create unique filename
                    $fileName = uniqid('product_', true) . '.' . $fileExtension;

                    $uploadPath = $uploadDir . $fileName;

                    // Move uploaded image
                    if (move_uploaded_file(
                        $_FILES['product_image']['tmp_name'],
                        $uploadPath
                    )) {

                        // Path stored in database
                        $image_url = 'images/products/' . $fileName;

                    } else {

                        $error = 'There was a problem uploading the image.';
                    }
                }
            }
        }

        /*
        ==========================================
        SAVE PRODUCT
        ==========================================
        */

        if (empty($error)) {

            if ($product_id) {

                // UPDATE PRODUCT
                $stmt = $pdo->prepare("
                    UPDATE products
                    SET
                        name = ?,
                        category = ?,
                        size = ?,
                        price = ?,
                        stock_status = ?,
                        description = ?,
                        image_url = ?
                    WHERE product_id = ?
                ");

                $stmt->execute([
                    $name,
                    $category,
                    $size,
                    $price,
                    $stock_status,
                    $description,
                    $image_url,
                    $product_id
                ]);

                setFlash(
                    'success',
                    'Product updated successfully.'
                );

            } else {

                // INSERT NEW PRODUCT
                $stmt = $pdo->prepare("
                    INSERT INTO products
                    (
                        name,
                        category,
                        size,
                        price,
                        stock_status,
                        description,
                        image_url
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $name,
                    $category,
                    $size,
                    $price,
                    $stock_status,
                    $description,
                    $image_url
                ]);

                setFlash(
                    'success',
                    'Product added successfully.'
                );
            }

            header("Location: manage-products.php");
            exit();
        }
    }
}

// Fetch all products
$products = $pdo->query("
    SELECT *
    FROM products
    ORDER BY category, name
")->fetchAll();

require_once '../includes/header.php';
?>

<style>
.admin-header {
    background: #1a1a2e;
    color: white;
    padding: 15px 0;
}

.product-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.no-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    background: #f1f1f1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 12px;
    text-align: center;
}
</style>

<!-- Admin Header -->
<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="bi bi-shield-lock"></i>
            Admin Panel
        </h5>

        <a href="dashboard.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

    </div>
</div>


<div class="container py-4">

    <div class="row g-4">

        <!-- ==========================================
             PRODUCT FORM
        =========================================== -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white fw-bold">

                    <?php
                    echo $edit_product
                        ? '<i class="bi bi-pencil"></i> Edit Product'
                        : '<i class="bi bi-plus-circle"></i> Add New Product';
                    ?>

                </div>

                <div class="card-body">

                    <!-- Error message -->
                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>

                    <?php endif; ?>


                    <!-- Product Form -->
                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <!-- Product ID when editing -->
                        <?php if ($edit_product): ?>

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php echo $edit_product['product_id']; ?>"
                            >

                        <?php endif; ?>


                        <!-- Product Name -->
                        <div class="mb-3">

                            <label class="form-label">
                                Product Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                required
                                value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>"
                                placeholder="e.g. Summer Floral Dress"
                            >

                        </div>


                        <!-- Category -->
                        <div class="mb-3">

                            <label class="form-label">
                                Category *
                            </label>

                            <select
                                name="category"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select...
                                </option>

                                <option
                                    value="Women"
                                    <?php echo ($edit_product['category'] ?? '') === 'Women'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Women
                                </option>

                                <option
                                    value="Men"
                                    <?php echo ($edit_product['category'] ?? '') === 'Men'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Men
                                </option>

                                <option
                                    value="Kids"
                                    <?php echo ($edit_product['category'] ?? '') === 'Kids'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Kids
                                </option>

                            </select>

                        </div>


                        <!-- Sizes -->
                        <div class="mb-3">

                            <label class="form-label">
                                Sizes
                            </label>

                            <input
                                type="text"
                                name="size"
                                class="form-control"
                                value="<?php echo htmlspecialchars($edit_product['size'] ?? ''); ?>"
                                placeholder="e.g. S, M, L or 28, 30, 32"
                            >

                        </div>


                        <!-- Price -->
                        <div class="mb-3">

                            <label class="form-label">
                                Price (R) *
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                required
                                step="0.01"
                                min="0"
                                value="<?php echo $edit_product['price'] ?? ''; ?>"
                                placeholder="e.g. 350.00"
                            >

                        </div>


                        <!-- Stock Status -->
                        <div class="mb-3">

                            <label class="form-label">
                                Stock Status
                            </label>

                            <select
                                name="stock_status"
                                class="form-select"
                            >

                                <option
                                    value="Available"
                                    <?php echo ($edit_product['stock_status'] ?? 'Available') === 'Available'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Available
                                </option>

                                <option
                                    value="Sold Out"
                                    <?php echo ($edit_product['stock_status'] ?? '') === 'Sold Out'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Sold Out
                                </option>

                            </select>

                        </div>


                        <!-- ==========================================
                             PRODUCT IMAGE
                        =========================================== -->

                        <div class="mb-3">

                            <label class="form-label">
                                Product Image
                            </label>

                            <input
                                type="file"
                                name="product_image"
                                class="form-control"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                            >

                            <div class="form-text">
                                JPG, PNG, GIF or WEBP. Maximum 5MB.
                            </div>

                        </div>


                        <!-- Current Image -->
                        <?php if (!empty($edit_product['image_url'])): ?>

                            <div class="mb-3">

                                <label class="form-label">
                                    Current Image
                                </label>

                                <div>
                                    <img
                                        src="<?php echo htmlspecialchars($edit_product['image_url']); ?>"
                                        alt="Current Product Image"
                                        class="product-thumbnail"
                                    >
                                </div>

                            </div>

                        <?php endif; ?>


                        <!-- Description -->
                        <div class="mb-3">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="3"
                                placeholder="Short product description"
                            ><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>

                        </div>


                        <!-- Submit -->
                        <div class="d-grid">

                            <button
                                type="submit"
                                class="btn btn-malaika"
                            >

                                <?php

                                echo $edit_product
                                    ? '<i class="bi bi-check"></i> Update Product'
                                    : '<i class="bi bi-plus"></i> Add Product';

                                ?>

                            </button>

                        </div>


                        <!-- Cancel Edit -->
                        <?php if ($edit_product): ?>

                            <a
                                href="manage-products.php"
                                class="btn btn-outline-secondary w-100 mt-2"
                            >
                                Cancel Edit
                            </a>

                        <?php endif; ?>

                    </form>

                </div>

            </div>

        </div>


        <!-- ==========================================
             PRODUCT LIST
        =========================================== -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <span class="fw-bold">

                        <i class="bi bi-box-seam"></i>

                        All Products
                        (<?php echo count($products); ?>)

                    </span>

                    <div class="btn-group btn-group-sm">

                        <a
                            href="?"
                            class="btn btn-outline-dark active"
                        >
                            All
                        </a>

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover mb-0 align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th style="width:120px">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (count($products) > 0): ?>

                                <?php foreach ($products as $p): ?>

                                    <tr>

                                        <!-- Product -->
                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <!-- Product Image -->

                                                <?php if (!empty($p['image_url'])): ?>

                                                    <img
                                                        src="<?php echo htmlspecialchars($p['image_url']); ?>"
                                                        alt="<?php echo htmlspecialchars($p['name']); ?>"
                                                        class="product-thumbnail"
                                                    >

                                                <?php else: ?>

                                                    <div class="no-image">
                                                        No Image
                                                    </div>

                                                <?php endif; ?>


                                                <!-- Product Details -->

                                                <div>

                                                    <strong>
                                                        <?php echo htmlspecialchars($p['name']); ?>
                                                    </strong>

                                                    <div class="small text-muted">
                                                        Sizes:
                                                        <?php echo htmlspecialchars($p['size']); ?>
                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- Category -->
                                        <td>

                                            <span
                                                class="badge bg-<?php

                                                echo $p['category'] == 'Women'
                                                    ? 'danger'
                                                    : (
                                                        $p['category'] == 'Men'
                                                            ? 'primary'
                                                            : 'warning'
                                                    );

                                                ?>"
                                            >

                                                <?php echo htmlspecialchars($p['category']); ?>

                                            </span>

                                        </td>


                                        <!-- Price -->
                                        <td class="fw-bold">

                                            R
                                            <?php echo number_format($p['price'], 2); ?>

                                        </td>


                                        <!-- Status -->
                                        <td>

                                            <span
                                                class="badge bg-<?php

                                                echo $p['stock_status'] == 'Available'
                                                    ? 'success'
                                                    : 'danger';

                                                ?>"
                                            >

                                                <?php echo htmlspecialchars($p['stock_status']); ?>

                                            </span>

                                        </td>


                                        <!-- Actions -->
                                        <td>

                                            <a
                                                href="?edit=<?php echo $p['product_id']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit Product"
                                            >

                                                <i class="bi bi-pencil"></i>

                                            </a>


                                            <a
                                                href="?delete=<?php echo $p['product_id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Product"
                                                onclick="return confirm('Delete this product?')"
                                            >

                                                <i class="bi bi-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-5 text-muted"
                                    >

                                        <i
                                            class="bi bi-box-seam"
                                            style="font-size:40px;"
                                        ></i>

                                        <p class="mt-2 mb-0">
                                            No products have been added yet.
                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once '../includes/footer.php'; ?>