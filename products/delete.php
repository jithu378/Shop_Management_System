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

// Get the product ID from the page address
$id = intval($_GET["id"]);

// Check for sales records before deleting the product
$sales_stmt = mysqli_prepare(
	$conn,
	"SELECT COUNT(*) AS total FROM sales WHERE product_id = ?"
);
mysqli_stmt_bind_param($sales_stmt, "i", $id);
mysqli_stmt_execute($sales_stmt);
$sales_result = mysqli_stmt_get_result($sales_stmt);
$sales_record = mysqli_fetch_assoc($sales_result);
mysqli_stmt_close($sales_stmt);

if ((int) $sales_record["total"] > 0) {
	$_SESSION["product_error"] = "This product cannot be deleted because it has sales records.";
	header("Location: view.php");
	exit();
}

// Delete the product after checking related sales
$delete_stmt = mysqli_prepare(
	$conn,
	"DELETE FROM products WHERE id = ?"
);
mysqli_stmt_bind_param($delete_stmt, "i", $id);

// Store a message to show after returning to the Products page
if (mysqli_stmt_execute($delete_stmt) && mysqli_stmt_affected_rows($delete_stmt) > 0) {
	$_SESSION["product_success"] = "Product deleted successfully.";
} else {
	$_SESSION["product_error"] = "Product could not be found or deleted.";
}

mysqli_stmt_close($delete_stmt);
// Return to the Products page
header("Location: view.php");
exit();
?>