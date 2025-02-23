<?php

namespace App\Http\Controllers;

use App\Models\FinalTest;
use App\Models\Partner;
use App\Models\SkillTest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FinalTestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
//            $pre = SkillTest::where('user_id', $request->user_id)->first();
//            $pre->status = 1;
//            $pre->update();
            $person = FinalTest::where('user_id', $request->user_id)->first();
            if ($person){
                return response()->json([
                    'success' => false,
                    'message' => 'failed!',
                    'error' => 'Already Sent To Final Test!',
                ]);
            }else{
                $data = new FinalTest();
                $data->user_id = $request->user_id;
                $data->enrolled_by = auth()->user()->id;
                $data->status = 0;
                $data->save();
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
    public function update(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = FinalTest::where('id', $request->id)->first();
//            $data->user_id = $request->user_id;
//            $data->enrolled_by = auth()->user()->id;
            $data->status = $request->status;
            $data->update();
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
    public function destroy(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = FinalTest::where('id', $request->id)->first();
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
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
            if (auth()->user()->role_id == 1){
                $data = FinalTest::orderby('id','desc')->with('user')->with('candidate')->get();
                $count = $data->count();
            }else{
                $data = FinalTest::orderby('id','desc')->where('status', 0)->where('enrolled_by', auth()->user()->id)->with('user')->with('candidate')->get();
                $count = $data->count();
            }

//            if ($request->status == 0){
//                $data = SkillTest::orderby('id','desc')->where('status',0)->with('user')->with('candidate')->get();
//            }
//            if ($request->status == 1){
//                $data = SkillTest::orderby('id','desc')->where('status',1)->with('user')->with('candidate')->get();
//            }
//            if(!$request->status){
//            }
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data,
                'count' => $count
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function all0(){
        try {
            $data = FinalTest::orderby('id','desc')->where('enrolled_by', auth()->user()->id)->where('status',0)->with('user')->with('candidate')->get();
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
    public function all1(){
        try {
            $data = FinalTest::orderby('id','desc')->where('enrolled_by', auth()->user()->id)->where('status',1)->with('user')->with('candidate')->get();
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
    public function getTrainingCenters(){
        try {
            $data = User::orderby('id', 'desc')->where('role_id', 2)->get();
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


    public function upload_certificate( Request $request,  $id){
        try {
    

            $final_test= FinalTest::where('id', $id)->first(); 
            Log::info("message".$final_test."id".$id);

            $final_test->certificate_upload = $request->certificate_upload ? $this->get_final_test_certificate_name($request) : null;

            $final_test->save();

            
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $final_test,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function get_final_test_certificate_name($request)
    {
        $image = $request->file('certificate_upload');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'candidate_photos/';
        $image->move($path, $imageName);
        return $path.$imageName;
    }
    
    public function filterTrainingReport(Request $request){
        try {
            if (auth()->user()->role_id == 1){
                $query = FinalTest::with('user')
                    ->when($request->user_id != '', function($query) use ($request) { $query->where('enrolled_by', $request->user_id);})
                    ->when($request->status != '', function($query) use ($request) { $query->where('status', $request->status);})
                    ->with('candidate')
                    ->with('candidate.designation')
                    ->with('user.createdby')
                    ->orderBy('id','desc')
                    ->get();
                $count = $query->count();
            }else{
                $query = FinalTest::with('user')->whereHas('user', function ($query) use ($request) {
                    $query->where('created_by', auth()->user()->id);})
                    ->when($request->user_id != '', function($query) use ($request) { $query->where('enrolled_by', $request->user_id);})
                    ->when($request->status != '', function($query) use ($request) { $query->where('status', $request->status);})
                    ->with('enrolledBy')
                    ->with('candidate')
                    ->with('candidate.designation')
                    ->with('user.createdby')
                    ->orderBy('id','desc')
                    ->get();
                $count = $query->count();
            }

            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $query,
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
}
