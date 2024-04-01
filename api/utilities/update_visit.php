<?php
date_default_timezone_set('America/Phoenix');
chdir(__DIR__ . '/../');
include_once 'config/database.php';

$client = $_GET['client'] ?? null;
$timestamp = $_GET['timestamp'] ?? null;
$pos = $_GET['pos'];

if ($client !== null && $timestamp !== null) {
    $totalQuantity = 0;
    $database = new Database();
    $db = $database->getConnection();
    $selectVisitItems = "SELECT quantity FROM visit_items WHERE c_id = :client and DATE(timestamp) = :timestamp";
    $stmt = $db->prepare($selectVisitItems);
    $stmt->bindParam(":client", $client);
    $stmt->bindParam(":timestamp", $timestamp);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    foreach ($rows as $row) {
        $quantity = $row['quantity'];
        $totalQuantity += intVal($quantity);
    }

    $insertQuery = "INSERT INTO visits (place_of_service, date_of_visit, numOfItems, client_id) 
        VALUES (:place_of_service, :date_of_visit, :numOfItems, :client_id)";
    $stmtInsert = $db->prepare($insertQuery);

    $stmtInsert->bindParam(':place_of_service', $pos);
    $stmtInsert->bindParam(':date_of_visit', $timestamp);
    $stmtInsert->bindParam(':numOfItems', $totalQuantity);
    $stmtInsert->bindParam(':client_id', $client);

    // Execute the insert statement
    if (!$stmtInsert->execute()) {
        // If execution fails, throw an error
        throw new Exception("Error executing insert statement: " . $stmtInsert->errorInfo()[2]);
    } else {
        // If execution is successful, proceed with other actions
        echo "Insert statement executed successfully!<br>";
        echo '<a href="check_stuck.php"><button>Go Back</button></a>';
    }
} else {
    // Handle case when parameters are missing
    echo "Client and/or Timestamp parameters are missing.";
}

?>