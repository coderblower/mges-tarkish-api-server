<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateMedicalTest;
use App\Models\Quota;
use App\Models\User;
use App\Notifications\UserDeletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function League\Flysystem\move;

use PDF;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;



class CandidateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['send_sms']]);
    }
    public function create(Request $request){
//        try {
//            $data = new Candidate();
//            $data->user_id = auth()->user()->id;
//            $data->gender = $request->gender;
//            $data->marital_status = $request->marital_status;
//            $data->issued_by = $request->issued_by;
//            $data->referred_by = $request->referred_by;
//            $data->firstName = $request->firstName;
//            $data->lastName = $request->lastName;
//            $data->dateOfIssue = $request->dateOfIssue;
//            $data->visitRussiaNumber = $request->visitRussiaNumber;
//            $data->russia_trip_date = $request->russia_trip_date;
//            $data->hostOrganization = $request->hostOrganization;
//            $data->route_Journey = $request->route_Journey;
//            $data->relativesStaying = $request->relativesStaying;
//            $data->refusedRussian = $request->refusedRussian;
//            $data->deportedRussia = $request->deportedRussia;
//            $data->spousesName = $request->spousesName;
//            $data->spouses_birth_date = $request->spouses_birth_date;
//            $data->full_name = $request->full_name;
//            $data->father_name = $request->father_name;
//            $data->mother_name = $request->mother_name;
//            $data->birth_date = $request->birth_date;
//            $data->religion = $request->religion;
//            $data->nid = $request->nid;
////            $data->nid_file = $request->nid_file ? $this->getNidUrl($request) : null;
//            $data->passport = $request->passport;
//            $data->expiry_date = $request->expiry_date;
//            $data->medical_center_id = $request->medical_center_id;
//            $data->address = json_encode($request->address);
//            $data->city = $request->city;
//            $data->academic = json_encode($request->academic);
//            $data->experience = json_encode($request->experience);
//            $data->training = json_encode($request->training);
//            $data->medical_status = $request->medical_status;
//            $data->training_status = $request->training_status;
//            $data->is_active = $request->is_active;
////            $data->photo = $request->photo?$this->getImageUrl($request) : null;
////            $data->passport_file = $request->passport_file?$this->getPassportUrl($request) : null;
////            $data->experience_file = $request->experience_file?$this->getExpUrl($request) : null;
////            $data->academic_file = $request->academic_file?$this->getAcademicUrl($request) : null;
////            $data->training_file = $request->training_file?$this->getTrainingUrl($request) : null;
////            $data->qr_code = $this->getQRUrl($request);
////            $data->save();
//            if ($data){
//                if (auth()->user->role_id == 4){
//                    $quota = Quota::where('country_id', $request->country)->where('designation_id', $request->designation_id)->where('agent', $data->user_id)->first();
//                    if ($quota){
//                        $used = $quota->quota_used;
//                        $quota->quota_used = $used + 1;
////                        $quota->update();
//                    }
//                }
//            }
//            return response()->json([
//                'success' => true,
//                'message' => 'Can not create candidate!',
//            ]);
//        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => 'failed!',
//                'error' => $e->getMessage(),
            ]);
