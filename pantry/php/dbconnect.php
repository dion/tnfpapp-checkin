<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
try {
	$host = $_ENV['PANTRY_DBHOST'] ?? null;
	$dbname = $_ENV['PANTRY_DBNAME'] ?? null;
	$user = $_ENV['PANTRY_DBUSER'] ?? null;
	$pass = $_ENV['PANTRY_DBPASS'] ?? null;

	$objDb = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
	$objDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
	echo json_encode(array(
		'error' => true,
		'message' => $e->getMessage()
	
	), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}