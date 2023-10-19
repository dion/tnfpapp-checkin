'use strict';

var pantryApp = angular.module('pantryApp', ['ngRoute']);

/*****************CONFIG AND RUN*****************/
pantryApp.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'loginCtrl'});
  $routeProvider.when('/search', {templateUrl: 'partials/searchclient.html', controller: 'navCtrl'});
  $routeProvider.when('/settings', {templateUrl: 'partials/settings.html', controller: 'setCtrl'});
  $routeProvider.when('/add', {templateUrl: 'partials/addclient.html'});
  $routeProvider.when('/reports', {templateUrl: 'partials/reports.html'});
  $routeProvider.when('/vol', {templateUrl: 'partials/volunteers.html'});
  $routeProvider.when('/edit/:id', {templateUrl: 'partials/editclient.html'});
  $routeProvider.otherwise({redirectTo: '/login'});
}]);

pantryApp.run(function($rootScope, $location, loginService){
	var routespermission = ['/search', '/add', '/edit', '/reports', '/vol'];  //route that require login
	var adminroutes = ['/reports', '/vol'];
	$rootScope.$on('$routeChangeStart', function(){
		
		if( routespermission.indexOf($location.path()) != -1){ // if you can find the current route in the routespermission array	
		
			// if they are on an admin route and are not an admin then redirect to search
			if(adminroutes.indexOf($location.path()) != -1 && sessionStorage['role'] != 'admin'){
				$location.path('/search');
			} 
			
			// if they are on any other route just check if they are logged in
			var connected = loginService.islogged();
			
			connected.then(function(msg){
				if(!msg.data) $location.path('/login');
			});
			
		}
	});
});
/***********************CONTROLLERS***************/
pantryApp.controller('logoutController', ['$scope','$location','loginService', function($scope,$location,loginService){
	
	$scope.logout=function(){
		$scope.authenticated = false;
		loginService.logout();
	}
	
	$scope.isActive = function (viewLocation) { 
        return viewLocation === $location.path();
    };
	
	$scope.userRole = function(role){
		return role === sessionStorage['role'];
	};
	
}]);

pantryApp.controller('loginCtrl', ['$scope','$location','loginService', function ($scope,$location,loginService) {
	
	$scope.login=function(data){
		loginService.login(data,$scope); //call login service
	};
	
	$scope.isActive = function (viewLocation) { 
        return viewLocation === $location.path();
    };
	
}]);

pantryApp.controller('setCtrl', function($scope, $http){
	
	$scope.itemTypeOptions = [
		{ name: 'Weight', value: 'Weight' }, 
		{ name: '# of items', value: 'Number' }
    ];
	
	$scope.placeOfServiceOptions = [
		{ name: 'Food pantry', value: 'Food pantry' }, 
		{ name: 'Clothing Bank', value: 'Clothing Bank' },
		{ name: 'Mobile Resource Center', value: 'Mobile Resource Center' },
		{ name: 'Other', value: 'Other' }
    ];
    
	$scope.form = {
		itemType : $scope.itemTypeOptions[0].value, 
		pof : $scope.placeOfServiceOptions[0].value
	};
	
	var urlSettings = 'php/settings.php';
	
	$http({
		method: 'POST',
		url: urlSettings,
		headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
	})
	
	.success(function(data, status) {
		$scope.items = data.items;
	})
	
	.error(function(data, status) {
		$scope.data = data || "Request failed";
		$scope.status = status;			
	});
	
	$scope.deleteItem = function (id) {
		
		var thisData = "id=" + id;
		thisData += "&items=delete";
				
		$http({
			method: 'POST',
			url: urlSettings,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.items = data.items;
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});	
	};
	
	$scope.insert = function(){
		var thisData = "items=insert";
		thisData += "&name=" + $scope.name;
		thisData += "&sortOrder=" + $scope.sortOrder;
		thisData += "&itemType=" + $scope.form.itemType;
		thisData += "&pof=" + $scope.form.pof;

		$http({
			method: 'POST',
			url: urlSettings,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.items = data.items;
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;	
		});	

	};
	
});

