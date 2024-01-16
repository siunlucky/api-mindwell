<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/insert', function() {
    $stuRef = app('firebase.firestore')->database()->collection("Students")->newDocument();
    $stuRef->set([
        'firstName' => 'Seven',
        'lastName' => 'Stac',
        'age' => 19
    ]);
});
