<?php
date_default_timezone_set('America/Phoenix');

// 'client' object
class Client{

	// database connection and table name
	private $conn;
	private $table_name = "clients_checkin";

	// object properties
	public $id;
	public $c_id;
	public $fname;
	public $lname;
	public $address;
	public $inhouse;
	public $city;
	public $state;
	public $zip;
	public $email;
	public $status;
	public $familyNumber;
	public $placeOfService;
	public $error;

	// constructor
	public function __construct($db){
		$this->conn = $db;
	}

	// save client checkin
    function save(){
        // select query
        $selectQuery = "SELECT * FROM clients WHERE email = :email";

        // prepare the query
    	$selectStmt = $this->conn->prepare($selectQuery);

        // sanitize
    	$this->email=htmlspecialchars(strip_tags($this->email));

    	// bind the value
    	$selectStmt->bindParam(':email', $this->email);

    	// execute the query, also check if query was successful
    	$resultSelect = $selectStmt->execute();

        $selectStmt->setFetchMode(PDO::FETCH_ASSOC);
    	$client = $selectStmt->fetch();
		$quantity = 0;

    	if($client){

    	    // check if client is not checked in already
            $selectQueryCheckin = "SELECT * FROM " . $this->table_name . " WHERE c_id = :c_id";

            // prepare the query
        	$selectStmtCheckin = $this->conn->prepare($selectQueryCheckin);

        	// bind the value
        	$selectStmtCheckin->bindParam(':c_id', $client['id']);

        	// execute the query, also check if query was successful
        	$resultSelectCheckin = $selectStmtCheckin->execute();

            $selectStmtCheckin->setFetchMode(PDO::FETCH_ASSOC);
        	$clientCheckedIn = $selectStmtCheckin->fetch();

    	if(!$clientCheckedIn){
    	    // insert without the email
			// TODO: add note column or not lawls
        	$query = "INSERT INTO " . $this->table_name . "
        	        (c_id, fname, lname, status, familyNumber, placeOfService, methodOfPickup, active, checked_in)
                        VALUES
                    (:c_id, :fname, :lname, :status, :familyNumber, :placeOfService, :methodOfPickup, 1, :checked_in)";

        	// prepare the query
        	$stmt = $this->conn->prepare($query);

        	// sanitize
        	$this->fname=htmlspecialchars(strip_tags($this->fname));
        	$this->lname=htmlspecialchars(strip_tags($this->lname));
        	$this->familyNumber=htmlspecialchars(strip_tags($this->familyNumber));
        	$this->placeOfService=htmlspecialchars(strip_tags($this->placeOfService));
        	$this->methodOfPickup=htmlspecialchars(strip_tags($this->methodOfPickup));
			$timestamp = date("Y-m-d H:i:s");

        	// bind the values
        	$stmt->bindParam(':c_id', $client['id']);
        	$stmt->bindParam(':fname', $this->fname);
        	$stmt->bindParam(':lname', $this->lname);
        	$stmt->bindParam(':status', $this->status);
        	$stmt->bindParam(':familyNumber', $this->familyNumber);
        	$stmt->bindParam(':placeOfService', $this->placeOfService);
        	$stmt->bindParam(':methodOfPickup', $this->methodOfPickup);
			$stmt->bindParam(':checked_in', $timestamp);

        	// execute the query, also check if query was successful
        	if($stmt->execute()){

        	    // insert items in items table
        	    foreach ($this->items as $item) {
					// TODO: pass notes into here on checkin
        	        $query = "INSERT INTO visit_items
        	        (c_id, item, place_of_service, timestamp, quantity, notes)
                        VALUES
                    (:c_id, :item, :placeOfService, :timestamp, :quantity, :notes)";

                    // prepare the query
        	        $stmt = $this->conn->prepare($query);

        	        // bind the values
        	        $stmt->bindParam(':c_id', $client['id']);
        	        $stmt->bindParam(':item', $item);
        	        $stmt->bindParam(':placeOfService', $this->placeOfService);
					$stmt->bindParam(':timestamp', $timestamp);
					$stmt->bindParam(':quantity', $quantity);

					if ($item == 'Other') {
						$stmt->bindParam(':notes', $this->notes);
					} else {
						$blank = '';
						$stmt->bindParam(':notes', $blank);
					}

        	        // execute the query
        	        $stmt->execute();
                }

        		return true;
        	}

        	return false;
    	} else {
    	    // check if client is in since Monday
    	    $selectQueryLast7days = "SELECT * FROM `clients_checkin` WHERE YEARWEEK(`checked_in`, 1) = YEARWEEK(CURDATE(), 1) AND c_id = :c_id AND placeOfService = :placeOfService";

            // prepare the query
        	$selectStmtLast7days = $this->conn->prepare($selectQueryLast7days);

        	// bind the value
        	$selectStmtLast7days->bindParam(':c_id', $client['id']);
        	$selectStmtLast7days->bindParam(':placeOfService', $this->placeOfService);

        	// execute the query, also check if query was successful
        	$resultSelectLast7days = $selectStmtLast7days->execute();

            $selectStmtLast7days->setFetchMode(PDO::FETCH_ASSOC);
        	$clientCheckedInLast7days = $selectStmtLast7days->fetch();

        	if($clientCheckedInLast7days){
        	    // $this->error = json_encode($clientCheckedIn);
        	    $this->error = "Weekly limit exceeded.";
    	        return false;
        	}
        	else {
        	    // insert without the email
            	$query = "INSERT INTO " . $this->table_name . "
            	        (c_id, fname, lname, status, familyNumber, placeOfService, methodOfPickup, active, checked_in)
                            VALUES
                        (:c_id, :fname, :lname, :status, :familyNumber, :placeOfService, :methodOfPickup, 1, :checked_in)";

            	// prepare the query
            	$stmt = $this->conn->prepare($query);

            	// sanitize
            	$this->fname=htmlspecialchars(strip_tags($this->fname));
            	$this->lname=htmlspecialchars(strip_tags($this->lname));
            	$this->familyNumber=htmlspecialchars(strip_tags($this->familyNumber));
            	$this->placeOfService=htmlspecialchars(strip_tags($this->placeOfService));
				$this->methodOfPickup=htmlspecialchars(strip_tags($this->methodOfPickup));
    			$timestamp = date("Y-m-d H:i:s");

            	// bind the values
            	$stmt->bindParam(':c_id', $client['id']);
            	$stmt->bindParam(':fname', $this->fname);
            	$stmt->bindParam(':lname', $this->lname);
            	$stmt->bindParam(':status', $this->status);
            	$stmt->bindParam(':familyNumber', $this->familyNumber);
            	$stmt->bindParam(':placeOfService', $this->placeOfService);
				$stmt->bindParam(':methodOfPickup', $this->methodOfPickup);
            	$stmt->bindParam(':checked_in', $timestamp);

            	// execute the query, also check if query was successful
            	if($stmt->execute()){

            	    // insert items in items table
            	    foreach ($this->items as $item) {
						// TODO: pass notes into here on checkin
						// TODO: only pass notes if item == 'Other'
            	        $query = "INSERT INTO visit_items
            	        (c_id, item, place_of_service, timestamp, quantity, notes)
                            VALUES
                        (:c_id, :item, :placeOfService, :timestamp, :quantity, :notes)";

                        // prepare the query
            	        $stmt = $this->conn->prepare($query);

            	        // bind the values
            	        $stmt->bindParam(':c_id', $client['id']);
            	        $stmt->bindParam(':item', $item);
            	        $stmt->bindParam(':placeOfService', $this->placeOfService);
						$stmt->bindParam(':timestamp', $timestamp);
						$stmt->bindParam(':quantity', $quantity);

						if ($item == 'Other') {
							$stmt->bindParam(':notes', $this->notes);
						} else {
							$blank = '';
							$stmt->bindParam(':notes', $blank);
						}

            	        // execute the query
            	        $stmt->execute();
                    }
            		return true;
            	}

            	return false;
        	}
    	}
    	}
    	else {
    	    $this->error = "Client not found";
    	   return false;
    	}
    }

