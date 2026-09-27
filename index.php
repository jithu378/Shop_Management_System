<?php
// Start the session
session_start();

// Connect to the database
include "db.php";

// Store a login error message for the page
$error = "";

// Check whether the Login form was submitted
if (isset($_POST["login"])) {
	// Get the username and password entered in the form
	$username = $_POST["username"];
	$password = $_POST["password"];

	// Check that both login fields have values
	if (empty($username) || empty($password)) {
		$error = "Please enter username and password.";
	} else {
		// BINARY keeps the username and password comparison case-sensitive.
		// Find the admin account that matches these credentials
		$sql = "SELECT * FROM admin
		        WHERE BINARY username = BINARY ?
		            AND BINARY password = BINARY ?";
		$stmt = mysqli_prepare($conn, $sql);

		mysqli_stmt_bind_param($stmt, "ss", $username, $password);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		if (mysqli_num_rows($result) == 1) {
			// Store admin details in the session after a successful login
			$admin = mysqli_fetch_assoc($result);

			$_SESSION["admin_id"] = $admin["id"];
			$_SESSION["username"] = $admin["username"];
			$_SESSION["show_welcome"] = true;

			header("Location: dashboard.php");
			exit();
		}

		// Show an error when the credentials do not match
		$error = "Invalid username or password.";
		mysqli_stmt_close($stmt);
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Admin Login | Shop Management</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<main class="login-layout">
		<section class="brand-panel" aria-labelledby="brand-title">
			<a class="brand" href="index.php" aria-label="Shop Management home">
				<span class="brand-icon" aria-hidden="true">
					<i class="fa-solid fa-store fa-icon" aria-hidden="true"></i>
				</span>
				<span>Shop Management</span>
			</a>

			<div class="brand-message">
				<p class="eyebrow">Shop Management System</p>
				<h1 id="brand-title">Manage your shop from one place.</h1>
				<p class="brand-description">
					Manage products, sales, employees and shop operations through a simple and organized system.
				</p>
			</div>

			<p class="brand-footer">A clearer view of your day-to-day operations.</p>
		</section>

		<section class="login-panel" aria-labelledby="login-title">
			<div class="login-card">
				<div class="admin-icon" aria-hidden="true">
					<i class="fa-solid fa-user-shield fa-icon" aria-hidden="true"></i>
				</div>
				<h2 id="login-title">Admin Login</h2>
				<p class="login-subtitle">Sign in to continue to your dashboard.</p>

				<?php if ($error !== ""): ?>
					<div class="error-message">
						<i class="fa-solid fa-circle-exclamation"></i>
						<span><?php echo $error; ?></span>
					</div>
				<?php endif; ?>

				<form class="login-form" method="post">
					<div class="form-field">
						<label for="username">Username</label>
						<div class="input-wrap">
							<i class="input-icon fa-solid fa-user fa-icon" aria-hidden="true"></i>
							<input
								id="username"
								name="username"
								type="text"
								placeholder="Enter your username"
								autocomplete="username"
								required
							>
						</div>
					</div>

					<div class="form-field">
						<label for="password">Password</label>
						<div class="input-wrap">
							<i class="input-icon fa-solid fa-lock fa-icon" aria-hidden="true"></i>
							<input
								id="password"
								name="password"
								type="password"
								placeholder="Enter your password"
								autocomplete="current-password"
								style="padding-right: 42px;"
								required
							>
							<button
								id="password-toggle"
								type="button"
								aria-label="Show password"
								aria-controls="password"
								aria-pressed="false"
								style="position: absolute; top: 50%; right: 12px; padding: 4px; border: 0; background: transparent; color: var(--color-muted); cursor: pointer; transform: translateY(-50%);"
							>
								<i id="password-toggle-icon" class="fa-solid fa-eye" aria-hidden="true"></i>
							</button>
						</div>
					</div>

					<button class="login-button" type="submit" name="login">Login</button>
				</form>
			</div>
		</section>
	</main>
	<!-- Controls password visibility -->
	<script>
		const passwordInput = document.getElementById("password");
		const passwordToggle = document.getElementById("password-toggle");
		const passwordToggleIcon = document.getElementById("password-toggle-icon");

		passwordToggle.addEventListener("click", () => {
			const showPassword = passwordInput.type === "password";
			passwordInput.type = showPassword ? "text" : "password";
			passwordToggleIcon.classList.toggle("fa-eye", !showPassword);
			passwordToggleIcon.classList.toggle("fa-eye-slash", showPassword);
			passwordToggle.setAttribute("aria-label", showPassword ? "Hide password" : "Show password");
			passwordToggle.setAttribute("aria-pressed", String(showPassword));
		});
	</script>
</body>
</html>
