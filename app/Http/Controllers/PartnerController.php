<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Country;
use App\Models\Designation;
use App\Models\Partner;
use App\Models\Quota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request)
    {
//        return dd($request);
        $req = Validator::make($request->all(), [
            'role_id' => 'required',
            'password' => 'required',
            'full_name' => 'required',
            'email' => 'required|unique:partners',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            } else {
                $user = new User();
                $user->role_id = $request->role_id;
                if ($request->role_id ==  3){
                    $user->unique_id = $request->unique_id;
                }
                $user->name = $request->full_name;
                $user->phone = $request->phone;
                $user->email = $request->email;
                $user->created_by = auth()->user()->id;
                $user->password = bcrypt($request->password);
                $savedUser = $user->save();
                if ($savedUser){
                    $data = new Partner();
                    $data->user_id = $user->id;
                    $data->role_id = $request->role_id;
                    $data->full_name = $request->full_name;
                    $data->phone = $request->phone;
                    $data->email = $request->email;
                    $data->password = bcrypt($request->password);
                    $data->license_no = $request->license_no;
                    $data->license_file = $request->license_file?$this->getLicenseUrl($request) : null;
                    $data->authorize_person = $request->authorize_person;
                    $data->trade_license_no = $request->trade_license_no;
                    $data->address = json_encode($request->address);
                    $data->bank_account_name = $request->bank_account_name;
                    $data->bank_account_number = $request->bank_account_number;
                    $data->bank_name = $request->bank_name;
                    $data->branch_name = $request->branch_name;
                    $data->routing_number = $request->routing_number;
                    $data->allotment = $request->allotment;
                    $data->quota = 10;
                    $data->save();
//                    if ($request->role_id == 4){
//                        $countries = Country::all();
//                        $designations = Designation::all();
//                        foreach ($countries as $country){
//                            foreach ($designations as $designation){
//                                $data = new Quota();
//                                $data->country_id = $country->id;
//                                $data->designation_id = $designation->id;
//                                $data->agent = $user->id;
//                                $data->save();
//                            }
//                        }
//                    }

                    $msg = $this->getMsg($request, $data);
                    $smsResponse = $this->send_sms($request, $msg);

                    return response()->json([
                        'success' => true,
                        'message' => 'Partner Created Successfully!',
                        'smsResponse' => $smsResponse,
                        'data' => $data,
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function update(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
            'role_id' => 'required',
            'password' => 'required',
            'full_name' => 'required',
            'email' => 'required|unique:partners,email,'.$request->id,
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }else{
                $data = Partner::where('id', $request->id)->first();
                $data->user_id = auth()->user()->id;
                $data->role_id = $request->role_id;
                $data->full_name = $request->full_name;
                $data->email = $request->email;
                $data->phone = $request->phone;
                $data->password = bcrypt($request->password);
                $data->license_no = $request->license_no;
                $data->authorize_person = $request->authorize_person;
                $data->trade_license_no = $request->trade_license_no;
                $data->address = json_encode($request->address);
                $data->bank_account_name = $request->bank_account_name;
                $data->bank_account_number = $request->bank_account_number;
                $data->bank_name = $request->bank_name;
                $data->branch_name = $request->branch_name;
                $data->routing_number = $request->routing_number;
                $data->allotment = $request->allotment;
                $data->quota = $request->quota;
                $data->update();
                return response()->json([
                    'success' => true,
                    'message' => 'Partner Update Successfully!',
                    'data' => $data,
                ]);
            }
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function quotaUpdate(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }else{
                $data = Partner::where('user_id', $request->id)->first();
                $quota = $data->quota;
                if ($quota >= 1000000){
                    $data->quota = $request->quota;
                }else{
                    $data->quota = $quota + $request->quota;
                }

                $data->update();
                return response()->json([
                    'success' => true,
                    'message' => 'Partner Update Successfully!',
                    'data' => $data,
                ]);
            }
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }


    
    public function destroy(Request $request){
        try {
            $data = Partner::where('id', $request->id)->first();
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Partner Deleted Successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function all(){
        try {
            $data = Partner::orderby('id', 'desc')->with('user')->with('role')->get();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function percentages(){
        try {
            if (auth()->user()->role_id == 1){
                $data = User::where('role_id', 4)
                    ->with('child.candidate')
                    ->get();

                $countData = [];

                foreach ($data as $user) {
                    $childCount = $user->child()->count();
                    $totalCandidateCount = 0;
                    $childWithPhotoCount = 0;
                    $childWithNidCount = 0;
                    $childWithPassportCount = 0;
                    $childWithExpCount = 0;
                    $childWithAcademicCount = 0;
                    $childWithTrainingCount = 0;

                    foreach ($user->child as $child) {
                        if (is_object($child->candidate)) {
                            if ($child->candidate->photo !== null) {
                                $childWithPhotoCount++;
                            }
                            if ($child->candidate->nid_file !== null) {
                                $childWithNidCount++;
                            }
                            if ($child->candidate->passport_file !== null) {
                                $childWithPassportCount++;
                            }
                            if ($child->candidate->experience_file !== null) {
                                $childWithExpCount++;
                            }
                            if ($child->candidate->academic_file !== null) {
                                $childWithAcademicCount++;
                            }
                            if ($child->candidate->training_file !== null) {
                                $childWithTrainingCount++;
                            }
                        }
                    }
                    $percentageWithPhoto = ($childCount > 0) ? ($childWithPhotoCount / $childCount) * 100 : 0;
                    $percentageWithNid = ($childCount > 0) ? ($childWithNidCount / $childCount) * 100 : 0;
                    $percentageWithPassport = ($childCount > 0) ? ($childWithPassportCount / $childCount) * 100 : 0;
                    $percentageWithExperience = ($childCount > 0) ? ($childWithExpCount / $childCount) * 100 : 0;
                    $percentageWithAcademic = ($childCount > 0) ? ($childWithAcademicCount / $childCount) * 100 : 0;
                    $percentageWithTraining = ($childCount > 0) ? ($childWithTrainingCount / $childCount) * 100 : 0;

                    $countData[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'can_count' => $childCount,
                        'can_with_photo_count' => $childWithPhotoCount,
                        'can_with_nid_count' => $childWithNidCount,
                        'can_with_passport_count' => $childWithPassportCount,
                        'can_with_experience_count' => $childWithExpCount,
                        'can_with_academic_count' => $childWithAcademicCount,
                        'can_with_training_count' => $childWithTrainingCount,
                        'percentage_with_photo' => $percentageWithPhoto,
                        'percentage_with_nid' => $percentageWithNid,
                        'percentage_with_passport' => $percentageWithPassport,
                        'percentage_with_experience' => $percentageWithExperience,
                        'percentage_with_academic' => $percentageWithAcademic,
                        'percentage_with_training' => $percentageWithTraining
                    ];
                }
                return $countData;
            }else{
                $user = User::where('id', auth()->user()->id)
                    ->with('child.candidate')
                    ->first();

                $countData = [];

//                foreach ($data as $user) {
                    $childCount = $user->child()->count();
                    $totalCandidateCount = 0;
                    $childWithPhotoCount = 0;
                    $childWithNidCount = 0;
                    $childWithPassportCount = 0;
                    $childWithExpCount = 0;
                    $childWithAcademicCount = 0;
                    $childWithTrainingCount = 0;

                    foreach ($user->child as $child) {
                        if (is_object($child->candidate)) {
                            if ($child->candidate->photo !== null) {
                                $childWithPhotoCount++;
                            }
                            if ($child->candidate->nid_file !== null) {
                                $childWithNidCount++;
                            }
                            if ($child->candidate->passport_file !== null) {
                                $childWithPassportCount++;
                            }
                            if ($child->candidate->experience_file !== null) {
                                $childWithExpCount++;
                            }
                            if ($child->candidate->academic_file !== null) {
                                $childWithAcademicCount++;
                            }
                            if ($child->candidate->training_file !== null) {
                                $childWithTrainingCount++;
                            }
                        }
                    }
                    $percentageWithPhoto = ($childCount > 0) ? ($childWithPhotoCount / $childCount) * 100 : 0;
                    $percentageWithNid = ($childCount > 0) ? ($childWithNidCount / $childCount) * 100 : 0;
                    $percentageWithPassport = ($childCount > 0) ? ($childWithPassportCount / $childCount) * 100 : 0;
                    $percentageWithExperience = ($childCount > 0) ? ($childWithExpCount / $childCount) * 100 : 0;
                    $percentageWithAcademic = ($childCount > 0) ? ($childWithAcademicCount / $childCount) * 100 : 0;
                    $percentageWithTraining = ($childCount > 0) ? ($childWithTrainingCount / $childCount) * 100 : 0;

                    $countData[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'can_count' => $childCount,
                        'can_with_photo_count' => $childWithPhotoCount,
                        'can_with_nid_count' => $childWithNidCount,
                        'can_with_passport_count' => $childWithPassportCount,
                        'can_with_experience_count' => $childWithExpCount,
                        'can_with_academic_count' => $childWithAcademicCount,
                        'can_with_training_count' => $childWithTrainingCount,
                        'percentage_with_photo' => $percentageWithPhoto,
                        'percentage_with_nid' => $percentageWithNid,
                        'percentage_with_passport' => $percentageWithPassport,
                        'percentage_with_experience' => $percentageWithExperience,
                        'percentage_with_academic' => $percentageWithAcademic,
                        'percentage_with_training' => $percentageWithTraining
                    ];
//                }
                return $countData;
            }
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getPartners(Request $request){
        try {
            if ($request->pg == ''){
                $data = User::where('role_id', $request->role_id)->with('partner')->with('createdBy')->with('role')->get();
                $count = [];
                foreach ($data as $val){
                    $count[] = User::where('role_id', 5)->where('created_by', $val->id)->count();
                }
            }else{
                $data1 = User::where('role_id', $request->role_id)->get();
                $data = User::where('role_id', $request->role_id)->with('partner')->with('createdBy')->with('role')->paginate(10);
                $count = [];
                foreach ($data1 as $val){
                    $count[] = User::where('role_id', 5)->where('created_by', $val->id)->count();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data,
                'count' => $count,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function get_partners_name(Request $request){

        $data = User::where('role_id', $request->role_id)
        // ->with('createdBy:id,email,name') // Only fetch the `id` and `email` fields for `createdBy`>with('role:id') // Only fetch the `id` field for `role`
        ->select('users.name', 'users.created_by', 'users.role_id') // Select relevant fields from `users`
        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data,

            ]);

    }


    public function getMsg($request, $data){
        if ($data->role_id == 5) {
            $msg = 'প্রিয় আবেদনকারী,
TSM-MGES এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি- ' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBAL';
            return $msg;
        } elseif ($data->role_id == 4) {
            $msg = 'Dear, ' . $request->full_name . '
আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি-
' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBA';
            return $msg;
        } elseif ($data->role_id == 3) {
            $msg = 'Dear, ' . $request->full_name . '
আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি-
' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBAL';
            return $msg;
        } elseif ($data->role_id == 2) {
            $msg = 'Dear, ' . $request->full_name . '
আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি-
' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBAL';
            return $msg;
        } elseif ($data->role_id == 1) {
            $msg = 'You are Admin';
            return $msg;
        }
    }
    public function send_sms($request, $msg)
    {
        $url = "http://bulksmsbd.net/api/smsapi";
        $api_key = "OyrexM3Rft3HiP3IfZ8C";
        $senderid = "8809617613568";
        $number = $request->phone;
        $message = $msg;

        $data = [
            "api_key" => $api_key,
            "senderid" => $senderid,
            "number" => $number,
            "message" => $message
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function getLicenseUrl($request)
    {
        $image = $request->file('license_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'partner_files/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }


    
    private function send_message($number, $message)
    {
        $url = "http://bulksmsbd.net/api/smsapi";
        $api_key = "OyrexM3Rft3HiP3IfZ8C";
        $senderid = "8809617613568";

        $data = [
            "api_key"  => $api_key,
            "senderid" => $senderid,
            "number"   => $number,
            "message"  => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function sendRoleBasedMessage($role_id, $mobile)
    {
        // Find user by phone number
        $user = User::where('phone', $mobile)->first();

        if (!$user) {
            return "User not found for this mobile number.";
        }

        // Generate a strong random 6-character password (letters + numbers)
        $plainPassword = Str::random(6);

        // Save hashed password
        $user->password = Hash::make($plainPassword);
        $user->save();

        // Prepare message based on role_id
        switch ($role_id) {
            case 5:
                $msg = "প্রিয় আবেদনকারী, TSM-MGES এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি- {$user->email} এবং পাসওয়ার্ড- {$plainPassword} Web link: MGES.GLOBAL";
                break;
            case 4:
            case 3:
            case 2:
                $msg = "Dear, {$user->full_name} আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি- {$user->email} এবং পাসওয়ার্ড- {$plainPassword} Web link: MGES.GLOBAL";
                break;
            case 1:
                $msg = "You are Admin";
                break;
            default:
                return "Invalid role_id";
        }

        // Send SMS
        return $this->send_message($mobile, $msg);
    }
}
