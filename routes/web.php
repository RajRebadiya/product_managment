<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
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
})->name('welcome');

Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'product_detail')->name('product_detail');
});

Route::controller(HomeController::class)->group(function () {

    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/dashboard_2', 'dashboard_2')->name('dashboard_2');
    Route::get('/category', 'category')->name('category');
    Route::post('/add-product', 'add_product')->name('add-product');
    Route::get('/add-category', 'add_category')->name('add-category');
    Route::post('/add-category-post', 'add_category_post')->name('add-category-post');
    Route::get('/search-products', 'search_products')->name('search-products');
    Route::get('delete_product/{id}', 'delete');
    Route::get('delete_category/{id}', 'delete_category');
    Route::get('/edit_product', 'edit')->name('edit_product');
    // Route::get('/edit_category', 'edit_category')->name('edit_category');

    Route::post('/update_product', 'update')->name('update_product');

    Route::post('/update-stock-status', 'updateStockStatus')->name('update_stock_status');
    Route::post('/update-status', 'updateStatus')->name('update_status');

    Route::post('/add_to_cart', 'addToCart')->name('add_to_cart');
    Route::get('/cart', 'cart')->name('cart');
    Route::get('/clear_cart', 'clearCart')->name('clear_cart_product');
    Route::get('/generate_pdf', 'generatePdf')->name('generate_pdf');
    Route::get('/cart-detail' , 'cart_detail')->name('cart_detail');


    // Route::get('edit_category/{id}', 'edit')->name('edit_category');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    // Route::post('/login-post', 'login_post')->name('login-post');
    Route::get('/register', 'register')->name('register');
    // Route::post('/register-post', 'register_post')->name('register-post');
    // Route::get('/logout', 'logout')->name('logout');
});
