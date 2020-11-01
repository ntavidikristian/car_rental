<?php

    


    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    
    require_once("credentials.php");
    $database = connectDB();
    $contents = file_get_contents('php://input');
    $USE_AUTHENTICATION = true;
    @$token = $_GET['token'];

    if($USE_AUTHENTICATION){
        if(! is_token_valid($token)){
            echo makeResponse(401, "Bad request. Invalid token");
            die();
        }
    }

    

    if ( isset($_GET['action'] ) ){
        $action = $_GET['action'];
        switch($action){
            case 'get_all_cars':
                validate_reading($USE_AUTHENTICATION, $token);
                $cars = getAllCars();
                echo json_encode($cars);
                break;
            case 'get_all_bookings':
                validate_reading($USE_AUTHENTICATION, $token);
                $bookings = getAllBookings();
                echo json_encode($bookings);
                break;

            case 'get_available_cars':
                validate_reading($USE_AUTHENTICATION, $token);
                echo json_encode(getAvailableCars());
                break;

            case 'get_current_bookings':
                validate_reading($USE_AUTHENTICATION, $token);
                echo json_encode(getCurrentBookings());
                break;
            
            case 'get_past_bookings':
                validate_reading($USE_AUTHENTICATION, $token);
                echo json_encode(getPastBookings());
                break;
            case 'get_future_bookings':
                validate_reading($USE_AUTHENTICATION, $token);
                echo json_encode(getFutureBookings());
                break;
            case 'get_booking_by_car_id':
                validate_reading($USE_AUTHENTICATION, $token);
                if(isset($_GET['car_id'])){
                    $car_id = $_GET['car_id'];
                    $result = getBookingsByCarId($car_id);
                    echo json_encode($result);
                }else{
                    echo makeResponse(400, "Bad request. car_id not provided");
                }
                break;
            default:
                echo makeResponse(400, "Bad request");
                break;
        }
    }else{
        echo makeResponse(400, "Bad request");
    }


?>