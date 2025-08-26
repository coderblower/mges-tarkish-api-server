<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Designation;
use App\Models\Quota;
use App\Models\SkillList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'country_id' => 'required|integer',
            'designation_id' => 'required|integer',
            'agent' => 'required|integer',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = Quota::where('country_id', $request->country_id)->where('designation_id', $request->designation_id)->where('agent', $request->agent)->first();
            if ($data){
                $data->quota = $request->quota;
                $data->quota_used = 0;
                $data->update();
            }else{
                $data = new Quota();
                $data->country_id = $request->country_id;
                $data->designation_id = $request->designation_id;
                $data->agent = $request->agent;
                $data->quota = $request->quota;
                $data->quota_used = 0;
                $data->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Quota Update Successful!',
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
//        $req = Validator::make($request->all(), [
//            'id' => 'required',
//        ]);
//        try {
//            if ($req->fails()) {
//                return response()->json($req->errors(), 422);
//            }
//            $data = Quota::where('id', $request->id)->first();
//            $data->quota = $request->quota;
//            $data->update();
//            return response()->json([
//                'success' => true,
//                'message' => 'Quota Update Successful!',
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
//    public function destroy(Request $request){
//        $req = Validator::make($request->all(), [
//            'id' => 'required',
//        ]);
//        try {
//            if ($req->fails()) {
//                return response()->json($req->errors(), 422);
//            }
//            $data = SkillList::where('id', $request->id)->first();
//            $data->delete();
//            return response()->json([
//                'success' => true,
//                'message' => 'Successful!',
//            ]);
//        }catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'failed!',
//                'error' => $e->getMessage(),
//            ]);
//        }
//    }
    public function all(){
        try {
            $data = Quota::orderby('id','desc')
                ->with(['country'=> function ($query) {
                $query->select('id', 'name');
            }])
                ->with(['designation'=> function ($query) {
                $query->select('id', 'name');
            }])
                ->with(['agent'=> function ($query) {
                $query->select('id', 'name');
            }])->get();
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
