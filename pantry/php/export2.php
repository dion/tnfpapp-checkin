<?php


ob_start();

	ini_set('max_execution_time', 5500);

ini_set('memory_limit', '120M');
set_time_limit(1200);

require_once('tcpdf/tcpdf.php');
include 'dbconnect.php';
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
/*
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Mihai Smarandache');
$pdf->SetTitle('Client Visits');
$pdf->SetSubject('Client Visits');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));

//$pdf->SetHeaderData(PDF_HEADER_LOGO_WIDTH, 'Visits Report', array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
*/
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(5, 7, 5, true);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(5);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
/*$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);
*/
// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 9, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage('L');

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

	if($_POST['report'] == 'clientVisits'){
		
$sql = "SELECT v.date_of_visit AS date, c.id AS id, c.fname AS fname, c.lname AS lname, c.address AS address, c.phone AS phone, c.inhouse AS inhouse, c.email AS email, v.weight AS weight 
		FROM `clients` AS c, `visits` AS v WHERE c.id = v.client_id AND (v.date_of_visit BETWEEN :from AND :to)";
$stmt = $objDb->prepare($sql);
$result = $stmt->execute(array('from' => $_POST['from'], 'to' => $_POST['to']));
$stmt->setFetchMode(PDO::FETCH_ASSOC);

$weight = 0;
// Set some content to print
$html .= '<br/><h1>Client Visits By Date Range</h1>';
/**/
$html .= '<table><tr><th>Date</th><th>ID</th><th>First Name</th><th>Last Name</th><th>Address</th><th>Phone</th><th># In House</th><th>Email</th><th>Weight</th></tr>';
/**/
while($row = $stmt->fetch()){
	$weight = $weight + $row['weight'];
	$html .= '<tr><td>'.$row['date'].'</td><td>'.$row['id'].'</td><td>'.$row['fname'].'</td><td>'.$row['lname'].'</td><td>'.$row['address'].'</td><td>'.$row['phone'].'</td><td>'.$row['inhouse'].'</td><td>'.$row['email'].'</td><td>'.$row['weight'].'</td></tr>';
}

$html .= '</table>
<p><i>This report was generated between '.$_POST['from'].' and '.$_POST['to'].' and the total weight for this period is: '.$weight.' lbs</i>.</p>';
	} else {
		
		
		
$sql = "SELECT * FROM `clients`";
$stmt = $objDb->prepare($sql);
$result = $stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);

// Set some content to print
$html .= '<h1>Client Visits By Date Range</h1>';

$html .= '<table><tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Address</th><th>Phone</th><th># In House</th><th>Email</th><th>Annual Income</th><th>Income Updated</th></tr>';
while($row = $stmt->fetch()){
	$html .= '<tr><td>'.$row['id'].'</td><td>'.$row['fname'].'</td><td>'.$row['lname'].'</td><td>'.$row['address'].'</td><td>'.$row['phone'].'</td><td>'.$row['inhouse'].'</td><td>'.$row['email'].'</td><td>'.$row['annual_income'].'</td><td>'.$row['income_updated'].'</td></tr>';
	
	
	
}

$html .= '</table>
<p><i>This report was generated for all clients in database.</i></p>';

	}
// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('report.pdf', 'I');

/*
try {
	ini_set('max_execution_time', 5500);

ini_set('memory_limit', '10024M');
set_time_limit(1200);

require_once('tcpdf/tcpdf.php');
include 'dbconnect.php';
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Mihai Smarandache');
$pdf->SetTitle('Client Visits');
$pdf->SetSubject('Client Visits');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));

//$pdf->SetHeaderData(PDF_HEADER_LOGO_WIDTH, 'Visits Report', array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(5, 7, 5, true);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(5);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 9, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage('L');

// set text shadow effect
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
	if($_POST['report'] == 'clientVisits'){
$sql = "SELECT v.date_of_visit AS date, c.id AS id, c.fname AS fname, c.lname AS lname, c.address AS address, c.phone AS phone, c.inhouse AS inhouse, c.email AS email, v.weight AS weight 
		FROM `clients` AS c, `visits` AS v WHERE c.id = v.client_id AND (date_of_visit BETWEEN :from AND :to)";
$stmt = $objDb->prepare($sql);
$result = $stmt->execute(array('from' => $_POST['from'], 'to' => $_POST['to']));
$stmt->setFetchMode(PDO::FETCH_ASSOC);

$weight = 0;
// Set some content to print
$html .= '<br/><h1>Client Visits By Date Range</h1>';

$html .= '<table><tr><th>Date</th><th>ID</th><th>First Name</th><th>Last Name</th><th>Address</th><th>Phone</th><th># In House</th><th>Email</th><th>Weight</th></tr>';
while($row = $stmt->fetch()){
	$weight = $weight + $row['weight'];
	$html .= '<tr><td>'.$row['date'].'</td><td>'.$row['id'].'</td><td>'.$row['fname'].'</td><td>'.$row['lname'].'</td><td>'.$row['address'].'</td><td>'.$row['phone'].'</td><td>'.$row['inhouse'].'</td><td>'.$row['email'].'</td><td>'.$row['weight'].'</td></tr>';
}

$html .= '</table>
<p><i>This report was generated between '.$_POST['from'].' and '.$_POST['to'].' and the total weight for this period is: '.$weight.' lbs</i>.</p>';
	} else {
$sql = "SELECT * FROM `clients`";
$stmt = $objDb->prepare($sql);
$result = $stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);

// Set some content to print
$html .= '<h1>Client Visits By Date Range</h1>';

$html .= '<table><tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Address</th><th>Phone</th><th># In House</th><th>Email</th><th>Annual Income</th><th>Income Updated</th></tr>';
while($row = $stmt->fetch()){
	$html .= '<tr><td>'.$row['id'].'</td><td>'.$row['fname'].'</td><td>'.$row['lname'].'</td><td>'.$row['address'].'</td><td>'.$row['phone'].'</td><td>'.$row['inhouse'].'</td><td>'.$row['email'].'</td><td>'.$row['annual_income'].'</td><td>'.$row['income_updated'].'</td></tr>';
}

$html .= '</table>
<p><i>This report was generated for all clients in database.</i></p>';

	}
// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('report.pdf', 'I');

} catch(PDOException $e) {
	print_r($e);
}
*/
$content = ob_get_contents();
ob_end_clean();
print $content;
