<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Quota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'getUser', 'groupBy', 'approveCandidatesWithAllDocuments']]);
    }
    public function create(Request $request){
//        return response()->json($request);
        $req = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'phone' => 'required|string|min:11|unique:users',
            'password' => 'required|string|min:6',
        ]);
        try {
            if ($req->fails()) {
//                return response()->json($req->errors(), 422);
                return response()->json([
                    'success' => false,
                    'message' => $req->errors(),
                    'error' => $req->errors(),
                ]);
            }else{
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->created_by = auth()->user()?auth()->user()->id:$user->id;
                $user->role_id = 5;
                $user->password = bcrypt($request->password);
                $user->save();
                $user_id = $user->id;
//                $quota = Quota::where('country_id', $request->country)->where('designation_id', $request->designation_id)->where('agent', auth()->user()->id)->first();

//                if (auth()->user()->role_id == 4){
//                    $quota = Quota::where('country_id', $request->country)->where('designation_id', $request->designation_id)->where('agent', auth()->user()->id)->first();
//                    if ($quota && $quota->quota != 0){
//                        $used = $quota->quota_used;
//                        $quota->quota_used = $used + 1;
//                        $quota->update();
//                        if ($quota->quota_used == 0){
//                            return response()->json([
//                                'success' => false,
//                                'msg' => 'quota problem',
//                            ]);
//                        }
//                    }else{
//                        return response()->json([
//                            'success' => false,
//                            'msg' => 'quota problem2',
//                        ]);
//                    }
//                }else{
//                    return response()->json([
//                        'success' => false,
//                        'msg' => 'role problem',
//                    ]);
//                }

                if ($request->has('role_id')){
                    $user->role_id = $request->role_id;

                }else{
                    $user->role_id = 5;
                    try {
                        $req1 = Validator::make($request->all(), [
                            'passport' => 'required|unique:candidates',
                        ]);
                        if ($req1->fails()) {
//                            return response()->json($req1->errors(), 422);
                            return response()->json([
                                'success' => false,
                                'message' => $req1->errors(),
                                'error' => $req1->errors(),
                            ]);
                        }else {
                            $data = new Candidate();
                            $data->user_id = $user_id;
                            $data->gender = $request->gender;
                            $data->marital_status = $request->marital_status;
                            $data->issued_by = $request->issued_by;
                            $data->referred_by = $request->referred_by;
                            $data->firstName = $request->firstName;
                            $data->lastName = $request->lastName;
                            $data->dateOfIssue = $request->dateOfIssue;
                            $data->visitRussiaNumber = $request->visitRussiaNumber;
                            $data->russia_trip_date = $request->russia_trip_date;
                            $data->hostOrganization = $request->hostOrganization;
                            $data->route_Journey = $request->route_Journey;
                            $data->relativesStaying = $request->relativesStaying;
                            $data->refusedRussian = $request->refusedRussian;
                            $data->deportedRussia = $request->deportedRussia;
                            $data->spousesName = $request->spousesName;
                            $data->spouses_birth_date = $request->spouses_birth_date;
                            $data->full_name = $request->full_name;
                            $data->father_name = $request->father_name;
                            $data->mother_name = $request->mother_name;
                            $data->birth_date = $request->birth_date;
                            $data->religion = $request->religion;
                            $data->country = $request->country;
                            $data->nid = $request->nid;
                            $data->nid_file = $request->nid_file ? $this->getNidUrl($request) : null;
                            $data->passport = $request->passport;
                            $data->expiry_date = $request->expiry_date;
                            $data->medical_center_id = $request->medical_center_id;
                            $data->designation_id = $request->designation_id;
                            $data->address = json_encode($request->address);
                            $data->city = $request->city;
                            $data->academic = json_encode($request->academic);
                            $data->experience = json_encode($request->experience);
                            $data->training = json_encode($request->training);
                            if (auth()->user()->role_id ==1){
                                $data->approval_status = 'approved';
                            }
//                        $data->medical_status = $request->medical_status;
//                        $data->training_status = $request->training_status;
                            $data->is_active = $request->is_active ? $request->is_active : 0;
                            $data->photo = $request->photo ? $this->getImageUrl($request) : null;
                            $data->passport_file = $request->passport_file ? $this->getPassportUrl($request) : null;
                            $data->experience_file = $request->experience_file ? $this->getExpUrl($request) : null;
                            $data->academic_file = $request->academic_file ? $this->getAcademicUrl($request) : null;
                            $data->training_file = $request->training_file ? $this->getTrainingUrl($request) : null;
                            $data->qr_code = $this->getQRUrl($user_id);
                            $data->save();
                        }
                    }catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'failed at Candidate!',
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                $user->created_by = auth()->user()? auth()->user()->id : $user->id;
                $user->update();
                $msg = $this->getMsg($request, $user);
                $smsResponse = $this->send_sms($request, $msg);
                return response()->json([
                    'success' => true,
                    'message' => 'successfully created candidate!',
                    'smsResponse' => $smsResponse,
                    'data' => $user,
                ]);
            }
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
//                'error' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function update(Request $request){
        $req = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email,'.$request->id,
            'phone' => 'required|string|min:11|unique:users,phone,'.$request->id,
            'password' => 'required|string|min:6',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $user = User::where('id', $request->id)->first();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = bcrypt($request->password);
            $user->role_id = $request->role_id? $request->role_id : 5;
            $user->created_by = auth()->user()? auth()->user()->id : null;
            $user->update();
            return response()->json([
                'success' => true,
                'message' => 'User Updated Successfully!',
                'data' => $user,
            ]);
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
            $user = User::where('id', $request->id)->first();
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User Deleted Successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function softDestroy(Request $request){
        try {
            $user = User::where('id', $request->id)->first();
            $user->delete();
//            $user->forceDelete();
            return response()->json([
                'success' => true,
                'message' => 'User Deleted Successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function restore(Request $request){
        try {
            $user = User::where('id', $request->id)->first();
            $user->restore();
            return response()->json([
                'success' => true,
                'message' => 'User Restored Successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getTrashedUsers(){
        try {
            $user = User::onlyTrashed()->get();
//            $user = User::withTrashed()->get();
            return response()->json([
                'success' => true,
                'message' => 'User Fetched Successfully!',
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
            $data = User::with('role')->orderby('id','desc')->get();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getUser(Request $request){
        try {
            $data = User::with('role')->with('candidate')->with('partner')->with('candidate.designation')->with('report')->with('preskilled')->with('skill')->where('id',$request->id)->first();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getUser01(Request $request){
        try {
            $data = User::with('role')->with('candidate')->with('partner')->where('id',$request->id)->first();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function approveCandidatesWithAllDocuments() {
        // Fetch only candidates with all required documents
//        $candidates = Candidate::whereNotNull('photo')
//            ->whereNotNull('nid_file')
//            ->whereNotNull('passport_file')
//            ->whereNotNull('training_file')
//            ->get();
//
//        foreach ($candidates as $candidate) {
//            // Update approval status to 'approved'
//            $candidate->approval_status = 'approved';
//            // Save the updated record
//            $candidate->update();
//        }
        return 'pp';
    }
    public function count(){
        try {
            $candidate_count = User::orderby('id', 'desc')->where('role_id', 5)->count();
            $medical_count = User::orderby('id', 'desc')->where('role_id', 3)->count();
            $training_count = User::orderby('id', 'desc')->where('role_id', 2)->count();
            $agent_count = User::orderby('id', 'desc')->where('role_id', 4)->count();

            $datayy = User::where('role_id', 5)
                ->with('candidate')
                ->get();
            $childCount = $datayy->count();
            $countData = [];
            $childWithPhotoCount = 0;
            foreach ($datayy as $user) {
                $totalCandidateCount = 0;


//                foreach ($user->candidate as $child) {
                    if (is_object($user->candidate)) {
                        if ($user->candidate->photo !== null &&
                            $user->candidate->nid_file !== null &&
                            $user->candidate->passport_file !== null &&
                            $user->candidate->training_file !== null ) {
                            $childWithPhotoCount++;
                        }
                    }
//                }

            }
            $percentageWithPhoto = ($childCount > 0) ? ($childWithPhotoCount / $childCount) * 100 : 0;
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => [
                    'candidate'=>$candidate_count,
                    'medical'=>$medical_count,
                    'training'=>$training_count,
                    'agent'=>$agent_count,
                    'childWithMinFile'=>$childWithPhotoCount,
                    'percentageWithMinFile'=>$percentageWithPhoto,
                ],
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function groupBy(Request $request){
        try {
            if ($request->role_id == 2){
                $data = User::orderby('id', 'desc')->where('role_id', 2)->paginate(10);
            }elseif ($request->role_id == 3){
                $data = User::orderby('id', 'desc')->with('partner')->where('role_id', 3)->paginate(10);
            }elseif ($request->role_id == 4){
                $data = User::orderby('id', 'desc')->where('role_id', 4)->paginate(10);
            }elseif ($request->role_id == 5){
                $data = User::orderby('id', 'desc')->where('role_id', 5)->paginate(10);
            }elseif ($request->role_id == 1){
                $data = User::orderby('id', 'desc')->where('role_id', 1)->paginate(10);
            }elseif ($request->role_id == 'four'){
                $candidate = User::orderby('id', 'desc')->where('role_id', 5)->paginate(10);
                $medical = User::orderby('id', 'desc')->where('role_id', 3)->paginate(10);
                $training = User::orderby('id', 'desc')->where('role_id', 2)->paginate(10);
                $agent = User::orderby('id', 'desc')->where('role_id', 4)->paginate(10);
                $data = [
                    'candidate'=>$candidate,
                    'medical'=>$medical,
                    'training'=>$training,
                    'agent'=>$agent,
                ];
            }
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
    public function getImageUrl($request)
    {
        $image = $request->file('photo');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
//        $imageUrl = $path.$imageName;
//        Storage::disk('s3')->put($path.$this->imageName, file_get_contents($request->photo));
        return $path.$imageName;
    }
    public function getQRUrl($user_id)
    {
        $user = User::where('id', $user_id)->with('role')->first();
        $path = 'candidate_qrcode/'.time().'.svg';
        QrCode::size(600)->generate('https://www.mges.global/user_details/'.$user->id, $path);
        return $path;
    }
    public function getMsg($request, $user){
        if ($user->role_id == 5) {
            $msg = 'প্রিয় আবেদনকারী,
TSM-MGES এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি- ' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBAL';
            return $msg;
        } elseif ($user->role_id == 4) {
            $msg = 'Dear, ' . $request->full_name . '
আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি-
' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBA';
            return $msg;
        } elseif ($user->role_id == 3) {
            $msg = 'Dear, ' . $request->full_name . '
আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি-
' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBAL';
            return $msg;
        } elseif ($user->role_id == 2) {
            $msg = 'Dear, ' . $request->full_name . '
আপনাকে স্বাগতম, MGES-এ আপনার একাউন্ট সম্পন্ন হয়েছে। লগইন আইডি-
' . $request->email . ' এবং পাসওয়ার্ড- ' . $request->password . '
Web link: MGES.GLOBAL';
            return $msg;
        } elseif ($user->role_id == 1) {
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


public function searchCandidate(Request $request)
{
    $startTime = microtime(true);

    // Base query with required relationships
	$query = User::with(['candidate:id,user_id,passport,country,qr_code,photo', 'createdBy:id,name'])
        ->where('role_id', 5);

    // Apply creator filter if provided
    if ($request->filled('creator')) {
        $query->where('created_by', $request->creator);
    }

    // Full-text search on 'country' in 'candidates' table
    if ($request->filled('country')) {
        $query->whereExists(function ($q) use ($request) {
            $q->select(DB::raw(1))
              ->from('candidates')
              ->whereColumn('candidates.user_id', 'users.id')
              ->where('country', $request->country);
        });
    }

    // Full-text search for phone, email, and passport
    if ($request->filled('phone')) {
        $searchText = $request->phone;
        $query->where(function ($q) use ($searchText) {
            $q->whereRaw("MATCH(phone, email) AGAINST(? IN BOOLEAN MODE)", [$searchText])
              ->orWhereExists(function ($q) use ($searchText) {
                  $q->select(DB::raw(1))
                    ->from('candidates')
                    ->whereColumn('candidates.user_id', 'users.id')
                    ->whereRaw("MATCH(passport) AGAINST(? IN BOOLEAN MODE)", [$searchText]);
              });
        });
    }

    // Pagination based on user role
    $perPage = auth()->user()->role_id == 1 || auth()->user()->role_id == 3 ? 10 : 5;
    $results = $query->orderBy('updated_at', 'desc')->paginate($perPage);

    // Measure execution time
    $endTime = microtime(true);
    $queryTime = round(($endTime - $startTime), 2);

    return response()->json([
        'data' => $results,
        'query_time_sec' => $queryTime,
    ]);
}

     public function searchCandidate_test(Request $request)
    {
        // Start measuring time
        $startTime = microtime(true);


        $participants = User::query()
        ->when($request->filled('phone'), function ($query) use ($request) {
            $query->where( function ($q) use ($request) {
                // first name or last name or email or phone
                $q->where('phone', 'like', "%{$request->phone}%")
                          ->orWhere('email', 'like', "%{$request->phone}%")
                          ->orWhereHas('candidate', function ($query) use ($request) {
                              $query->where('passport', 'like', "%{$request->phone}%");
                          });

            });
        })
        ->with(['candidate', 'createdBy'])

        ->where('role_id', 5)

        ->paginate(20);




        // Apply additional role-specific filters


        // End measuring time
        $endTime = microtime(true);
        $queryTime = round(($endTime - $startTime), 2); // Keep it in seconds

        return response()->json([
            'data' => $results,

            'query_time_sec' => $queryTime, // Include query time in response in seconds
        ]);
    }

   
    public function profileUpdate(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }else{
                $user = User::where('id', $request->id)->first();
                $user->name = $request->name;
                if ($request->password != null){
                    $user->password = bcrypt($request->password);
                }
                $user->update();
                return response()->json([
                    'success' => true,
                    'message' => 'User profile Updated Successfully!',
                    'data' => $user,
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
    public function getTrainingUrl($request)
    {
        $image = $request->file('training_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }
    public function getExpUrl($request)
    {
        $image = $request->file('experience_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }
    public function getAcademicUrl($request)
    {
        $image = $request->file('academic_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }
    public function getPassportUrl($request)
    {
        $image = $request->file('passport_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }
    public function getNidUrl($request)
    {
        $image = $request->file('nid_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }
}
