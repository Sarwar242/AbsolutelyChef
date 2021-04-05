<?php

namespace App\Http\Controllers;

use App\Country;
use App\Experience;
use App\JobApplication;
use App\Order;
use App\State;
use App\User;
use App\Payment;
use PDF;
use App\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use App\Notifications\MembershipMail;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function index(){
        $title = trans('app.users');
        $current_user = Auth::user();
        $users = User::where('id', '!=', $current_user->id)->orderBy('name', 'asc')->paginate(20);
        return view('admin.users', compact('title', 'users'));
    }


    public function show($id = 0){
        if ($id){
            $title = trans('app.profile');
            $user = User::find($id);
            $info = $user->userInfo;
            $is_user_id_view = true;
            return view('admin.profile', compact('title', 'user','info', 'is_user_id_view'));
        }
    }

    /**
     * @param $id
     * @param null $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function statusChange($id, $status = null){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = User::find($id);
        if ($user && $status){
            if ($status == 'approve'){
                $user->active_status = 1;
                $user->save();

            }elseif($status == 'block'){
                $user->active_status = 2;
                $user->save();
            }
        }
        return back()->with('success', trans('app.status_updated'));
    }

    
    public function employerOrders(Request $request)
    {
        $title = __('app.packages');
        $orders = auth()->user()->orders->sortByDesc('updated_at');
        return view('admin.orders',compact('title','orders'));

    }

    public function appliedJobs(){
        $title = __('app.applicants');
        $user_id = Auth::user()->id;
        $applications = JobApplication::whereUserId($user_id)->orderBy('id', 'desc')->paginate(20);


        return view('admin.applied_jobs', compact('title', 'applications'));
    }

    public function registerJobSeeker(){
        if(auth()->user()){
            return redirect()->route('dashboard');
        };
        $title = __('app.register_job_seeker');
        return view('register-job-seeker', compact('title'));
    }

    public function registerJobSeekerPost(Request $request){
        $rules = [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        $this->validate($request, $rules);

        $data = $request->input();
        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'user_type'     => 'user',
            'password'      => Hash::make($data['password']),
            'active_status' => 1,
        ]);
        
        $user->userInfo()->create([
            'user_id' =>  $user->id,
            'website_link1' => json_encode(['type' => '' , 'link' => '']),
            'website_link2' => json_encode(['type' => '' , 'link' => '']),
            'current_salary' => json_encode(['type' => '' , 'price' => '']),
            'deisred_salary' => json_encode(['type' => '' , 'price' => '']),
            'job_type' => json_encode(['type' => '' , 'price' => '']),
            'languages' => json_encode([])
        ]);

        try{
            $user->notify(new MembershipMail($user));
        }
        catch(\Exception  $e){
            Log::info('Mail couldn\'t be sent. '.$e->getMessage());
        }
        return redirect(route('login'))->with('success', __('app.registration_successful'));
    }

    public function registerEmployer(){

        if(auth()->user()){
            return redirect()->route('dashboard');
        };

        $title = __('app.employer_register');
        $countries = Country::all();
        $old_country = false;
        if (old('country')){
            $old_country = Country::find(old('country'));
        }
        return view('employer-register', compact('title', 'countries', 'old_country'));
    }

    public function registerEmployerPost(Request $request){
        $rules = [
            'name'      => ['required', 'string', 'max:190'],
            'company'   => 'required',
            'email'     => ['required', 'string', 'email', 'max:190', 'unique:users'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'phone'     => 'required',
            'address'   => 'required',
            'country'   => 'required',
            'state'     => 'required',
        ];
        $this->validate($request, $rules);

        $company = $request->company;
        $company_slug = unique_slug($company, 'User', 'company_slug');

        $country = Country::find($request->country);
        $state_name = null;
        if ($request->state){
            $state = State::find($request->state);
            $state_name = $state->state_name;
        }

        $user = User::create([
            'name'          => $request->name,
            'company'       => $company,
            'company_slug'  => $company_slug,
            'email'         => $request->email,
            'user_type'     => 'employer',
            'password'      => Hash::make($request->password),

            'phone'         => $request->phone,
            'address'       => $request->address,
            'address_2'     => $request->address_2,
            'country_id'    => $request->country,
            'country_name'  => $country->country_name,
            'state_id'      => $request->state,
            'state_name'    => $state_name,
            'city'          => $request->city,
            'active_status' => 1,
        ]);

        $user->userInfo()->create([
            'website_link1' => json_encode(['type' => '' , 'link' => '']),
            'website_link2' => json_encode(['type' => '' , 'link' => '']),
            'current_salary' => json_encode(['type' => '' , 'price' => '']),
            'deisred_salary' => json_encode(['type' => '' , 'price' => '']),
            'job_type' => json_encode(['type' => '' , 'price' => ''])        
        ] );
        try{
            $user->notify(new MembershipMail($user));
        }
        catch(\Exception  $e){
            Log::info('Mail couldn\'t be sent. '.$e->getMessage());
        }
        return redirect(route('login'))->with('success', __('app.registration_successful'));
    }


    public function registerAgent(){
        if(auth()->user()){
            return redirect()->route('dashboard');
        };

        $title = __('app.agent_register');
        $countries = Country::all();
        $old_country = false;
        if (old('country')){
            $old_country = Country::find(old('country'));
        }
        return view('agent-register', compact('title', 'countries', 'old_country'));
    }

    public function registerAgentPost(Request $request){
        $rules = [
            'name'      => ['required', 'string', 'max:190'],
            'company'   => 'required',
            'email'     => ['required', 'string', 'email', 'max:190', 'unique:users'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'phone'     => 'required',
            'address'   => 'required',
            'country'   => 'required',
            'state'     => 'required',
        ];
        $this->validate($request, $rules);

        $company = $request->company;
        $company_slug = unique_slug($company, 'User', 'company_slug');

        $country = Country::find($request->country);
        $state_name = null;
        if ($request->state){
            $state = State::find($request->state);
            $state_name = $state->state_name;
        }

       $user =  User::create([
            'name'          => $request->name,
            'company'       => $company,
            'company_slug'  => $company_slug,
            'email'         => $request->email,
            'user_type'     => 'agent',
            'password'      => Hash::make($request->password),

            'phone'         => $request->phone,
            'address'       => $request->address,
            'address_2'     => $request->address_2,
            'country_id'    => $request->country,
            'country_name'  => $country->country_name,
            'state_id'      => $request->state,
            'state_name'    => $state_name,
            'city'          => $request->city,
            'active_status' => 1,
        ]);
        
       $user->userInfo()->create([
            'user_id' =>  $user->id,
            'website_link1' => json_encode(['type' => '' , 'link' => '']),
            'website_link2' => json_encode(['type' => '' , 'link' => '']),
            'current_salary' => json_encode(['type' => '' , 'price' => '']),
            'deisred_salary' => json_encode(['type' => '' , 'price' => '']),
            'job_type' => json_encode(['type' => '' , 'price' => ''])
        ]);
        try{
            $user->notify(new MembershipMail($user));
        }
        catch(\Exception  $e){
            Log::info('Mail couldn\'t be sent. '.$e->getMessage());
        }
        return redirect(route('login'))->with('success', __('app.registration_successful'));
    }


    public function employerProfile(){
        $title = __('app.employer_profile');
        $user = Auth::user();


        $countries = Country::all();
        $old_country = false;
        if ($user->country_id){
            $old_country = Country::find($user->country_id);
        }

        return view('admin.employer-profile', compact('title', 'user', 'countries', 'old_country'));
    }

    public function employerProfilePost(Request $request){
        $user = Auth::user();

        $rules = [
            'company_size'   => 'required',
            'phone'     => 'required',
            'address'   => 'required',
            'country'   => 'required',
            'state'     => 'required',
        ];

        $this->validate($request, $rules);


        $logo = null;
        if ($request->hasFile('logo')){
            $image = $request->file('logo');

            $valid_extensions = ['jpg','jpeg','png'];
            if ( ! in_array(strtolower($image->getClientOriginalExtension()), $valid_extensions) ){
                return redirect()->back()->withInput($request->input())->with('error', 'Only .jpg, .jpeg and .png is allowed extension') ;
            }
            
            $file_base_name = str_replace('.'.$image->getClientOriginalExtension(), '', $image->getClientOriginalName());
            
            // $resized_thumb = Image::make($image)->resize(256, 256);

            $logo = strtolower(time().str_random(5).'-'.str_slug($file_base_name)).'.' . $image->getClientOriginalExtension();

            $logoPath = 'uploads/images/logos/'.$logo;

            try{
                Storage::disk('public')->put($logoPath,file_get_contents($image));
            } catch (\Exception $e){
                return redirect()->back()->withInput($request->input())->with('error', $e->getMessage()) ;
            }
        }

        $country = Country::find($request->country);
        $state_name = null;
        if ($request->state){
            $state = State::find($request->state);
            $state_name = $state->state_name;
        }

        $data = [
            'company_size'  => $request->company_size,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'address_2'     => $request->address_2,
            'country_id'    => $request->country,
            'country_name'  => $country->country_name,
            'state_id'      => $request->state,
            'state_name'    => $state_name,
            'city'          => $request->city,
            'about_company' => $request->about_company,
            'website'       => $request->website,
        ];

        if ($logo){
            $data['logo'] = $logo;
        }

        $user->update($data);

        return back()->with('success', __('app.updated'));
    }


    public function employerApplicant(){
        $title = __('app.applicant');
        $employer_id = Auth::user()->id;
        $applications = JobApplication::whereEmployerId($employer_id)->orderBy('id', 'desc')->paginate(20);
        foreach ($applications  as $app) {
            $user = User::find($app->user_id);
            if($user){
                if($app->resume){
                    $app['cv'] = $app->resume;
                }
                elseif($user->cv){
                    $app['cv'] = $user->cv;
                }else{
                    $app['cv'] = '';
                }
            }else{
                if($app->resume){
                    $app['cv'] = $app->resume;
                }else{
                    $app['cv'] = '';
                }
                
            }
            
        }
        return view('admin.applicants', compact('title', 'applications'));
    }

    public function makeShortList($application_id){
        $applicant = JobApplication::find($application_id);
        $applicant->is_shortlisted = 1;
        $applicant->save();
        return back()->with('success', __('app.success'));
    }

    public function shortlistedApplicant(){
        $title = __('app.shortlisted');
        $employer_id = Auth::user()->id;
        $applications = JobApplication::whereEmployerId($employer_id)->whereIsShortlisted(1)->orderBy('id', 'desc')->paginate(20);

        return view('admin.applicants', compact('title', 'applications'));
    }


    public function profile(){
        $title = trans('app.profile');
        $user = Auth::user();
        $info = $user->userInfo;
        $userExperiences = $user->experiences;
        return view('admin.profile', compact('title', 'user','info','userExperiences'));
    }

    public function profileEdit($id = null){
        $title = trans('app.profile_edit');
        $user = Auth::user();
        $info = $user->userInfo;
        $experiences = Experience::all();
        $userExperiences = $user->experiences()->pluck('id')->toArray();
        if ($id){
            $user = User::find($id);
        }

        $countries = Country::all();

        return view('admin.profile_edit', compact('title', 'user', 'countries','info','experiences','userExperiences'));
    }

    public function profileEditPost($id = null, Request $request){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }
        


        $user = Auth::user();
        if ($id){
            $user = User::find($id);
        }

        if($request->input('experiences')){
            $user->experiences()->detach($user->experiences);
            $user->experiences()->attach($request->input('experiences'));
        }


        $inputsInfo =  $request->except(['_token','experiences','address','title','visiable_cv','resume','surename','name','postal_code','onCLick_apply','country_id','phone','homePostcode','email','gender']);

        $userInfo = UserInfo::where('user_id',$id)->update($inputsInfo);

        $inputs = request()->input();
        if(isset($inputs['employer_type'])){
            $employerTypes = $inputs['employer_type'];
        }else{
            $employerTypes = [];
        }
        $user->userInfo()->update([
            'website_link1'      =>  json_encode($request->website_link1),
            'website_link2'      =>  json_encode($request['website_link2']),
            'employer_type'      =>  json_encode($employerTypes),
            'current_salary'      =>  json_encode($request['current_salary']),
            'languages'      =>  json_encode($request->input('languages')),
            'deisred_salary'      =>  json_encode($request['deisred_salary']),
            'job_type'      =>  json_encode($request['job_type']),
        ]);


        $user->update([
            'title'  =>  $request->title,
            'gender'  =>  $request->gender,
            'surename'  =>  $request->surename,
            'postal_code'  => $request->homePostcode,
            'on_click_apply'  =>  $request->onCLick_apply,
            'visiable_cv'  =>   $request->visiable_cv,
        ]);

        



        if($file = $request->file('resume')){
            $cvName =  $file->hashName();
            Storage::disk('public')->put('uploads\resume',$file);
            $user->cv = "resume/$cvName";
            $user->save();
        }
        // //Validating
        // $rules = [
        //     'email'    => 'required|email|unique:users,email,'.$user->id,
        // ];
        // $this->validate($request, $rules);

        // $inputs = array_except($request->input(), ['_token', 'photo']);
        // $user->update($inputs);

        return back()->with('success', trans('app.profile_edit_success_msg'));
    }


    public function changePassword()
    {
        $title = trans('app.change_password');
        return view('admin.change_password', compact('title'));
    }

    public function changePasswordPost(Request $request)
    {
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }
        $rules = [
            'old_password'  => 'required',
            'new_password'  => 'required|confirmed',
            'new_password_confirmation'  => 'required',
        ];
        $this->validate($request, $rules);

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        //$new_password_confirmation = $request->new_password_confirmation;

        if(Auth::check())
        {
            $logged_user = Auth::user();

            if(Hash::check($old_password, $logged_user->password))
            {
                $logged_user->password = Hash::make($new_password);
                $logged_user->save();
                return redirect()->back()->with('success', trans('app.password_changed_msg'));
            }
            return redirect()->back()->with('error', trans('app.wrong_old_password'));
        }
    }

    public function downloadCv($file)
    { 
         return Storage::disk('public')->download('uploads/resume/'.$file);
    }     
    public function invoice($payment)
    { 
        set_time_limit(300);
        $payment = Payment::find($payment);
        $carbon = new Carbon($payment->created_at);
        $expire= $carbon->addDays(30);
        // dd($expire);
        if($payment->type=='premium'){
     
            $user = $payment->user;
            $pdf = PDF::setOptions(['debugCss' => true, 'debugPng' => true, 'isRemoteEnabled' => true])->loadView('layouts.invoice', compact(['order','payment', 'user', 'expire']));
        }else{
            $order = $payment->order;
            $user = $payment->user;
            $pdf = PDF::setOptions(['debugCss' => true, 'debugPng' => true, 'isRemoteEnabled' => true])->loadView('layouts.invoice', compact(['order','payment', 'user', 'expire'])); 
        }
        // // //return $pdf->download('invoice.pdf');
        error_reporting(E_ALL ^ E_DEPRECATED);
        return $pdf->stream('invoice.pdf');
        // return view('layouts.invoice',compact(['order','payment', 'user']));
    }      
    public function success2()
    {   $user = User::find(1);
        $order=$user->applications->last();
        
        return view('paymentsuccess', compact(['user','order']));
    }   

}
