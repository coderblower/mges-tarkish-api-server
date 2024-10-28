<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = new Designation();
            $data->name = $request->name;
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
//    public function update(Request $request){
//        $req = Validator::make($request->all(), [
//            'id' => 'required',
//            'name' => 'required|string',
//        ]);
//        try {
//            if ($req->fails()) {
//                return response()->json($req->errors(), 422);
//            }
//            $data = Country::where('id',$request->id)->first();
//            $data->name = $request->name;
//            $data->total_vacancy = $request->total_vacancy;
//            $data->skills = json_encode($request->skills);
//            $data->medical_tests = json_encode($request->medical_tests);
//            $data->active = $request->active;
//            $data->update();
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
//    }
//    public function destroy(Request $request){
//        $req = Validator::make($request->all(), [
//            'id' => 'required',
//        ]);
//        try {
//            if ($req->fails()) {
//                return response()->json($req->errors(), 422);
//            }
//            $data = Country::where('id',$request->id)->first();
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
            $data = Designation::orderby('id','desc')->get();
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
