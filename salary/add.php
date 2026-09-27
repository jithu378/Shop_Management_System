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
$error = "";
$success = "";
$success_net_salary = "";
$employee_id = "";
$salary_month = "";
$basic_salary = "";
$bonus = "";
$deduction = "";
$payment_date = "";

// Check whether the Add Salary form was submitted
if (isset($_POST["add_salary"])) {
	// Get salary details entered in the form
	$employee_id = intval($_POST["employee_id"] ?? 0);
	$salary_month = trim($_POST["salary_month"] ?? "");
	$basic_salary = trim($_POST["basic_salary"] ?? "");
	$bonus = trim($_POST["bonus"] ?? "");
	$deduction = trim($_POST["deduction"] ?? "");
	$payment_date = trim($_POST["payment_date"] ?? "");

	// Check that all required fields are filled
	if ($employee_id <= 0 || $salary_month === "" || $basic_salary === "" || $payment_date === "") {
		$error = "Please fill in all required fields.";
	// Check that salary amounts contain valid numbers
	} elseif (!is_numeric($basic_salary) || ($bonus !== "" && !is_numeric($bonus)) || ($deduction !== "" && !is_numeric($deduction))) {
		$error = "Please fill in all required fields.";
	} else {
		$basic_salary_value = floatval($basic_salary);
		$bonus_value = floatval($bonus);
		$deduction_value = floatval($deduction);

		// Check that salary amounts are not negative
		if ($basic_salary_value < 0) {
			$error = "Basic salary cannot be negative.";
		} elseif ($bonus_value < 0 || $deduction_value < 0) {
			$error = "Bonus and deduction cannot be negative.";
		} else {
			// Calculate net salary after adding bonus and subtracting deduction
			$net_salary = $basic_salary_value + $bonus_value - $deduction_value;

			if ($net_salary < 0) {
				$error = "Net salary cannot be negative.";
			} else {
				// Prepare the query to add the salary record
				$stmt = mysqli_prepare(
					$conn,
					"INSERT INTO salary (
						employee_id,
						salary_month,
						basic_salary,
						bonus,
						deduction,
						net_salary,
						payment_date
					) VALUES (?, ?, ?, ?, ?, ?, ?)"
				);

				if ($stmt) {
					mysqli_stmt_bind_param(
						$stmt,
						"isdddds",
						$employee_id,
						$salary_month,
						$basic_salary_value,
						$bonus_value,
						$deduction_value,
						$net_salary,
						$payment_date
					);

					if (mysqli_stmt_execute($stmt)) {
						// Show a success message and clear the form values
						$success = "Salary added successfully.";
						$success_net_salary = "Net salary: ₹" . number_format($net_salary, 2);
						$employee_id = "";
						$salary_month = "";
						$basic_salary = "";
						$bonus = "";
						$deduction = "";
						$payment_date = "";
					} else {
						$error = "Failed to add salary record.";
					}

					mysqli_stmt_close($stmt);
				} else {
					$error = "Failed to add salary record.";
				}
			}
		}
	}
}

