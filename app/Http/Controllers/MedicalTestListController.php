<?php

namespace App\Http\Controllers;

use App\Models\MedicalTestList;
use App\Models\SkillList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicalTestListController extends Controller
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
            $data = new MedicalTestList();
            $data->name = $request->name;
            $data->min = $request->min;
            $data->max = $request->max;
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
    public function update(Request $request){
        $req = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|string',
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = MedicalTestList::where('id', $request->id)->first();
            $data->name = $request->name;
            $data->min = $request->min;
            $data->max = $request->max;
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
            $data = MedicalTestList::where('id', $request->id)->first();
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
            $data = MedicalTestList::orderby('id','desc')->get();
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
