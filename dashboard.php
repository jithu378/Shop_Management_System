<?php
// Start the session
session_start();

// Check whether the admin is logged in
if (!isset($_SESSION["admin_id"])) {
	header("Location: index.php");
	exit();
}

// Connect to database
include "db.php";

// Show welcome message only after successful login
$showWelcome = !empty($_SESSION["show_welcome"]);
unset($_SESSION["show_welcome"]);

// Get admin username
$admin_username = htmlspecialchars($_SESSION["username"]);

// Get the total number of products
$total_products_query = mysqli_query(
	$conn,
	"SELECT COUNT(*) AS total FROM products"
);
$total_products = mysqli_fetch_assoc($total_products_query)["total"];

// Get the total number of sales
$total_sales_query = mysqli_query(
	$conn,
	"SELECT COUNT(*) AS total FROM sales"
);
$total_sales = mysqli_fetch_assoc($total_sales_query)["total"];

// Get the total number of employees
$total_employees_query = mysqli_query(
	$conn,
	"SELECT COUNT(*) AS total FROM employees"
);
$total_employees = mysqli_fetch_assoc($total_employees_query)["total"];

// Get the total number of salary records
$total_salary_records_query = mysqli_query(
	$conn,
	"SELECT COUNT(*) AS total FROM salary"
);
$total_salary_records = mysqli_fetch_assoc($total_salary_records_query)["total"];

// Get the total amount from all sales
$total_sales_amount_query = mysqli_query(
	$conn,
	"SELECT COALESCE(SUM(total_amount), 0) AS total FROM sales"
);
$total_sales_amount = mysqli_fetch_assoc($total_sales_amount_query)["total"];

// Calculate the average sale amount
$average_sale = 0;
if ((int) $total_sales > 0) {
	$average_sale = (float) $total_sales_amount / (int) $total_sales;
}

// Set up the last seven dates for the sales chart
$daily_sales = [];
for ($day_offset = 6; $day_offset >= 0; $day_offset--) {
	$sales_date = date("Y-m-d", strtotime("-" . $day_offset . " days"));
	$daily_sales[$sales_date] = 0;
}

$daily_sales_query = mysqli_query(
	$conn,
	"SELECT DATE(sale_date) AS sales_date, SUM(total_amount) AS daily_total
	FROM sales
	WHERE sale_date >= CURDATE() - INTERVAL 6 DAY
		AND sale_date < CURDATE() + INTERVAL 1 DAY
	GROUP BY DATE(sale_date)"
);

// Store each day's sales and find the highest amount for the chart
$max_daily_sales = 0;
while ($daily_sale = mysqli_fetch_assoc($daily_sales_query)) {
	$sales_date = $daily_sale["sales_date"];
	$daily_sales[$sales_date] = (float) $daily_sale["daily_total"];

	if ($daily_sales[$sales_date] > $max_daily_sales) {
		$max_daily_sales = $daily_sales[$sales_date];
	}
}

$daily_sales_rows = [];
foreach ($daily_sales as $sales_date => $daily_amount) {
	$bar_width = 0;
	if ($max_daily_sales > 0) {
		$bar_width = ($daily_amount / $max_daily_sales) * 100;
	}

	$daily_sales_rows[] = [
		"day" => date("D", strtotime($sales_date)),
		"amount" => $daily_amount,
		"width" => $bar_width
	];
}

