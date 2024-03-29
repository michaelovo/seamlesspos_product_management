<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\StatusController;
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

Route::group(['prefix' => '/v1'], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
    Route::get('/resend-otp/{email}', [AuthController::class, 'resendOtp']);

    Route::post('/login', [AuthController::class, 'login']);
    Route::delete('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    /* Status */
    Route::group(['prefix' => 'status'], function () {
        Route::get('', [StatusController::class, 'fetchStatuses']);
        Route::get('/{statusId}', [StatusController::class, 'fetchStatusById']);
    });

    /* Category */
    Route::group(['prefix' => 'category'], function () {
        Route::get('', [CategoryController::class, 'fetchCategories']);
        Route::get('/{categoryId}', [CategoryController::class, 'fetchCategoryById']);
    });

    /** Product */
    Route::group(['prefix' => 'product', 'middleware' => (['auth:sanctum'])], function () {
        Route::get('', [ProductController::class, 'fetchAllProducts']);
        Route::post('/create', [ProductController::class, 'createProduct']);
        Route::get('/{productId}/fetch', [ProductController::class, 'fetchProductById']);
        Route::patch('/{productId}/update', [ProductController::class, 'updateProduct']);
        Route::delete('/{productId}/delete', [ProductController::class, 'deleteProduct']);
        Route::put('/{productId}/category/{categoryId}/update', [ProductCategoryController::class, 'updateProductCategory']);
    });
});