    // get client
    function detail(){

    	// insert query
    	$query = "SELECT * FROM clients WHERE email = :value OR phone = :value";

    	// prepare the query
    	$stmt = $this->conn->prepare($query);

    	// sanitize
        $this->fname=htmlspecialchars(strip_tags($this->email));

        // bind the values
        $stmt->bindParam(':value', $this->email);

     	// execute the query, also check if query was successful
    	$result = $stmt->execute();

    	if($result){
    	    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    	    $client = $stmt->fetch();

    		return $client;
    	}

    	return false;
    }

    // get clients
    function all(){

     	$query = "SELECT * FROM " . $this->table_name . " WHERE placeOfService = :placeOfService AND active = 1 ORDER BY timestamp ASC";

    	// prepare the query
    	$stmt = $this->conn->prepare($query);

    	// bind the values
        $stmt->bindParam(':placeOfService', $this->placeOfService);

     	// execute the query, also check if query was successful
    	$result = $stmt->execute();

    	if($result){
    	    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    	    $clients = $stmt->fetchAll();

    	    for($i = 0; $i < count($clients); $i++){
					// $query = "SELECT visit_items.* FROM visit_items INNER JOIN items ON visit_items.item = items.name AND items.place_of_service= :placeOfService WHERE c_id = :c_id and visit_items.timestamp <= ( NOW() - INTERVAL 7 DAY ) and status = 'serving' or status = ''  OR c_id = :c_id and visit_items.timestamp <= NOW() and status = 'serving' or status = ''";
					$query = "SELECT visit_items.* FROM visit_items INNER JOIN items ON visit_items.item = items.name AND items.place_of_service= :placeOfService WHERE c_id = :c_id and active = 1"; // new fix
                    // prepare the query
        	        $stmt = $this->conn->prepare($query);

        	        // bind the values
        	        $stmt->bindParam(':placeOfService', $this->placeOfService);
        	        $stmt->bindParam(':c_id', $clients[$i]['c_id']);

        	        // execute the query
        	        $result = $stmt->execute();

        	        if($result){
        	            $stmt->setFetchMode(PDO::FETCH_ASSOC);
    	                $clientItems = $stmt->fetchAll();

    	                $clients[$i]['items'] = $clientItems;
        	        }
            }

    		return $clients;
    	}


    	return false;
    }

