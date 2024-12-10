<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::group(['middleware' => 'api'], function () {

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'userProfile']);

});

Route::post('/transactions', [TransactionController::class, 'store'])->middleware('auth:api');
Route::post('/transactions/{id}/refund', [TransactionController::class, 'refund'])->middleware('auth:api');

Route::put('/transactions/{id}', [TransactionController::class, 'update'])->middleware('auth:api'); // Update transaction
Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->middleware('auth:api');
Route::get('/laporan', [LaporanController::class, 'laporan'])->middleware('auth:api');

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::put('/users/{id}/activate', [AuthController::class, 'activate']);
    Route::get('/products', [ProductController::class, 'index']); // Menampilkan daftar produk
    Route::post('/products', [ProductController::class, 'store']); // Menambah produk baru

    Route::put('/products/{id}', [ProductController::class, 'update']); // Mengupdate produk
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Menghapus produk


});


