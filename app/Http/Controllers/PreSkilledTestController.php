<?php

namespace App\Http\Controllers;

use App\Models\PreSkilledTest;
use App\Models\SkillList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PreSkilledTestController extends Controller
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
            $person = PreSkilledTest::where('user_id', $request->user_id)->first();
            if ($person){
                return response()->json([
                    'success' => false,
                    'message' => 'failed!',
                    'error' => 'Already Qualified!',
                ]);
            }else{
                $data = new PreSkilledTest();
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
            $data = PreSkilledTest::where('id', $request->id)->first();
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
            $data = PreSkilledTest::where('id', $request->id)->first();
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
            $data = PreSkilledTest::orderby('id','desc')->where('status', 0)->where('enrolled_by', auth()->user()->id)->with('user')->with('candidate')->get();
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