    // update client status, update visit_items with checkout for that client
    function updateStatus(){

    	// update client checkin table with new status
    	$query = "UPDATE " . $this->table_name . "
            		SET status = :status
            		WHERE id = :id";

    	// prepare the query
    	$stmt = $this->conn->prepare($query);

    	// bind the value
    	$stmt->bindParam(':status', $this->status);
    	$stmt->bindParam(':id', $this->id);

     	// execute the query, also check if query was successful
    	$result = $stmt->execute();

    	if($result){
    	    if($this->status === "checkout"){
				// select all items from visit_items for that user to be inserted into visits for main app only if its moving from serving to checkout
				// $query2 = "SELECT * FROM visit_items WHERE timestamp BETWEEN :startDate AND :endDate AND c_id = :c_id AND active = 1";
				$query2 = "SELECT * FROM visit_items WHERE c_id = :c_id AND active = 1"; // new fix

				// prepare the query
				$stmt2 = $this->conn->prepare($query2);

				// bind the value
				// $stmt2->bindParam(':startDate', date("Y-m-d 0:00:00")); // origDateFix? // new fix
				// $stmt2->bindParam(':endDate', date("Y-m-d H:i:s")); // new fix
				$stmt2->bindParam(':c_id', $this->c_id);

				// execute the query, also check if query was successful
				$result2 = $stmt2->execute();

				if($result2){
					$stmt2->setFetchMode(PDO::FETCH_ASSOC);
					$itemsArray = $stmt2->fetchAll();

					$items = [];
					$quantity = 0;

					foreach($itemsArray as $key => $item){
						array_push($items, $item['item']);
						$quantity = $quantity + $item['quantity'];
						// $lastTimeStamp = $item['timestamp']; // TODO: move out of here and change this to $this->date_of_visit lol
					}

					// insert into visits table also so that the visit appears in main app
					$query3 = "INSERT INTO `visits` (`id`, `place_of_service`, `date_of_visit`, `program`, `numBags`, `weight`, `numOfItems`, `client_id`) VALUES (NULL, :placeOfService, :dateOfVisit, :program, 0, '', :numOfItems, :c_id)";

					// prepare the query
					$stmt3 = $this->conn->prepare($query3);

					// bind the values
					$stmt3->bindParam(':placeOfService', $this->placeOfService);
					// $stmt3->bindParam(':dateOfVisit', date("Y-m-d")); // origDateFix remove this line
					$stmt3->bindParam(':dateOfVisit', $this->date_of_visit); // origDateFix this is the fix
					$stmt3->bindParam(':program', implode(", ", $items));
					$stmt3->bindParam(':numOfItems', $quantity);
					$stmt3->bindParam(':c_id', $this->c_id);

					// execute the query
					$result3 = $stmt3->execute();

					if($result3){
						//$query4 = "UPDATE visit_items SET status = :status WHERE c_id = :c_id AND active = 1 and timestamp <= ( NOW() - INTERVAL 7 DAY ) and status = 'serving' or status = ''  OR c_id = :c_id and visit_items.timestamp <= NOW() and status = 'serving' or status = ''";
						// $query4 = "UPDATE visit_items SET status = :status WHERE c_id = :c_id AND active = 1 and timestamp <= ( NOW() - INTERVAL 7 DAY ) OR c_id = :c_id and visit_items.timestamp <= NOW() and active = 1";
						$query4 = "UPDATE visit_items SET status = :status WHERE c_id = :c_id AND active = 1"; // new fix

							// prepare the query
							$stmt4 = $this->conn->prepare($query4);

							// bind the value
							$stmt4->bindParam(':status', $this->status);
							$stmt4->bindParam(':c_id', $this->c_id);

							// execute the query, also check if query was successful
							$result4 = $stmt4->execute();

							if($result4){
								return true;
							}
					}
				}
    	    }
    	    else {
                // $query5 = "UPDATE visit_items SET status = :status WHERE c_id = :c_id AND active = 1 and timestamp <= ( NOW() - INTERVAL 7 DAY ) and status = 'serving' or status = '' OR c_id = :c_id and visit_items.timestamp <= NOW() and status = 'serving' or status = ''";
				// $query5 = "UPDATE visit_items SET status = :status WHERE c_id = :c_id AND active = 1 and timestamp <= ( NOW() - INTERVAL 7 DAY ) OR c_id = :c_id and visit_items.timestamp <= NOW() and active = 1";
				$query5 = "UPDATE visit_items SET status = :status WHERE c_id = :c_id AND active = 1"; // new fix

            	// prepare the query
            	$stmt5 = $this->conn->prepare($query5);

            	// bind the value
            	$stmt5->bindParam(':status', $this->status);
            	$stmt5->bindParam(':c_id', $this->c_id);

             	// execute the query, also check if query was successful
            	$result5 = $stmt5->execute();

             	if($result5){
             	    return true;
             	}
    	    }
    	}

    	return false;
    }

