<?php

use App\Http\Controllers\BeritaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//GET
Route::get('index', action: [BeritaController::class, 'index']);
Route::get('beritaAwal', action: [BeritaController::class, 'beritaAwal']);
Route::get('getImage', action: [BeritaController::class, 'getImage']);
Route::get('berita/{id}', action: [BeritaController::class, 'show']);






//PUT
Route::post('update/berita/{id}', action: [BeritaController::class, 'update']);



//POST
Route::post('add/berita', action: [BeritaController::class, 'store']);
Route::post('login', action: [BeritaController::class, 'login']);
Route::post('register', action: [BeritaController::class, 'register']);
