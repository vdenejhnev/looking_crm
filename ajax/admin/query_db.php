<?php
	require_once __DIR__ . '/../../vendor/autoload.php';
	require_once __DIR__ . '/../../settings.php';
	require_once __DIR__ . '/../../functions.php';

	Form\check_ajax('admin');

	$query = "SELECT id FROM user WHERE email = 'user52696@example.com'";
    $stmt = $CONNECTION->prepare($query);
    $stmt->execute();
?>