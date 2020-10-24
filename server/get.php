<?php
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    
    require_once("credentials.php");
    $database = connectDB();
    $contents = file_get_contents('php://input');
    

    if ( isset($_GET['action'] ) ){
        $action = $_GET['action'];
        switch($action){
            case 'get_all_cars':
                $cars = getAllCars();
                echo json_encode($cars);
                break;
            case 'get_all_bookings':
                 $bookings = getAllBookings();
                 echo json_encode($bookings);
                break;
            case 'add_car':
                if(isset($_GET['plate']) && isset($_GET['name']) ){
                    $plate = $_GET['plate'];
                    $name = $_GET['name'];
                    $result_id = addCar($name, $plate);
                    echo json_encode(getCarById($result_id));
    
                }else{
                    echo makeResponse(400,"Bad request. To add a new car a plate=<plate_number> and name=<car_name> must be provided");
                }
                
                break;
            case 'add_booking':
                if(isset($_GET['date_from']) && isset($_GET['date_to']) && isset($_GET['car_id'])){
                    $date_from = $_GET['date_from'];
                    $date_to = $_GET['date_to'];
                    $car_id = $_GET['car_id'];
                    
                    $pickup_point = isset($_GET['pickup_point'])? $_GET['pickup_point']:null;
                    $dropoff_point = isset($_GET['dropoff_point'])?$_GET['dropoff_point']: null;
                    $client_name = isset($_GET['client_name'])?$_GET["client_name"]: null;
                    $tel = isset($_GET['telephone'])?$_GET["telephone"]: null;
                    $address = isset($_GET['address'])?$_GET["address"] : null;

                    //isws kanoume kai mia parapanw epeksergasia
                    addBooking($date_from,$date_to,$car_id, $pickup_point, $dropoff_point, $client_name, $tel, $address);    
                }else{
                    echo makeResponse(400, "Bad request. To add a booking a date_from, date_to, and car_id must be provided");
                }
                break;
            case 'get_available_cars':
                echo json_encode(getAvailableCars());
                break;

            case 'get_current_bookings':
                echo json_encode(getCurrentBookings());
                break;
            
            case 'get_past_bookings':
                echo json_encode(getPastBookings());
                break;
            case 'get_future_bookings':
                echo json_encode(getFutureBookings());
                break;
            case 'get_booking_by_car_id':
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