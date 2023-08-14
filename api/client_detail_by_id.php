<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// required to encode json web token
include_once 'libs/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/php-jwt-master/src/ExpiredException.php';
include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/client.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// instantiate user object
$client = new Client($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// get jwt
$jwt=isset($data->jwt) ? $data->jwt : "";

// if jwt is not empty
if($jwt){

    // if decode succeed, show user details
    try {
        $key = "Pantry_Check_In_System";
        
        // decode jwt, if it was a fake jwt it would not be able to decode it using this key
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        
        // set client property values
        $client->c_id = $data->id;
        
        if($client->detailById()){
            
            // set response code
            http_response_code(200);
            
            // response in json format
            echo json_encode(
                    array(
                        "client" => $client->detailById()
                    )
                );
        }
        else {
    
            // set response code
            http_response_code(200);
        
            // display message: unable to get client
            echo json_encode(array("error" => "Client not found"));
        }
    }
    // if decode fails, it means jwt is invalid
    catch (Exception $e){
    
        // set response code
        http_response_code(401);
    
        // show error message
        echo json_encode(array(
            "error" => $e->getMessage()
        ));
    }
}
else {
    // show error message
    echo json_encode(array(
        "error" => "Missing email or jwt token!"
    ));
}
?>