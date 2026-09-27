<?php

// Connect to the MySQL database
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "shop_management"
);

// Check whether the database connection was successful
if (!$conn) {

    // Stop the program and display the connection error
    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}

?>