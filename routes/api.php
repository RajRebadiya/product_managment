<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ApiController;
use App\Http\Controllers\api\StaffAuthController;
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

Route::controller(ApiController::class)->group(function () {
    Route::post('/categories', 'category_data');
    Route::get('/products', 'product_data');
    Route::post('/product-search', 'search_products');
    Route::post('/product-add', 'product_add');
    Route::post('/all-products-with-pagination', 'all_products_with_pagination');
    Route::post('/all-category-with-pagination', 'all_category_with_pagination');
    Route::post('/category-add', 'category_add');
    Route::post('/delete-product', 'delete_product')->name('delete-product');
    Route::post('/delete-category', 'delete_category')->name('delete-category');
    Route::post('/edit-category', 'edit_category')->name('edit-category');
    Route::post('/edit-product', 'edit_product')->name('edit-product');
    Route::post('/product_stock_update', 'product_stock_update')->name('product_stock_update');
});

Route::controller(StaffAuthController::class)->group(function () {
    Route::post('/register',  'register');
    Route::post('/login', 'login');
    Route::get('/check-auth', 'checkAuth');
});
