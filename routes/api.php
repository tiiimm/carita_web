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

Route::post('/register_user', "Controller@register_user"); //params: name, username, email, password
Route::post('/set_user_type', "Controller@set_user_type"); //params: user_type (philanthropist: id, contact_number, birthday, sex, category), (charity: id, organization, contact_number, account_number, address, category)
Route::post('/login_user', "Controller@login_user"); //params: username, password
Route::post('/reward_user', "Controller@reward_user"); //params: user_id, charity_id
Route::post('/update_profile_picture', "Controller@update_profile_picture"); //params: id, photo
Route::post('/update_profile', "Controller@update_profile"); //params: id, name, username, email
Route::post('/update_password', "Controller@update_password"); //params: id, password
Route::post('/reset_password', "Controller@reset_password"); //params: id, password

Route::post('/add_achievement', "Controller@add_achievement"); //params: id, title, description, photo, held_on
Route::post('/add_role', "Controller@add_role"); //params: name
Route::post('/add_charity_category', "Controller@add_charity_category"); //params: name
Route::post('/add_user', "Controller@add_user"); //params: name, email, username, password

Route::post('/update_achievement', "Controller@update_achievement"); //params: id, title, description, photo, held_on 
Route::post('/update_role', "Controller@update_role"); //params: id, name
Route::post('/update_charity_category', "Controller@update_charity_category"); //params: id, name
Route::post('/update_user', "Controller@update_user"); //params: id, name, email, username, password

Route::post('/delete_achievement', "Controller@delete_achievement"); //params: id
Route::post('/delete_role', "Controller@delete_role"); //params: id
Route::post('/delete_charity_category', "Controller@delete_charity_category"); //params: id
Route::post('/delete_user', "Controller@delete_user"); //params: id

Route::post('/get_charity_achievements', "Controller@get_charity_achievements"); //params: id
Route::post('/get_user_details', "Controller@get_user_details"); //params: id

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
        'photo'=>'carita/profile_picture.png',
        'verified'=>1,
        'role_id'=>1,
        'password'=>bcrypt('asdasdasd')
    ]);
});
