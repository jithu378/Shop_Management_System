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

$id = intval($_GET["id"]);

// Check whether salary records prevent deleting this employee
$salary_stmt = mysqli_prepare(
	$conn,
	"SELECT COUNT(*) AS total FROM salary WHERE employee_id = ?"
);
if (!$salary_stmt) {
	echo "<script>
		alert('Unable to check salary records. Employee was not deleted.');
		window.location.href = 'view.php';
	</script>";
	exit();
}

mysqli_stmt_bind_param(
	$salary_stmt,
	"i",
	$id
);
$salary_check_success = mysqli_stmt_execute($salary_stmt);
$salary_result = $salary_check_success ? mysqli_stmt_get_result($salary_stmt) : false;
$salary = $salary_result ? mysqli_fetch_assoc($salary_result) : false;
mysqli_stmt_close($salary_stmt);

if (!$salary) {
	echo "<script>
		alert('Unable to check salary records. Employee was not deleted.');
		window.location.href = 'view.php';
	</script>";
	exit();
}

// Keep the employee if salary records exist
if ((int) $salary["total"] > 0) {
	echo "<script>
		alert('This employee cannot be deleted because salary records exist for this employee.');
		window.location.href = 'view.php';
	</script>";
	exit();
}

// Delete the employee after checking related salary records
$delete_stmt = mysqli_prepare(
	$conn,
	"DELETE FROM employees WHERE id = ?"
);
$employee_deleted = false;
if ($delete_stmt) {
	mysqli_stmt_bind_param(
		$delete_stmt,
		"i",
		$id
	);
	$employee_deleted = mysqli_stmt_execute($delete_stmt) && mysqli_stmt_affected_rows($delete_stmt) === 1;
	mysqli_stmt_close($delete_stmt);
}

// Store a success message for the Employees page
if ($employee_deleted) {
	$_SESSION["employee_success"] = "Employee deleted successfully.";
}

// Return to the Employees page
header("Location: view.php");
exit();
?>