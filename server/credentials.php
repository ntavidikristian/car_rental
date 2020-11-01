<?php

require_once('database.php');

define('PERMISSION_WRITE', "PERMISSION_WRITE");
define('PERMISSION_READ', "PERMISSION_READ");

//create connection


function connectDB(){
    
    try{
        $db = new mysqli(servername, username, password, db_name);
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }else{
            return $db;
        }
    }catch (Exception $e){
        echo "Piasama exception amaga mou";
    }
    
    // Check connection
}

function getAllCars(){
    
    $query = "select * from " . DATABASE_CARS_TABLE;
    $values = array();

    $cars_array = getQuerySecure($query, $values);
    
    appendNextBooking($cars_array);
    return $cars_array;
}

function getAllBookings(){

    $query = "select * from ". DATABASE_BOOKINGS_TABLE ." 
    order by id desc";

    $bookings_array = getQuerySecure($query, array());

    ///enrich array with car element
    parseCarBooking($bookings_array); 

    return $bookings_array;
}

function getBookingsByCarId($car_id){

    $query = "select * from ". DATABASE_BOOKINGS_TABLE ." where ". DATABASE_BOOKINGS_CARID ." = ?";
    $values = array($car_id);

    $result = getQuerySecure($query, $values);

    return parseCarBooking($result);
}

function parseCarBooking(&$my_array){
    foreach($my_array as &$object){
        $object->car = getCarById($object->car_id);
        unset($object->car_id);
    }

    return $my_array;
}
/**
 * Basic function to get data from database
 * 
 * Returns an array of objects matching the query givven
 */

//  -------------------         DEPRECATED
// function getQuery($query){
//     $database = connectDB();
//     $apotelesma = $database->query($query);
//     $array = array();
//     if($apotelesma == false){
//         return $array;
//     }
//     while($element = mysqli_fetch_object($apotelesma)){
//         $array[] = $element;
//     }
//     $database->close();
//     return $array;
// }

/**
 * Prepared query
 * 
 */
function getQuerySecure($query, $values){
    $database = connectDB();

    $statement = $database->prepare($query);

    if(count($values) >0){
        $value_types = str_repeat('s',count($values));
        $statement->bind_param($value_types, ...$values);
    }
    
    $statement->execute();
    $result = $statement->get_result();
    $array = array();
    if($result == false){
        return $array;
    }
    while($element = mysqli_fetch_object($result)){
        $array[] = $element;
    }

    $statement->close();
    $database->close();
    return $array;
}


function getCarById($id){
    $query = "select * from ".DATABASE_CARS_TABLE." where ".DATABASE_CARS_ID." = ?";
    $result = getQuerySecure($query, array($id));

    $to_return = null;
    if(count($result)>0){
        $to_return = $result[0];
    }
    return $to_return;
}

function appendNextBooking(&$cars_array){
    foreach($cars_array as &$car){
        $car->next_booking = getCarNextBookings($car->id);
    }
}
function getCarByPlate($plate){
    $query = "select * from ". DATABASE_CARS_TABLE." where ".DATABASE_CARS_PLATE." = ?";
    return getQuerySecure($query, array($plate));
}

function getCarByName($name){
    $query = "select * from ".DATABASE_CARS_TABLE. " where " .DATABASE_CARS_NAME. " like  ?";
    $result = getQuerySecure($query, $name);
    // $result->next_bookings = getCarNextBookings($result->id);
    return $result;
}

