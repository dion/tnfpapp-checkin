<?php
try {

	include 'dbconnect.php';

	if ($_POST['items'] == 'insert'){
			$stmt = $objDb->prepare("INSERT INTO items (`name`, `sortOrder`, `itemType`, `place_of_service`) VALUES (:name, :sortOrder, :itemType, :pof)");
			$stmt->execute(array('name' => $_POST['name'], 'sortOrder' => $_POST['sortOrder'], 'itemType' => $_POST['itemType'], 'pof' => $_POST['pof']));
	}
	if($_POST['items'] == 'delete'){
			$stmt = $objDb->prepare('DELETE FROM items WHERE id = :id');
			$stmt->execute(array('id' => $_POST['id']));
	}

	if($_POST['items'] == 'changed'){
			$stmt = $objDb->prepare("SELECT id, name, sortOrder, itemType, place_of_service AS pof FROM items WHERE place_of_service = :pof ORDER BY pof, sortOrder ASC");
			$stmt->execute(array('pof' => $_POST['pof']));
	} else {
			$stmt = $objDb->prepare("SELECT id, name, sortOrder, itemType, place_of_service AS pof FROM items ORDER BY pof, sortOrder ASC");
			$stmt->execute();
	}
	
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$items = $stmt->fetchAll();

	echo json_encode(array(
		'error' => false,
		'items' => $items
	), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

} catch(PDOException $e) {

	echo json_encode(array(
		'error' => true,
		'message' => $e->getMessage()
	), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	
}