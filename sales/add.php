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
$product_id = 0;
$quantity = "";

// Check whether the Add Sale form was submitted
if (isset($_POST["add_sale"])) {
	// Get the selected product and quantity from the form
	$product_id = (int) ($_POST["product_id"] ?? 0);
	$quantity_input = trim($_POST["quantity"] ?? "");
	$quantity = (int) $quantity_input;

	// Check that a product was selected
	if ($product_id <= 0) {
		$error = "Please select a product.";
	// Check that the quantity is a whole number greater than zero
	} elseif ($quantity_input === "" || !ctype_digit($quantity_input) || $quantity <= 0) {
		$error = "Quantity must be a whole number greater than zero.";
	} else {
		// Start a transaction so the sale and stock update stay together.
		mysqli_begin_transaction($conn);

		// Lock the selected product while checking its stock.
		$product_stmt = mysqli_prepare(
			$conn,
			"SELECT id, product_name, price, quantity FROM products WHERE id = ? FOR UPDATE"
		);
		mysqli_stmt_bind_param(
			$product_stmt,
			"i",
			$product_id
		);
		mysqli_stmt_execute($product_stmt);
		$product_result = mysqli_stmt_get_result($product_stmt);

		if (mysqli_num_rows($product_result) === 0) {
			mysqli_rollback($conn);
			$error = "Selected product was not found.";
			mysqli_stmt_close($product_stmt);
		} else {
			$product = mysqli_fetch_assoc($product_result);
			$available_stock = (int) $product["quantity"];

			// Check that enough stock is available.
			if ($quantity > $available_stock) {
				mysqli_rollback($conn);
				$error = "Insufficient stock. Available stock: " . $available_stock;
				mysqli_stmt_close($product_stmt);
			} else {
				// Calculate the sale total.
				$total_amount = (float) $product["price"] * $quantity;

				// Insert the sale record.
				$sale_stmt = mysqli_prepare(
					$conn,
					"INSERT INTO sales (product_id, quantity, total_amount) VALUES (?, ?, ?)"
				);
				mysqli_stmt_bind_param(
					$sale_stmt,
					"iid",
					$product_id,
					$quantity,
					$total_amount
				);
				$sale_inserted = mysqli_stmt_execute($sale_stmt);

				if ($sale_inserted) {
					// Subtract the sold quantity from stock.
					$stock_stmt = mysqli_prepare(
						$conn,
						"UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?"
					);
					mysqli_stmt_bind_param(
						$stock_stmt,
						"iii",
						$quantity,
						$product_id,
						$quantity
					);
					$stock_updated = mysqli_stmt_execute($stock_stmt) && mysqli_stmt_affected_rows($stock_stmt) === 1;

					if ($stock_updated) {
						// Save the sale and stock change together
						if (mysqli_commit($conn)) {
							// Show a success message and clear the form values
							$success = "Sale recorded successfully.";
							$product_id = 0;
							$quantity = "";
						} else {
							mysqli_rollback($conn);
							$error = "The sale could not be completed. Please try again.";
						}
					} else {
						mysqli_rollback($conn);
						$error = "The product stock could not be updated. No sale was recorded.";
					}

					mysqli_stmt_close($stock_stmt);
				} else {
					mysqli_rollback($conn);
					$error = "The sale could not be recorded. Please try again.";
				}

				mysqli_stmt_close($sale_stmt);
				mysqli_stmt_close($product_stmt);
			}
		}
	}
}

