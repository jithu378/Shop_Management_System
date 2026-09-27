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

// Get the admin name
$admin_username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");

// Get the selected sales filters
$search = trim($_GET["search"] ?? "");
$product_id = intval($_GET["product_id"] ?? 0);
$date_filter = trim($_GET["date_filter"] ?? "");
$amount_filter = trim($_GET["amount_filter"] ?? "");

// Keep only supported date filters
if (
	$date_filter !== "today"
	&& $date_filter !== "yesterday"
	&& $date_filter !== "last_7_days"
	&& $date_filter !== "last_30_days"
) {
	$date_filter = "";
}

// Keep only supported amount filters
if (
	$amount_filter !== "under_500"
	&& $amount_filter !== "500_1000"
	&& $amount_filter !== "1000_5000"
	&& $amount_filter !== "above_5000"
) {
	$amount_filter = "";
}

$date_filter_active = $date_filter !== "" ? 1 : 0;
$today = date("Y-m-d");
$date_start = $today . " 00:00:00";
$date_end = date("Y-m-d", strtotime("+1 day")) . " 00:00:00";

// Set the date range from the selected date filter
if ($date_filter === "yesterday") {
	$date_start = date("Y-m-d", strtotime("-1 day")) . " 00:00:00";
	$date_end = $today . " 00:00:00";
} elseif ($date_filter === "last_7_days") {
	$date_start = date("Y-m-d", strtotime("-6 days")) . " 00:00:00";
} elseif ($date_filter === "last_30_days") {
	$date_start = date("Y-m-d", strtotime("-29 days")) . " 00:00:00";
}

$product_filter_active = $product_id > 0 ? 1 : 0;
$search_value = "%" . $search . "%";
$filters_active = $search !== "" || $product_filter_active === 1 || $date_filter !== "" || $amount_filter !== "";

// Get products for the product filter
$products_result = mysqli_query(
	$conn,
	"SELECT id, product_name FROM products ORDER BY product_name ASC"
);

// Get sales that match the selected filters
$sales_stmt = mysqli_prepare(
	$conn,
	"SELECT
		sales.id,
		products.product_name,
		products.category,
		products.price,
		sales.quantity,
		sales.total_amount,
		sales.sale_date
	FROM sales
	INNER JOIN products ON sales.product_id = products.id
	WHERE products.product_name LIKE ?
		AND (? = 0 OR sales.product_id = ?)
		AND (? = 0 OR (sales.sale_date >= ? AND sales.sale_date < ?))
		AND (
			? = ''
			OR (? = 'under_500' AND sales.total_amount < 500)
			OR (? = '500_1000' AND sales.total_amount >= 500 AND sales.total_amount < 1000)
			OR (? = '1000_5000' AND sales.total_amount >= 1000 AND sales.total_amount < 5000)
			OR (? = 'above_5000' AND sales.total_amount >= 5000)
		)
	ORDER BY sales.id DESC"
);
mysqli_stmt_bind_param(
	$sales_stmt,
	"siiisssssss",
	$search_value,
	$product_filter_active,
	$product_id,
	$date_filter_active,
	$date_start,
	$date_end,
	$amount_filter,
	$amount_filter,
	$amount_filter,
	$amount_filter,
	$amount_filter
);

mysqli_stmt_execute($sales_stmt);
$sales_query = mysqli_stmt_get_result($sales_stmt);

