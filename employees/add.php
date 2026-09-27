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
$name = "";
$phone = "";
$email = "";
$position = "";
$joining_date = "";

// Check whether the Add Employee form was submitted
if (isset($_POST["add_employee"])) {
	// Get employee details entered in the form
	$name = trim($_POST["name"] ?? "");
	$phone = trim($_POST["phone"] ?? "");
	$email = trim($_POST["email"] ?? "");
	$position = trim($_POST["position"] ?? "");
	$joining_date = trim($_POST["joining_date"] ?? "");

	// Check that all required fields are filled
	if ($name === "" || $position === "" || $joining_date === "") {
		$error = "Please fill in all required fields.";
	// Check that the phone number has 10 digits
	} elseif ($phone !== "" && !preg_match("/^[0-9]{10}$/", $phone)) {
		$error = "Phone number must contain exactly 10 digits.";
	// Check that the email address is valid
	} elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = "Please enter a valid email address.";
	} else {
		// Prepare the query to add the employee
		$stmt = mysqli_prepare(
			$conn,
			"INSERT INTO employees (name, phone, email, position, joining_date) VALUES (?, ?, ?, ?, ?)"
		);

		if ($stmt) {
			mysqli_stmt_bind_param(
				$stmt,
				"sssss",
				$name,
				$phone,
				$email,
				$position,
				$joining_date
			);

			if (mysqli_stmt_execute($stmt)) {
				// Show a success message and clear the form values
				$success = "Employee added successfully.";
				$name = "";
				$phone = "";
				$email = "";
				$position = "";
				$joining_date = "";
			} else {
				$error = "Failed to add employee.";
			}

			mysqli_stmt_close($stmt);
		} else {
			$error = "Failed to add employee.";
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
	<title>Add Employee | Shop Management</title>
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

		<main class="dashboard-main products-main product-form-main">
			<!-- Page Header -->
			<header class="dashboard-header products-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">EMPLOYEE MANAGEMENT</p>
					<h1>Add Employee</h1>
					<p>Add a new employee to your shop staff.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel product-form-panel" aria-labelledby="add-employee-title">
				<div class="dashboard-panel-heading product-form-heading">
					<div>
						<h2 id="add-employee-title">Add New Employee</h2>
						<p>Enter the employee information below.</p>
					</div>
					<a class="product-back-link product-back-link--button" href="view.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Back to Employees</span></a>
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
							<label for="employee-name">Employee Name <span aria-hidden="true">*</span></label>
							<div class="product-form-input">
								<i class="fa-solid fa-user" aria-hidden="true"></i>
								<input id="employee-name" name="name" type="text" placeholder="Enter employee name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
						<div class="product-form-field">
							<label for="employee-phone">Phone</label>
							<div class="product-form-input">
								<i class="fa-solid fa-phone" aria-hidden="true"></i>
								<input id="employee-phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter exactly 10 digits." placeholder="Enter phone number" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, "UTF-8"); ?>">
							</div>
						</div>
						<div class="product-form-field">
							<label for="employee-email">Email</label>
							<div class="product-form-input">
								<i class="fa-solid fa-envelope" aria-hidden="true"></i>
								<input id="employee-email" name="email" type="email" placeholder="Enter email address" value="<?php echo htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?>">
							</div>
						</div>
						<div class="product-form-field">
							<label for="employee-position">Position <span aria-hidden="true">*</span></label>
							<div class="product-form-input">
								<i class="fa-solid fa-briefcase" aria-hidden="true"></i>
								<input id="employee-position" name="position" type="text" placeholder="Example: Sales Assistant" value="<?php echo htmlspecialchars($position, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
					</div>

					<br>
					<div class="product-form-field">
						<label for="employee-joining-date">Joining Date <span aria-hidden="true">*</span></label>
						<div class="product-form-input">
							<i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
							<input id="employee-joining-date" name="joining_date" type="date" value="<?php echo htmlspecialchars($joining_date, ENT_QUOTES, "UTF-8"); ?>" required>
						</div>
					</div>

					<div class="product-form-actions">
						<a class="product-secondary-button" href="view.php">Cancel</a>
						<button class="products-add-button" type="submit" name="add_employee">
							<i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add Employee</span>
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