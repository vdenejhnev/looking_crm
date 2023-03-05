<?php
	require_once 'vendor/autoload.php';
	require_once 'settings.php';
	require_once 'functions.php';

	//$query = "ALTER TABLE dealer ADD tg_state VARCHAR(24)";
	//$query = "ALTER TABLE dealer MODIFY tg_notification varchar(12)";
	//$query = "UPDATE dealer SET tg_notification = 12345 WHERE user_id = 132";
	//$stmt = $CONNECTION->prepare($query);
    //$stmt->execute();

	//$query = "DELETE FROM lead WHERE chat_id = '1002298082'";
    $query = "SELECT * FROM lead INNER JOIN lead_phone ON lead.id = lead_phone.lead_id WHERE chat_id = '1002298082'";
    $stmt = $CONNECTION->prepare($query);
    $stmt->execute();

    print_r( $stmt->get_result()->fetch_all(MYSQLI_ASSOC));

	$query = "SELECT * FROM dealer WHERE user_id = 132";
    $stmt = $CONNECTION->prepare($query);
    $stmt->execute();

    print_r( $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>