?>
<!-- HTML/Page Layout -->
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Sales | Shop Management</title>
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
				<a class="dashboard-nav-link" href="../products/view.php">
					<i class="fa-solid fa-box" aria-hidden="true"></i><span>Products</span>
				</a>
				<a class="dashboard-nav-link is-current" href="view.php" aria-current="page">
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
					<p class="product-page-label">SALES MANAGEMENT</p>
					<h1>Sales</h1>
					<p>Review your recorded sales and transaction details.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel products-panel" aria-labelledby="sales-history-title">
				<div class="dashboard-panel-heading products-panel-heading">
					<div>
						<h2 id="sales-history-title">Sales History</h2>
						<p>Review completed sales and their product details.</p>
					</div>
					<a class="products-add-button" href="add.php">
						<i class="fa-solid fa-plus" aria-hidden="true"></i>
						<span>Add Sale</span>
					</a>
				</div>

				<!-- Filter Section -->
				<form class="products-toolbar" method="get" action="view.php">
					<div class="products-field products-search-field">
						<label for="sales-search">Search Sales</label>
						<div class="products-search-control">
							<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
							<input
								id="sales-search"
								name="search"
								type="search"
								placeholder="Search sales..."
								value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>"
							>
						</div>
					</div>
					<div class="products-field products-category-field">
						<label for="sales-product">Product</label>
						<select id="sales-product" name="product_id" onchange="this.form.submit()">
							<option value="0" <?php if ($product_id === 0) echo "selected"; ?>>All Products</option>
							<?php while ($product = mysqli_fetch_assoc($products_result)): ?>
								<option
									value="<?php echo (int) $product["id"]; ?>"
									<?php if ($product_id === (int) $product["id"]) echo "selected"; ?>
								>
									<?php echo htmlspecialchars($product["product_name"], ENT_QUOTES, "UTF-8"); ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="sales-date-filter">Date</label>
						<select id="sales-date-filter" name="date_filter" onchange="this.form.submit()">
							<option value="" <?php if ($date_filter === "") echo "selected"; ?>>All Dates</option>
							<option value="today" <?php if ($date_filter === "today") echo "selected"; ?>>
								Today
							</option>
							<option value="yesterday" <?php if ($date_filter === "yesterday") echo "selected"; ?>>
								Yesterday
							</option>
							<option value="last_7_days" <?php if ($date_filter === "last_7_days") echo "selected"; ?>>
								Last 7 Days
							</option>
							<option value="last_30_days" <?php if ($date_filter === "last_30_days") echo "selected"; ?>>
								Last 30 Days
							</option>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="sales-amount-filter">Amount</label>
						<div class="products-status-control">
							<select id="sales-amount-filter" name="amount_filter" onchange="this.form.submit()">
								<option value="" <?php if ($amount_filter === "") echo "selected"; ?>>All Amounts</option>
								<option value="under_500" <?php if ($amount_filter === "under_500") echo "selected"; ?>>
									Under ₹500
								</option>
								<option value="500_1000" <?php if ($amount_filter === "500_1000") echo "selected"; ?>>
									₹500 to under ₹1,000
								</option>
								<option value="1000_5000" <?php if ($amount_filter === "1000_5000") echo "selected"; ?>>
									₹1,000 to under ₹5,000
								</option>
								<option value="above_5000" <?php if ($amount_filter === "above_5000") echo "selected"; ?>>
									₹5,000 and above
								</option>
							</select>
							<a class="products-clear-link" href="view.php">
								<i class="fa-solid fa-xmark" aria-hidden="true"></i><span>Clear</span>
							</a>
						</div>
					</div>
				</form>

				<!-- Table -->
				<div class="recent-sales-table-wrap">
					<table class="recent-sales-table sales-records-table">
						<thead>
							<tr>
								<th scope="col">ID</th>
								<th scope="col">Product</th>
								<th scope="col">Category</th>
								<th scope="col">Price</th>
								<th scope="col">Quantity</th>
								<th scope="col">Total Amount</th>
								<th scope="col">Sale Date</th>
							</tr>
						</thead>
						<tbody>
							<?php if (mysqli_num_rows($sales_query) > 0): ?>
								<?php while ($sale = mysqli_fetch_assoc($sales_query)): ?>
									<tr>
										<td><?php echo $sale["id"]; ?></td>
										<td>
											<div class="product-info">
												<span class="product-icon" aria-hidden="true">
													<i class="fa-solid fa-box"></i>
												</span>
												<span class="product-name"><?php echo htmlspecialchars($sale["product_name"], ENT_QUOTES, "UTF-8"); ?></span>
											</div>
										</td>
										<td><?php echo htmlspecialchars($sale["category"], ENT_QUOTES, "UTF-8"); ?></td>
										<td>₹<?php echo number_format((float) $sale["price"], 2); ?></td>
										<td><?php echo $sale["quantity"]; ?></td>
										<td>₹<?php echo number_format((float) $sale["total_amount"], 2); ?></td>
										<td><?php echo date("d M Y, h:i A", strtotime($sale["sale_date"])); ?></td>
									</tr>
								<?php endwhile; ?>
							<?php else: ?>
								<tr>
									<td colspan="7" class="recent-sales-empty">No sales found</td>
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
</body>
</html>