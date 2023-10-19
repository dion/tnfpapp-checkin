<?php
include 'dbconnect.php';

function insertIntoVisits($objDb) {
	$weight = $_POST['weight'] == 'undefined' ? 0 : $_POST['weight'];
	$numOfItems = $_POST['numOfItems'] == 'undefined' ? 0 : $_POST['numOfItems'];

	$stmt = $objDb->prepare('INSERT INTO visits (`place_of_service`, `date_of_visit`, `lname`, `fname`, `how_many_in_house`, `phone`, `email`, `program`, `volunteer`, `numBags`, `weight`, `numOfItems`, `visitNotes`, `client_id`) 
					VALUES (:placeOfService, :dateOfVisit, :lname, :fname, :howManyInHouse, :phone, :email, :program, :volunteer, :numBags, :weight, :numOfItems, :visitNotes, :clientID)');
	if(!$stmt->execute(array('placeOfService' => $_POST['placeOfService'], 'dateOfVisit' => $_POST['dateOfVisit'], 'lname' => $_POST['lname'], 'fname' => $_POST['fname'], 'howManyInHouse' => $_POST['inHouse'], 
					'phone' => $_POST['phone'], 'email' => $_POST['email'], 'program' => $_POST['program'], 'volunteer' => $_POST['volunteer'], 
					'numBags' => trim($_POST['numBags']) == "" || $_POST['numBags'] == 'undefined' ? 0 : $_POST['numBags'], 
					'weight' => $weight, 
					'numOfItems' => $numOfItems, 'visitNotes' => $_POST['visitNotes'], 'clientID' => $_POST['id']))){
						
		echo json_encode(array(
			'error' => true,
			'message' => $e->getMessage()
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}
}
try {
	
	if(empty($_POST['id']) || empty($_POST['fname']) || empty($_POST['lname'])){
		throw new PDOException('Invalid request');
	}

	$stmt = $objDb->prepare('INSERT INTO clients (`id`, `fname`, `lname`, `address`, `city`, `state`, `postalCode`, `phone`, `email`, `employed`, `annual_income`, `income_updated`, `inhouse`, `howManyMales`, `howManyFemales`, `ageGroups`, `comments`) 
							 VALUES (:id, :fname, :lname, :address, :city, :state, :postalCode, :phone, :email, :employed, :annualIncome, :incomeUpdated, :inhouse, :howManyMales, :howManyFemales, :ageGroups, :comments)');
							 
	if($stmt->execute(
		array('id' => $_POST['id'], 'fname' => $_POST['fname'], 'lname' => $_POST['lname'], 'address' => $_POST['address'], 
			'city' => $_POST['city'], 'state' => $_POST['state'], 'postalCode' => $_POST['postalCode'], 'phone' => $_POST['phone'], 'email' => $_POST['email'], 'employed' => $_POST['employed'], 
			'annualIncome' => trim($_POST['annualIncome']) == "" ? 0 : $_POST['annualIncome'], 
			'incomeUpdated' => trim($_POST['incomeUpdated']) == "" ? NULL : $_POST['incomeUpdated'], 
			'inhouse' => $_POST['inHouse'], 'howManyMales' => $_POST['howManyMales'], 'howManyFemales' => $_POST['howManyFemales'], 'ageGroups' => $_POST['ageGroups'], 'comments' => $_POST['comments']
		)
	)){
		insertIntoVisits($objDb);
	} else {
		echo json_encode(array(
			'error' => true,
			'message' => $e->getMessage()
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}
	
} catch(PDOException $e) {
	
	$sql = 'SELECT MAX(id) + 1 AS id FROM clients';
	$stmt = $objDb->prepare($sql);
	$result1 = $stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$row = $stmt->fetch();
	
	$clientId2 = $row['id'];
		
	if($result1){
		
		
		$stmt2 = $objDb->prepare('INSERT INTO clients (`id`, `fname`, `lname`, `address`, `city`, `state`, `postalCode`, `phone`, `email`, `employed`, `annual_income`, `income_updated`, `inhouse`, `howManyMales`, `howManyFemales`, `ageGroups`, `comments`) 
							 VALUES (:id, :fname, :lname, :address, :city, :state, :postalCode, :phone, :email, :employed, :annualIncome, :incomeUpdated, :inhouse, :howManyMales, :howManyFemales, :ageGroups, :comments)');
							 
		if ($stmt2->execute(
			array(
				'id' => $clientId2, 'fname' => $_POST['fname'], 'lname' => $_POST['lname'], 'address' => $_POST['address'], 
				'city' => $_POST['city'], 'state' => $_POST['state'], 'postalCode' => $_POST['postalCode'], 'phone' => $_POST['phone'], 'email' => $_POST['email'], 'employed' => $_POST['employed'], 
				'annualIncome' => trim($_POST['annualIncome']) == "" ? 0 : $_POST['annualIncome'], 
				'incomeUpdated' => trim($_POST['incomeUpdated']) == "" ? NULL : $_POST['incomeUpdated'], 
				'inhouse' => $_POST['inHouse'], 'howManyMales' => $_POST['howManyMales'], 'howManyFemales' => $_POST['howManyFemales'], 'ageGroups' => $_POST['ageGroups'], 'comments' => $_POST['comments']
			)
		)) {
			
				insertIntoVisits($objDb);
		}

		echo json_encode(array(
			'error' => true,
			'message' => 'Client has been added with a new ID <b>'.$clientId2.'</b>'
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	} else {
		echo json_encode(array(
			'error' => true,
			'message' => $e->getMessage()
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}
}
