<?php

namespace App\Http\Controllers;

use App\Charity;
use App\CharityAchievement;
use App\CharityEvent;
use App\CharityCategory;
use App\CharityPoint;
use App\EventWatchLog;
use App\Philanthropist;
use App\PhilanthropistPoint;
use App\Role;
use App\User;
use App\WatchLog;
use Error;
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

            if (!User::where('username', $inputs->username)->get()->isEmpty())
            {
                return ['error'=>true, 'message'=>'Username already taken', 'txt_username'=>true];
            }
            if (!User::where('email', $inputs->email)->get()->isEmpty())
            {
                return ['error'=>true, 'message'=>'Email already taken', 'txt_email'=>true];
            }
            
            $user = User::create([
                'name'=>$inputs->name,
                'username'=>$inputs->username,
                'email'=>$inputs->email,
                'photo' => 'carita/profile_picture.png',
                'verified' => 0,
                'password'=>bcrypt($inputs->password)
            ]);
            return json_encode([
                'success'=>true,
                'user' => $user,
                'points'=>0,
            ]);
        }
        catch(Exception $error)
        {
            return ['error'=>true, 'message'=>'Something went wrong!'];
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
                User::findOrFail($inputs->id)->update(['role_id'=>Role::where('name','user')->value('id'), 'photo'=>$inputs->profile_picture_path, 'verified'=>1]);
            }
            elseif ($inputs->user_type == 'charity')
            {
                $charity = Charity::create([
                    'user_id'=>$inputs->id,
                    'organization'=>$inputs->organization,
                    'contact_number'=>$inputs->contact_number,
                    'bio'=>$inputs->bio,
                    'bio_path'=>$inputs->bio_path,
                    'bio_path_type'=>$inputs->bio_path_type,
                    'photo'=>$inputs->logo_path,
                    'account_name'=>$inputs->account_name,
                    'account_number'=>$inputs->account_number,
                    'address'=>$inputs->address,
                    'charity_category_id'=>CharityCategory::where('name', $inputs->category)->value('id'),
                ]);
                CharityPoint::create([
                    'charity_id'=>$charity->id,
                    'points'=>0
                ]);
                User::findOrFail($inputs->id)->update(['role_id'=>Role::where('name','charity')->value('id')]);
            }

            return json_encode([
                'success'=>true,
                'user' => User::findOrFail($inputs->id),
                'user_type'=>$inputs->user_type,
                'points'=>0
            ]);
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

            if ($user_details->isEmpty())
            {
                return json_encode(['error'=>true, 'message'=>'Invalid Username and Password combination']);
            }
            
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
                    return json_encode([
                        'success'=>true,
                        'user' => $current_user,
                        'user_type'=>$user_type,
                        'points'=>$points
                    ]);
                }
                return json_encode(['error'=>true, 'message'=>'Invalid Username and Password combination']);
            }
        } catch (Error $error) {
            return json_encode(['error'=>true, 'message'=>'Invalid Username and Password combination']);
        }
    }

    public function verify_user()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            User::findOrFail($inputs->id)->update([
                'verified'=>1
            ]);
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
                'user_id' => $user->id,
                'charity_id' => $inputs->charity_id
            ]);
            $points = 0;
            try {
                $user->philanthropist->point->increment('points');
                $points = $user->philanthropist->point->points;
            } catch (Exception $th) {
                //throw $th;
            }
            Charity::findOrFail($inputs->charity_id)->point->increment('points');
            return json_encode(['message'=>"successful", 'points'=>$points]);
        } catch (Exception $error) {
            return json_encode(['message'=>$error]);
        }
    }

    public function reward_user_event()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            $user = User::findOrFail($inputs->user_id);
            EventWatchLog::create([
                'user_id' => $user->id,
                'event_id' => $inputs->event_id
            ]);
            $points = 0;
            try {
                $user->philanthropist->point->increment('points');
                $points = $user->philanthropist->point->points;
            } catch (Exception $th) {
                //throw $th;
            }
            CharityEvent::findOrFail($inputs->event_id)->increment('points');
            return json_encode(['message'=>"successful", 'points'=>$points]);
        } catch (Exception $error) {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_profile_picture()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            User::findOrFail($inputs->id)->update([
                'photo'=>$inputs->photo
            ]);
            return json_encode(['message'=>'successful']);
        }
        catch(Exception $error)
        {
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
            return json_encode(['message'=>'successful']);
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
            return json_encode(['message'=>'successful']);
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
            return json_encode(['message'=>'successful']);
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
            
            $user = User::findOrFail($inputs->id);
            CharityAchievement::create([
                'charity_id'=>$user->charity->id,
                'title' => $inputs->title,
                'description' => $inputs->description,
                'photo' => $inputs->photo,
                'held_on' => $inputs->held_on,
            ]);
            return json_encode(['message'=>'successful']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }
    
    public function add_event()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            $user = User::findOrFail($inputs->id);
            CharityEvent::create([
                'charity_id'=>$user->charity->id,
                'title' => $inputs->title,
                'description' => $inputs->description,
                'photo' => $inputs->photo,
                'venue' => $inputs->venue,
                'event_date' => $inputs->event_date,
                'event_from' => $inputs->event_from,
                'event_to' => $inputs->event_to,
                'points' => 0
            ]);
            return json_encode(['message'=>'successful']);
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
                'password'=>bcrypt($inputs->password),
                'photo' => 'carita/profile_picture.png'
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
                'photo' => $inputs->photo,
                'held_on' => $inputs->held_on
            ]);
            return json_encode(['message'=>'Success']);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function update_event()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            CharityEvent::findOrFail($inputs->id)->update([
                'title' => $inputs->title,
                'description' => $inputs->description,
                'photo' => $inputs->photo,
                'venue' => $inputs->venue,
                'event_date' => $inputs->event_date,
                'event_from' => $inputs->event_from,
                'event_to' => $inputs->event_to
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

    public function delete_event()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            CharityEvent::findOrFail($inputs->id)->delete();
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

    public function get_charity_events()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            return ['events'=>User::findOrFail($inputs->id)->charity->events];
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
            return ['name'=>$user->name, 'username'=>$user->username, 'email'=>$user->email, 'photo'=>$user->photo];
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }
    
    public function get_charities()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);
            
            $charities = Charity::join('users', 'users.id', 'charities.user_id')
            ->join('charity_categories', 'charity_categories.id', 'charities.charity_category_id')
            ->join('charity_points', 'charity_points.charity_id', 'charities.id')
            ->select('charities.id', 'organization', 'contact_number', 'account_name', 'account_number', 'users.name as handler', 'users.id as handler_id','charity_categories.name as category', 'charities.photo', 'bio', 'bio_path', 'bio_path_type', 'address', 'points')
            ->get();

            foreach ($charities as $charity)
            {
                $charity['watch_count'] = 0;
                try {
                    $charity['watch_count'] = WatchLog::where('user_id', $inputs->id)->where('charity_id', $charity->id)->count();
                } catch (\Throwable $th) {
                }
            }

            return response()->json(['charities'=>$charities]);
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_donations()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            $january = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '1')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '1')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $february = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '2')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '2')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $march = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '3')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '3')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $april = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '4')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '4')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $may = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '5')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '5')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $june = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '6')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '6')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $july = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '7')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '7')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $august = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '8')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '8')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $september = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '9')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '9')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $october = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '10')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '10')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $november = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '11')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '11')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $december = WatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '12')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::where('user_id', $inputs->id)
            ->whereMonth('created_at', '12')
            ->whereYear('created_at', $inputs->year)
            ->count();      
            
            return ['january'=>$january, 'february'=>$february, 'march'=>$march, 'april'=>$april, 'may'=>$may, 'june'=>$june, 'july'=>$july, 'august'=>$august, 'september'=>$september, 'october'=>$october, 'november'=>$november, 'december'=>$december];
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_supports()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            $id = User::findOrFail($inputs->id)->charity->id;

            $january = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '1')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $february = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '2')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $march = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '3')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $april = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '4')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $may = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '5')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $june = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '6')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $july = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '7')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $august = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '8')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $september = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '9')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $october = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '10')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $november = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '11')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $december = WatchLog::where('charity_id', $id)
            ->whereMonth('created_at', '12')
            ->whereYear('created_at', $inputs->year)
            ->count();   

            
            $january_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '1')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $february_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '2')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $march_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '3')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $april_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '4')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $may_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '5')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $june_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '6')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $july_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '7')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $august_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '8')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $september_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '9')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $october_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '10')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $november_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '11')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $december_charity = EventWatchLog::join('charity_events', 'charity_events.id', 'event_watch_logs.event_id')
            ->where('charity_id', $id)
            ->whereMonth('created_at', '12')
            ->whereYear('created_at', $inputs->year)
            ->count();      
            
            return ['january'=>$january, 'february'=>$february, 'march'=>$march, 'april'=>$april, 'may'=>$may, 'june'=>$june, 'july'=>$july, 'august'=>$august, 'september'=>$september, 'october'=>$october, 'november'=>$november, 'december'=>$december,
            'january_charity'=>$january_charity, 'february_charity'=>$february_charity, 'march_charity'=>$march_charity, 'april_charity'=>$april_charity, 'may_charity'=>$may_charity, 'june_charity'=>$june_charity, 'july_charity'=>$july_charity, 'august_charity'=>$august_charity, 'september_charity'=>$september_charity, 'october_charity'=>$october_charity, 'november_charity'=>$november_charity, 'december_charity'=>$december_charity];
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_admin_donations()
    {
        try {
            $inputs = array();
            $inputs = file_get_contents('php://input');
            $inputs = json_decode($inputs);

            $january = WatchLog::whereMonth('created_at'_charity, '1')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '1')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $february = WatchLog::whereMonth('created_at', '2')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '2')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $march = WatchLog::whereMonth('created_at', '3')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '3')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $april = WatchLog::whereMonth('created_at', '4')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '4')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $may = WatchLog::whereMonth('created_at', '5')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '5')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $june = WatchLog::whereMonth('created_at', '6')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '6')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $july = WatchLog::whereMonth('created_at', '7')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '7')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $august = WatchLog::whereMonth('created_at', '8')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '8')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $september = WatchLog::whereMonth('created_at', '9')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '9')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $october = WatchLog::whereMonth('created_at', '10')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '10')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $november = WatchLog::whereMonth('created_at', '11')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '11')
            ->whereYear('created_at', $inputs->year)
            ->count();      

            $december = WatchLog::whereMonth('created_at', '12')
            ->whereYear('created_at', $inputs->year)
            ->count() 
            + 
            EventWatchLog::whereMonth('created_at', '12')
            ->whereYear('created_at', $inputs->year)
            ->count();      
            
            return ['january'=>$january, 'february'=>$february, 'march'=>$march, 'april'=>$april, 'may'=>$may, 'june'=>$june, 'july'=>$july, 'august'=>$august, 'september'=>$september, 'october'=>$october, 'november'=>$november, 'december'=>$december];
        }
        catch(Exception $error)
        {
            return json_encode(['message'=>$error]);
        }
    }

    public function get_all_events()
    {
        return CharityEvent::orderBy('created_at')->get();
    }

    public function get_achievements()
    {
        $achievements = CharityAchievement::join('charities', 'charities.id', 'charity_achievements.charity_id')
        ->select('charity_achievements.id', 'title', 'description', 'photo', 'held_on')
        ->get();
        return response()->json($achievements);
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
        $users = User::select('id', 'name', 'role_id as type', 'photo', 'verified')
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

    public function get_top_charities()
    {
        return Charity::join('charity_points', 'charity_points.charity_id', 'charities.id')->orderBy('points', 'DESC')->limit(3)->get();
    }

    public function get_latest_events()
    {
        return CharityEvent::latest()->limit(4)->get();
    }
}