function getBookingByCarName($name){
    $query = "select ".DATABASE_BOOKINGS_TABLE.".".DATABASE_BOOKINGS_ID." as id, ".DATABASE_BOOKINGS_DATEFROM.", ".DATABASE_BOOKINGS_DATETO.", ".DATABASE_BOOKINGS_CARID.", ".DATABASE_BOOKINGS_PICKUPPOINT.", ".DATABASE_BOOKINGS_DROPOFFPOINT." ,".DATABASE_BOOKINGS_TELEPHONE.", ".DATABASE_BOOKINGS_CLIENTNAME.", ".DATABASE_BOOKINGS_ADDRESS.", ".DATABASE_BOOKINGS_PAID."
    from ".DATABASE_BOOKINGS_TABLE.", ".DATABASE_CARS_TABLE."
     where ".DATABASE_BOOKINGS_CARID." = ".DATABASE_CARS_TABLE.".".DATABASE_CARS_ID." and ".DATABASE_CARS_TABLE.".".DATABASE_CARS_NAME." like ?";
    return getQuerySecure($query, array($name));
}

function removeBooking($booking_id){
    $query = "delete from ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_BOOKINGS_ID." = ?";
    executeQuerySecure($query, array($booking_id));
}
function addCar($name, $plate){
    $query = "insert into ".DATABASE_CARS_TABLE."(".DATABASE_CARS_NAME.", ".DATABASE_CARS_PLATE.") values (?,?)";
    $values =  array($name, $plate);
    return executeQuerySecure($query, $values);
}

function removeCar($car_id){

    $query = "delete from ".DATABASE_CARS_TABLE." where ".DATABASE_CARS_ID."=?";//SECONDARY KEY
    executeQuerySecure($query, array($car_id));
    $query = "delete from ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_BOOKINGS_CARID." = ?";
    executeQuerySecure($query, array($car_id));
}
function addBooking($date_from, $date_to, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid){
    $query = "insert into ".DATABASE_BOOKINGS_TABLE." (".DATABASE_BOOKINGS_DATEFROM.", ".DATABASE_BOOKINGS_DATETO.", ".DATABASE_BOOKINGS_CARID.", ".DATABASE_BOOKINGS_PICKUPPOINT.", ".DATABASE_BOOKINGS_DROPOFFPOINT.", ".DATABASE_BOOKINGS_CLIENTNAME.", ".DATABASE_BOOKINGS_TELEPHONE.", ".DATABASE_BOOKINGS_ADDRESS.", ".DATABASE_BOOKINGS_PAID.") values (?,?,?,?,?,?,?,?,?)";
    // echo $query;
    $values = array(
        $date_from,
        $date_to,
        $car_id,
        $pick_up_point,
        $drop_off_point,
        $client_name,
        $tel,
        $address,
        $paid
    );
    executeQuerySecure($query, $values);
}
function updateBooking($id, $date_from, $date_to, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid){
    $query = "update ".DATABASE_BOOKINGS_TABLE." 
    set ".DATABASE_BOOKINGS_DATETO." = ?, ".DATABASE_BOOKINGS_DATEFROM."=?, ".DATABASE_BOOKINGS_CARID." = ?, ".DATABASE_BOOKINGS_PICKUPPOINT." = ?, ".DATABASE_BOOKINGS_DROPOFFPOINT." = ?, ".DATABASE_BOOKINGS_CLIENTNAME." = ?, ".DATABASE_BOOKINGS_TELEPHONE." = ?, ".DATABASE_BOOKINGS_ADDRESS." = ?, ".DATABASE_BOOKINGS_PAID." = ? where ".DATABASE_BOOKINGS_ID." = ?";
    //echo $query;
    $values = array($date_to, $date_from, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid, $id);
    executeQuerySecure($query, $values);
}
function getCurrentBookings(){
    $current_time = time();
    $query = "select * from ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_BOOKINGS_DATEFROM."<? and ".DATABASE_BOOKINGS_DATETO.">?";
    $result = getQuerySecure($query, array($current_time, $current_time));
    parseCarBooking($result);
    return $result;
}
function getPastBookings(){
    $current_time = time();
    $query = "select * from ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_BOOKINGS_DATETO." < ?";
    $result = getQuerySecure($query, array($current_time));
    parseCarBooking($result);
    return $result;
}
function getFutureBookings(){
    $current_time = time();
    $query = "select * from ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_BOOKINGS_DATEFROM.">?";
    $result = getQuerySecure($query, array($current_time));
    parseCarBooking($result);
    return $result;
}
function getCarNextBookings($car_id){
    $current_time = time();
    $query = "select * from ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_BOOKINGS_CARID." = ? and ".DATABASE_BOOKINGS_DATETO." > ? order by ".DATABASE_BOOKINGS_DATEFROM." asc";
    $result = getQuerySecure($query, array($car_id, $current_time));
    parseCarBooking($result);
    return $result;

}

