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

// Get the admin name and one-time success message
$admin_username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");
$success_message = $_SESSION["employee_success"] ?? "";
unset($_SESSION["employee_success"]);

// Get the selected employee filters
$search = trim($_GET["search"] ?? "");
$position_filter = trim($_GET["position"] ?? "");
$date_filter = trim($_GET["date_filter"] ?? "");

// Keep only supported joining-date filters
if (
	$date_filter !== "today"
	&& $date_filter !== "this_week"
	&& $date_filter !== "this_month"
	&& $date_filter !== "this_year"
) {
	$date_filter = "";
}

$date_filter_active = $date_filter !== "" ? 1 : 0;
$date_start = date("Y-m-d");
$date_end = date("Y-m-d", strtotime("+1 day"));

// Set the date range based on the selected filter
if ($date_filter === "this_week") {
	$date_start = date("Y-m-d", strtotime("monday this week"));
	$date_end = date("Y-m-d", strtotime($date_start . " +7 days"));
} elseif ($date_filter === "this_month") {
	$date_start = date("Y-m-01");
	$date_end = date("Y-m-01", strtotime("first day of next month"));
} elseif ($date_filter === "this_year") {
	$date_start = date("Y-01-01");
	$date_end = ((int) date("Y") + 1) . "-01-01";
}

$search_value = "%" . $search . "%";

// Get the position options for the filter
$positions_result = mysqli_query(
	$conn,
	"SELECT DISTINCT position
	FROM employees
	WHERE position IS NOT NULL AND position != ''
	ORDER BY position ASC"
);

