<?php
require('fpdf17/fpdf.php');
include("dbconnect.php");

$pdf = new FPDF();
$pdf->open();
$pdf->AddPage('L');
$pdf->SetAutoPageBreak(false);

if($_POST['report'] == 'clientVisits'){
	switch ($_POST['placeOfService']) {
		case 0:
			$placeOfService = 'Food pantry';
		break;
		case 1:
			$placeOfService = 'Storehouse';
		break;
		case 2:
			$placeOfService = 'Mobile Resource Center';
		break;
		case 3:
			$placeOfService = 'Other';
		break;
	}
	
	$pdf->SetFillColor(170, 170, 170); //gray
	$pdf->setFont("times","","11");
	$pdf->setXY(3, 3);
	$pdf->Cell(25, 10, "Date", 1, 0, "C", 1);
	$pdf->Cell(20, 10, "ID", 1, 0, "C", 1);
	$pdf->Cell(30, 10, "First Name", 1, 0, "C", 1);
	$pdf->Cell(30, 10, "Last Name", 1, 0, "C", 1);
	$pdf->Cell(55, 10, "Address", 1, 0, "C", 1);
	$pdf->Cell(26, 10, "Phone", 1, 0, "C", 1);
	$pdf->Cell(20, 10, "# In House", 1, 0, "C", 1);
	$pdf->Cell(40, 10, "Email", 1, 0, "C", 1);
	$pdf->Cell(15, 10, "Weight", 1, 0, "C", 1);
	
	$pdf->Cell(15, 10, "Item", 1, 0, "C", 1);
	$pdf->Cell(15, 10, "Notes", 1, 0, "C", 1);
	
	$pdf->Ln();

	$y = $pdf->GetY();
	$x = 3;
	$pdf->setXY($x, $y);
	 // old
	//$sql = "SELECT v.date_of_visit AS date, c.id AS id, c.fname AS fname, c.lname AS lname, c.address AS address, c.phone AS phone, c.inhouse AS inhouse, c.email AS email, v.weight AS weight 
		//	FROM `clients` AS c, `visits` AS v WHERE c.id = v.client_id AND (v.date_of_visit BETWEEN :from AND :to) ORDER BY inhouse DESC, id DESC";
	$sql = "SELECT v.place_of_service, v.date_of_visit AS date, v.numOfItems AS item, v.visitNotes AS notes, c.id AS id, c.fname AS fname, c.lname AS lname, c.address AS address, c.phone AS phone, c.inhouse AS inhouse, c.email AS email, v.weight AS weight 
		FROM `clients` AS c, `visits` AS v WHERE c.id = v.client_id AND (v.date_of_visit BETWEEN :from AND :to) AND v.place_of_service = :placeOfService ORDER BY inhouse DESC, id DESC";

	
	
	$stmt = $objDb->prepare($sql);
	$result = $stmt->execute(array('from' => $_POST['from'], 'to' => $_POST['to'], 'placeOfService' => $placeOfService));
	$result = $stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	$weight = 0;
	$count = 0;
	
	while($row = $stmt->fetch())
	{
		$pdf->Cell(25, 10, $row['date'], 1);
		$pdf->Cell(20, 10, $row['id'], 1);
		$pdf->Cell(30, 10, $row['fname'], 1);
		$pdf->Cell(30, 10, $row['lname'], 1);
		$pdf->Cell(55, 10, $row['address'], 1);
		$pdf->Cell(26, 10, $row['phone'], 1);
		$pdf->Cell(20, 10, $row['inhouse'], 1);
		$pdf->Cell(40, 10, $row['email'], 1);
		$pdf->Cell(15, 10, $row['weight'], 1);
		
		$pdf->Cell(15, 10, $row['item'], 1);
		$pdf->Cell(15, 10, $row['notes'], 1);
	
		$y += 10;
		
		if ($y > 180)
		{
			$pdf->AddPage('L');
			$y = 20;
		}
		
		$pdf->setXY($x, $y);
		$weight = $weight + $row['weight'];
		$count++;
	}
	$pdf->Cell(55, 10, 'Total weight: '.$weight.' lbs', 1);
	$pdf->Cell(50, 10, 'Total count: '.$count.' records', 1);

} else {
	$pdf->SetFillColor(170, 170, 170); //gray
	$pdf->setFont("times","","11");
	$pdf->setXY(3, 3);
	$pdf->Cell(17, 10, "Client ID", 1, 0, "C", 1);
	$pdf->Cell(30, 10, "First Name", 1, 0, "C", 1);
	$pdf->Cell(35, 10, "Last Name", 1, 0, "C", 1);
	$pdf->Cell(60, 10, "Address", 1, 0, "C", 1);
	$pdf->Cell(25, 10, "Phone", 1, 0, "C", 1);
	$pdf->Cell(15, 10, "Inhouse", 1, 0, "C", 1);
	$pdf->Cell(50, 10, "Email", 1, 0, "C", 1);
	$pdf->Cell(25, 10, "Annual income", 1, 0, "C", 1);
	$pdf->Cell(30, 10, "Income updated", 1, 0, "C", 1);
	
	$pdf->Ln();

	$y = $pdf->GetY();
	$x = 3;
	$pdf->setXY($x, $y);
	 
	$sql = "SELECT id, fname, lname, address, phone, inhouse, email, annual_income, income_updated FROM `clients` WHERE annual_income >= :min AND annual_income <= :max ORDER BY annual_income DESC";
	$stmt = $objDb->prepare($sql);
	$result = $stmt->execute(array('min' => $_POST['min'], 'max' => $_POST['max']));
	$result = $stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	while($row = $stmt->fetch())
	{
		$pdf->Cell(17, 10, $row['id'], 1);
		$pdf->Cell(30, 10, $row['fname'], 1);
		$pdf->Cell(35, 10, $row['lname'], 1);
		$pdf->Cell(60, 10, $row['address'], 1);
		$pdf->Cell(25, 10, $row['phone'], 1);
		$pdf->Cell(15, 10, $row['inhouse'], 1);
		$pdf->Cell(50, 10, $row['email'], 1);
		$pdf->Cell(25, 10, $row['annual_income'], 1);
		$pdf->Cell(30, 10, $row['income_updated'], 1);
	
		$y += 10;
		
		if ($y > 180)
		{
			$pdf->AddPage('L');
			$y = 20;
		}
		
		$pdf->setXY($x, $y);
	}
}
$pdf->Output();
?>