//DEPRECATED --------------------------------
// function executeQuery($query){
//     $database = connectDB();
//     $database->query($query);
//     $insert_id = $database->insert_id;
//     $database->close();
//     return $insert_id;
// }

function executeQuerySecure($query, $values){
    $database = connectDB();

    $value_types = str_repeat("s", count($values));

    $statement = $database->prepare($query);
    $statement->bind_param($value_types, ...$values);

    $statement->execute();
    
    $insert_id = $database->insert_id;//TODO NA TO DOUME

    $statement->close();
    $database->close();
    return $insert_id;
}

function getAvailableCars(){
    $current_time = time();

    $query = "select * from ".DATABASE_CARS_TABLE." where ".DATABASE_CARS_ID." not in (
        select ".DATABASE_BOOKINGS_CARID." as ".DATABASE_CARS_ID." from ".DATABASE_CARS_TABLE.", ".DATABASE_BOOKINGS_TABLE." where ".DATABASE_CARS_TABLE.".".DATABASE_CARS_ID." = ".DATABASE_BOOKINGS_TABLE.".".DATABASE_BOOKINGS_CARID." and (".DATABASE_BOOKINGS_DATEFROM."<? and ".DATABASE_BOOKINGS_DATETO.">?)
    ) ";
    $result = getQuerySecure($query, array($current_time, $current_time));
    appendNextBooking($result);
    return $result;
}


function makeResponse($status_code, $message){
    http_response_code($status_code);
    $response = array(
        'message' => $message
    );
    return json_encode($response);
}



function is_token_valid($token){
    $query = "select ".DATABASE_AUTH_VALID." from ".DATABASE_AUTH_TABLE." where ".DATABASE_AUTH_AUTHTOKEN." = ?";
    $response = getQuerySecure($query , array($token));

    if (count($response)>0 ){
        // echo "found ". count($response) . " results <br>";
        // var_dump($response);
        if ($response[0]->valid == '1'){
            return true;
        }else {
            return false;
        }
    }else{
        // echo "no result <br>";
        return false;
    }
}

function is_token_granted($token, $permission){
    switch( $permission){
        case PERMISSION_WRITE:
            $query = "select ".DATABASE_AUTH_WRITEGRANTED." from ".DATABASE_AUTH_TABLE." where ".DATABASE_AUTH_AUTHTOKEN." = ?";
            $response = getQuerySecure($query, array($token));
            if( count($response)> 0){
                if( $response[0]->write_granted == '1'){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
            break; 
        case PERMISSION_READ:
            $query = "select ".DATABASE_AUTH_READGRANTED." from ".DATABASE_AUTH_TABLE." where ".DATABASE_AUTH_AUTHTOKEN." = ?";
            $response = getQuerySecure($query, array($token));
            if( count($response)> 0){
                if( $response[0]->read_granted =='1'){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
            break;
        default:
            return false;
    }
}

function validate_writing($USE_AUTHENTICATION, $token){
    if(! $USE_AUTHENTICATION){
        return;
    }
    if( is_token_granted($token, PERMISSION_WRITE)){
        return;
    }

    echo makeResponse(401, "Unauthorized to write");
    die();
}

function validate_reading($USE_AUTHENTICATION, $token){
    if(! $USE_AUTHENTICATION){
        return true;
    }
    if( is_token_granted($token , PERMISSION_READ)){
        return true;
    }

    echo makeResponse(401, "Unauthorized to read");
    die();
}
?>