// Get products to show in the selection list
$products_query = mysqli_query(
	$conn,
	"SELECT id, product_name, price, quantity FROM products ORDER BY product_name ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#F5F7FA">
	<title>Add Sale | Shop Management</title>
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

		<main class="dashboard-main products-main product-form-main">
			<!-- Page Header -->
			<header class="dashboard-header products-page-header">
				<div class="dashboard-heading">
					<p class="product-page-label">Sales Management</p>
					<h1>Add Sale</h1>
					<p>Record a sale and update product stock.</p>
				</div>
				<div class="dashboard-profile" aria-label="Admin profile">
					<span class="dashboard-profile-icon" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
					<span><?php echo $admin_username; ?></span>
				</div>
			</header>

			<section class="dashboard-panel product-form-panel" aria-labelledby="record-sale-title">
				<div class="dashboard-panel-heading product-form-heading">
					<div>
						<h2 id="record-sale-title">Record New Sale</h2>
						<p>Select a product and enter the quantity sold.</p>
					</div>
					<a class="product-back-link product-back-link--button" href="view.php">
						<i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
						<span>Back to Sales</span>
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
						<span><?php echo htmlspecialchars($success, ENT_QUOTES, "UTF-8"); ?></span>
					</div>
				<?php endif; ?>

				<!-- Form -->
				<form class="product-data-form" method="post" action="add.php">
					<div class="sale-product-fields">
						<div class="product-form-field">
							<label for="product-id">Product</label>
							<div class="sale-product-select-wrap">
								<i class="fa-solid fa-box" aria-hidden="true"></i>
								<select id="product-id" name="product_id" required>
									<option value="">Select Product</option>
									<?php while ($product = mysqli_fetch_assoc($products_query)): ?>
										<option
											value="<?php echo (int) $product["id"]; ?>"
											data-price="<?php echo (float) $product["price"]; ?>"
											data-stock="<?php echo (int) $product["quantity"]; ?>"
											<?php if ($product_id === (int) $product["id"]) echo "selected"; ?>
										>
											<?php echo htmlspecialchars($product["product_name"], ENT_QUOTES, "UTF-8"); ?>
										</option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="product-form-field">
							<label for="sale-quantity">Quantity Sold</label>
							<div class="product-form-input">
								<i class="fa-solid fa-cubes" aria-hidden="true"></i>
								<input id="sale-quantity" name="quantity" type="number" min="1" step="1" placeholder="Enter quantity" value="<?php echo htmlspecialchars((string) $quantity, ENT_QUOTES, "UTF-8"); ?>" required>
							</div>
						</div>
					</div>
					<div class="sale-summary-grid" aria-live="polite">
						<div class="sale-summary-item">
							<span>Product Price</span>
							<strong id="sale-product-price">—</strong>
						</div>
						<div class="sale-summary-item">
							<span>Available Stock</span>
							<strong id="sale-available-stock">—</strong>
						</div>
						<div class="sale-summary-item">
							<span>Total Amount</span>
							<strong id="sale-total-amount">—</strong>
						</div>
					</div>
					<p id="zero-stock-message" class="error-message" role="alert" hidden>This product is out of stock.</p>
					<p class="sale-helper-text">
						Total amount is calculated automatically and stock is updated after the sale.
					</p>
					<div class="product-form-actions sale-form-actions">
						<a class="product-secondary-button" href="view.php">Cancel</a>
						<button id="add-sale-button" class="products-add-button" type="submit" name="add_sale">
							<i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add Sale</span>
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
	<script>
		const productSelect = document.getElementById("product-id");
		const quantityInput = document.getElementById("sale-quantity");
		const productPriceDisplay = document.getElementById("sale-product-price");
		const availableStockDisplay = document.getElementById("sale-available-stock");
		const totalAmountDisplay = document.getElementById("sale-total-amount");
		const zeroStockMessage = document.getElementById("zero-stock-message");
		const addSaleButton = document.getElementById("add-sale-button");

		// Refresh the displayed amount and disable sales for out-of-stock products.
		function updateSaleSummary() {
			const selectedProduct = productSelect.options[productSelect.selectedIndex];
			if (!selectedProduct || selectedProduct.value === "") {
				productPriceDisplay.textContent = "—";
				availableStockDisplay.textContent = "—";
				totalAmountDisplay.textContent = "—";
				zeroStockMessage.hidden = true;
				addSaleButton.disabled = true;
				return;
			}

			const productPrice = Number(selectedProduct.dataset.price);
			const availableStock = Number(selectedProduct.dataset.stock);
			const isOutOfStock = availableStock <= 0;
			const saleQuantity = Number.parseInt(quantityInput.value, 10);
			const totalAmount = Number.isInteger(saleQuantity) && saleQuantity > 0
				? productPrice * saleQuantity
				: 0;
			const currencyOptions = { minimumFractionDigits: 2, maximumFractionDigits: 2 };

			productPriceDisplay.textContent = `₹${productPrice.toLocaleString("en-IN", currencyOptions)}`;
			availableStockDisplay.textContent = availableStock.toLocaleString("en-IN");
			totalAmountDisplay.textContent = `₹${totalAmount.toLocaleString("en-IN", currencyOptions)}`;
			zeroStockMessage.hidden = !isOutOfStock;
			addSaleButton.disabled = isOutOfStock;
		}

		productSelect.addEventListener("change", updateSaleSummary);
		quantityInput.addEventListener("input", updateSaleSummary);
		updateSaleSummary();
	</script>
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