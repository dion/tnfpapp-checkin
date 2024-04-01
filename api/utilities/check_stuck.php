<?php
date_default_timezone_set('America/Phoenix');
chdir(__DIR__ . '/../');
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$selectDistinctVisitItems = "SELECT DISTINCT c_id, place_of_service, timestamp, status, active FROM visit_items WHERE status = 'serving' GROUP BY timestamp";
$stmt = $db->prepare($selectDistinctVisitItems);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectMatchingVisits = "SELECT * FROM visits WHERE client_id = :client_id AND date_of_visit = :date_of_visit";
$stmt2 = $db->prepare($selectMatchingVisits);

$selectMatchingCheckins = "SELECT * FROM clients_checkin WHERE c_id = :c_id AND DATE(checked_in) = :checked_in";
$stmt3 = $db->prepare($selectMatchingCheckins);
$matchingVisitsRowCount = $stmt->rowCount();

echo "<h1>Total rows: $matchingVisitsRowCount</h1>"; 

foreach ($rows as $row) {
    echo "<div style='margin:50px 10px;outline:1px solid red;width:500px;padding:30px;'>";
        $c_id = $row['c_id'];
        $timestamp = $row['timestamp'];
        $timestampWithoutTime = date('Y-m-d', strtotime($timestamp));
        $placeOfService = $row['place_of_service'];

        echo "visit_items found for c_id: " . $row['c_id'] . ", timestamp: " . $timestampWithoutTime . ", status: " . $row['status'] . ", active: " . $row['active'] . "<br>";

        $stmt2->bindParam(":client_id", $c_id);
        $stmt2->bindParam(":date_of_visit", $timestampWithoutTime);
        $stmt2->execute();
        $rowCount = $stmt2->rowCount();

        if ($rowCount == 0) {
            echo "<strong>No visits found for c_id $c_id and timestamp $timestamp</strong><br>";
            echo "<a href='update_visit.php?pos=$placeOfService&client=$c_id&timestamp=$timestampWithoutTime'><button>Add missing visit entry to db</button></a><br><br>";
        } else {
            echo "<strong>Visit found for c_id $c_id and timestamp $timestamp</strong><br><br>";
            if ($row['status'] == 'serving' && $row['active'] == 1) {
                echo "<a href='update_visit_items.php?client=$c_id&timestamp=$timestampWithoutTime'><button>Clear checkout</button></a><br><br>"; // This should be available only when visit exists
            }
        }

        $stmt3->bindParam(":c_id", $c_id);
        $stmt3->bindParam(":checked_in", $timestampWithoutTime);
        $stmt3->execute();
        $rowCount2 = $stmt3->rowCount();

        if ($rowCount2 == 0) {
            echo "<i>No client_checkin found for c_id $c_id and timestamp $timestamp</i><br>";
        } else {
            echo "<i style='color:red;'>client_checkin found for c_id $c_id and timestamp $timestamp</i><br>";
            while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                echo "c_id: " . $row['c_id'] . ", ";
                echo "status: " . $row['status'] . ", ";
                echo "active: " . $row['active'] . "<br>";

                // if ($row['status'] == 'checkout' && $row['active'] == 0) {
                //     echo "<button>Move back to checkout</button><br>";
                //     echo "<button>Move back to serving</button>";
                // }
            }
        }
    echo "</div>";
}
?>