//        }
    }
    public function updatePIF(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = Candidate::where('id', $request->id)->first();
            $pif_file = $data->pif_file?$data->pif_file:null;
            if($request->file('pif_file')) {
                if (file_exists($pif_file)) {
                    unlink($pif_file);
                }
                $this->pifUrl = $this->getPifUrl($request);
            }
            else {
                $this->pifUrl = $pif_file;
            }
            $data->pif_file = $this->pifUrl;
            $data->update();

            return response()->json([
                'success' => true,
                'message' => 'Candidate Info Updated Successfully!',
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
    public function deletePIF(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = Candidate::where('id', $request->id)->first();
            $pif_file = $data->pif_file?$data->pif_file:null;
            if (file_exists($pif_file)) {
                unlink($pif_file);
            }
            $data->pif_file = null;
            $data->update();

            return response()->json([
                'success' => true,
                'message' => 'PIF deleted Successfully!',
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

    public function deleteUser(Request $request, $id)
    {
        
        try {

            // Eager load the 'createdby' relationship to get the user who created the current user
            $data = User::with('createdby', 'candidate')->find($id);  // Find the user and load their creator
            $can = Candidate::where('user_id', $id)->first();  // Find the candidate by user_id
    
    
              
    
            // Check and delete associated files for the candidate
            if ($can) {
                foreach (['pif_file', 'passport_all_page', 'birth_certificate', 'resume', 'cv', 'nid_file', 'photo', 'experience_file', 'academic_file', 'passport_file', 'training_file'] as $fileType) {
                    $file = $can->$fileType;
                    if ($file && file_exists($file)) {
                        
                        unlink($file);  // Delete the file
                    }
                }
    
                $can->forceDelete();  // Force delete the candidate record
            }
    
            if ($data) {
                $data->forceDelete();  // Force delete the user record
            }

            User::find($data->createdby->id)->notify( new UserDeletedNotification($data, $request->note)); 
    
            return response()->json([
                'success' => true,
                'message' => 'User Deleted Successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    

    public function updateApprovalStatus(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = Candidate::where('id', $request->id)->first();
            $data->approval_status = $request->approval_status;
            $data->note = $request->note;
            $data->update();

            return response()->json([
                'success' => true,
                'message' => 'Candidate Info Updated Successfully!',
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
    public function update(Request $request){

        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = Candidate::where('id', $request->id)->first();
          
            $qr = $data->qr_code;
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
            $data->full_name = $request->fullName;
            $user = User::where('id', $data->user_id)->first();
            $user->name = $request->fullName;
            $user->update();
            $data->father_name = $request->father_name;
            $data->mother_name = $request->mother_name;
            $data->birth_date = $request->birth_date;
            $data->country = $request->country;
            $data->religion = $request->religion;
            $data->nid = $request->nid;
            if (auth()->user()->role_id ==1){
                $data->approval_status = 'approved';
            }else{
                $data->approval_status = 'pending';
            }
            $data->passport = $request->passport;
            $data->expiry_date = $request->expiry_date;
            $data->medical_center_id = $request->medical_center_id;
            $data->designation_id = $request->designation_id;
            $data->address = json_encode($request->address);
            $data->city = $request->city;
            $data->academic = json_encode($request->academic);
            $data->experience = json_encode($request->experience);
            $data->training = json_encode($request->training);
//            $data->medical_status = $request->medical_status;
//            $data->training_status = $request->training_status;
            $data->is_active = $request->is_active;
            
            



            foreach (['pif_file', 'passport_all_page', 'birth_certificate', 'resume', 'cv', 'nid_file', 'photo', 'experience_file', 'academic_file', 'passport_file', 'training_file' ] as $fileType) {
                $data->$fileType = $this->reset_file_link($request->file($fileType), $data->$fileType);
            }
            
            // Update the data in the database
            $data->update();






           

            // Check if the file exists before deleting
            if (file_exists($qr)) {
                unlink($qr); // Delete the existing file
            }

            // Generate the new QR file URL
            $data->qr_code = $this->getQRUrl($request);

            $data->update();
//            $user = User::where('id', $data->user_id)->first();
//            $pass = $data->password;
//            $user->password = $request->password != null?bcrypt($request->password):$pass;
//            $user->update();

            return response()->json([
                'success' => true,
                'message' => 'Candidate Info Updated Successfully!',
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

    public function destroy(Request $request){
        try {
            $data = Candidate::where('id', $request->id)->first();
            $image = $data->photo;
            $qr = $data->qr_code;
            if (file_exists($image)) {
                unlink($image);
            }
            if (file_exists($qr)) {
                unlink($qr);
            }
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Candidate Deleted Successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function all(Request $request){

        try {
            $query = User::orderby('id', 'desc')
                ->where('role_id', 5)
                ->with('candidate')
                ->when($request->has('approval_status'), function ($query) use ($request) {
                    return $query->whereHas('candidate', function ($q) use ($request) {
                        $q->where('approval_status', $request->approval_status);
                    });
                })
                ->with('partner')
                ->with('createdBy')
                ->with('candidate.designation')
                ->with('role');

            // Apply the `doesntHave('report')` condition only if `$request->pg` is not empty
            if ($request->pg != '') {
                $query->doesntHave('report');
            }

            // Fetch data (either paginated or all records)



            if ($request->filled('agent')) {
                $query->whereHas('createdBy', function ($q) use ($request) {
                    $q->where('name', $request->agent);
                });
            }
            if ($request->filled('country')) {
                $query->whereExists(function ($q) use ($request) {
                    $q->select(DB::raw(1))
                      ->from('candidates')
                      ->whereColumn('candidates.user_id', 'users.id')
                      ->where('country', $request->country);
                });
            }



               // Export all data as CSV if requested
        if ($request->filled('export_all') && $request->export_all == true) {



            $filename = "candidates_export_" . now()->format('Y_m_d_H_i_s') . ".csv";

            $serialNumber = 1;


            // Create a StreamedResponse to write CSV data
            $response = Response::stream(function () use ($query, &$serialNumber) {
                ob_end_clean(); // Clear any previous output
                $handle = fopen('php://output', 'w');

                // Write CSV header
                fputcsv($handle, [
                    'SL', 'Name',  'Passport', 'Created By',
                    'Training Status', 'Medical Status', 'Passport Expiry Date'
                ]);

                // Fetch data and write each row to the CSV
                $query->chunk(100, function ($users) use ($handle, &$serialNumber) {

                    foreach ($users as $user) {



                        fputcsv($handle, [
                            $serialNumber++,
                            $user->name,
                            $user->candidate?->passport ?? null,
                            $user->createdBy?->name ?? null,
                            $user->candidate?->training_status ?? null,
                            $user->candidate?->medical_status ?? null,
                            $user->candidate?->expiry_date ?? null,
                        ]);
                    }
                });

                fclose($handle);
            }, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
            ]);

            return $response;
        }

        $data = $request->pg == '' ? $query->get() : $query->paginate(10);

            // Count total records by cloning the query without fetching all relationships
            $dataC = (clone $query)->count();

            return response()->json([
                'success' => true,
                'message' => 'Successful!',
//                'data_all' => $data1,
                'data' => $data,
                'count' => $dataC,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getAll(){
//        try {
//            $data = User::orderby('id', 'desc')->where('role_id', 5)->with('role')->with('candidate')->get();
//            return response()->json([
//                'success' => true,
//                'message' => 'Successful!',
//                'data' => $data,
//            ]);
//        }catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'failed!',
//                'error' => $e->getMessage(),
//            ]);
//        }
    }

    public function getImageUrl($request)
    {
        $image = $request->file('photo');
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
    public function getPifUrl($request)
    {
        $image = $request->file('pif_file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
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
    public function getQRUrl($request)
    {
        $candidate = Candidate::where('id', $request->id)->first();
        $user = User::where('id', $candidate->user_id)->with('role')->first();
        $path = $candidate->qr_code;
        QrCode::size(600)->generate('https://www.mges.global/user_details/'.$user->id, $path);
        return $path;
//        QrCode::size(600)->generate('https://mges.global/,
//         Candidate: '.$user->name.
//            ', id: '.$user->id.
//            ', email: '.$user->email.
//            ', phone: '.$user->phone.
//            ', gender: '.$candidate->gender.
//            ', marital_status: '.$candidate->marital_status.
//            ', religion: '.$candidate->religion.
//            ', nid: '.$candidate->nid.
//            ', passport: '.$candidate->passport.
//            ', medical_status: '.$candidate->medical_status.
//            ', training_status: '.$candidate->training_status.
//            ', is_active: '.$candidate->is_active.
//            ', role: '.$user->role->roleName,
//            $path);
//        return $path;
    }
    public function send_sms()
    {

//        1.	Md. Nayem	A 01456540	Insulator
//2.	Md. Suhag Mia	A11836751	Welder
//3.	Liton Ali	A 07067589	Welder
//4.	Liton Hossan	A 01633372	Welder
//5.	Md. Munsur Alam	A 07618873	Pipefitter
//6.	Md. Rasel Khan	A 08549917	Pipe Fitter
//7.	Md. Suzat Ali	A 05970911	Pipe Fitter
//8.	Md. Hasan Tareq Rokey	A 07423864	Pipe Fitter
//9.	Alauddin Mandol	A 13549369	Pipefitter
//10.	Md. Anichur Rahman	A00206092	Pipefitter
//11.	Md. Reza	A11794702	Pipefitter
//12.
//	MD NADIM MIA	A13126366	Welder
//13.
//	MD RAFIQUL KHAN	EH0796674	Steel Fixer
//
//14.	MAMUNUL HUQUE MAZUMDER	A00186359	Welder
//15.
//
//	IQBAL HOSSEN	EL0167307	Welder
//16.	AL AMIN	A13111211	Welder
//17.	MOHIN UDDIN	EL0066369	Welder Forman
//18. 	MD UZZUL	A06253299	Welder
//19.	SABBIR MIA	A03530445	Welder
//20.	MOHAMMAD ISLAM	EG0479380	Welder
//21.	MD SAKIB KHAN	A02670787	Welder
//22.	MD NIPU	A02223632	Steel Fixer
//23.	ANISUR RAHMAN	A13313636	Steel Fixer
//24.	MD SOHEL RANA	EH0204279	Steel Fixer
//25.	KAZI NASIR UDDIN	B00469882	Steel Fixer
//26.	MD ASIKUR RAHMAN	A07222892	Forman Civil
//27.	MD BODIUZZAMAN	A12973746	Welder
//28.	NAJIM UDDIN	EH0293238	Welder
//29.	SHAYMOL	A11297460	Auxiliary Worker
//30.	RAFIKUL ISLAM SOURAV	A11175267	Welder
//31.	MD MASUD RANA	A13566269	Welder
//32.	RAFIQUL MIA	A02904718	Welder

//        $data = ['A11837437', 'A11618896', 'A13879016','A06116240','A13333452'];
//        $data = ['A01456540', 'A11836751', 'A07067589','A01633372', 'A07618873',
//            'A08549917','A05970911','A07423864','A13549369','A00206092',
//            'A11794702','A13126366','EH0796674','A00186359','EL0167307',
//            'A13111211','EL0066369','A06253299','A03530445','EG0479380',
//            'A02670787','A02223632','A13313636','EH0204279','B00469882',
//            'A07222892','A12973746','EH0293238','A11297460','A11175267',
//            'A13566269','A02904718'];
//        $query = User::join('candidates', 'users.id', '=', 'candidates.user_id')
//            ->join('users as creators', 'users.created_by', '=', 'creators.id') // Joining the users table again to get info about the creator
//            ->whereIn('candidates.passport', $data)
//            ->orderByRaw("FIELD(candidates.passport, '" . implode("', '", $data) . "')");
//
//        $final = $query->select(
//            'users.id as user_id',
//            'users.name as user_name',
//            'users.created_by as agency',
//            'candidates.id as canId',
//            'candidates.passport',
//            'creators.id as creator_id',
//            'creators.name as creator_name'
//        )->get();

//        $usersToUpdate = $query->get()->pluck('user_id')->toArray();
//        $dt =User::whereIn('id', $usersToUpdate)->update(['created_by' => 1402]);

        return response()->json([
//            'count'=>$usersToUpdate->count(),
            'data'=>$final,
        ]);





//        $url = "http://bulksmsbd.net/api/smsapi";
//        $api_key = "OyrexM3Rft3HiP3IfZ8C";
//        $senderid = "8809617613568";
//        $number = '8801784033051';
//        $message = 'Hello Sree';
//
//        $data = [
//            "api_key" => $api_key,
//            "senderid" => $senderid,
//            "number" => $number,
//            "message" => $message
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        $response = curl_exec($ch);
//        curl_close($ch);
//        return $response;
    }
    public function candidateByCreator(Request $request)
    {
        try {
            if ($request->has('user_id')){
                $creator = User::where('id', $request->user_id)->first();
                $cans = User::where('created_by', $request->user_id)->where('role_id', 5)->with('candidate')->with('partner')->with('createdBy')->with('candidate.designation')->with('role')->get();
                $data = User::where('created_by', $request->user_id)->where('role_id', 5)->with('candidate')
                    ->with('partner')->with('createdBy')->with('role')->paginate(10);
                $count = User::where('created_by', $request->user_id)->where('role_id', 5)->with('candidate')->with('partner')->with('createdBy')->with('role')->count();
            }else{
                $cans = User::where('created_by', auth()->user()->id)->where('role_id', 5)->with('candidate')->with('partner')->with('createdBy')->with('candidate.designation')->with('role')->get();
                $data = User::where('created_by', auth()->user()->id)->where('role_id', 5)->with('candidate')
                    ->with('partner')->with('createdBy')->with('role')->paginate(10);
                $count = User::where('created_by', auth()->user()->id)->where('role_id', 5)->with('candidate')->with('partner')->with('createdBy')->with('role')->count();
                $creator = User::where('id', auth()->user()->id)->first();
            }
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data_all' => $cans,
                'data' => $data,
                'count' => $count,
                'creator' => $creator,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function candidateByCreatorCount(Request $request)
    {
        try {
            if ($request->has('user_id')){
                $registered = User::where('created_by', $request->user_id)->where('role_id', 5)->with('report')->with('candidate')->get();
            }else{
                $registered = User::where('created_by', auth()->user()->id)->where('role_id', 5)->with('report')->with('candidate')->get();
            }
            $count = $registered->count();
            $med_count = 0;
            $complete_count = 0;
            foreach ($registered as $val){
                if ($val->report){
                    if ($val->report->result != null){
                        $med_count += 1;
                    }
                }
                if ($val->candidate){
                    if ($val->candidate->passport_file != null &&
                        $val->candidate->nid_file != null &&
                        $val->candidate->academic_file != null &&
                        $val->candidate->experience_file != null &&
                        $val->candidate->training_file != null &&
                        $val->candidate->photo != null
                    ){
                        $complete_count += 1;
                    }
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'count' => $count,
                'med_count' => $med_count,
                'complete_count' => $complete_count,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getCandidateById(Request $request)
    {
        try {
            $data = User::where('id', $request->id)->where('role_id', 5)->with('candidate')->with('role')->first();
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
    public function getCandidateInfo()
    {
        try {
            $user = User::where('id', Auth::user()->id)->with('role')->with('candidate')->first();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
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


    public function getBirthCertificate($request)
    {
        $image = $request->file('birth_certificate');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }


    public function getCvUrl($request)
    {
        $image = $request->file('cv');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }

    public function getResumeUrl($request)
    {
        $image = $request->file('resume');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }

    public function getPassportAllPageUrl($request)
    {
        $image = $request->file('passport_all_page');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }



    public function saveQr(Request $request)
    {
        try {
//            return $request->all();

            return response('pp');
//            $filePath = public_path($data->qr_code);
////            return  $filePath;
//
//            if (file_exists($filePath)) {
//                return response()->download($filePath, $data->qr_code, [], 'attachment');
//            } else {
//                return response()->json(['error' => 'File not found'], 404);
//            }
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleteFile(Request $request, $id)
    {


        // Validate incoming request
        $request->validate([
            'file' => 'required|string', // File name must be a non-empty string
        ]);

        // Find the user instance by ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Get the incoming file name
        $columnName = $request->input('file');

        // Check if the file name exists in the user's candidates
        $candidate = $user->candidate()->whereNotNull($columnName)->first();

        // Check if the candidate exists
        if (!$candidate) {
            return response()->json(['message' => "No candidate with a non-null value in the column '$columnName'."], 404);
        }


        // Prepare the delete file data
        $deleteFiles = $candidate->delete_files ?? []; // Get current delete files

        // Store the current date with the file name
        $deleteFiles[$columnName] = now(); // or use a specific date

        // Update the delete_files column for the candidate
        $candidate->delete_files = $deleteFiles;
        $candidate->save();

        return response()->json(['message' => 'File deletion recorded successfully.']);
    }

    public function get_qr(Request $request, $id)
{
    $data = Candidate::where('user_id', $id)->first();

    if (!$data || !$data->qr_code) {
        return response()->json(['message' => 'QR code not found'], 404);
    }

    $qrPath = public_path($data->qr_code);
    $logo = public_path("logo.png");
     // Path to the QR code image

    // Check if the QR code image exists
    if (!file_exists($qrPath)) {
        return response()->json(['message' => 'QR code file not found'], 404);
    }

    // Convert the image to a base64 blob
    function convertToImage($qrPath){
        $imageData = base64_encode(file_get_contents($qrPath));
        $base64Image = 'data:image/png;base64,' . $imageData;
        return $base64Image;
    }

    // Embed the image in the HTML
// Embed the image in the HTML
$html = '<html>
            <body>
                <div style="text-align: center;">
                <span><img src="' . convertToImage($logo) . '" style="width: 100px; height: 60px;" alt="QR Code">
                    <h2>MGES GLOBAL </h2></span>
                    <p>Name: ' . $data->firstName . ' ' . $data->lastName . '</p>
                    <p>Passport: ' . $data->passport . '</p>
                    <h2> QR Code</h2>
                    <img src="' . convertToImage($qrPath) . '" style="width: 300px; height: 300px;" alt="QR Code">
                </div>
            </body>
         </html>';


    // Create an instance of Dompdf
    $dompdf = new \Dompdf\Dompdf();

    // Load the HTML content
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the PDF
    $dompdf->render();

    // Output the generated PDF for download
    return response($dompdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="qr_code.pdf"');
}


private function get_url($server_file)
{
    $image = $server_file;
    $imageName = time() . $image->getClientOriginalName();
    $path = 'candidate_photos/';
    $image->move($path, $imageName);
    return $path.$imageName;


}

private function reset_file_link($server_file, $file_name)
{
    if ($server_file) {
        // Check and delete the existing file
        if (file_exists($file_name)) {
            Log::info('File ', ['file' => $file_name]);
            unlink($file_name);
        }

        // Generate and return the new file URL
        return $this->get_url($server_file);
    }

    // Return the existing file name if no new file is uploaded
    return $file_name;
}


}
