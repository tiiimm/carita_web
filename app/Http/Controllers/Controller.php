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
    
    public function register_user(Request $request)
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
    
    public function set_user_type(Request $request)
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

    public function reward_user(Request $request)
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

    public function charities()
    {
        $charity = Charity::join('users', 'users.id', 'charities.user_id')
        ->join('charity_categories', 'charity_categories.id', 'charities.charity_category_id')
        ->select('charities.id', 'organization', 'contact_number', 'account_number', 'users.name as handler', 'charity_categories.name as category')
        ->get();
        return response()->json($charity);
    }

    public function add_achievement(Request $request)
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

    public function add_role(Request $request)
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
}
