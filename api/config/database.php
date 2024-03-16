<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
// used to get mysql database connection
class Database{
	private $host;
	private $db_name;
	private $username;
	private $password;
	public $conn;

	public function __construct() {
		$this->host = $_ENV['TNFPAPP_DBHOST'] ?? null;
		$this->db_name = $_ENV['TNFPAPP_DBNAME'] ?? null;
		$this->username = $_ENV['TNFPAPP_DBUSER'] ?? null;
		$this->password = $_ENV['TNFPAPP_DBPASS'] ?? null;
	}

	// get the database connection
	public function getConnection(){

		$this->conn = null;

		try{
			$this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" .$this->db_name, $this->username, $this->password);
		}catch(PDOException $exception){
			echo "Connection error: " . $exception->getMessage();
		}

		return $this->conn;
	}
}
?>