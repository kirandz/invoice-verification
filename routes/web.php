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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('', 'HomeController@index')->name('home');
Route::get('user-profile', 'UserController@index');
Route::post('user-update', 'UserController@update');

Auth::routes();

Route::get('home', 'HomeController@index')->name('home');
Route::get('dashboard', 'HomeController@dashboard');
Route::get('dashboard-pie', 'HomeController@dashboardpie');

Route::get('charge-rc', 'RouteChargeController@index');
Route::get('charge-rc-summary', 'RouteChargeController@summary');
Route::get('charge-rc-summary-period', 'RouteChargeController@summaryperiod');
Route::post('charge-rc-summary-update-type', 'RouteChargeController@summaryupdatetype');
Route::get('charge-rc-invoices', 'RouteChargeController@invoices');
Route::get('charge-rc-details', 'RouteChargeController@details');
Route::get('charge-rc-pivot', 'RouteChargeController@pivot');
Route::post('charge-rc-pivot-service', 'RouteChargeController@pivservice');
Route::get('charge-rc-pivot-invoices', 'RouteChargeController@pivinvoices');
Route::get('charge-rc-pivot-details', 'RouteChargeController@pivdetails');
Route::get('charge-rc-pivot-check', 'RouteChargeController@pivcheck');
Route::get('charge-rc-pivot-correction', 'RouteChargeController@pivotcorrection');
Route::get('charge-rc-pivot-inv-update', 'RouteChargeController@pivinvupdate');
Route::get('charge-rc-pivot-inv-fn', 'RouteChargeController@pivinvfn');
Route::get('charge-rc-pivot-inv-service', 'RouteChargeController@pivinvservice');
Route::get('download-charge-rc/{id}', 'RouteChargeController@downloadcharge');

Route::get('charge-tnc', 'TNChargeController@index');
Route::get('charge-tnc-summary', 'TNChargeController@summary');
Route::get('charge-tnc-summary-period', 'TNChargeController@summaryperiod');
Route::post('charge-tnc-summary-update-type', 'TNChargeController@summaryupdatetype');
Route::get('charge-tnc-invoices', 'TNChargeController@invoices');
Route::get('charge-tnc-details', 'TNChargeController@details');
Route::get('charge-tnc-pivot', 'RouteChargeController@pivot');
Route::post('charge-tnc-pivot-service', 'TNChargeController@pivservice');
Route::get('charge-tnc-pivot-invoices', 'TNChargeController@pivinvoices');
Route::get('charge-tnc-pivot-details', 'TNChargeController@pivdetails');
Route::get('charge-tnc-pivot-check', 'TNChargeController@pivcheck');
Route::get('charge-tnc-pivot-correction', 'TNChargeController@pivotcorrection');
Route::get('charge-tnc-pivot-inv-update', 'TNChargeController@pivinvupdate');
Route::get('charge-tnc-pivot-inv-fn', 'TNChargeController@pivinvfn');
Route::get('charge-tnc-pivot-inv-service', 'TNChargeController@pivinvservice');
Route::get('download-charge-tnc/{id}', 'TNChargeController@downloadcharge');

//Route::get('charge-ovc', 'OverChargeController@index');

Route::get('import-invoices', 'InvoiceController@importpage');
Route::get('flight-period', 'InvoiceController@flightperiod');
Route::post('import-invoices', 'InvoiceController@import');
Route::get('import-addinvoices', 'InvoiceController@importaddpage');
Route::post('import-addinvoices', 'InvoiceController@importadd');
Route::get('find-flight-type', 'InvoiceController@findflighttype');

Route::get('import-flightdetails', 'FlightController@importpage');
Route::post('import-flightdetails', 'FlightController@import');
Route::get('flight-details', 'FlightController@flightdetails');

Route::get('unbilled-rc', 'RouteChargeController@unbilled');
Route::get('unbilled-rc-details', 'RouteChargeController@unbilleddetails');
Route::get('download-unbilled-rc/{str}', 'RouteChargeController@downloadunbilled');

Route::get('unbilled-tnc', 'TNChargeController@unbilled');
Route::get('unbilled-tnc-details', 'TNChargeController@unbilleddetails');
Route::get('download-unbilled-tnc/{str}', 'TNChargeController@downloadunbilled');

//Route::get('unbilled-ovc', 'OverChargeController@unbilled');

Route::get('manage-airports', 'AirportController@index');
Route::post('add-airport', 'AirportController@add');
Route::post('update-airport', 'AirportController@update');
Route::delete('delete-airport', 'AirportController@delete');
Route::delete('delete-all-airport', 'AirportController@deleteall');
Route::post('upload-airport', 'AirportController@upload');

Route::get('manage-actypes', 'ACTypeController@index');
Route::post('add-actype', 'ACTypeController@add');
Route::post('update-actype', 'ACTypeController@update');
Route::delete('delete-actype', 'ACTypeController@delete');

Route::get('manage-hajjflights', 'HajjFlightController@index');
Route::post('add-hajjflight', 'HajjFlightController@add');
Route::post('update-hajjflight', 'HajjFlightController@update');
Route::delete('delete-hajjflight', 'HajjFlightController@delete');
Route::delete('delete-all-hajjflight', 'HajjFlightController@deleteall');
Route::post('upload-hajjflight', 'HajjFlightController@upload');

Route::get('manage-routeunits', 'ChargeController@index');
Route::post('add-routeunit', 'ChargeController@add');
Route::post('update-routeunit', 'ChargeController@update');
Route::delete('delete-routeunit', 'ChargeController@delete');

Route::get('manage-invoices', 'InvoiceController@index');
Route::post('update-invoice', 'InvoiceController@update');
Route::delete('delete-invoice', 'InvoiceController@delete');

Route::get('manage-flightdetails', 'FlightController@index');
Route::post('update-flight', 'FlightController@update');
Route::delete('delete-flight', 'FlightController@delete');
Route::get('download-fd/{id}', 'FlightController@downloadfd');

Route::get('document', 'DocumentController@index');
Route::post('doc-upload', 'DocumentController@upload');
Route::delete('doc-delete', 'DocumentController@delete');