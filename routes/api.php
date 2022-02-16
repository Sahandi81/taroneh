<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AmazingOfferController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategory;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShoppingController as AdminShoppingController;
use App\Http\Controllers\Admin\SpecialSaleController as AdminSpecialSale;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AmazingOffersController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\errorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShoppingController;
use App\Http\Controllers\SpecialSaleController;
use App\Http\Controllers\WishListController;
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

# Public Routes
Route::get('client_connection_test', function (){ return ['Connection'=> 'OK'];});

# Client
Route::post('send_verification_code',       [UserController::class, 'sendVerificationCode'])                    ->name('send.verification.code');
Route::post('code_verify',                  [UserController::class, 'codeVerify'])                              ->name('code.verify');
Route::post('login',                        [UserController::class, 'login'])                                   ->name('login');

Route::get('shop',                          [ProductController::class, 'index'])                                ->name('shop');
Route::get('shop/single_product',           [ProductController::class, 'singleProduct'])                        ->name('single.product');
Route::get('special_sales',                 [SpecialSaleController::class, 'index'])                            ->name('special.sales');
Route::get('categories/',                   [CategoryController::class, 'index'])                               ->name('categories');
Route::get('amazing_offer',                 [AmazingOffersController::class, 'index'])                          ->name('amazing.offer');
Route::get('search',                        [SearchController::class, 'searchProduct'])                         ->name('search.products');
Route::get('setting',                       [SettingController::class, 'index'])                                ->name('get.setting');

# System routes
Route::get('error.handler',                 [errorController::class, 'error'])                                  ->name('error.handler');




# Protected Routes
Route::group(['middleware' => ['auth:sanctum']], function (){

    Route::get('client_authentication_connection_test', function (){ return ['Connection'=> 'OK'];});
    Route::get('user_details',              [UserController::class, 'details'])                                     ->name('user.details');

    # Routes with Panel in first of them
    Route::group(['prefix' => 'panel'], function () {
        Route::put('complete_profile',          [UserController::class, 'complete'])                                ->name('complete.prof');
        Route::get('logout',                    [UserController::class, 'logout'])                                  ->name('logout');

        # Protect routes from those users who DO NOT complete their profile
        Route::middleware('profile.checker')->group(function(){
            Route::get('profile',               [UserController::class, 'profile'])                                 ->name('profile');
            Route::post('comment',              [CommentController::class, 'store'])                                ->name('add.comment');
            Route::patch('update_profile',      [UserController::class, 'editProfile'])                             ->name('edit.profile');
            Route::post('add_to_wish_list',     [WishListController::class, 'store'])                               ->name('add.product.to.wish.list');
            Route::get('wish_list',             [WishListController::class, 'index'])                               ->name('wish.list');
            Route::post('add_to_cart',          [CartController::class, 'store'])                                   ->name('add.product.to.cart');
            Route::get('cart',                  [CartController::class, 'index'])                                   ->name('add.product.to.cart');
            Route::post('buy',                  [ShoppingController::class, 'index'])                               ->name('add.product.to.cart');
            Route::get('shopping_history',      [ShoppingController::class, 'shoppingHistory'])                     ->name('add.product.to.cart');
        });
    });

});

# Admin routs
Route::group(['prefix' => 'admin'], function () {

    Route::post('login',                        [AdminController::class, 'login'])                                  ->name('admin.login');

    Route::group(['middleware' => ['auth:sanctum']], function (){
        Route::middleware('admin')->group(function (){

            Route::get('dashboard',             [AdminController::class, 'dashboard'])                          	->name('dashboard');
            Route::get('users',             	[AdminUserController::class, 'index'])                          	->name('users');
            Route::get('orders',             	[AdminShoppingController::class, 'index'])                          ->name('orders');
            Route::get('comment',               [CommentController::class, 'index'])                                ->name('comments');

            Route::post('add_product', 			[AdminProductController::class, 'store'])							->name('add.product');
            Route::post('add_offer', 			[DiscountController::class, 'store'])								->name('add.offer');
            Route::post('add_special_sale', 	[AdminSpecialSale::class, 'store'])									->name('add.special.sale');
            Route::post('add_amazing_offer', 	[AmazingOfferController::class, 'store'])							->name('add.amazing.offer');
            Route::post('add_category',			[AdminCategory::class, 'storeCategory'])							->name('add.category');
            Route::post('add_subcategory',		[AdminCategory::class, 'storeSubCategory'])							->name('add.subcategory');
            Route::post('add_blog',		        [BlogController::class, 'store'])							        ->name('add.blog');
            Route::post('upload_photo',			[PhotoController::class, 'store'])									->name('upload.photo');

            Route::patch('update_user',         [AdminUserController::class, 'update'])                          	->name('update.user');
            Route::patch('update_product',		[AdminProductController::class, 'update'])							->name('update.products');
            Route::put('update_amazing_offer',	[AmazingOfferController::class, 'update'])							->name('update.amazing.offer');
            Route::put('update_offer',			[DiscountController::class, 'update'])								->name('update.offer');
            Route::put('update_category',		[AdminCategory::class, 'updateCategory'])							->name('update.category');
            Route::put('update_subcategory',	[AdminCategory::class, 'updateSubCategory'])						->name('update.subcategory');
            Route::put('update_setting',	    [SettingController::class, 'update'])       						->name('update.setting');
            Route::patch('update_order',        [AdminShoppingController::class, 'update'])                          ->name('update.order');

            Route::delete('delete_user',        [AdminUserController::class, 'destroy'])                          	->name('update.user');
            Route::delete('delete_product',		[AdminProductController::class, 'destroy'])							->name('add.product');
            Route::delete('delete_amazing_offer',[AmazingOfferController::class, 'destroy'])						->name('update.amazing.offer');
            Route::delete('delete_offer',		[DiscountController::class, 'destroy'])								->name('update.offer');
            Route::delete('delete_category',	[AdminCategory::class, 'destroyCategory'])							->name('update.category');
            Route::delete('delete_subcategory',	[AdminCategory::class, 'destroySubCategory'])						->name('update.category');

        });

    });
});



