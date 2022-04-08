<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

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




Route::get('text-db', function (){
	return \App\Models\Product::all();
});


Route::get('gateway',               [PaymentController::class, 'gateway'])                              ->name('gateway');
Route::get('callBack',              [PaymentController::class, 'callback'])                             ->name('payment.callback');


Route::get('/', function () {
    return view('welcome');
});

Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');

    return "Cleared!";

});


Route::get('noobie_seeder', function () {
    for ($i = 40; $i < 50; $i++) {

        \App\Models\Product::create([
            'title' => 'رطب جنووب',
            'code' => 12312,
            'quality' => 2,
            'unit_measurement' => 'package',
            'amount' => 50,
            'rank' => 3.2,
            'scores' => 50,
            'buyers' => 50,
            'types' => [
                [
                    [
                        'name' => 'رطب آبدار',
                        'package' => [
                            250 => 20000,
                            500 => 30000,
                            1000 => 60000,
							'inventory' => 200
                        ]
                    ],
                    [
                        'name' => 'رطب آبندار',
                        'package' => [
                            250 => 20000,
                            500 => 30000,
                            1000 => 60000,
							'inventory' => 200
                        ]
                    ],

                ]
            ],
            'short_explanation' => 'خرماست',
            'sub_category_id' => '618cccce8eedae75b493bfde',
            'Description' => 'بخدا خرماست',
            'attributes' => [
                'سیاهه',
                'درازه',
                'شیرینه',
                'خرماست',
            ],
            'photos' => [
                'img.png',
                'png.img',
            ],
        ]);
    }
});
