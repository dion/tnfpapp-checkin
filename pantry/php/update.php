<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try{
	if($_POST['id'] === 'undefined'){
		throw new PDOException('Empty data');
	} 

	include 'dbconnect.php';

	if(isset($_POST['dateOfVisit']) && isset($_POST['program']) && isset($_POST['volunteer'])){
		$weight = $_POST['weight'] == 'undefined' ? '' : $_POST['weight'];
		$numOfItems = $_POST['numOfItems'] == 'undefined' ? '' : $_POST['numOfItems'];
		$dateOfVisit = $_POST['dateOfVisit'] == 'undefined' ? '' : $_POST['dateOfVisit'];
		$numBags = $_POST['numBags'] == 'undefined' ? '' : $_POST['numBags'];
		
		$stmt = $objDb->prepare('INSERT INTO visits (`place_of_service`, `date_of_visit`, `lname`, `fname`, `how_many_in_house`, `phone`, `email`, `program`, `volunteer`, `numBags`, `weight`, `numOfItems`, `visitNotes`, `client_id`) 
						VALUES (:placeOfService, :dateOfVisit, :lname, :fname, :inHouse, :phone, :email, :program, :volunteer, :numBags, :weight, :numOfItems, :visitNotes, :id)');

						// TODO: convert $_POST['dateOfVisit'] to date format or something
		if(!$stmt->execute(array('placeOfService' => $_POST['placeOfService'], 'dateOfVisit' => $dateOfVisit, 'lname' => $_POST['lname'], 'fname' => $_POST['fname'], 'inHouse' => $_POST['inHouse'], 
						'phone' => $_POST['phone'], 'email' => $_POST['email'], 'program' => $_POST['program'], 'volunteer' => $_POST['volunteer'], 
						'numBags' => $numBags, 
						'weight' => $weight, 
						'numOfItems' => $numOfItems, 
						'visitNotes' => $_POST['visitNotes'], 'id' => $_POST['id']))){
							 
			throw new PDOException('The execute method failed');
		}
		
		$stmt = $objDb->prepare('SELECT id, place_of_service, date_of_visit as dateOfVisit, program, volunteer, numBags, weight, numOfItems, visitNotes FROM visits WHERE client_id = :id ORDER BY place_of_service, dateOfVisit ASC');
		if(!$stmt->execute(array('id' => $_POST['id']))){ 
			throw new PDOException('The execute method failed');
		}
		
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$row = $stmt->fetchAll();

		echo json_encode(array(
			'error' => false,
			'client' => $row
		));

		// , JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
		
	} else {
	
		$stmt = $objDb->prepare('UPDATE clients SET fname = :fname, lname = :lname, address = :address, city = :city, state = :state, postalCode = :postalCode, phone = :phone,
		email = :email, employed = :employed, lastDateWorked = :lastDateWorked, annual_income = :annualIncome, income_updated = :incomeUpdated, inhouse = :inHouse, howManyMales = :howManyMales, howManyFemales = :howManyFemales, ageGroups = :ageGroups, comments = :comments
		WHERE id = :id');
		if(!$stmt->execute(array('fname' => $_POST['fname'], 'lname' => $_POST['lname'], 'address' => $_POST['address'], 'city' => $_POST['city'], 'state' => $_POST['state'],
'postalCode' => $_POST['postalCode'], 'phone' => $_POST['phone'], 'email' => $_POST['email'], 'employed' => $_POST['employed'], 'lastDateWorked' => $_POST['lastDateWorked'], 'annualIncome' => $_POST['annualIncome'], 'incomeUpdated' => $_POST['incomeUpdated'], 'inHouse' => $_POST['inHouse'], 'howManyMales' => $_POST['howManyMales'],
'howManyFemales' => $_POST['howManyFemales'], 'ageGroups' => $_POST['ageGroups'], 'comments' => $_POST['comments'],	'id' => $_POST['id']))){ 
			throw new PDOException('The execute method failed');
		}				
		
		echo json_encode(array(
			'error' => false,
			'test' => 'passed'
		));
		// , JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
		
	}
} catch(PDOException $e) {

	echo json_encode(array(
		'error' => true,
		'message' => $e->getMessage()
	));
	// , JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
}