// Get the five most recent sales
$recent_sales_query = mysqli_query(
	$conn,
	"SELECT
		sales.id,
		products.product_name,
		sales.quantity,
		sales.total_amount,
		sales.sale_date
	FROM sales
	INNER JOIN products
		ON sales.product_id = products.id
	ORDER BY sales.id DESC
	LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Dashboard | Shop Management</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body class="shop-dashboard dashboard-home">
	<div class="dashboard-shell">
		<!-- Sidebar -->
		<aside class="dashboard-sidebar" aria-label="Main navigation">
			<a class="dashboard-brand" href="dashboard.php">
				<span class="dashboard-brand-icon" aria-hidden="true"><i class="fa-solid fa-store"></i></span>
				<span>Shop Management</span>
			</a>

			<nav class="dashboard-nav" aria-label="Primary">
				<a class="dashboard-nav-link is-current" href="dashboard.php" aria-current="page">
					<i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Dashboard</span>
				</a>
				<a class="dashboard-nav-link" href="products/view.php">
					<i class="fa-solid fa-box" aria-hidden="true"></i><span>Products</span>
				</a>
				<a class="dashboard-nav-link" href="sales/view.php">
					<i class="fa-solid fa-receipt" aria-hidden="true"></i><span>Sales</span>
				</a>
				<a class="dashboard-nav-link" href="employees/view.php">
					<i class="fa-solid fa-users" aria-hidden="true"></i><span>Employees</span>
				</a>
				<a class="dashboard-nav-link" href="salary/view.php">
					<i class="fa-solid fa-wallet" aria-hidden="true"></i><span>Salary</span>
				</a>
			</nav>

			<a class="dashboard-logout" href="logout.php">
				<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Logout</span>
			</a>
		</aside>

		<main class="dashboard-main dashboard-home-main">
			<!-- Page Header -->
			<header class="dashboard-header">
				<div class="dashboard-heading">
					<p class="product-page-label">SHOP OVERVIEW</p>
					<h1>Dashboard</h1>
					<p>Overview of your shop operations</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<?php if ($showWelcome): ?>
				<section class="dashboard-welcome" aria-labelledby="welcome-title">
					<div>
						<p class="welcome-kicker">Welcome back</p>
						<h2 id="welcome-title">Welcome back, <?php echo $admin_username; ?></h2>
						<p>Here's an overview of your shop operations.</p>
					</div>
				</section>
			<?php endif; ?>

			<!-- Dashboard Summary -->
			<section class="dashboard-summary" aria-label="Shop summary">
				<article class="summary-card">
					<span class="summary-card-icon" aria-hidden="true"><i class="fa-solid fa-box"></i></span>
					<div class="summary-card-copy">
						<h2>Total Products</h2>
						<p class="summary-value"><?php echo $total_products; ?></p>
						<p class="summary-note">Product count</p>
					</div>
				</article>
				<article class="summary-card">
					<span class="summary-card-icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
					<div class="summary-card-copy">
						<h2>Total Sales</h2>
						<p class="summary-value"><?php echo $total_sales; ?></p>
						<p class="summary-note">Recorded transactions</p>
					</div>
				</article>
				<article class="summary-card">
					<span class="summary-card-icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
					<div class="summary-card-copy">
						<h2>Employees</h2>
						<p class="summary-value"><?php echo $total_employees; ?></p>
						<p class="summary-note">Team members</p>
					</div>
				</article>
				<article class="summary-card">
					<span class="summary-card-icon" aria-hidden="true"><i class="fa-solid fa-wallet"></i></span>
					<div class="summary-card-copy">
						<h2>Salary Records</h2>
						<p class="summary-value"><?php echo $total_salary_records; ?></p>
						<p class="summary-note">Payroll entries</p>
					</div>
				</article>
			</section>

			<div class="dashboard-content-grid">
				<!-- Sales Overview -->
				<section class="dashboard-panel sales-overview" aria-labelledby="sales-overview-title">
					<div class="dashboard-panel-heading sales-overview-heading">
						<div>
							<h2 id="sales-overview-title">Sales Overview</h2>
							<p>Track activity across your shop</p>
						</div>
						<span class="sales-period">Last 7 Days</span>
					</div>
					<div class="sales-summary-grid">
						<div class="sales-summary-item">
							<p>Total Sales Amount</p>
							<strong>₹<?php echo number_format((float) $total_sales_amount, 2); ?></strong>
						</div>
						<div class="sales-summary-item">
							<p>Transactions</p>
							<strong><?php echo $total_sales; ?></strong>
						</div>
						<div class="sales-summary-item">
							<p>Average Sale</p>
							<strong>₹<?php echo number_format($average_sale, 2); ?></strong>
						</div>
					</div>
					<div class="sales-activity">
						<h3>Sales Activity</h3>
						<div class="sales-activity-list">
							<?php foreach ($daily_sales_rows as $daily_sales_row): ?>
								<div class="sales-activity-row">
									<span class="sales-activity-day"><?php echo $daily_sales_row["day"]; ?></span>
									<div class="sales-bar-track" aria-hidden="true">
										<span class="sales-bar" style="width: <?php echo $daily_sales_row["width"]; ?>%;"></span>
									</div>
									<span class="sales-activity-amount">₹<?php echo number_format($daily_sales_row["amount"], 2); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<!-- Quick Actions -->
				<section class="dashboard-panel quick-actions" aria-labelledby="quick-actions-title">
					<div class="dashboard-panel-heading">
						<div>
							<h2 id="quick-actions-title">Quick Actions</h2>
						</div>
					</div>
					<div class="quick-actions-list">
						<a class="quick-action-link" href="products/add.php">
							<span class="quick-action-icon" aria-hidden="true"><i class="fa-solid fa-box"></i></span>
							<span class="quick-action-copy"><strong>Add Product</strong><small>Add a new product</small></span>
							<i class="fa-solid fa-arrow-right quick-action-arrow" aria-hidden="true"></i>
						</a>
						<a class="quick-action-link" href="sales/add.php">
							<span class="quick-action-icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
							<span class="quick-action-copy"><strong>New Sale</strong><small>Record a new sale</small></span>
							<i class="fa-solid fa-arrow-right quick-action-arrow" aria-hidden="true"></i>
						</a>
						<a class="quick-action-link" href="employees/add.php">
							<span class="quick-action-icon" aria-hidden="true"><i class="fa-solid fa-user-plus"></i></span>
							<span class="quick-action-copy"><strong>Add Employee</strong><small>Register an employee</small></span>
							<i class="fa-solid fa-arrow-right quick-action-arrow" aria-hidden="true"></i>
						</a>
						<a class="quick-action-link" href="salary/add.php">
							<span class="quick-action-icon" aria-hidden="true"><i class="fa-solid fa-wallet"></i></span>
							<span class="quick-action-copy"><strong>Add Salary</strong><small>Create salary record</small></span>
							<i class="fa-solid fa-arrow-right quick-action-arrow" aria-hidden="true"></i>
						</a>
					</div>
				</section>
			</div>

			<!-- Recent Sales Table -->
			<section class="dashboard-panel recent-sales" aria-labelledby="recent-sales-title">
				<div class="dashboard-panel-heading">
					<div>
						<h2 id="recent-sales-title">Recent Sales</h2>
						<p>Your latest shop transactions</p>
					</div>
					<a class="product-back-link dashboard-view-all" href="sales/view.php">
						<span>View All</span>
						<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
					</a>
				</div>
				<div class="recent-sales-table-wrap">
					<table class="recent-sales-table">
						<thead>
							<tr>
								<th scope="col">Product</th>
								<th scope="col">Quantity</th>
								<th scope="col">Amount</th>
								<th scope="col">Date</th>
							</tr>
						</thead>
						<tbody>
							<?php if (mysqli_num_rows($recent_sales_query) > 0): ?>
								<?php while ($sale = mysqli_fetch_assoc($recent_sales_query)): ?>
									<tr>
										<td>
											<div class="product-info">
												<span class="product-icon" aria-hidden="true"><i class="fa-solid fa-box"></i></span>
												<span class="product-name"><?php echo htmlspecialchars($sale["product_name"]); ?></span>
											</div>
										</td>
										<td><?php echo $sale["quantity"]; ?></td>
										<td>₹<?php echo number_format($sale["total_amount"], 2); ?></td>
										<td><?php echo date("d M Y", strtotime($sale["sale_date"])); ?></td>
									</tr>
								<?php endwhile; ?>
							<?php else: ?>
								<tr>
									<td colspan="4" class="recent-sales-empty">No sales recorded yet.</td>
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
	<?php if ($showWelcome): ?>
	<!-- Automatically hide the welcome message after 10 seconds. -->
	<script>
		window.setTimeout(() => {
			const welcomePanel = document.querySelector(".dashboard-welcome");
			if (!welcomePanel) {
				return;
			}

			welcomePanel.classList.add("is-dismissing");
			window.setTimeout(() => welcomePanel.remove(), 350);
		}, 10000);
	</script>
	<?php endif; ?>
</body>
</html>
