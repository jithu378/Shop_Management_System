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

// Get the admin name and result messages
$admin_username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");

// Show product result messages once
$success_message = $_SESSION["product_success"] ?? "";
$error_message = $_SESSION["product_error"] ?? "";
unset($_SESSION["product_success"], $_SESSION["product_error"]);

// Get the search text and selected category
$search = "";
if (isset($_GET["search"])) {
	$search = trim($_GET["search"]);
}

$category = "";
if (isset($_GET["category"])) {
	$category = trim($_GET["category"]);
}

// Get the selected price and stock filters
$price_filter = "";
if (isset($_GET["price"])) {
	$price_filter = trim($_GET["price"]);
}

$stock_status = "";
if (isset($_GET["status"])) {
	$stock_status = trim($_GET["status"]);
}

// Ignore filter values that are not listed on the page
if (
	$price_filter !== "under_100"
	&& $price_filter !== "100_500"
	&& $price_filter !== "500_1000"
	&& $price_filter !== "above_1000"
) {
	$price_filter = "";
}

if (
	$stock_status !== "available"
	&& $stock_status !== "low_stock"
	&& $stock_status !== "out_of_stock"
) {
	$stock_status = "";
}

// Get the available product categories for the filter
$category_query = mysqli_query(
	$conn,
	"SELECT DISTINCT category FROM products WHERE category != '' ORDER BY category ASC"
);

// Build the price condition from the selected filter
$search_value = "%" . $search . "%";
$price_condition = "";
if ($price_filter === "under_100") {
	$price_condition = " AND price < 100";
} elseif ($price_filter === "100_500") {
	$price_condition = " AND price >= 100 AND price <= 500";
} elseif ($price_filter === "500_1000") {
	$price_condition = " AND price > 500 AND price <= 1000";
} elseif ($price_filter === "above_1000") {
	$price_condition = " AND price > 1000";
}

$stock_condition = "";
// Build the stock condition from the selected filter
if ($stock_status === "available") {
	$stock_condition = " AND quantity >= 6";
} elseif ($stock_status === "low_stock") {
	$stock_condition = " AND quantity BETWEEN 1 AND 5";
} elseif ($stock_status === "out_of_stock") {
	$stock_condition = " AND quantity = 0";
}

$sql = "SELECT * FROM products
	WHERE product_name LIKE ?
	AND (? = '' OR category = ?)" . $price_condition . $stock_condition . "
	ORDER BY id DESC";
