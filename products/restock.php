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

if (!isset($_GET["id"])) {
	header("Location: view.php");
	exit();
}

// Get the selected product ID
$id = intval($_GET["id"]);

// Prepare the query to get the selected product
	$product_stmt = mysqli_prepare(
		$conn,
		"SELECT * FROM products WHERE id = ?"
	);
mysqli_stmt_bind_param($product_stmt, "i", $id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);

if (mysqli_num_rows($product_result) === 0) {
	mysqli_stmt_close($product_stmt);
	header("Location: view.php");
	exit();
}

$product = mysqli_fetch_assoc($product_result);
mysqli_stmt_close($product_stmt);

// Store the admin name and page messages
$admin_username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");
$error = "";
$success = "";
$restock_quantity = "";

// Check whether the Restock Product form was submitted
if (isset($_POST["restock_product"])) {
	// Get the quantity entered by the admin
	$restock_quantity = trim($_POST["restock_quantity"] ?? "");

	// Check that the quantity is a whole number greater than zero
	if ($restock_quantity === "" || !ctype_digit($restock_quantity) || (int) $restock_quantity <= 0) {
		$error = "Enter a whole-number quantity greater than zero.";
	} else {
		$restock_value = (int) $restock_quantity;

		// Add the restocked quantity to the existing stock
		$restock_stmt = mysqli_prepare(
			$conn,
			"UPDATE products SET quantity = quantity + ? WHERE id = ?"
		);
		mysqli_stmt_bind_param($restock_stmt, "ii", $restock_value, $id);

		if (mysqli_stmt_execute($restock_stmt)) {
			$success = "Product restocked successfully.";
			$product["quantity"] = (int) $product["quantity"] + $restock_value;
			$restock_quantity = "";
		}

		mysqli_stmt_close($restock_stmt);
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Restock Product | Shop Management</title>
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
					<h1>Restock Product</h1>
					<p>Add stock to the existing product quantity.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel product-form-panel" aria-labelledby="restock-title">
				<div class="dashboard-panel-heading product-form-heading">
					<div>
						<h2 id="restock-title">Restock Product</h2>
						<p>Add stock to the existing product quantity.</p>
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
				<div class="restock-product-summary">
					<div>
						<span>Product Name</span>
						<strong><?php echo htmlspecialchars($product["product_name"], ENT_QUOTES, "UTF-8"); ?></strong>
					</div>
					<div>
						<span>Current Stock</span>
						<strong><?php echo (int) $product["quantity"]; ?></strong>
					</div>
				</div>
				<!-- Form -->
				<form class="product-data-form" method="post" action="restock.php?id=<?php echo $id; ?>">
					<div class="product-form-field restock-quantity-field">
						<label for="restock-quantity">Restock Quantity</label>
						<div class="product-form-input">
							<i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
							<input id="restock-quantity" name="restock_quantity" type="number" min="1" step="1" placeholder="Enter quantity to add" value="<?php echo htmlspecialchars($restock_quantity, ENT_QUOTES, "UTF-8"); ?>" required>
						</div>
					</div>
					<div class="product-form-actions">
						<a class="product-secondary-button" href="view.php">Cancel</a>
						<button class="products-add-button" type="submit" name="restock_product">
							<i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i><span>Restock Product</span>
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