// Get employees for the employee selection list
$employees_result = mysqli_query(
	$conn,
	"SELECT id, name, position FROM employees ORDER BY name ASC"
);
$employee_count = mysqli_num_rows($employees_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Add Salary | Shop Management</title>
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
					<i class="fa-solid fa-chart-line" aria-hidden="true"></i>
					<span>Dashboard</span>
				</a>
				<a class="dashboard-nav-link" href="../products/view.php">
					<i class="fa-solid fa-box" aria-hidden="true"></i>
					<span>Products</span>
				</a>
				<a class="dashboard-nav-link" href="../sales/view.php">
					<i class="fa-solid fa-receipt" aria-hidden="true"></i>
					<span>Sales</span>
				</a>
				<a class="dashboard-nav-link" href="../employees/view.php">
					<i class="fa-solid fa-users" aria-hidden="true"></i>
					<span>Employees</span>
				</a>
				<a class="dashboard-nav-link is-current" href="view.php" aria-current="page">
					<i class="fa-solid fa-wallet" aria-hidden="true"></i>
					<span>Salary</span>
				</a>
			</nav>
			<a class="dashboard-logout" href="../logout.php">
				<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
				<span>Logout</span>
			</a>
		</aside>

		<main class="dashboard-main products-main product-form-main">
			<!-- Page Header -->
			<header class="dashboard-header products-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">SALARY MANAGEMENT</p>
					<h1>Add Salary</h1>
					<p>Record an employee salary payment.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel product-form-panel" aria-labelledby="add-salary-title">
				<div class="dashboard-panel-heading product-form-heading">
					<div>
						<h2 id="add-salary-title">Add Salary Record</h2>
						<p>Enter the employee salary and payment details.</p>
					</div>
					<a class="product-back-link product-back-link--button" href="view.php">
						<i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
						<span>Back to Salary</span>
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
						<span>
							<?php echo htmlspecialchars($success, ENT_QUOTES, "UTF-8"); ?>
							<?php echo htmlspecialchars($success_net_salary, ENT_QUOTES, "UTF-8"); ?>
						</span>
					</div>
				<?php endif; ?>

				<?php if ($employee_count === 0): ?>
					<p class="sale-helper-text">No employees available</p>
				<?php endif; ?>

				<!-- Form -->
				<form class="product-data-form" method="post" action="add.php">
					<div class="product-form-grid">
						<div class="product-form-field">
							<label for="salary-employee">Employee <span aria-hidden="true">*</span></label>
							<div class="sale-product-select-wrap">
								<i class="fa-solid fa-user" aria-hidden="true"></i>
								<select id="salary-employee" name="employee_id" required>
									<option value="">Select Employee</option>
									<?php while ($employee = mysqli_fetch_assoc($employees_result)): ?>
										<?php $employee_position = trim((string) $employee["position"]) !== "" ? $employee["position"] : "-"; ?>
										<option
										value="<?php echo (int) $employee["id"]; ?>"
										<?php if ((int) $employee_id === (int) $employee["id"]) echo "selected"; ?>
										>
										<?php echo htmlspecialchars((string) $employee["name"], ENT_QUOTES, "UTF-8"); ?>
										-
										<?php echo htmlspecialchars((string) $employee_position, ENT_QUOTES, "UTF-8"); ?>
										</option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="product-form-field">
							<label for="salary-month">Salary Month <span aria-hidden="true">*</span></label>
							<div class="product-form-input">
								<i class="fa-solid fa-calendar" aria-hidden="true"></i>
								<input id="salary-month" name="salary_month" type="month" value="<?php echo htmlspecialchars((string) $salary_month, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
						<div class="product-form-field">
							<label for="basic-salary">Basic Salary <span aria-hidden="true">*</span></label>
							<div class="product-form-input">
								<i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
								<input id="basic-salary" name="basic_salary" type="number" min="0" step="0.01" placeholder="Enter basic salary" value="<?php echo htmlspecialchars((string) $basic_salary, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
						<div class="product-form-field">
							<label for="salary-bonus">Bonus</label>
							<div class="product-form-input">
								<i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
								<input id="salary-bonus" name="bonus" type="number" min="0" step="0.01" placeholder="Enter bonus" value="<?php echo htmlspecialchars((string) $bonus, ENT_QUOTES, "UTF-8"); ?>">
							</div>
						</div>
						<div class="product-form-field">
							<label for="salary-deduction">Deduction</label>
							<div class="product-form-input">
								<i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
								<input id="salary-deduction" name="deduction" type="number" min="0" step="0.01" placeholder="Enter deduction" value="<?php echo htmlspecialchars((string) $deduction, ENT_QUOTES, "UTF-8"); ?>">
							</div>
						</div>
						<div class="product-form-field">
							<label for="payment-date">Payment Date <span aria-hidden="true">*</span></label>
							<div class="product-form-input">
								<i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
								<input id="payment-date" name="payment_date" type="date" value="<?php echo htmlspecialchars((string) $payment_date, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
					</div>

					<div class="product-form-actions">
						<a class="product-secondary-button" href="view.php">Cancel</a>
						<button class="products-add-button" type="submit" name="add_salary">
							<i class="fa-solid fa-plus" aria-hidden="true"></i>
							<span>Add Salary</span>
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