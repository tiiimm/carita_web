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
Route::post('/update_profile', "Controller@update_profile");

Route::post('/add_achievement', "Controller@add_achievement");
Route::post('/add_role', "Controller@add_role");
Route::post('/add_charity_category', "Controller@add_charity_category");
Route::post('/add_user', "Controller@add_user");

Route::post('/update_achievement', "Controller@update_achievement");
Route::post('/update_role', "Controller@update_role");
Route::post('/update_charity_category', "Controller@update_charity_category");
Route::post('/update_user', "Controller@update_user");

Route::post('/delete_achievement', "Controller@delete_achievement");
Route::post('/delete_role', "Controller@delete_role");
Route::post('/delete_charity_category', "Controller@delete_charity_category");
Route::post('/delete_user', "Controller@delete_user");

Route::get('/get_achievements', "Controller@get_achievements");
Route::get('/get_charities', "Controller@get_charities");
Route::get('/get_roles', "Controller@get_roles");
Route::get('/get_charity_categories', "Controller@get_charity_categories");
Route::get('/get_users', "Controller@get_users");
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
