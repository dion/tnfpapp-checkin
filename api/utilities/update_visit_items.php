<?php
date_default_timezone_set('America/Phoenix');
chdir(__DIR__ . '/../');
include_once 'config/database.php';

$client = $_GET['client'] ?? null;
$timestamp = $_GET['timestamp'] ?? null;

if ($client !== null && $timestamp !== null) {
    $database = new Database();
    $db = $database->getConnection();
    $updateVisitItems = "UPDATE visit_items SET status = 'checkout', active = 0 WHERE c_id = :c_id and DATE(timestamp) = :timestamp";
    $stmt = $db->prepare($updateVisitItems);

    $stmt->bindParam(':timestamp', $timestamp);
    $stmt->bindParam(':c_id', $client);
    $stmt->execute();

    if (!$stmt->execute()) {
        // If execution fails, throw an error
        throw new Exception("Error executing insert statement: " . $stmt->errorInfo()[2]);
    } else {
        // If execution is successful, proceed with other actions
        echo "Visit Items successfully cleared!<br>";
        echo '<a href="check_stuck.php"><button>Go Back</button></a>';
    }
} else {
    // Handle case when parameters are missing
    echo "Client and/or Timestamp parameters are missing.";
}

?>