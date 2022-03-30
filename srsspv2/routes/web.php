<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShurjopayController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/domainpay', [ShurjopayController::class, 'checkout']);
Route::get('/verify',[ShurjopayController::class, 'verify']);
Route::post('domain_return',[ShurjopayController::class, 'ReturnPay']);