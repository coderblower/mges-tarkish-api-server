<?php

namespace App\Http\Controllers;

use App\Models\MedicalTestList;
use App\Models\TestByCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestByCountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'test_id' => 'required',
            'country_id' => 'required',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            foreach ($request->test_id as $test_id){
                $data = new TestByCountry();
                $data->country_id = $request->country_id;
                $data->test_id = $test_id;
                $data->save();
            }
            return response()->json([
                'success' => true,
                'message' => 'Test By Country Saved Successfully!',
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
            $data = TestByCountry::where('id', $request->id)->first();
            $data->test_id = $request->test_id;
            $data->country_id = $request->country_id;
            $data->save();
            return response()->json([
                'success' => true,
                'message' => 'Test By Country Updated Successfully!',
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
            $data = TestByCountry::where('id', $request->id)->first();
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Test By Country Deleted Successfully!',
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
            $data = TestByCountry::orderby('id','desc')->with('country')->with('test')->get();
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
