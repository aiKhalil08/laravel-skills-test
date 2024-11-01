<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductsController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/{id?}', [ProductsController::class, 'index']);
