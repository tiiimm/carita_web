<?php

use App\CharityCategory;
use App\Role;
use App\User;
use Illuminate\Http\Request;

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

Route::post('/register_user', "Controller@register_user");
Route::post('/login_user', "Controller@login_user");
Route::post('/set_user_type', "Controller@set_user_type");
Route::post('/reward_user', "Controller@reward_user");
Route::post('/add_achievement', "Controller@add_achievement");
Route::post('/add_role', "Controller@add_role");

Route::get('/charities', "Controller@charities");
Route::get('/seed', function () {
    Role::create([
        'name' => 'admin'
    ]);
    Role::create([
        'name' => 'user'
    ]);
    Role::create([
        'name' => 'charity'
    ]);
    CharityCategory::create([
        'name' => 'Child Sponsorship Organizations'
    ]);
    CharityCategory::create([
        'name' => 'International Development NGOs'
    ]);
    CharityCategory::create([
        'name' => 'Disaster Relief and Humanitarian NGO'
    ]);
    CharityCategory::create([
        'name' => 'Peace and Human Rights NGOs'
    ]);
    CharityCategory::create([
        'name' => 'Conservation NGOs'
    ]);
    User::create([
        'name'=>'FATIMA MERCY A ONRUBIA',
        'email'=>'onrubia.fatima98@gmail.com',
        'username'=>'tim',
        'role_id'=>1,
        'password'=>bcrypt('asdasdasd')
    ]);
});
