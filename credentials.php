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
    $database = connectDB();
    $apotelesma = $database->query("select * from cars_table");
    
    $cars_array = array();

    while($car  = mysqli_fetch_object($apotelesma)){
        $cars_array[] = $car;
    }
    $database->close();
    appendNextBooking($cars_array);
    echo json_encode($cars_array);
}

function getAllBookings(){
    // $database = connectDB();
    $query = "select * from bookings
    order by id desc";

    $bookings_array = getQuery($query);
    parseCarBooking($bookings_array); ///TODO NA TSEKAROUME OTI SIGOURA DOULEVEI O NEOS TROPOS
    // $apotelesma = $database->query($query);

    // $bookings_array = array();
    // while($booking = mysqli_fetch_object($apotelesma)){
    //     $booking->car = getCarById($booking->car_id);
    //     unset($booking->car_id);
    //     $bookings_array[] = $booking;
    // }


    // $database->close();
    echo json_encode($bookings_array);
}

function getBookingsByCarId($car_id){
    $query = "select * from bookings where car_id = '". $car_id ."'";
    $result = getQuery($query);
    return parseCarBooking( $result);
}

function parseCarBooking(&$my_array){
    foreach($my_array as &$object){
        $object->car = getCarById($object->car_id);
        unset($object->car_id);
    }

    return $my_array;
}

function getQuery($query){
    $database = connectDB();
    $apotelesma = $database->query($query);
    $array = array();
    if($apotelesma == false){
        return $array;
    }
    while($element = mysqli_fetch_object($apotelesma)){
        $array[] = $element;
    }
    $database->close();
    return $array;
}

function getCarById($id){
    $query = "select * from cars_table where id = '". $id."'";//Prepei na doume gia slq injection
    $apotelesma = getQuery($query);

    $to_return = null;
    if(count($apotelesma)>0){
        $to_return = $apotelesma[0];
        // $to_return->next_bookings;
    }
    return $to_return;
}
function appendNextBooking(&$cars_array){
    foreach($cars_array as &$car){
        $car->next_booking = getCarNextBookings($car->id);
    }
}
function getCarByPlate($plate){
    $query = "select * from cars_table where plate = '" . $plate."'";
    return getQuery($query);
}

function getCarByName($name){
    $query = "select * from cars_table where name like  '" . $name. "'";
    $result = getQuery($query);
    // $result->next_bookings = getCarNextBookings($result->id);
    return $result;
}

function getBookingByCarName($name){
    $query = "select bookings.id as id, date_from, date_to, car_id, pickup_point, dropoff_point ,telephone, client_name, address, paid
    from bookings, cars_table
     where car_id = cars_table.id and cars_table.name like '". $name ."'";
    return getQuery($query);
}

function removeBooking($booking_id){
    $query = "delete from bookings where id = '".$booking_id."'";
    executeQuery($query);
}
function addCar($name, $plate){
    $query = "insert into cars_table (name,plate) values ('".$name."','".$plate."')";
    return executeQuery($query);
}

function removeCar($car_id){
    $query = "delete from cars_table where id='".$car_id."'";//SECONDARY KEY
    executeQuery($query);
    $query = "delete from bookings where car_id = '".$car_id."'";
    executeQuery($query);
}
function addBooking($date_from, $date_to, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid){
    $query = "insert into bookings (date_from, date_to, car_id, pickup_point, dropoff_point, client_name, telephone, address, paid) values ('".$date_from."','".$date_to."','".$car_id."','".$pick_up_point."','".$drop_off_point."','".$client_name."','".$tel."','".$address."','".$paid."')";
    echo $query;
    executeQuery($query);
}
function updateBooking($id, $date_from, $date_to, $car_id, $pick_up_point, $drop_off_point, $client_name, $tel, $address, $paid){
    $query = "update bookings 
    set date_to = '".$date_to.
    "', date_from='".$date_from.
    "', car_id = '".$car_id.
    "', pickup_point = '".$pick_up_point.
    "', dropoff_point = '".$drop_off_point.
    "', client_name = '".$client_name.
    "', telephone = '".$tel.
    "', address = '".$address.
    "', paid = '".$paid.
    "' where id = '".$id."'";
    echo $query;
    executeQuery($query);
}
function getCurrentBookings(){
    $current_time = time();
    $query = "select * from bookings where date_from<".$current_time." and date_to>".$current_time."";
    $result = getQuery($query);
    parseCarBooking($result);
    return $result;
}
function getPastBookings(){
    $current_time = time();
    $query = "select * from bookings where date_to<".$current_time."";
    $result = getQuery($query);
    parseCarBooking($result);
    return $result;
}
function getFutureBookings(){
    $current_time = time();
    $query = "select * from bookings where date_from>".$current_time."";
    $result = getQuery($query);
    parseCarBooking($result);
    return $result;
}
function getCarNextBookings($car_id){
    $current_time = time();
    $query = "select * from bookings where car_id = '".$car_id."' and date_to > '".$current_time."' order by date_from asc";
    $result = getQuery($query);
    parseCarBooking($result);
    return $result;

}

function executeQuery($query){
    $database = connectDB();
    $database->query($query);
    $insert_id = $database->insert_id;
    $database->close();
    return $insert_id;
}

function getAvailableCars(){
    $current_time = time();

    $query = "select * from cars_table where id not in (
        select car_id as id from cars_table, bookings where cars_table.id = bookings.car_id and (date_from<".$current_time." and date_to>".$current_time.")
    ) ";
    $result = getQuery($query);
    appendNextBooking($result);
    return $result;
}


?>