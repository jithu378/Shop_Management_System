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

// Get the selected salary filters
$search = trim($_GET["search"] ?? "");
$employee_id = intval($_GET["employee_id"] ?? 0);
$salary_month = trim($_GET["salary_month"] ?? "");
$amount_filter = trim($_GET["amount_filter"] ?? "");

// Keep only supported salary amount filters
if (
	$amount_filter !== "under_10000"
	&& $amount_filter !== "10000_20000"
	&& $amount_filter !== "20000_30000"
	&& $amount_filter !== "above_30000"
) {
	$amount_filter = "";
}

$employee_filter_active = $employee_id > 0 ? 1 : 0;
$search_value = "%" . $search . "%";
$filters_active = $search !== "" || $employee_filter_active === 1 || $salary_month !== "" || $amount_filter !== "";

// Get employees for the employee filter
$employees_result = mysqli_query(
	$conn,
	"SELECT id, name FROM employees ORDER BY name ASC"
);

// Get salary months for the month filter
$months_result = mysqli_query(
	$conn,
	"SELECT DISTINCT salary_month
	FROM salary
	WHERE salary_month IS NOT NULL AND salary_month != ''
	ORDER BY salary_month DESC"
);

// Get salary records that match the selected filters
$stmt = mysqli_prepare(
	$conn,
	"SELECT
		salary.id,
		employees.name,
		employees.position,
		salary.salary_month,
		salary.basic_salary,
		salary.bonus,
		salary.deduction,
		salary.net_salary,
		salary.payment_date
	FROM salary
	INNER JOIN employees
		ON salary.employee_id = employees.id
	WHERE employees.name LIKE ?
		AND (? = 0 OR salary.employee_id = ?)
		AND (? = '' OR salary.salary_month = ?)
		AND (
			? = ''
			OR (? = 'under_10000' AND salary.net_salary < 10000)
			OR (? = '10000_20000' AND salary.net_salary >= 10000 AND salary.net_salary <= 20000)
			OR (? = '20000_30000' AND salary.net_salary > 20000 AND salary.net_salary <= 30000)
			OR (? = 'above_30000' AND salary.net_salary > 30000)
		)
	ORDER BY salary.id DESC"
);
mysqli_stmt_bind_param(
	$stmt,
	"siisssssss",
	$search_value,
	$employee_filter_active,
	$employee_id,
	$salary_month,
	$salary_month,
	$amount_filter,
	$amount_filter,
	$amount_filter,
	$amount_filter,
	$amount_filter
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Salary | Shop Management</title>
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
				<a class="dashboard-nav-link" href="../sales/view.php">
					<i class="fa-solid fa-receipt" aria-hidden="true"></i><span>Sales</span>
				</a>
				<a class="dashboard-nav-link" href="../employees/view.php">
					<i class="fa-solid fa-users" aria-hidden="true"></i><span>Employees</span>
				</a>
				<a class="dashboard-nav-link is-current" href="view.php" aria-current="page">
					<i class="fa-solid fa-wallet" aria-hidden="true"></i><span>Salary</span>
				</a>
			</nav>

			<a class="dashboard-logout" href="../logout.php">
				<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Logout</span>
			</a>
		</aside>

		<main class="dashboard-main">
			<!-- Page Header -->
			<header class="dashboard-header products-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">SALARY MANAGEMENT</p>
					<h1>Salary</h1>
					<p>Manage employee salary records and payments.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel products-panel" aria-labelledby="salary-records-title">
				<div class="dashboard-panel-heading products-panel-heading">
					<div>
						<h2 id="salary-records-title">Salary Records</h2>
						<p>View employee salary records and payment details.</p>
					</div>
					<a class="products-add-button" href="add.php">
						<i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add Salary</span>
					</a>
				</div>

				<!-- Filter Section -->
				<form class="products-toolbar" method="get" action="view.php">
					<div class="products-field products-search-field">
						<label for="salary-search">Search Salary</label>
						<div class="products-search-control">
							<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
							<input
								id="salary-search"
								name="search"
								type="search"
								placeholder="Search salary records..."
								value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>"
							>
						</div>
					</div>
					<div class="products-field products-category-field">
						<label for="salary-employee">Employee</label>
						<select id="salary-employee" name="employee_id" onchange="this.form.submit()">
							<option value="0" <?php if ($employee_id === 0) echo "selected"; ?>>All Employees</option>
							<?php while ($employee = mysqli_fetch_assoc($employees_result)): ?>
								<option
									value="<?php echo (int) $employee["id"]; ?>"
									<?php if ($employee_id === (int) $employee["id"]) echo "selected"; ?>
								>
									<?php echo htmlspecialchars($employee["name"], ENT_QUOTES, "UTF-8"); ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="salary-month">Salary Month</label>
						<select id="salary-month" name="salary_month" onchange="this.form.submit()">
							<option value="" <?php if ($salary_month === "") echo "selected"; ?>>All Months</option>
							<?php while ($month = mysqli_fetch_assoc($months_result)): ?>
								<option
									value="<?php echo htmlspecialchars($month["salary_month"], ENT_QUOTES, "UTF-8"); ?>"
									<?php if ($salary_month === $month["salary_month"]) echo "selected"; ?>
								>
									<?php echo date("F Y", strtotime($month["salary_month"])); ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="salary-amount-filter">Salary Amount</label>
						<div class="products-status-control">
							<select id="salary-amount-filter" name="amount_filter" onchange="this.form.submit()">
								<option value="" <?php if ($amount_filter === "") echo "selected"; ?>>
									All Amounts
								</option>
								<option value="under_10000" <?php if ($amount_filter === "under_10000") echo "selected"; ?>>
									Under ₹10,000
								</option>
								<option value="10000_20000" <?php if ($amount_filter === "10000_20000") echo "selected"; ?>>
									₹10,000 - ₹20,000
								</option>
								<option value="20000_30000" <?php if ($amount_filter === "20000_30000") echo "selected"; ?>>
									₹20,000 - ₹30,000
								</option>
								<option value="above_30000" <?php if ($amount_filter === "above_30000") echo "selected"; ?>>
									Above ₹30,000
								</option>
							</select>
							<?php if ($filters_active): ?>
								<a class="products-clear-link" href="view.php">
									<i class="fa-solid fa-xmark" aria-hidden="true"></i>
									<span>Clear</span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</form>

				<?php if (mysqli_num_rows($result) > 0): ?>
					<!-- Table -->
					<div class="recent-sales-table-wrap">
						<table class="recent-sales-table salary-records-table">
							<thead>
								<tr>
									<th scope="col">No.</th>
									<th scope="col">Employee</th>
									<th scope="col">Position</th>
									<th scope="col">Salary Month</th>
									<th scope="col">Basic Salary</th>
									<th scope="col">Bonus</th>
									<th scope="col">Deduction</th>
									<th scope="col">Net Salary</th>
									<th scope="col">Payment Date</th>
								</tr>
							</thead>
							<tbody>
								<?php $number = 1; ?>
								<?php while ($salary = mysqli_fetch_assoc($result)): ?>
									<?php $employee_initial = strtoupper(substr((string) $salary["name"], 0, 1)); ?>
									<tr data-salary-id="<?php echo (int) $salary["id"]; ?>">
										<td><?php echo $number++; ?></td>
										<td>
											<div class="employee-name-cell">
												<span class="employee-avatar" aria-hidden="true">
													<?php echo htmlspecialchars($employee_initial, ENT_QUOTES, "UTF-8"); ?>
												</span>
												<span class="employee-name">
													<?php echo htmlspecialchars((string) $salary["name"], ENT_QUOTES, "UTF-8"); ?>
												</span>
										</div>
										</td>
										<td><span class="employee-position"><?php echo htmlspecialchars((string) $salary["position"], ENT_QUOTES, "UTF-8"); ?></span></td>
										<td><?php echo date("M Y", strtotime($salary["salary_month"])); ?></td>
										<td>₹<?php echo number_format((float) $salary["basic_salary"], 2); ?></td>
										<td>₹<?php echo number_format((float) $salary["bonus"], 2); ?></td>
										<td>₹<?php echo number_format((float) $salary["deduction"], 2); ?></td>
										<td><strong>₹<?php echo number_format((float) $salary["net_salary"], 2); ?></strong></td>
										<td><?php echo !empty($salary["payment_date"]) ? date("d M Y", strtotime($salary["payment_date"])) : "-"; ?></td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="employee-empty-state">
						<i class="fa-solid fa-wallet" aria-hidden="true"></i>
						<h3>No salary records found</h3>
						<p>Add a salary record to see it here.</p>
					</div>
				<?php endif; ?>
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