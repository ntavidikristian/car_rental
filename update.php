<?php
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: Content-Type");

    require_once("credentials.php");
    $database = connectDB();
    $contents = json_decode(file_get_contents('php://input'),true);
    // echo $contents;
    // echo "swag";

    $action = $contents['action'];
    var_dump($contents);
    var_dump($action);
    switch($action){
        case 'remove_car':
            $car_id = $contents['car_id'];
            removeCar($car_id);
            break;
        case 'remove_booking':
            $booking_id = $contents['booking_id'];//TODO NA TO DOUME NA KANOUME TESTAKIA AN GINETIA
            removeBooking($booking_id);
            break;
        case 'update_booking':
            echo "updating booking";
            $booking = $contents['booking'];

            $id = $booking['id'];
            $date_from = $booking['date_from'];
            $date_to = $booking['date_to'];
            $car_id = $booking['car_id'];
            $pickup_point = $booking['pickup_point'];
            $dropoff_point = $booking['dropoff_point'];
            $client_name = $booking['client_name'];
            $tel = $booking['telephone'];
            $address = $booking['address'];
            $paid = $booking['paid'];

            updateBooking($id, $date_from,$date_to, $car_id, $pickup_point, $dropoff_point, $client_name, $tel, $address, $paid);
            break;
        case 'add_booking':
            $booking = $contents['booking'];
            
            $date_from = $booking['date_from'];
            $date_to = $booking['date_to'];
            $car_id = $booking['car_id'];
            $pickup_point = $booking['pickup_point'];
            $dropoff_point = $booking['dropoff_point'];
            $client_name = $booking['client_name'];
            $telephone = $booking['telephone'];
            $address = $booking['address'];
            $paid = $booking['paid'];

            if($paid != '1' || $paid!= '0'){
                $paid = '0';
            }
            
            addBooking($date_from, $date_to, $car_id, $pickup_point, $dropoff_point, $client_name, $telephone, $address, $paid);
            break;
    }


?>