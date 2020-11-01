var parking_app=angular.module("parking",[]);

parking_app.controller("parkingController", function($scope,$http){
    $scope.timenow = new Date();

    $scope.cars = [];
    $scope.bookings = [];
    $scope.newCar = {};
    $scope.newBooking = {};
    $scope.filter = {};
    $scope.currentBooking = {};
    $scope.nextBookings = [];

    var api_get = "http://192.168.33.10/car_rental/server/get.php";
    var api_post = "http://192.168.33.10/car_rental/server/post.php";
    var token = "mytoken";



    
    $scope.setCurrentBooking = function(booking){
        $scope.currentBooking = booking;
    }
    $scope.setCarNextBooking = function(bookings){
        $scope.nextBookings = bookings;
    }

    $scope.getAllCars = function(){

       $http.get(api_get + '?action=get_all_cars&token='+token)
       .then(
           function(data){
               console.log(data.data);
               $scope.cars = data.data;
           },
           function (data){
               console.log(data);
           }
       );
    };
    $scope.getAvailableCars =  function(){
        $http.get(api_get + '?action=get_available_cars&token='+token)
        .then(
            function(data){
                console.log(data.data);
                $scope.cars = data.data;
            },
            function (data){
                console.log(data);
            }
        );
    };

    $scope.getAllBookings = function(){

        $http.get(api_get + '?action=get_all_bookings&token='+token)
        .then(
            function (data){
                $scope.bookings = data.data;
                console.log(data.data);
            },
            function (data){
                console.log(data);//TODO NA DOUME TI PAIZEI prepei na allaksoume tin grafiki diepafi
            }
        );
    };
    $scope.getCurrentBookings = function(){
        $http.get(api_get +'?action=get_current_bookings&token='+token)
        .then(
            function (data){
                $scope.bookings = data.data;
                console.log(data.data);
            },
            function (data){
                console.log(data);//TODO NA DOUME TI PAIZEI prepei na allaksoume tin grafiki diepafi
            }
        );
    }
    $scope.getPastBookings = function(){
        $http.get(api_get + '?action=get_past_bookings&token='+token)
        .then(
            function (data){
                $scope.bookings = data.data;
                console.log(data.data);
            },
            function (data){
                console.log(data);//TODO NA DOUME TI PAIZEI prepei na allaksoume tin grafiki diepafi
            }
        );
    }
    $scope.getFutureBookings = function(){
        $http.get(api_get + '?action=get_future_bookings&token='+token)
        .then(
            function (data){
                $scope.bookings = data.data;
                console.log(data.data);
            },
            function (data){
                console.log(data);//TODO NA DOUME TI PAIZEI prepei na allaksoume tin grafiki diepafi
            }
        );
    }
    $scope.getBookingsByCarId = function (carId){
        console.log(carId);
        $http.get(api_get + '?action=get_booking_by_car_id&car_id='+carId+'&token='+token)
        .then(
            function (data){
                console.log(data.data);
                $scope.bookings = data.data;
            },
            function (data){
                console.log(data);//TODO NA DOUME TI PAIZEI prepei na allaksoume tin grafiki diepafi
            }
        );
    }
    
    $scope.addCar = function(name, plate){
        var car = {
            'name': name,
            'plate': plate
        };
        var attrs = {
            'action':'add_car',
            'car': car,
            'token': token
        };
        console.log(attrs);
        var config = {
            headers : {
                'Content-Type': 'application/json'
            }
        };
        $http.post(api_post, attrs, config)
        .then(function (data, status, headers, config){
            console.log(data.data);
            $scope.cars.push(data.data);
            $scope.newCar = {};
        });

    };
    $scope.updateBooking = function(booking){
        var attrs = {
            'action':'update_booking',
            'booking': booking,
            'token': token
        };
        console.log(attrs);
        var config = {
            headers : {
                'Content-Type': 'application/json'
            }
        };
        $http.post(api_post, attrs, config)
        .then(function (data, status, headers, config){
            console.log("to booking enimerothike");
            console.log(data.data);
            $scope.getAllBookings();
        });
    }
    $scope.addBooking = function(booking){
        console.log(booking);
        booking.date_from = new Date(booking.date_from).getTime()/1000;
        booking.date_to = new Date(booking.date_to).getTime()/1000;
        
        
        var attrs = {
            'action'    :   'add_booking',
            'booking'   :   booking,
            'token'     :   token
        };
        var config = {
            headers : {
                'Content-Type': 'application/json'
            }
        };        
        $http.post(api_post, attrs, config)
        .then(
            function (data){
                $scope.getAllBookings();
                $scope.newBooking = {};
                console.log(data.data);
            },
            function (data){
                console.log(data);
            }
        );
    };

    $scope.removeCar = function(carId){

        console.log("mpikame");
        var attrs = {
            'action'    :   "remove_car",
            'car_id'    :   carId,
            'token'     :   token
        };
        var config = {
            headers : {
                'Content-Type': 'application/json'
            }
        };
        
        $http.post(api_post, attrs, config)
        .then(function (data, status, headers, config){
            console.log("to autokitino diagraftike");
            $scope.getAllCars();
            $scope.getAllBookings();
        });

    };

    $scope.removeBooking = function(bookingId){
        console.log("removing booking");
        var attrs = {
            'action'        :   "remove_booking",
            'booking_id'    :   bookingId,
            'token'         :   token
        };
        var config = {
            headers : {
                'Content-Type': 'application/json'
            }
        };
        
        $http.post(api_post, attrs, config)
        .then(function (data, status, headers, config){
            console.log("to booking diagraftike");
            $scope.getAllBookings();
        });
    };

    $scope.getAllCars();
    $scope.getAllBookings();

});