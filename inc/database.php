<?php
    $servername = "localhost"; // Define servername.
    $username = "root"; // Define username.
    $password = ""; // Define password.
    $dbname = "guessthenumber"; // Define database name.

    // Create connection, contained in variable $conn, using new mysqli, servername, username, password and database.
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection, if connection error is encountered print error message to webpage.
    if ($conn->connect_error) {
        echo ("Connection failed: " . $conn->connect_error . ". Please try again later.");
    }
?>