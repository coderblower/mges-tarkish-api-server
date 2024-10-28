<?php

namespace App\Http\Controllers;

use App\Models\CandidateSkillTest;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function Illuminate\Routing\Controllers\except;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => 'all']);
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = new Country();
            $data->name = $request->name;
            $data->total_vacancy = $request->total_vacancy;
            $data->skills = json_encode($request->skills);
            $data->medical_tests = json_encode($request->medical_tests);
            $data->active = $request->active;
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
            'name' => 'required|string',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = Country::where('id',$request->id)->first();
            $data->name = $request->name;
            $data->total_vacancy = $request->total_vacancy;
            $data->skills = json_encode($request->skills);
            $data->medical_tests = json_encode($request->medical_tests);
            $data->active = $request->active;
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
            $data = Country::where('id',$request->id)->first();
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
            $data = Country::orderby('id','desc')->get();
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
