<?php
include 'Database.php';
Database::connect();
Database::setNumberSmsLeftOnAllPhones(5000);
Database::disconnect();
?>