// Get employee records that match the selected filters
$stmt = mysqli_prepare(
	$conn,
	"SELECT * FROM employees
	WHERE (name LIKE ? OR phone LIKE ? OR email LIKE ?)
		AND (? = '' OR position = ?)
		AND (? = 0 OR (joining_date >= ? AND joining_date < ?))
	ORDER BY id DESC"
);
mysqli_stmt_bind_param(
	$stmt,
	"sssssiss",
	$search_value,
	$search_value,
	$search_value,
	$position_filter,
	$position_filter,
	$date_filter_active,
	$date_start,
	$date_end
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
	<title>Employees | Shop Management</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="shop-dashboard products-page employees-page">
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
				<a class="dashboard-nav-link is-current" href="view.php" aria-current="page">
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

		<main class="dashboard-main">
			<!-- Page Header -->
			<header class="dashboard-header employees-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">EMPLOYEE MANAGEMENT</p>
					<h1>Employees</h1>
					<p>Manage your shop employees and staff information.</p>
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

			<section class="dashboard-panel employee-panel" aria-labelledby="employee-list-title">
				<div class="dashboard-panel-heading employee-list-heading">
					<div>
						<h2 id="employee-list-title">Employee List</h2>
						<p>Manage your shop employees.</p>
					</div>
					<a class="products-add-button" href="add.php">
						<i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add Employee</span>
					</a>
				</div>

				<!-- Filter Section -->
				<form class="products-toolbar" method="get" action="view.php">
					<div class="products-field products-search-field">
						<label for="employee-search">Search Employees</label>
						<div class="products-search-control">
							<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
							<input
								id="employee-search"
								name="search"
								type="search"
								placeholder="Search employees..."
								value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>"
							>
						</div>
					</div>
					<div class="products-field products-category-field">
						<label for="employee-position">Position</label>
						<select id="employee-position" name="position" onchange="this.form.submit()">
							<option value="" <?php if ($position_filter === "") echo "selected"; ?>>All Positions</option>
							<?php while ($position_row = mysqli_fetch_assoc($positions_result)): ?>
								<option
									value="<?php echo htmlspecialchars((string) $position_row["position"], ENT_QUOTES, "UTF-8"); ?>"
									<?php if ($position_filter === $position_row["position"]) echo "selected"; ?>
								>
									<?php echo htmlspecialchars((string) $position_row["position"], ENT_QUOTES, "UTF-8"); ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>
					<div class="products-field products-category-field">
						<label for="employee-date-filter">Joining Date</label>
						<select id="employee-date-filter" name="date_filter" onchange="this.form.submit()">
							<option value="" <?php if ($date_filter === "") echo "selected"; ?>>All Dates</option>
							<option value="today" <?php if ($date_filter === "today") echo "selected"; ?>>
								Joined Today
							</option>
							<option value="this_week" <?php if ($date_filter === "this_week") echo "selected"; ?>>
								Joined This Week
							</option>
							<option value="this_month" <?php if ($date_filter === "this_month") echo "selected"; ?>>
								Joined This Month
							</option>
							<option value="this_year" <?php if ($date_filter === "this_year") echo "selected"; ?>>
								Joined This Year
							</option>
						</select>
					</div>
					<div class="products-field products-category-field">
						<a class="products-clear-link" href="view.php">
							<i class="fa-solid fa-xmark" aria-hidden="true"></i><span>Clear</span>
						</a>
					</div>
				</form>

				<?php if (mysqli_num_rows($result) > 0): ?>
					<!-- Table -->
					<div class="employee-table-wrap">
						<table class="employee-table">
							<thead>
								<tr>
									<th scope="col">No.</th>
									<th scope="col">Employee</th>
									<th scope="col">Phone</th>
									<th scope="col">Email</th>
									<th scope="col">Position</th>
									<th scope="col">Joining Date</th>
									<th scope="col">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php $number = mysqli_num_rows($result); ?>
								<?php while ($employee = mysqli_fetch_assoc($result)): ?>
									<?php $employee_initial = strtoupper(substr((string) $employee["name"], 0, 1)); ?>
									<tr>
										<td><?php echo $number--; ?></td>
										<td>
											<div class="employee-name-cell">
												<span class="employee-avatar" aria-hidden="true">
													<?php echo htmlspecialchars($employee_initial, ENT_QUOTES, "UTF-8"); ?>
												</span>
												<span class="employee-name">
													<?php echo htmlspecialchars((string) $employee["name"], ENT_QUOTES, "UTF-8"); ?>
												</span>
											</div>
										</td>
										<td>
											<?php echo trim((string) $employee["phone"]) !== ""
												? htmlspecialchars((string) $employee["phone"], ENT_QUOTES, "UTF-8")
												: "-"; ?>
										</td>
										<td>
											<?php echo trim((string) $employee["email"]) !== ""
												? htmlspecialchars((string) $employee["email"], ENT_QUOTES, "UTF-8")
												: "-"; ?>
										</td>
										<td>
											<span class="employee-position">
												<?php echo trim((string) $employee["position"]) !== ""
													? htmlspecialchars((string) $employee["position"], ENT_QUOTES, "UTF-8")
													: "-"; ?>
											</span>
										</td>
										<td>
											<?php echo !empty($employee["joining_date"])
												? date("d M Y", strtotime($employee["joining_date"]))
												: "-"; ?>
										</td>
										<td>
											<div class="employee-actions">
												<a class="product-action-button is-edit" href="edit.php?id=<?php echo (int) $employee["id"]; ?>">
													<i class="fa-solid fa-pen" aria-hidden="true"></i><span>Edit</span>
												</a>
												<a class="product-action-button is-danger" href="delete.php?id=<?php echo (int) $employee["id"]; ?>" onclick="return confirm('Are you sure you want to delete this employee?')">
													<i class="fa-solid fa-trash" aria-hidden="true"></i><span>Delete</span>
												</a>
											</div>
										</td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="employee-empty-state">
						<i class="fa-solid fa-users" aria-hidden="true"></i>
						<h3>No employees found</h3>
						<p>Add your first employee to start managing staff.</p>
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
	<?php if ($success_message !== ""): ?>
	<!-- Automatically hide the success message after 5 seconds. -->
	<script>
		window.setTimeout(() => {
			const successMessage = document.querySelector(".success-message");
			if (successMessage) {
				successMessage.remove();
			}
		}, 5000);
	</script>
	<?php endif; ?>
</body>
</html>