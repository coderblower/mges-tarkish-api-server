<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Quota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'getUser', 'groupBy', 'approveCandidatesWithAllDocuments']]);
    }

    public function create(Request $request)
    {

        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'phone' => 'required|string|min:11|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Return validation error response if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'error' => $validator->errors(),
            ]);
        }

        try {
            // Create the user
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->created_by = auth()->user() ? auth()->user()->id : null;
            $user->role_id = $request->role_id ?? 5;  // Default to role_id = 5
            $user->password = bcrypt($request->password);
            $user->save();

            // Additional validation for role 5 (candidate creation)
            if ($user->role_id == 5) {
                $candidateValidator = Validator::make($request->all(), [
                    'passport' => 'required|unique:candidates',
                ]);

                if ($candidateValidator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $candidateValidator->errors(),
                        'error' => $candidateValidator->errors(),
                    ]);
                }

                // Create candidate
                $candidate = $this->createCandidate($request, $user->id);
            }

            // Update user's created_by and send SMS
            $user->created_by = auth()->user() ? auth()->user()->id : $user->id;
            $user->update();

            $msg = $this->getMsg($request, $user);
            $smsResponse = $this->send_sms($request, $msg);

            return response()->json([
                'success' => true,
                'message' => 'Successfully created candidate!',
                'smsResponse' => $smsResponse,
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred during user creation!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    // Extracted method to handle candidate creation
    private function createCandidate(Request $request, $userId)
    {
        try {
            $candidate = new Candidate();
            $candidate->user_id = $userId;
            $candidate->gender = $request->gender;
            $candidate->marital_status = $request->marital_status;
            $candidate->issued_by = $request->issued_by;
            $candidate->referred_by = $request->referred_by;
            $candidate->firstName = $request->firstName;
            $candidate->lastName = $request->lastName;
            $candidate->dateOfIssue = trim($request->dateOfIssue, '"');
            $candidate->visitRussiaNumber = $request->visitRussiaNumber;
            $candidate->russia_trip_date = trim($request->russia_trip_date, '"');
            $candidate->hostOrganization = $request->hostOrganization;
            $candidate->route_Journey = $request->route_Journey;
            $candidate->relativesStaying = $request->relativesStaying;
            $candidate->refusedRussian = $request->refusedRussian;
            $candidate->deportedRussia = $request->deportedRussia;
            $candidate->spousesName = $request->spousesName;
            $candidate->spouses_birth_date = trim($request->spouses_birth_date, '"');
            $candidate->full_name = $request->full_name;
            $candidate->father_name = $request->father_name;
            $candidate->mother_name = $request->mother_name;
            $candidate->birth_date = trim($request->birth_date, '"');
            $candidate->religion = $request->religion;
            $candidate->country = $request->country;
            $candidate->nid = $request->nid;
            $candidate->nid_file = $request->nid_file ? $this->getNidUrl($request) : null;
            $candidate->passport = $request->passport;
            $candidate->expiry_date = trim($request->expiry_date, '"');
            $candidate->medical_center_id = $request->medical_center_id;
            $candidate->designation_id = $request->designation_id;
            $candidate->address = json_encode($request->address);
            $candidate->city = $request->city;
            $candidate->academic = json_encode($request->academic);
            $candidate->experience = json_encode($request->experience);
            $candidate->training = json_encode($request->training);
            $candidate->is_active = $request->is_active ?? 0;
            $candidate->photo = $request->photo ? $this->getImageUrl($request) : null;
            $candidate->passport_file = $request->passport_file ? $this->getPassportUrl($request) : null;
            $candidate->experience_file = $request->experience_file ? $this->getExpUrl($request) : null;
            $candidate->academic_file = $request->academic_file ? $this->getAcademicUrl($request) : null;
            $candidate->training_file = $request->training_file ? $this->getTrainingUrl($request) : null;
            $candidate->qr_code = $this->getQRUrl($userId);

            // If the user is an admin, automatically approve
            if (auth()->user()->role_id == 1) {
                $candidate->approval_status = 'approved';
            }

            $candidate->save();

            return $candidate;

        } catch (\Exception $e) {
            throw new \Exception('Failed to create candidate: ' . $e->getMessage());
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
    public function searchCandidate(Request $request){
        if (auth()->user()->role_id== 1 || auth()->user()->role_id== 3){
            $results = User::with('candidate')
                ->when($request->has('creator'), function($query) use ($request) {
                    $query->where('created_by', $request->creator);})
                ->when($request->country != '', function($query) use ($request) {
                    $query->WhereHas('candidate', function ($query) use ($request) {
                        $query->where('country',$request->country);});
                })
                ->where('role_id', 5)
                ->when($request->phone != '', function ($query) use ($request) {
                $query->where('phone', 'like', "%$request->phone%")
                    ->orWhere('email', 'like', "%$request->phone%")
                    ->orWhereHas('candidate', function ($query) use ($request) {
                        $query->where('passport', 'like', "%$request->phone%");});
            })
                ->with('createdBy')
                ->WhereHas('candidate', function ($query) use ($request) {
                    $query->orderBy('updated_at', 'desc');})


                ->paginate(10);
            $results1 = User::with('candidate')
                ->when($request->has('creator'), function($query) use ($request) {
                    $query->where('created_by', $request->creator);})
                ->where('role_id', 5)
                ->when($request->phone != '', function ($query) use ($request) {
                $query->where('phone', 'like', "%$request->phone%")
                    ->orWhere('email', 'like', "%$request->phone%")
                    ->orWhereHas('candidate', function ($query) use ($request) {
                        $query->where('passport', 'like', "%$request->phone%");});
            })
                ->with('createdBy')
                ->orderBy('id', 'desc')
                ->count();
        }
        else if (auth()->user()->role_id== 2){
            $results = User::with('candidate')
                ->when($request->has('creator'), function($query) use ($request) {
                    $query->where('created_by', $request->creator);})
                ->when($request->country != '', function($query) use ($request) {
                    $query->WhereHas('candidate', function ($query) use ($request) {
                        $query->where('country',$request->country);});
                })
                ->where('role_id', 5)
                ->when($request->phone != '', function ($query) use ($request) {
                $query->where('phone', 'like', "%$request->phone%")
                    ->orWhere('email', 'like', "%$request->phone%")
                    ->orWhereHas('candidate', function ($query) use ($request) {
                        $query->where('passport', 'like', "%$request->phone%");});
            })
//                ->WhereHas('candidate', function ($query) use ($request) {
//                    $query->where('approval_status','!=','pending')->orderBy('updated_at', 'desc');})
                ->with('createdBy')
                ->WhereHas('candidate', function ($query) use ($request) {
                    $query->orderBy('updated_at', 'desc');})
                ->get();
            $results1 = User::with('candidate')
                ->when($request->has('creator'), function($query) use ($request) {
                    $query->where('created_by', $request->creator);})
                ->where('role_id', 5)
                ->when($request->phone != '', function ($query) use ($request) {
                $query->where('phone', 'like', "%$request->phone%")
                    ->orWhere('email', 'like', "%$request->phone%")
                    ->orWhereHas('candidate', function ($query) use ($request) {
                        $query->where('passport', 'like', "%$request->phone%");});
            })
                ->with('createdBy')
                ->orderBy('id', 'desc')
                ->count();
        }else{
            $results = User::with('candidate')->where('created_by', auth()->user()->id)->where('role_id', 5)
                ->when($request->country != '', function($query) use ($request) {
                    $query->WhereHas('candidate', function ($query) use ($request) {
                        $query->where('country',$request->country);});
                })
                ->when($request->phone != '', function ($query) use ($request) {
                $query->where('phone', 'like', "%$request->phone%")
                    ->orWhere('email', 'like', "%$request->phone%")
                    ->orWhereHas('candidate', function ($query) use ($request) {
                        $query->where('passport', 'like', "%$request->phone%");});
            })
//                ->WhereHas('candidate', function ($query) use ($request) {
//                    $query->where('approval_status','!=','pending')->orderBy('updated_at', 'desc');})
                ->WhereHas('candidate', function ($query) use ($request) {
                    $query->orderBy('updated_at', 'desc');})
                ->get();
            $results1 = User::with('candidate')->where('created_by', auth()->user()->id)->where('role_id', 5)
                ->when($request->phone != '', function ($query) use ($request) {
                $query->where('phone', 'like', "%$request->phone%")
                    ->orWhere('email', 'like', "%$request->phone%")
                    ->orWhereHas('candidate', function ($query) use ($request) {
                        $query->where('passport', 'like', "%$request->phone%");});
            })
                ->orderBy('id', 'desc')
                ->count();
        }


        return response()->json([
            'data'=> $results,
            'count'=> $results1,
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
