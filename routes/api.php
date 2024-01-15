<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\authcontroller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories',[CategoryController::class, 'getList']);
Route::get('/getById/{id}', [CategoryController::class, 'getById']);
Route::get('/getByName/{name}', [CategoryController::class, 'getByName']);

Route::post('/categories',[CategoryController::class, 'create']);
Route::delete('/categories/{id}', [CategoryController::class, 'delete']);
Route::post("/categories/edit/{id}", [CategoryController::class, "edit"]);

Route::post('/login',[authcontroller::class, 'login']);
Route::post('/register',[authcontroller::class, 'register']);
Route::get('/getImage',[authcontroller::class, 'getUserImageNameByEmail']);

Route::get('/products',[ProductController::class, 'getList']);
Route::post('/product',[ProductController::class, 'store']);

