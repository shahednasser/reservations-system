<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/", "CalendarController@showCalendar");

Route::get("/login", "GuestController@showLogin")->name("login");

Route::post("/login", "GuestController@login");

Route::get('/logout', "UserController@logout");

Route::get('/new-reservations', "ReservationsController@showNewReservations");

Route::get('/reservation/{id}', "ReservationsController@showReservation");

Route::post("/checkReservations/{id}", "ReservationsController@checkReservations");

Route::post("/send-edit-reservation/{id}", "ReservationsController@sendEditReservation");

Route::get("/view-reservation/{id}", "ReservationsController@viewReservationAdmin");

Route::get("/my-reservations", "ReservationsController@getUserReservations");

Route::get("/show-reservation/{id}", "ReservationsController@viewReservation");

Route::get("/delete-reservation/{id}", "ReservationsController@deleteReservation");

Route::get("/approve-edit/{id}", "ReservationsController@approveEdit");

Route::get("/edit-reservation/{id}", "ReservationsController@editReservation");

Route::post("/edit-reservation/{id}", "ReservationsController@postEditReservation");

Route::post("/approve-reservation/{id}", "ReservationsController@approveReservation");

Route::post("/reject-reservation/{id}", "ReservationsController@rejectReservation");

Route::get('/reject-request/{id}', "ReservationsController@rejectRequest");

Route::get("/add-reservation/{type}", "ReservationsController@addReservation");

Route::post("/add-reservation/{type}", "ReservationsController@postAddReservation");

Route::get("/admin-add-reservation", "ManualReservationController@adminAddReservation");

Route::post("/checkNewReservation/{type}", "ReservationsController@checkNewReservation");

Route::get('/all-reservations', "ReservationsController@allReservations");

Route::post('/admin-check-reservations', "ManualReservationController@checkReservation");

Route::post('/admin-add-reservation', 'ManualReservationController@postAddAdminReservation');

Route::get('/view-admin-reservation/{id}', 'ManualReservationController@viewReservation');

Route::get('/delete-admin-reservation/{id}', "ManualReservationController@deleteReservation");

Route::get('/edit-admin-reservation/{id}', "ManualReservationController@showEditReservation");

Route::post('/edit-admin-reservation/{id}', "ManualReservationController@editReservation");

Route::get('/view-account/{id?}', "UserController@viewAccount");

Route::get('/delete-user/{id}', "UserController@deleteUser");

Route::get('/view-users', 'UserController@getUsers');

Route::get('/edit-account/{id}', 'UserController@showEditUser');

Route::post("/edit-user/{id}", "UserController@editUser");

Route::get("/add-user", "UserController@showAddUser");

Route::post('/add-user', 'UserController@addUser');

Route::get('/search', 'DashboardController@search');

Route::get('/generate-long-reservation-pdf/{id?}', 'PdfGeneratorController@generateLongReservation');

Route::get('/generate-temp-reservation-pdf/{id?}', 'PdfGeneratorController@generateTempReservation');

Route::get('/generate-manual-reservation-pdf/{id?}', 'PdfGeneratorController@generateManualReservation');

Route::get('/calendar/{date?}', 'CalendarController@showCalendar');

Route::get('/notifications', 'NotificationsController@getNotifications');

Route::get('/newNotifications/{timestamp?}', 'NotificationsController@getNewNotifications');

Route::get('/read-notifications', 'NotificationsController@readNotifications');

Route::get('/approve-delete-reservation', 'ReservationsController@approveDeleteReservation');

Route::get('/manage-places', 'PlacesController@showManagePlaces');

Route::get('/delete-floor/{id}', 'PlacesController@deleteFloor');

Route::get('/delete-room/{id}', 'PlacesController@deleteRoom');

Route::get('/edit-floor/{id}', 'PlacesController@showEditFloor');

Route::post('/edit-floor/{id}', 'PlacesController@editFloor');

Route::get('/edit-room/{id}', 'PlacesController@showEditRoom');

Route::post('/edit-room/{id}', 'PlacesController@editRoom');

Route::get('/add-floor', 'PlacesController@showAddFloor');

Route::post('/add-floor', 'PlacesController@addFloor');

Route::get('/add-room/{id}', 'PlacesController@showAddRoom');

Route::post('/add-room/{id}', 'PlacesController@addRoom');

Route::get('/help/{section?}', 'HelpController@showHelp');

Route::get('/mass-editing', 'MassEditingController@showList');

Route::post('/mass-reject', 'MassEditingController@massReject');

Route::post('/mass-delete', 'MassEditingController@massDelete');

Route::post('/mass-pause', 'MassEditingController@massPause');

Route::post('/pause-reservation', 'MassEditingController@pauseReservation');

Route::post('/edit-paused-reservation', 'MassEditingController@editPausedReservation');

Route::post('/delete-pause-reservation', 'MassEditingController@deletePauseReservation');

Route::post('/mass-delete-paused-reservations', 'MassEditingController@massDeletePausedReservations');

Route::get('/get-reservations-calendar/{currentDate?}', 'CalendarController@getReservationsCalendar');

Route::get('/weekly-calendar', 'CalendarController@showWeeklyCalendar');

Route::get('/get-week-reservations-calendar/{currentDate}', 'CalendarController@getWeekReservations');

Route::get('/get-calendar-resources', 'CalendarController@getCalendarResources');

//Route::get('/updates', 'HelpController@showUpdates');

Route::fallback(function(){
    return view('errors.404');
});
