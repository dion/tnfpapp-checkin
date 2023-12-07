<?php
date_default_timezone_set('America/Phoenix');

// 'client' object
class Clients{

	// database connection and table name
	private $conn;
	private $table_name = "clients";

	// object properties
	public $fname;
	public $lname;
	public $address;
	public $inhouse;
	public $city;
	public $state;
	public $zip;
	public $phone;
	public $email;
	
	// constructor
	public function __construct($db){
		$this->conn = $db;
	}
    
    // register client
    function registerClient(){
		$checkQuery = "SELECT * FROM clients WHERE phone = :phone";
		$stmt0 = $this->conn->prepare($checkQuery);
		$stmt0->bindParam(':phone', $this->phone);
		$result0 = $stmt0->execute();
    	
		$this->error = json_encode($stmt0->rowCount());
    	if($stmt0->rowCount() === 0){
			// user doesn't exist run insert query
			$query = "INSERT INTO clients (id, fname, lname, address, inhouse, city, state, postalCode, phone, email) VALUES (:id, :fname, :lname, :address, :inhouse, :city, :state, :zip, :phone, :email)";
    
			// prepare the query
			$stmt = $this->conn->prepare($query);
			
			// sanitize
			$this->fname=htmlspecialchars(strip_tags($this->fname));
			$this->lname=htmlspecialchars(strip_tags($this->lname));
			$this->address=htmlspecialchars(strip_tags($this->address));
			$this->inhouse=htmlspecialchars(strip_tags($this->inhouse));
			$this->city=htmlspecialchars(strip_tags($this->city));
			$this->state=htmlspecialchars(strip_tags($this->state));
			$this->zip=htmlspecialchars(strip_tags($this->zip));
			$this->phone=htmlspecialchars(strip_tags($this->phone));
			$this->email=htmlspecialchars(strip_tags($this->email));
	
			// bind the values 
			$stmt->bindParam(':id', abs(crc32(uniqid())));
			$stmt->bindParam(':fname', $this->fname);
			$stmt->bindParam(':lname', $this->lname);
			$stmt->bindParam(':address', $this->address);
			$stmt->bindParam(':inhouse', $this->inhouse);
			$stmt->bindParam(':city', $this->city);
			$stmt->bindParam(':state', $this->state);
			$stmt->bindParam(':zip', $this->zip);
			$stmt->bindParam(':phone', $this->phone);
			$stmt->bindParam(':email', $this->email);
	
			 // execute the query, also check if query was successful
			$result = $stmt->execute();
			
			if($result){
				return true;
			}

    	    return false;
    	} else {
			// return error, user already exists!
			return false;
		}
    	
    	return false;
    }
}