    // clear checked out clients
    function clearCheckout(){
    	// delete query client from clients_checkin
		// this wasn't working because status in clients_checkin and visit_items was still 'serving'
    	$query = "UPDATE " . $this->table_name . " SET active = 0 WHERE status = 'checkout' AND placeOfService = :placeOfService AND active = 1";

    	// prepare the query
    	$stmt = $this->conn->prepare($query);

    	// bind the value
    	$stmt->bindParam(':placeOfService', $this->placeOfService);

     	// execute the query, also check if query was successful
    	$result = $stmt->execute();

		// check that current clients_checkedin users visit_items aren't in serving state

		$query2 = "UPDATE visit_items SET active = 0 WHERE status = 'checkout' AND active = 1";
		$stmt2 = $this->conn->prepare($query2);
		$result2 = $stmt2->execute();

    	if($result){
    	    // select the remaining clients
			// this is ok
        	$query = "SELECT * FROM " . $this->table_name . " WHERE placeOfService = :placeOfService AND active = 1";

        	// prepare the query
        	$stmt = $this->conn->prepare($query);

        	// bind the value
    	    $stmt->bindParam(':placeOfService', $this->placeOfService);

         	// execute the query, also check if query was successful
        	$result = $stmt->execute();

    	    if($result){
        	    $stmt->setFetchMode(PDO::FETCH_ASSOC);
        	    $clients = $stmt->fetchAll();

        		return $clients;
        	}
    	}

    	return false;
    }