// Get products that match the selected filters
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $search_value, $category, $category);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$filters_active = $search !== "" || $category !== "" || $price_filter !== "" || $stock_status !== "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Products | Shop Management</title>
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

		<main class="dashboard-main products-main">
			<!-- Page Header -->
			<header class="dashboard-header products-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">PRODUCT MANAGEMENT</p>
					<h1>Products</h1>
					<p>Manage your shop products and inventory.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<?php if ($success_message !== ""): ?>
				<div class="success-message" role="status">
					<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
					<span><?php echo htmlspecialchars($success_message, ENT_QUOTES, "UTF-8"); ?></span>
				</div>
			<?php endif; ?>
			<?php if ($error_message !== ""): ?>
				<div class="error-message" role="alert">
					<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
					<span><?php echo htmlspecialchars($error_message, ENT_QUOTES, "UTF-8"); ?></span>
				</div>
			<?php endif; ?>

			<section class="dashboard-panel products-panel" aria-labelledby="product-list-title">
				<div class="dashboard-panel-heading products-panel-heading">
					<div>
						<h2 id="product-list-title">Product List</h2>
						<p>View and manage your shop inventory.</p>
					</div>
					<a class="products-add-button" href="add.php">
						<i class="fa-solid fa-plus" aria-hidden="true"></i>
						<span>Add Product</span>
					</a>
				</div>

				<!-- Filter Section -->
				<form class="products-toolbar" method="get" action="view.php">
					<div class="products-field products-search-field">
						<div class="products-field-heading">
							<label for="product-search">Search Products</label>
						</div>
						<div class="products-search-control">
							<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
							<input
								id="product-search"
								name="search"
								type="search"
								placeholder="Search by product name..."
								value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>"
							>
						</div>
					</div>
					<div class="products-field products-category-field">
						<label for="product-category">Category</label>
						<select id="product-category" name="category" onchange="this.form.submit()">
							<option value="" <?php if ($category === "") echo "selected"; ?>>All Categories</option>
							<?php while ($category_row = mysqli_fetch_assoc($category_query)): ?>
								<option
									value="<?php echo htmlspecialchars($category_row["category"], ENT_QUOTES, "UTF-8"); ?>"
									<?php if ($category === $category_row["category"]) echo "selected"; ?>
								>
									<?php echo htmlspecialchars($category_row["category"], ENT_QUOTES, "UTF-8"); ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="product-price">Price</label>
						<select id="product-price" name="price" onchange="this.form.submit()">
							<option value="" <?php if ($price_filter === "") echo "selected"; ?>>All Prices</option>
							<option value="under_100" <?php if ($price_filter === "under_100") echo "selected"; ?>>
								Under ₹100
							</option>
							<option value="100_500" <?php if ($price_filter === "100_500") echo "selected"; ?>>
								₹100 - ₹500
							</option>
							<option value="500_1000" <?php if ($price_filter === "500_1000") echo "selected"; ?>>
								₹500 - ₹1,000
							</option>
							<option value="above_1000" <?php if ($price_filter === "above_1000") echo "selected"; ?>>
								Above ₹1,000
							</option>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="product-status">Stock Status</label>
						<div class="products-status-control">
							<select id="product-status" name="status" onchange="this.form.submit()">
								<option value="" <?php if ($stock_status === "") echo "selected"; ?>>All Status</option>
								<option value="available" <?php if ($stock_status === "available") echo "selected"; ?>>
									Available
								</option>
								<option value="low_stock" <?php if ($stock_status === "low_stock") echo "selected"; ?>>
									Low Stock
								</option>
								<option value="out_of_stock" <?php if ($stock_status === "out_of_stock") echo "selected"; ?>>
									Out of Stock
								</option>
							</select>
								<a class="products-clear-link" href="view.php">
									<i class="fa-solid fa-xmark" aria-hidden="true"></i><span>Clear</span>
								</a>
						</div>
					</div>
				</form>

				<!-- Table -->
				<div class="products-table-wrap">
					<table class="products-table">
						<thead>
							<tr>
								<th scope="col">No.</th>
								<th scope="col">Product</th>
								<th scope="col">Category</th>
								<th scope="col">Price</th>
								<th scope="col">Stock</th>
								<th scope="col">Status</th>
								<th scope="col">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// Display sequential product number
							$number = mysqli_num_rows($result);
							?>
							<?php if ($number > 0): ?>
								<?php while ($product = mysqli_fetch_assoc($result)): ?>
									<?php
									$quantity = (int) $product["quantity"];

										// Determine the stock status from the available quantity
									if ($quantity == 0) {
										$status = "Out of Stock";
										$status_class = "out-stock";
									} elseif ($quantity <= 5) {
										$status = "Low Stock";
										$status_class = "low-stock";
									} else {
										$status = "Available";
										$status_class = "available";
									}
									?>
									<tr>
										<td><?php echo $number; ?></td>
										<td>
											<div class="product-info">
												<span class="product-icon" aria-hidden="true">
													<i class="fa-solid fa-box"></i>
												</span>
												<span class="product-name"><?php echo htmlspecialchars($product["product_name"]); ?></span>
											</div>
										</td>
										<td><?php echo htmlspecialchars($product["category"]); ?></td>
										<td>₹<?php echo number_format($product["price"], 2); ?></td>
										<td><?php echo $quantity; ?></td>
										<td>
											<span class="product-status <?php echo $status_class; ?>"><?php echo $status; ?></span>
										</td>
										<td>
											<div class="product-row-actions">
												<a class="product-action-button is-edit" href="edit.php?id=<?php echo $product["id"]; ?>">
													<i class="fa-solid fa-pen" aria-hidden="true"></i><span>Edit</span>
												</a>
												<a class="product-action-button is-restock" href="restock.php?id=<?php echo $product["id"]; ?>">
													<i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i><span>Restock</span>
												</a>
												<a class="product-action-button is-danger" href="delete.php?id=<?php echo $product["id"]; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
													<i class="fa-solid fa-trash" aria-hidden="true"></i><span>Delete</span>
												</a>
											</div>
										</td>
									</tr>
									<?php $number--; ?>
								<?php endwhile; ?>
							<?php else: ?>
								<tr>
									<td colspan="7" class="products-empty-state">
											<span class="products-empty-icon" aria-hidden="true">
												<i class="fa-solid fa-box-open"></i>
											</span>
										<span><?php echo $filters_active ? "No products match your search or filters." : "No products found."; ?></span>
										<?php if (!$filters_active): ?>
											<span>Add your first product to start managing stock.</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
			<!-- Footer -->
			<footer class="dashboard-footer">
				<span>Shop Management System</span>
				<span>&copy; 2026 All Rights Reserved.</span>
			</footer>
		</main>
	</div>
	<?php if ($success_message !== ""): ?>
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
	<?php if ($error_message === "This product cannot be deleted because it has sales records."): ?>
	<!-- Automatically hide this product deletion message after 5 seconds. -->
	<script>
		window.setTimeout(() => {
			const errorMessage = document.querySelector(".error-message");
			if (errorMessage) {
				errorMessage.remove();
			}
		}, 5000);
	</script>
	<?php endif; ?>

</body>
</html>