pantryApp.controller('editController', function ($scope, $http, $routeParams, $location){ 

	$scope.placeOfServiceOptions = [
		{ name: 'Food pantry', value: 'Food Pantry' }, 
		{ name: 'Clothing Bank', value: 'Clothing Bank' },
		{ name: 'Mobile Resource Center', value: 'Mobile Resource Center' },
		{ name: 'Other', value: 'Other' }
    ];
    
    $scope.form = {placeOfService : $scope.placeOfServiceOptions[0].value};
	
	var urlEdit = 'php/edit.php';	
	var urlUpdate = 'php/update.php';	
	var urlExport = 'php/export.php';
	var urlDelete = 'php/delete.php';
	var urlSettings = 'php/settings.php';

	// load initial item list based on first item in place of service
	var thisData = "items=changed";
	thisData += "&pof=" + $scope.placeOfServiceOptions[0].value;

	$http({
		method: 'POST',
		url: urlSettings,
		data: thisData,
		headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
	})

	.success(function(data, status) {
		$scope.programOptions = data.items;
		
		console.log(data.items, 'data.items')
	})
	
	.error(function(data, status) {
		$scope.data = data || "Request failed";
		$scope.status = status;			
	});
	
	var id = "id=" + $routeParams.id;
	
	$http({
		method: 'POST',
		url: urlEdit,
		data: id,
		headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
	})
	
	.success(function(data, status) {
		if(data.client){
			console.log('data ',data)
			$scope.orderByField = 'dateOfVisit';
			$scope.reverseSort = true;
			
			$scope.client = data.client;
			$scope.visits = data.visits;			
			
			$scope.volunteers = data.volunteers;
			$scope.client[0].ageGroups = $scope.client[0].ageGroups.split(',');
			
			console.log($scope.visits)
		}
	})
	
	.error(function(data, status) {
		$scope.data = data || "Request failed";
		$scope.status = status;			
	});
	
	$scope.sort = {
            column: '',
            descending: false
    };
						
	$scope.changeSorting = function(column) {

            var sort = $scope.sort;
 
            if (sort.column == column) {
                sort.descending = !sort.descending;
            } else {
                sort.column = column;
                sort.descending = false;
            }
    };

	$scope.update = function(){
	
			$.validate({
			    validateOnBlur : true, // disable validation when input looses focus
    			scrollToTopOnError : true, // Set this property to true if you have a long form
				onError : function() {
				  console.log('Validation failed');
				},
				onSuccess : function() {
						var address = (typeof $scope.client[0].address === 'undefined') ? '' :  $scope.client[0].address;
						var city = (typeof $scope.client[0].city === 'undefined') ? '' :  $scope.client[0].city;
						var postalCode = (typeof $scope.client[0].postalCode === 'undefined') ? '' :  $scope.client[0].postalCode;
						var phone = (typeof $scope.client[0].phone === 'undefined') ? '' :  $scope.client[0].phone;
						var email = (typeof $scope.client[0].email === 'undefined') ? '' :  $scope.client[0].email;
						var lastDateWorked = (typeof $scope.client[0].lastDateWorked === 'undefined') ? '' :  $scope.client[0].lastDateWorked;
						var annualIncome = (typeof $scope.client[0].annualIncome === 'undefined') ? '' :  $scope.client[0].annualIncome;
						var incomeUpdated = (typeof $scope.client[0].incomeUpdated === 'undefined') ? '' :  $scope.client[0].incomeUpdated;
						var inHouse = (typeof $scope.client[0].inHouse === 'undefined') ? '' :  $scope.client[0].inHouse;
						var ageGroups = (typeof $scope.client[0].ageGroups === 'undefined') ? '' :  $scope.client[0].ageGroups;
						var comments = (typeof $scope.client[0].comments === 'undefined') ? '' :  $scope.client[0].comments;
						///////////////////
						var numMales = $scope.client[0].howManyMales;
						var numFemales = $scope.client[0].howManyFemales;
						var totalInHouse = parseInt(numMales) + parseInt(numFemales);
						var flag = false;
						
						if(typeof numMales != 'undefined'){
							flag = true;
						}
						if(typeof numFemales != 'undefined'){
							flag = true;
						}
						if(typeof $scope.inHouse != 'undefined'){
							flag = true;
						}
						
						if(flag == true){
							if(parseInt(inHouse) != totalInHouse){									
								if(inHouse < 10){
									alert('The number of males and females do not equal the total in house');
									return false;
								}
							}
						}
//////////////////
						var thisData = id;
						thisData += "&fname=" + $scope.client[0].fname;
						thisData += "&lname=" + $scope.client[0].lname;
						thisData += "&address=" + address;
						thisData += "&city=" + city;
						thisData += "&state=" + $scope.client[0].state;
						thisData += "&postalCode=" + postalCode;
						thisData += "&phone=" + phone;
						thisData += "&email=" + email;
						thisData += "&employed=" + $scope.client[0].employed;
						thisData += "&lastDateWorked=" + lastDateWorked;
						thisData += "&annualIncome=" + annualIncome;
						thisData += "&incomeUpdated=" + incomeUpdated;
						thisData += "&inHouse=" + inHouse;
						thisData += "&howManyMales=" + numMales;
						thisData += "&howManyFemales=" + numFemales;
						thisData += "&ageGroups=" + ageGroups;
						thisData += "&comments=" + comments;
								
						$http({
							method: 'POST',
							url: urlUpdate,
							data: thisData,
							headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
						})
						
						.success(function(data, status) {
							$location.path('/search');
						})
						
						.error(function(data, status) {
							$scope.data = data || "Request failed";
							$scope.status = status;			
						});				
				}
		  });
	
	};
	
	$scope.addVisit = function(){
		var place = $scope.form.placeOfService;
		var visit = $scope.dateOfVisit;
		var visits = $scope.visits;
		var duplicate = false;
					
					var dateOfVisit = $('#dateOfVisit').val();
					var weight = $('#weight').val();
					var program = $('#program option:selected').text();
					var volunteer = $('#volunteer').val();
					
					if(dateOfVisit && program && volunteer){
	
								var thisData = id;
								thisData += "&lname=" + $scope.client[0].lname;
								thisData += "&fname=" + $scope.client[0].fname;
								thisData += "&inHouse=" + $scope.client[0].inHouse;
								thisData += "&phone=" + $scope.client[0].phone;
								thisData += "&email=" + $scope.client[0].email;
						
								thisData += "&placeOfService=" + $scope.form.placeOfService;
								thisData += "&dateOfVisit=" + $scope.dateOfVisit;
								thisData += "&program=" + program;
								thisData += "&numBags=" + $scope.numBags;
								thisData += "&weight=" + $scope.weight;
								thisData += "&numOfItems=" + $scope.numOfItems;
								var visitNotes = typeof $scope.visitNotes == "undefined" ? "" : $scope.visitNotes;
								thisData += "&visitNotes=" + visitNotes;
								thisData += "&volunteer=" + $scope.volunteer.volunteer;
							
								$http({
									method: 'POST',
									url: urlUpdate,
									data: thisData,
									headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
								})
								
								.success(function(data, status) { 
									if(data.client){
										
										var visits = data.client;	
										visits.sort(compare);
										
										console.log('saved new visit',visits)
										
										$scope.visits = visits;
										
										// Clear form
										$scope.dateOfVisit = "";
									//	$scope.form.placeOfService = $scope.placeOfServiceOptions[0].value;
										$scope.program = "";
										$scope.weight = "";
										$scope.numOfItems = "";
										$scope.visitNotes = "";
										$scope.volunteer = "";
										
									}
									window.location.reload();
								})
								
								.error(function(data, status) {
									$scope.data = data || "Request failed";
									$scope.status = status;			
								});	
								
								
					} else {
							$('.fields').show();
							setTimeout(function(){ $('.fields').hide(); }, 10000);
							return false;
					}
	
	};	
	
	$scope.deleteVisit = function (visitid) {
		
		var thisData = id;
		thisData += "&visitid=" + visitid;
		thisData += "&visits=delete";
				
		$http({
			method: 'POST',
			url: urlDelete,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.visits = data.visits;
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});
	};
	
	$scope.changeItems = function(){
		
		var thisData = "items=changed";
		thisData += "&pof=" + $scope.form.placeOfService;
		
		$http({
			method: 'POST',
			url: urlSettings,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.programOptions = data.items;
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});
	}
	
	$scope.showType = function(){		
		$scope.itemType = function(type){
			return type === $scope.program.itemType;
		};
	}

	function compare(a,b) {
	  if (a.dateOfVisit > b.dateOfVisit)  
		 return -1;
	  if (a.dateOfVisit < b.dateOfVisit)
		return 1;
		
	  return 0;
	}

});