    // update clients
    function updateClientInfo(){

        	// update query
    	$query = "UPDATE clients
            		SET fname = :fname, lname = :lname, address = :address, inhouse = :inhouse, city = :city, state = :state, postalCode = :zip, phone = :phone, email = :email
            		WHERE id = :id";

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
    	$stmt->bindParam(':fname', $this->fname);
    	$stmt->bindParam(':lname', $this->lname);
    	$stmt->bindParam(':address', $this->address);
    	$stmt->bindParam(':inhouse', $this->inhouse);
    	$stmt->bindParam(':city', $this->city);
    	$stmt->bindParam(':state', $this->state);
    	$stmt->bindParam(':zip', $this->zip);
    	$stmt->bindParam(':phone', $this->phone);
    	$stmt->bindParam(':email', $this->email);
    	$stmt->bindParam(':id', $this->id);

     	// execute the query, also check if query was successful
    	$result = $stmt->execute();

    	if($result){
    	    // update query
        	$query2 = "UPDATE clients_checkin
                		SET fname = :fname, lname = :lname, address = :address, familyNumber = :inhouse, city = :city, state = :state, zip = :zip, email = :email
                		WHERE c_id = :id";

        	// prepare the query
        	$stmt2 = $this->conn->prepare($query2);

        	// sanitize
        	$this->fname=htmlspecialchars(strip_tags($this->fname));
        	$this->lname=htmlspecialchars(strip_tags($this->lname));
        	$this->address=htmlspecialchars(strip_tags($this->address));
        	$this->inhouse=htmlspecialchars(strip_tags($this->inhouse));
        	$this->city=htmlspecialchars(strip_tags($this->city));
        	$this->state=htmlspecialchars(strip_tags($this->state));
        	$this->zip=htmlspecialchars(strip_tags($this->zip));
        	$this->email=htmlspecialchars(strip_tags($this->email));

        	// bind the values
        	$stmt2->bindParam(':fname', $this->fname);
        	$stmt2->bindParam(':lname', $this->lname);
        	$stmt2->bindParam(':address', $this->address);
        	$stmt2->bindParam(':inhouse', $this->inhouse);
        	$stmt2->bindParam(':city', $this->city);
        	$stmt2->bindParam(':state', $this->state);
        	$stmt2->bindParam(':zip', $this->zip);
        	$stmt2->bindParam(':email', $this->email);
        	$stmt2->bindParam(':id', $this->id);

         	// execute the query, also check if query was successful
        	$result2 = $stmt2->execute();

        	if($result2){
        	    return true;
        	}
    	}

    	return false;
    }

    function detailById(){

    	// insert query
    	$query = "SELECT * FROM clients WHERE id = :id";

    	// prepare the query
    	$stmt = $this->conn->prepare($query);

    	$stmt->bindParam(':id', $this->c_id);

     	// execute the query, also check if query was successful
    	$result = $stmt->execute();

    	if($result){
    	    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    	    $client = $stmt->fetch();

    		return $client;
    	}

    	return false;
    }
}