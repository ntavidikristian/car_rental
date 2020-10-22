<?php
    header("Access-Control-Allow-Origin: *");
    require_once("credentials.php");
    $database = connectDB();
    $contents = file_get_contents('php://input');
    

    if ( isset($_GET['action'] ) ){
        $action = $_GET['action'];
        switch($action){
            case 'get_all_cars':
                getAllCars();
                break;
            case 'get_all_bookings':
                getAllBookings();
                break;
            case 'add_car':
                if(isset($_GET['plate']) && isset($_GET['name']) ){
                    $plate = $_GET['plate'];
                    $name = $_GET['name'];
                    $result_id = addCar($name, $plate);
                    echo json_encode(getCarById($result_id));
    
                }else{
                    die("die mf");
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
                    die("die mf");//TODO PREPEI NA TO DOUME
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
                    die("mf");//TODO PREPEI NA TO DOUME
                }
                break;
            default:
                // echo $action;
                break;
        }
    }else if(isset($_POST['action'])){
        
        $action = $_POST['action'];
        echo $action;

        switch($action){
            case 'remove_car':  
                if(isset($_POST['car_id'])){
                    $car_id = $_POST['car_id'];
                    removeCar($car_id);
                }
                break;
        }


    }else{
        if(count ($_POST)>0){
            echo"ecoume";
            
        }else{
            echo "den exoume";
            $params = json_decode(file_get_contents('php://input'),true);
            echo file_get_contents('php://input');
        }
    }

    

    // echo $_POST['action'];

?>