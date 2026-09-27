<?php
// Start the session
session_start();

// Check whether the admin is logged in
if (!isset($_SESSION["admin_id"])) {
	header("Location: ../index.php");
	exit();
}

// Connect to the database
include __DIR__ . "/../db.php";

// Store form values and messages for the page
$admin_username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");
$product_name = "";
$category = "";
$price = "";
$quantity = "";
$error = "";
$success = "";

// Check whether the Add Product form was submitted
if (isset($_POST["add_product"])) {
	// Get the product details entered by the admin
	$product_name = trim($_POST["product_name"] ?? "");
	$category = trim($_POST["category"] ?? "");
	$price = trim($_POST["price"] ?? "");
	$quantity = trim($_POST["quantity"] ?? "");

	// Check that all required fields are filled
	if ($product_name === "" || $category === "" || $price === "" || $quantity === "") {
		$error = "Please fill in all fields.";
	// Check that the price is a valid non-negative number
	} elseif (!is_numeric($price) || (float) $price < 0) {
		$error = "Price must be a valid non-negative number.";
	// Check that the quantity is a whole number
	} elseif (!ctype_digit($quantity)) {
		$error = "Quantity must be a non-negative whole number.";
	} else {
		$quantity_value = (int) $quantity;
		$price_value = (float) $price;

		// Check whether a product with this name already exists
		$duplicate_stmt = mysqli_prepare(
			$conn,
			"SELECT id FROM products WHERE product_name = ?"
		);
		mysqli_stmt_bind_param($duplicate_stmt, "s", $product_name);
		mysqli_stmt_execute($duplicate_stmt);
		$duplicate_result = mysqli_stmt_get_result($duplicate_stmt);

		if (mysqli_num_rows($duplicate_result) > 0) {
			$error = "A product with this name already exists.";
			mysqli_stmt_close($duplicate_stmt);
		} else {
			mysqli_stmt_close($duplicate_stmt);

			// Insert the new product into the database
			$stmt = mysqli_prepare(
				$conn,
				"INSERT INTO products (product_name, category, price, quantity)
				VALUES (?, ?, ?, ?)"
			);
			mysqli_stmt_bind_param(
				$stmt,
				"ssdi",
				$product_name,
				$category,
				$price_value,
				$quantity_value
			);

			if (mysqli_stmt_execute($stmt)) {
				// Show a success message and clear the form values
				$success = "Product added successfully.";
				$product_name = "";
				$category = "";
				$price = "";
				$quantity = "";
			}

			mysqli_stmt_close($stmt);
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Add Product | Shop Management</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="shop-dashboard products-page">
	<div class="dashboard-shell">
		<!-- Sidebar -->
		<aside class="dashboard-sidebar" aria-label="Main navigation">
			<a class="dashboard-brand" href="../dashboard.php">
				<span class="dashboard-brand-icon" aria-hidden="true"><i class="fa-solid fa-store"></i></span>
				<span>Shop Management</span>
			</a>
			<nav class="dashboard-nav" aria-label="Primary">
				<a class="dashboard-nav-link" href="../dashboard.php">
					<i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Dashboard</span>
				</a>
				<a class="dashboard-nav-link is-current" href="view.php" aria-current="page">
					<i class="fa-solid fa-box" aria-hidden="true"></i><span>Products</span>
				</a>
				<a class="dashboard-nav-link" href="../sales/view.php">
					<i class="fa-solid fa-receipt" aria-hidden="true"></i><span>Sales</span>
				</a>
				<a class="dashboard-nav-link" href="../employees/view.php">
					<i class="fa-solid fa-users" aria-hidden="true"></i><span>Employees</span>
				</a>
				<a class="dashboard-nav-link" href="../salary/view.php">
					<i class="fa-solid fa-wallet" aria-hidden="true"></i><span>Salary</span>
				</a>
			</nav>
			<a class="dashboard-logout" href="../logout.php">
				<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Logout</span>
			</a>
		</aside>

		<main class="dashboard-main products-main product-form-main">
			<!-- Page Header -->
			<header class="dashboard-header products-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">Product Management</p>
					<h1>Add Product</h1>
					<p>Add a new product to your shop inventory.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel product-form-panel" aria-labelledby="add-product-title">
				<div class="dashboard-panel-heading product-form-heading">
					<div>
						<h2 id="add-product-title">Add New Product</h2>
						<p>Enter the product details below.</p>
					</div>
					<a class="product-back-link product-back-link--button" href="view.php">
						<i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Back to Products</span>
					</a>
				</div>

				<?php if ($error !== ""): ?>
					<div class="error-message" role="alert">
						<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
						<span><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></span>
					</div>
				<?php endif; ?>
				<?php if ($success !== ""): ?>
					<div class="success-message" role="status">
						<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
						<span><?php echo htmlspecialchars($success, ENT_QUOTES, "UTF-8"); ?></span>
					</div>
				<?php endif; ?>

				<!-- Form -->
				<form class="product-data-form" method="post" action="add.php">
					<div class="product-form-grid">
						<div class="product-form-field">
							<label for="product-name">Product Name</label>
							<div class="product-form-input">
								<i class="fa-solid fa-box" aria-hidden="true"></i>
								<input id="product-name" name="product_name" type="text" placeholder="Enter product name" value="<?php echo htmlspecialchars($product_name, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
						<div class="product-form-field">
							<label for="product-category">Category</label>
							<div class="product-form-input">
								<i class="fa-solid fa-layer-group" aria-hidden="true"></i>
								<input id="product-category" name="category" type="text" placeholder="Enter category" value="<?php echo htmlspecialchars($category, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
						<div class="product-form-field">
							<label for="product-price">Price (₹)</label>
							<div class="product-form-input">
								<i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
								<input id="product-price" name="price" type="number" min="0" step="0.01" placeholder="Enter price" value="<?php echo htmlspecialchars($price, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
						<div class="product-form-field">
							<label for="product-quantity">Quantity / Stock</label>
							<div class="product-form-input">
								<i class="fa-solid fa-cubes" aria-hidden="true"></i>
								<input id="product-quantity" name="quantity" type="number" min="0" step="1" placeholder="Enter stock quantity" value="<?php echo htmlspecialchars($quantity, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
					</div>
					<div class="product-form-actions">
						<a class="product-secondary-button" href="view.php">Cancel</a>
						<button class="products-add-button" type="submit" name="add_product">
							<i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add Product</span>
						</button>
					</div>
				</form>
			</section>
			<!-- Footer -->
			<footer class="dashboard-footer">
				<span>Shop Management System</span>
				<span>&copy; 2026 All Rights Reserved.</span>
			</footer>
		</main>
	</div>
	<?php if ($success !== ""): ?>
	<!-- Automatically hide the success message after 3 seconds. -->
	<script>
		window.setTimeout(() => {
			const successMessage = document.querySelector(".success-message");
			if (successMessage) {
				successMessage.remove();
			}
		}, 3000);
	</script>
	<?php endif; ?>
</body>
</html>