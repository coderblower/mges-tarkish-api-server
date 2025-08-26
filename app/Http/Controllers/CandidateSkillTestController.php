<?php

namespace App\Http\Controllers;

use App\Models\CandidateSkillTest;
use App\Models\SkillList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidateSkillTestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'user_id' => 'required',
            'skill_id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = new CandidateSkillTest();
            $data->user_id = $request->user_id;
            $data->skill_id = $request->skill_id;
            $data->center_name = $request->center_name;
            $data->preskill_test = $request->preskill_test;
            $data->crash_training = $request->crash_training;
            $data->skill_test = $request->skill_test;
            $data->advence_training = $request->advence_training;
            $data->final_test = $request->final_test;
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
    public function update(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
            'user_id' => 'required',
            'skill_id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = CandidateSkillTest::where('id',$request->id)->first();
            $data->user_id = $request->user_id;
            $data->skill_id = $request->skill_id;
            $data->center_name = $request->center_name;
            $data->preskill_test = $request->preskill_test;
            $data->crash_training = $request->crash_training;
            $data->skill_test = $request->skill_test;
            $data->advence_training = $request->advence_training;
            $data->final_test = $request->final_test;
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
            $data = CandidateSkillTest::where('id',$request->id)->first();
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
            $data = CandidateSkillTest::orderby('id','desc')->with('user')->with('skill')->get();
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
