<?php

namespace App\Http\Controllers;

use App\Models\PreSkilledTest;
use App\Models\SkillTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SkillTestController extends Controller
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
            $pre = PreSkilledTest::where('user_id', $request->user_id)->first();
            $pre->status = 1;
            $pre->update();
            $person = SkillTest::where('user_id', $request->user_id)->first();
            if ($person){
                return response()->json([
                    'success' => false,
                    'message' => 'failed!',
                    'error' => 'Already Sent To Next Phase!',
                ]);
            }else{
                $data = new SkillTest();
                $data->user_id = $request->user_id;
                $data->enrolled_by = auth()->user()->id;
                $data->status = $request->status;
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
            $data = SkillTest::where('id', $request->id)->first();
            $data->user_id = $request->user_id;
            $data->enrolled_by = auth()->user()->id;
            $data->status = $request->status;
            $data->save();
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
            $data = SkillTest::where('id', $request->id)->first();
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
            $data = SkillTest::orderby('id','desc')->where('enrolled_by', auth()->user()->id)->with('user')->with('candidate')->get();

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
    public function all0(){
        try {
            $data = SkillTest::orderby('id','desc')->where('enrolled_by', auth()->user()->id)->where('status',0)->with('user')->with('candidate')->get();
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
            $data = SkillTest::orderby('id','desc')->where('enrolled_by', auth()->user()->id)->where('status',1)->with('user')->with('candidate')->get();
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
}
