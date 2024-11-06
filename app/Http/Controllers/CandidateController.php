<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateMedicalTest;
use App\Models\Quota;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function League\Flysystem\move;

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
            $image = $data->photo?$data->photo: null;
            $passport_file = $data->passport_file?$data->passport_file:null;
            $nid_file = $data->nid_file?$data->nid_file:null;
            $academic_file = $data->academic_file?$data->academic_file: Null;
            $experience_file = $data->experience_file?$data->experience_file: null;
            $training_file = $data->training_file?$data->training_file:null;
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
            if($request->file('nid_file')) {
                if (file_exists($nid_file)) {
                    unlink($nid_file);
                }
                $this->nidUrl = $this->getNidUrl($request);
            }
            else {
                $this->nidUrl = $nid_file;
            }
            $data->nid_file = $this->nidUrl;
            if($request->file('photo')) {
                if (file_exists($image)) {
                    unlink($image);
                }
                $this->imageUrl = $this->getImageUrl($request);
            }
            else {
                $this->imageUrl = $image;
            }
            $data->photo = $this->imageUrl;
            if($request->file('passport_file')) {
                if (file_exists($passport_file)) {
                    unlink($passport_file);
                }
                $this->passportUrl = $this->getPassportUrl($request);
            }
            else {
                $this->passportUrl = $passport_file;
            }
            $data->passport_file = $this->passportUrl;
            if($request->file('experience_file')) {
                if (file_exists($experience_file)) {
                    unlink($experience_file);
                }
                $this->expUrl = $this->getExpUrl($request);
            }
            else {
                $this->expUrl = $experience_file;
            }
            $data->experience_file = $this->expUrl;
            if($request->file('academic_file')) {
                if (file_exists($academic_file)) {
                    unlink($academic_file);
                }
                $this->academicUrl = $this->getAcademicUrl($request);
            }
            else {
                $this->academicUrl = $academic_file;
            }
            $data->academic_file = $this->academicUrl;
            if($request->file('training_file')) {
                if (file_exists($training_file)) {
                    unlink($training_file);
                }
                $this->trainingUrl = $this->getTrainingUrl($request);
            }
            else {
                $this->trainingUrl = $training_file;
            }
            $data->training_file = $this->trainingUrl;
//            if (file_exists($qr)) {
//                unlink($qr);
//            }
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
            if ($request->pg == ''){
                $data = User::orderby('id', 'desc')->where('role_id', 5)
                    ->with('candidate')
                    ->with('partner')->with('createdBy')->with('candidate.designation')->with('role')->get();
                $dataC = User::where('role_id', 5)->count();
            }else{
                $data = User::orderby('id', 'desc')->where('role_id', 5)
                    ->with('candidate')
                    ->with('partner')->with('createdBy')->doesntHave('report')->with('role')->paginate(10);
                $dataC = User::where('role_id', 5)->doesntHave('report')->count();
            }
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
}
