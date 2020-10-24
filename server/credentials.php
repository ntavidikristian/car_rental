<?php

require_once('database.php');

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
    
    $query = "select * from cars_table";
    $values = array();

    $cars_array = getQuerySecure($query, $values);
    
    appendNextBooking($cars_array);
    return $cars_array;
}

function getAllBookings(){

    $query = "select * from bookings
    order by id desc";

    $bookings_array = getQuerySecure($query, array());

    ///enrich array with car element
    parseCarBooking($bookings_array); 

    return $bookings_array;
}

function getBookingsByCarId($car_id){

    $query = "select * from bookings where car_id = ?";
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
    $query = "select * from cars_table where id = ?";
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
    $query = "select * from cars_table where plate = ?";
    return getQuerySecure($query, array($plate));
}

function getCarByName($name){
    $query = "select * from cars_table where name like  ?";
    $result = getQuerySecure($query, $name);
    // $result->next_bookings = getCarNextBookings($result->id);
    return $result;
}

function getBookingByCarName($name){
    $query = "select bookings.id as id, date_from, date_to, car_id, pickup_point, dropoff_point ,telephone, client_name, address, paid
    from bookings, cars_table
     where car_id = cars_table.id and cars_table.name like ?";
    return getQuerySecure($query, array($name));
}

function removeBooking($booking_id){
    $query = "delete from bookings where id = ?";
    executeQuerySecure($query, array($booking_id));
}
function addCar($name, $plate){
    $query = "insert into cars_table(name, plate) values (?,?)";
    $values =  array($name, $plate);
    return executeQuerySecure($query, $values);
}

function removeCar($car_id){

    $query = "delete from cars_table where id=?";//SECONDARY KEY
    executeQuerySecure($query, array($car_id));
    $query = "delete from bookings where car_id = ?";
    executeQuerySecure($query, array($car_id));
}
function addBooking($date_from, $date_to, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid){
    $query = "insert into bookings (date_from, date_to, car_id, pickup_point, dropoff_point, client_name, telephone, address, paid) values (?,?,?,?,?,?,?,?,?)";
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
    $query = "update bookings 
    set date_to = ?, date_from=?, car_id = ?, pickup_point = ?, dropoff_point = ?, client_name = ?, telephone = ?, address = ?, paid = ? where id = ?";
    //echo $query;
    $values = array($date_to, $date_from, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid, $id);
    executeQuerySecure($query, $value);
}
function getCurrentBookings(){
    $current_time = time();
    $query = "select * from bookings where date_from<? and date_to>?";
    $result = getQuerySecure($query, array($current_time, $current_time));
    parseCarBooking($result);
    return $result;
}
function getPastBookings(){
    $current_time = time();
    $query = "select * from bookings where date_to < ?";
    $result = getQuerySecure($query, array($current_time));
    parseCarBooking($result);
    return $result;
}
function getFutureBookings(){
    $current_time = time();
    $query = "select * from bookings where date_from>?";
    $result = getQuerySecure($query, array($current_time));
    parseCarBooking($result);
    return $result;
}
function getCarNextBookings($car_id){
    $current_time = time();
    $query = "select * from bookings where car_id = ? and date_to > ? order by date_from asc";
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

    $query = "select * from cars_table where id not in (
        select car_id as id from cars_table, bookings where cars_table.id = bookings.car_id and (date_from<? and date_to>?)
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

?>