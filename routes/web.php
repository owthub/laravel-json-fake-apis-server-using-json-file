<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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

Route::get('/', [ApiController::class, "index"]);
Route::get('/db', [ApiController::class, "loadJson"]);
Route::get('/{key}', [ApiController::class, "specificJSONKey"]);
Route::post('/{key}', [ApiController::class, "postSpecificJSONKey"]);
Route::get('/{key}/{id}', [ApiController::class, "specificJSONKeyDataByID"]);
Route::match(["put", "patch"], '/{key}/{id}', [ApiController::class, "putSpecificJSONKeyDataByID"]);
Route::delete('/{key}/{id}', [ApiController::class, "deleteSpecificJSONKeyDataByID"]);
Route::get('/{by}/{id}/{from}', [ApiController::class, "specificJSONKeyDataFromRelationship"]);