pantryApp.controller('searchController', function ($scope, $http){ 
	var urlSearch = 'php/search.php';	
	var urlDelete = 'php/delete.php';	
	
	$scope.typeOptions = [
		{ name: 'Contains', value: 'contains' }, 
		{ name: 'Starts With', value: 'startsWith' }
    ];
    
    $scope.form = {idType : $scope.typeOptions[0].value, fnameType : $scope.typeOptions[0].value, lnameType : $scope.typeOptions[0].value, 
					addressType : $scope.typeOptions[0].value, phoneType : $scope.typeOptions[0].value, emailType : $scope.typeOptions[0].value,
					numInHouseType : $scope.typeOptions[0].value, commentsType : $scope.typeOptions[0].value};
					
	$scope.sort = {
            column: '',
            descending: false
    };
						
	$scope.changeSorting = function(column) { console.log('column ',column)

            var sort = $scope.sort;
 
            if (sort.column == column) {
                sort.descending = !sort.descending;
            } else {
                sort.column = column;
                sort.descending = false;
            }
    };
	
	$scope.search = function(){ 
		var thisData = "id=" + $scope.clientID;
		thisData += "&idType=" + $scope.form.idType;
		thisData += "&fname=" + $scope.fname;
		thisData += "&fnameType=" + $scope.form.fnameType;
		thisData += "&lname=" + $scope.lname;
		thisData += "&lnameType=" + $scope.form.lnameType;
		thisData += "&address=" + $scope.address;
		thisData += "&addressType=" + $scope.form.addressType;
		thisData += "&phone=" + $scope.phone;
		thisData += "&phoneType=" + $scope.form.phoneType;
		thisData += "&email=" + $scope.email;
		thisData += "&emailType=" + $scope.form.emailType;
		thisData += "&numInHouse=" + $scope.numInHouse;
		thisData += "&numInHouseType=" + $scope.form.numInHouseType;
		thisData += "&comments=" + $scope.comments;
		thisData += "&commentsType=" + $scope.form.commentsType;
		
		$http({
			method: 'POST',
			url: urlSearch,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			if(data.clients){
				$scope.clients = data.clients;	
			}
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});	

	};	
	
	$scope.deleteModal = function(id){
		$('.ui-widget-overlay').css('display','block');
		$('#myModal').addClass('in');
		
		$(".delete").on("click", function(e){
			$('#myModal').removeClass('in');
			$('.ui-widget-overlay').css('display','none');
			
			deleteClient(id);
		});
		
		$(".close").on("click", function(e){
			$('#myModal').removeClass('in');
			$('.ui-widget-overlay').css('display','none');
		});
		
		$(".cancel").on("click", function(e){
			$('#myModal').removeClass('in');
			$('.ui-widget-overlay').css('display','none');
			
		});
		
		$(".ui-widget-overlay").on("click", function(e){
			$('#myModal').removeClass('in');
			$('.ui-widget-overlay').css('display','none');
		});
		
	};
	
	
	var deleteClient = function (id) {
		
		var thisData = "id=" + id;
				
		$http({
			method: 'POST',
			url: urlDelete,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {			
			var removeByAttr = function(arr, attr, value){
				var i = arr.length;
				while(i--){
				   if( arr[i] 
					   && arr[i].hasOwnProperty(attr) 
					   && (arguments.length > 2 && arr[i][attr] === value ) ){ 

					   arr.splice(i,1);

				   }
				}
				return arr;
			}
			
			removeByAttr($scope.clients, 'id', id.toString());
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});	
	};
});

pantryApp.controller('insertController', function ($scope, $http, $location){
	
	$scope.placeOfServiceOptions = [
		{ name: 'Food pantry', value: 'Food pantry' }, 
		{ name: 'Clothing Bank', value: 'Clothing Bank' },
		{ name: 'Mobile Resource Center', value: 'Mobile Resource Center' },
		{ name: 'Other', value: 'Other' }
    ];
    
    $scope.form = {placeOfService : $scope.placeOfServiceOptions[0].value};

	var urlInsert = 'php/insert.php';
	var urlVolunteers = 'php/volunteers.php';
	var urlSearch = 'php/search.php';
	var urlSettings = 'php/settings.php';

	// load initial item list based on first item in place of service
	var thisData = "items=changed";
	thisData += "&pof=" + $scope.placeOfServiceOptions[0].value;

	$http({
		method: 'POST',
		url: urlSettings,
		data: thisData,
		headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
	})

	.success(function(data, status) {
		$scope.programOptions = data.items;
	})
	
	.error(function(data, status) {
		$scope.data = data || "Request failed";
		$scope.status = status;			
	});
	
	$http.get(urlSearch).
	  success(function(data, status, headers, config) {
		$scope.clientID = data.id;
	  }).
	  error(function(data, status, headers, config) {
			$scope.data = data;
	  });
	  
	$http.get(urlVolunteers).
	  success(function(data, status, headers, config) {
		$scope.volunteers = data.volunteers;
	  }).
	  error(function(data, status, headers, config) {
			$scope.data = data;
	  });
		
	$scope.insert = function(){

		  $.validate({
			    validateOnBlur : true, // disable validation when input looses focus
    			scrollToTopOnError : true, // Set this property to true if you have a long form
				onError : function() {
				  console.log('Validation failed');
				},
				onSuccess : function() {
					
					var address = (typeof $scope.address === 'undefined') ? '' :  $scope.address;
					var city = (typeof $scope.city === 'undefined') ? '' :  $scope.city;
					var postalCode = (typeof $scope.postalCode === 'undefined') ? '' :  $scope.postalCode;
					var phone = (typeof $scope.phone === 'undefined') ? '' :  $scope.phone;
					var email = (typeof $scope.email === 'undefined') ? '' :  $scope.email;
					var lastDateWorked = (typeof $scope.lastDateWorked === 'undefined') ? '' :  $scope.lastDateWorked;
					var annualIncome = (typeof $scope.annualIncome === 'undefined') ? '' :  $scope.annualIncome;
					var incomeUpdated = (typeof $scope.incomeUpdated === 'undefined') ? '' :  $scope.incomeUpdated;
					var inHouse = (typeof $scope.inHouse === 'undefined') ? '' :  $scope.inHouse;
					var ageGroups = (typeof $scope.ageGroups === 'undefined') ? '' :  $scope.ageGroups;
					var comments = (typeof $scope.comments === 'undefined') ? '' :  $scope.comments;
					
					var numMales = $scope.howManyMales;
					var numFemales = $scope.howManyFemales;
					
					var totalInHouse = parseInt(numMales) + parseInt(numFemales);
					var flag = false;
					
					if(typeof numMales != 'undefined'){
						flag = true;
					}
					if(typeof numFemales != 'undefined'){
						flag = true;
					}
					if(typeof $scope.inHouse != 'undefined'){
						flag = true;
					}
					
					if(flag == true){
						if(parseInt(inHouse) != totalInHouse){
							if(inHouse < 10){
								alert('The number of males and females do not equal the total in house');
								return false;
							}
						}
					}
					
					var program = $('#program option:selected').text();

					var thisData = "id=" + $scope.clientID;
					thisData += "&fname=" + $scope.fname;
					thisData += "&lname=" + $scope.lname;
					thisData += "&address=" + address;
					thisData += "&city=" + city;
					thisData += "&state=" + $scope.state;
					thisData += "&postalCode=" + postalCode;
					thisData += "&phone=" + phone;
					thisData += "&email=" + email;
					thisData += "&employed=" + $scope.employed;
					thisData += "&lastDateWorked=" + lastDateWorked;
					thisData += "&annualIncome=" + annualIncome;
					thisData += "&incomeUpdated=" + incomeUpdated;
					thisData += "&inHouse=" + inHouse;
					thisData += "&howManyMales=" + numMales;
					thisData += "&howManyFemales=" + numFemales;
					thisData += "&ageGroups=" + ageGroups;
					thisData += "&comments=" + comments;
					
					thisData += "&placeOfService=" + $scope.form.placeOfService;
					thisData += "&dateOfVisit=" + $scope.dateOfVisit;
					thisData += "&program=" + program;
					thisData += "&numBags=" + $scope.numBags;
					thisData += "&weight=" + $scope.weight;
					thisData += "&numOfItems=" + $scope.numOfItems;
					var visitNotes = typeof $scope.visitNotes == "undefined" ? "" : $scope.visitNotes;
					thisData += "&visitNotes=" + visitNotes;					
					thisData += "&volunteer=" + $scope.volunteer.volunteer;
							
					$http({
						method: 'POST',
						url: urlInsert,
						data: thisData,
						headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
					})
					
					.success(function(data, status) {
						if ($scope.clientID) {
							window.location = "https://www.tnfpapp.org/#/edit/" + $scope.clientID;
						} else {
							window.location.reload();
						}
						if(data.error){
							$('#databaseMsg').html(data.message);
							$('.alertt').css('display','block');
							window.scrollTo(0, 0);
						} else{
							$location.path('/search');
						}
					})
					
					.error(function(data, status) {
						$scope.data = data || "Request failed";
						$scope.status = status;			
					});		
			
				}
		  });  
		 
	};
	
	$scope.changeItems = function(){
		
		var thisData = "items=changed";
		thisData += "&pof=" + $scope.form.placeOfService;
		
		$http({
			method: 'POST',
			url: urlSettings,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.programOptions = data.items;
				
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});
	}
	
	$scope.showType = function(){		
		$scope.itemType = function(type){
			return type === $scope.program.itemType;
		};
	}
	
});

pantryApp.controller('volunteerController', function ($scope, $http){ 
	var urlVolunteers = 'php/volunteers.php';
	var urlDelete = 'php/delete.php';
	var thisData = "volunteers=all";
	
	$http({
		method: 'POST',
		url: urlVolunteers,
		data: thisData,
		headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
	})
	
	.success(function(data, status) {
		$scope.volunteers = data.volunteers;
	})
	
	.error(function(data, status) {
		$scope.data = data || "Request failed";
		$scope.status = status;			
	});
	
	$scope.deleteVolunteer = function (id) {
		
		var thisData = "id=" + id;
		thisData += "&volunteers=delete";
				
		$http({
			method: 'POST',
			url: urlVolunteers,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.volunteers = data.volunteers;
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});	
	};
	
	$scope.toggle = function(id, active){ 
		var thisData = "id=" + id;	
		thisData += "&volunteers=update";
		thisData += "&active=" + active;
				
		$http({
			method: 'POST',
			url: urlVolunteers,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.volunteers = data.volunteers;
			//$location.path('/search');
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});	

	};
	
	$scope.insert = function(){
		var thisData = "volunteers=insert";
		thisData += "&volunteer=" + $scope.volunteer;
		thisData += "&active=" + $scope.active;

		$http({
			method: 'POST',
			url: urlVolunteers,
			data: thisData,
			headers : {'Content-Type' : 'application/x-www-form-urlencoded'}
		})
		
		.success(function(data, status) {
			$scope.volunteers = data.volunteers;
		})
		
		.error(function(data, status) {
			$scope.data = data || "Request failed";
			$scope.status = status;			
		});	

	};
	

});

pantryApp.controller('reportsController', function ($scope, $http, $routeParams, $location){ 

	$scope.placeOfServiceOptions = [
		{ name: 'Food pantry', value: 'Food Pantry' }, 
		{ name: 'Clothing Bank', value: 'Clothing Bank' },
		{ name: 'Mobile Resource Center', value: 'Mobile Resource Center' },
		{ name: 'Other', value: 'Other' }
    ];
    
    $scope.form = {placeOfService : $scope.placeOfServiceOptions[0].value};
	
});

pantryApp.controller('navCtrl', ['$scope', '$location', function ($scope, $location) {

    $scope.navClass = function (page) {
        var currentRoute = $location.path().substring(1) || 'search';
        return page === currentRoute ? 'active' : '';
    }; 

	$scope.isActive = function (viewLocation) { 
        return viewLocation === $location.path();
    };
	
	$scope.userRole = function(role){
		return role === sessionStorage['role'];
	};

}]);
/***********************SERVICES***************/
pantryApp.factory('loginService',function($rootScope, $http, $location, sessionService){
	return{
		login:function(data,scope){
			
			var $promise = $http.post('php/user.php',data);
			
			$promise.then(function(msg){
				console.log(msg);
				
				var role = msg.data.role;				
				var uid = msg.data.uid;
				
				if(uid){
					$rootScope.authenticated = true;
					
					sessionService.set('uid',uid);
					sessionService.set('role',role);
										
					$location.path('/search');
				}	       
				else  {
					scope.msgtxt = 'incorrect information';
					$location.path('/login');
				}				   
			});
			
			
		},
		
		logout:function(){
			sessionService.destroy('uid', 'role');
			$location.path('/login');
		},
		islogged:function(){
			var $checkSessionServer = $http.post('php/check_session.php');
			return $checkSessionServer;
		}
	}

});
pantryApp.factory('sessionService', ['$http', function($http){
	return{
		set:function(key,value){
			return sessionStorage.setItem(key,value);
		},
		get:function(key){
			return sessionStorage.getItem(key);
		},
		destroy:function(key, role){
			$http.post('php/destroy_session.php');
			sessionStorage.removeItem(role);
			return sessionStorage.removeItem(key);
		}
	};
}]);