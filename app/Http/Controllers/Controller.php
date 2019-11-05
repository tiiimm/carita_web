<?php

namespace App\Http\Controllers;

use App\Charity;
use App\CharityAchievement;
use App\CharityCategory;
use App\CharityPoint;
use App\Philanthropist;
use App\PhilanthropistPoint;
use App\Role;
use App\User;
use App\WatchLog;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function register_user()
    {
        try
        {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            $user = User::create([
                'name'=>$inputs->name,
                'username'=>$inputs->username,
                'email'=>$inputs->email,
                'password'=>bcrypt($inputs->password)
            ]);
            return json_encode(['message'=>"successful", 'name' => $user->name, 'username'=> $user->username, 'email'=> $user->email, 'id'=> $user->id, 'points'=>0]);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }
    
    public function set_user_type()
    {
        try
        {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            if ($inputs->user_type == 'user')
            {
                $philanthropist = Philanthropist::create([
                    'user_id'=>$inputs->id,
                    'contact_number'=>$inputs->contact_number,
                    'birthday'=>$inputs->birthday,
                    'sex'=>$inputs->sex,
                    'charity_category_id'=>CharityCategory::where('name', $inputs->category)->value('id'),
                ]);
                PhilanthropistPoint::create([
                    'philanthropist_id'=>$philanthropist->id,
                    'points'=>0
                ]);
                User::findOrFail($inputs->id)->update(['role_id'=>Role::where('name','user')->value('id')]);
            }
            elseif ($inputs->user_type == 'charity')
            {
                $charity = Charity::create([
                    'user_id'=>$inputs->id,
                    'organization'=>$inputs->organization,
                    'contact_number'=>$inputs->contact_number,
                    'account_number'=>$inputs->account_number,
                    'address'=>$inputs->address,
                    'charity_category_id'=>CharityCategory::where('name', $inputs->category)->value('id')
                ]);
                CharityPoint::create([
                    'charity_id'=>$charity->id,
                    'points'=>0
                ]);
                User::findOrFail($inputs->id)->update(['role_id'=>Role::where('name','charity')->value('id')]);
            }

            return json_encode(['message'=>'successful']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function login_user()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            $user_details = User::where('username', $inputs->username)->get();
            
            foreach($user_details as $user)
            {
                if (password_verify($inputs->password, $user->password))
                {
                    $current_user = User::findOrFail($user->id);
                    $user_type='';
                    $points = 0;
                    if (!is_null($current_user->role))
                    {
                        $user_type = $current_user->role->name;
                    }
                    if ($user_type == 'user')
                    {
                        $points = $current_user->philanthropist->point->points;
                    }
                    elseif ($user_type == 'charity')
                    {
                        $points = $current_user->charity->point->points;
                    }
                    return json_encode(['message'=>"successful", 'name' => $current_user->name, 'username'=> $current_user->username, 'email'=> $current_user->email, 'id'=> $current_user->id, 'user_type'=>$user_type, 'points'=>$points]);
                }
                return json_encode(['message'=>"invalid"]);
            }
        } catch (Exception $error) {
            return json_encode(['message'=>$error]);
        }
    }

    public function reward_user()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            $user = User::findOrFail($inputs->user_id);
            WatchLog::create([
                'philanthropist_id' => $user->philanthropist->id,
                'charity_id' => $inputs->charity_id
            ]);
            $user->philanthropist->point->increment('points');
            Charity::findOrFail($inputs->charity_id)->point->increment('points');
            return json_encode(['message'=>"successful", 'points'=>$user->philanthropist->point->points]);
        } catch (Exception $error) {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_profile()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            User::findOrFail($inputs->id)->update([
                'name' => $inputs->name,
                'username' => $inputs->username,
                'email' => $inputs->email
            ]);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_password()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            User::findOrFail($inputs->id)->update([
                'password' => bcrypt($inputs->password)
            ]);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function reset_password()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            User::findOrFail($inputs->id)->update([
                'password' => bcrypt('123456789')
            ]);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }
    
    public function add_achievement()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            // return ['inputs'=>$inputs];
            
            $user = User::findOrFail($inputs->id);
            CharityAchievement::create([
                'charity_id'=>$user->charity->id,
                'title' => $inputs->title,
                'description' => $inputs->description,
                'photo' => "PHOTO",
                'held_on' => $inputs->held_on,
            ]);
            return CharityAchievement::where('charity_id', $user->charity->id)->get();
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function add_role()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            Role::create([
                'name'=>$inputs->name
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function add_charity_category()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            CharityCategory::create([
                'name'=>$inputs->name
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }
    
    public function add_user()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            User::create([
                'name'=>$inputs->name,
                'email'=>$inputs->email,
                'username'=>$inputs->username,
                'password'=>bcrypt($inputs->password)
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_achievement()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            CharityAchievement::findOrFail($inputs->id)->update([
                'title' => $inputs->title,
                'description' => $inputs->description,
                'photo' => 'PHOTO',
                'held_on' => $inputs->held_on
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_role()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            Role::findOrFail($inputs->id)->update([
                'name' => $inputs->name
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_charity_category()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            CharityCategory::findOrFail($inputs->id)->update([
                'name' => $inputs->name
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_user()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            User::findOrFail($inputs->id)->update([
                'name' => $inputs->name,
                'email' => $inputs->email,
                'username' => $inputs->username,
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function delete_achievement()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            CharityAchievement::findOrFail($inputs->id)->delete();
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function delete_role()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            Role::findOrFail($inputs->id)->delete();
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function delete_charity_category()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            CharityCategory::findOrFail($inputs->id)->delete();
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function delete_user()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            User::findOrFail($inputs->id)->delete();
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_charity_achievements()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            return ['achievements'=>User::findOrFail($inputs->id)->charity->achievements];
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_user_details()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            $user = User::findOrFail($inputs->id);
            return ['name'=>$user->name, 'username'=>$user->username, 'email'=>$user->email];
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_achievements()
    {
        $achievements = CharityAchievement::join('charities', 'charities.id', 'charity_achievements.charity_id')
        ->select('charity_achievements.id', 'title', 'description', 'photo', 'held_on')
        ->get();
        return response()->json($achievements);
    }

    public function get_charities()
    {
        $charity = Charity::join('users', 'users.id', 'charities.user_id')
        ->join('charity_categories', 'charity_categories.id', 'charities.charity_category_id')
        ->select('charities.id', 'organization', 'contact_number', 'account_number', 'users.name as handler', 'charity_categories.name as category')
        ->get();
        return response()->json($charity);
    }

    public function get_roles()
    {
        $roles = Role::get();
        return response()->json($roles);
    }

    public function get_charity_categories()
    {
        return CharityCategory::all();
    }

    public function get_users()
    {
        $return_users=[];
        $users = User::select('id', 'name', 'role_id as type')
        ->get();
        foreach ($users as $user)
        {
            if($user['type'] == null)
            {
                $user['type'] = '';
                $user['points']=0;
                array_push($return_users, $user);
            }
            else
            { 
                $user['type']=User::findOrFail($user->id)->role->name;
                if ($user['type'] == 'charity')
                {
                    $user['points']=User::findOrFail($user->id)->charity->point->points;
                    array_push($return_users, $user);
                }
                elseif ($user['type'] == 'user')
                {
                    $user['points']=User::findOrFail($user->id)->philanthropist->point->points;
                    array_push($return_users, $user);
                }
            }
        }
        return $return_users;
    }
}
