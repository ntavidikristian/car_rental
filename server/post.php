<?php
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: Content-Type");

    require_once("credentials.php");
    $database = connectDB();
    $contents = json_decode(file_get_contents('php://input'), true);
    
    $USE_AUTHENTICATION = true;
    @$token = $contents['token'];

    if($USE_AUTHENTICATION){
        if(! is_token_valid($token)){
            echo makeResponse(401, "Bad request. Invalid token");
            die();
        }
    }

    @$action = $contents['action'];

    switch($action){
        case 'remove_car':
            validate_writing($USE_AUTHENTICATION, $token);
            if(isset($contents['car_id'])){
                $car_id = $contents['car_id'];
                removeCar($car_id);
            }else{
                echo makeResponse(400, "To remove a car, car_id must be provided.");
            }
            
            break;
        case 'remove_booking':
            validate_writing($USE_AUTHENTICATION, $token);
            if(isset($contents['booking_id'])){
                $booking_id = $contents['booking_id'];//TODO NA TO DOUME NA KANOUME TESTAKIA AN GINETIA
                removeBooking($booking_id);
            }else{
                echo makeResponse(400, "To remove a booking, booking_id must be provided.");
            }

            break;
        case 'update_booking':
            validate_writing($USE_AUTHENTICATION, $token);
            $booking = $contents['booking'];
            if(isset($booking)){
                if(!isset($booking['id'])){
                    echo makeResponse(400, "Booking id not porovided");
                    break;
                }
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
            }else{
                echo makeResponse(400, "Bad request. Booking bot provided.");
            }
            
            break;
        case 'add_booking':
            validate_writing($USE_AUTHENTICATION, $token);

            $booking = $contents['booking'];
            if( !(isset($booking['date_from']) && isset($booking['date_to']) && isset($booking['car_id']))){
                echo makeResponse(400, "Bad request. To add a booking a date_from, date_to and car_id must be provided.");
                break;
            }
            $date_from = $booking['date_from'];
            $date_to = $booking['date_to'];
            $car_id = $booking['car_id'];


            $pickup_point = isset($booking['pickup_point']) ? $booking['pickup_point'] : null;
            $dropoff_point = isset($booking['dropoff_point']) ? $booking['dropoff_point'] : null;
            $client_name = isset($booking['client_name']) ? $booking['client_name'] : null;
            $telephone = isset($booking['telephone']) ? $booking['telephone']: null;
            $address = isset($booking['address']) ? $booking['address']: null;
            $paid = isset($booking['paid']) ? $booking['paid'] : null;

            if($paid != '1' && $paid!= '0'){
                $paid = '0';
            }
                        
            addBooking($date_from, $date_to, $car_id, $pickup_point, $dropoff_point, $client_name, $telephone, $address, $paid);
            break;

        case 'add_car':
            validate_writing($USE_AUTHENTICATION, $token);
            $car = $contents['car'];
            if ( !(isset($car['plate']) || !isset(car['name']) )){
                echo makeResponse(400, "Bad request. To add a new car a plate and a name must be provided");
                break;
            }

            $plate = $car['plate'];
            $name = $car['name'];
            $result_id = addCar($name, $plate);
            echo json_encode(getCarById($result_id));
            break;
        default:
            echo makeResponse(400, "Bad Request. No valid action");
            break;
    }

?>