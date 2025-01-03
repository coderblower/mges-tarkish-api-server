<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateMedicalTest;
use App\Models\MedicalTestList;
use App\Models\Partner;
use App\Models\TestByCountry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidateMedicalTestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function create(Request $request){
        $req = Validator::make($request->all(), [
            'country_id' => 'required',
        ]);
//        return User::where('unique_id',$request->unique_id)->first();
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
//            if (auth()->user()->role_id == 3){
//                $quotaCount = CandidateMedicalTest::where('enrolled_by', auth()->user()->id)->count();
//            }
            $can = CandidateMedicalTest::where('user_id',$request->user_id)->first();
            $userr = User::where('unique_id',$request->unique_id)->first();
            $candidate = Candidate::where('user_id', $request->user_id)->first();
            $partner = Partner::where('user_id',auth()->user()->id)->first();
            $quota_used = $partner->quota_used;
            if ($userr){
                if ($can){
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed!',
                        'error' => 'Already Enrolled!',
                    ]);
                }else{
                    $data = new CandidateMedicalTest();
                    $data->enrolled_by = auth()->user()->id;

                    $data->user_id = $request->user_id;
                    $data->candidate_id = $request->candidate_id;
//            $data->user_id = $can->user_id;
                    $data->country_id = $request->country_id;
//            $data->medical_id = $request->medical_id;
//            $data->test_id = $request->test_id;
                    $data->min = $request->min;
                    $data->max = $request->max;
                    $data->result = $request->result;
                    $data->status = $request->status;
                    $data->note = $request->note;
//            $data->report = $request->report;
                    if ($partner->quota >=1){
                        $candidate->medical_center_id = auth()->user()->id;
                        $candidate->update();
                        $data->save();
                        $partner->quota_used = $quota_used + 1;
                        $partner->update();
                        return response()->json([
                            'success' => true,
                            'message' => 'Successful!',
                            'data' => $data,
                        ]);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient quota!',
                        ]);
                    }
                }
            }else{
                return response()->json([
                    'success' => false,
                    'unique' => $userr,
                    'message' => 'Unique Id invalid!',
                ]);
            }
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
            $data = CandidateMedicalTest::where('id', $request->id)->first();
//            $data->enrolled_by = auth()->user()->id;
//            $data->user_id = $request->user_id;
//            $data->country_id = $request->country_id;
//            $data->candidate_id = $request->candidate_id;
//            $data->medical_id = $request->medical_id;
//            $data->test_id = $request->test_id;
//            $data->min = $request->min;
//            $data->max = $request->max;
            $data->result = $request->result;
            if ($request->result == 'pending'){
                $data->max = 1;
            }
            $file = $data->file;
            $req_file = $request->file;
            $req_file?$data->file = $this->getImageUrl($request):$data->file = $file;
//            $data->status = $request->status;
            $data->report = json_encode($request->report);
            $data->note = $request->note;
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
            $data = CandidateMedicalTest::where('id', $request->id)->first();
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
    public function all(Request $request){
        try {
            if (auth()->user()->role_id == 1){
                if ($request->pg ==''){
                    $data = CandidateMedicalTest::orderby('id','desc')->with('country')->with('candidate')->with('candidate.designation')->with('user')->with('user.createdBy')->get();
                    $data_sub = CandidateMedicalTest::orderby('updated_at','desc')->whereIn('result', ['fit', 'unfit'])
                        ->with('country')
                        ->with('candidate.designation')
                        ->with('user.createdBy')
                        ->paginate(20);
                    $count = $data->count();
                }else{
                    $data = CandidateMedicalTest::orderby('id','desc')
                        ->with('country')
                        ->with('candidate.designation')
                        ->with('user.createdBy')
                        ->when($request->has('phone') || $request->phone != '', function ($query) use ($request) {
                            $query->WhereHas('user', function ($query) use ($request) {
                                $query->where('phone', 'like', "$request->phone%");})
                                ->orWhereHas('candidate', function ($query) use ($request) {
                                    $query->where('passport', 'like', "$request->phone%");});
                        })
                        ->paginate(20);
                    $data_sub = CandidateMedicalTest::orderby('updated_at','desc')->whereIn('result', ['fit', 'unfit'])->with('country')->with('candidate')->with('candidate.designation')->with('user')->with('user.createdBy')->paginate(20);
                    $count = $data->count();
                }
            }else{
                if ($request->pg ==''){
                    $data = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->with('country')->with('candidate')->with('candidate.designation')->with('user')->with('user.createdBy')->get();
                    $data_sub = CandidateMedicalTest::orderby('updated_at','desc')->where('enrolled_by',auth()->user()->id)->whereIn('result', ['fit', 'unfit'])->with('country')->with('candidate')->with('candidate.designation')->with('user')->with('user.createdBy')->paginate(20);
                    $count = $data->count();
                }else{
                    $data = CandidateMedicalTest::orderBy('id', 'desc')
                        ->with('country')
                        ->with('candidate.designation')
                        ->with('user.createdBy')
                        ->where('enrolled_by', auth()->user()->id)
                        ->when($request->has('phone') || $request->phone != '', function ($query) use ($request) {
                            $query->where(function ($query) use ($request) {
                                $query->whereHas('user', function ($query) use ($request) {
                                    $query->where('phone', 'like', "%{$request->phone}%");
                                })
                                    ->orWhereHas('candidate', function ($query) use ($request) {
                                        $query->where('passport', 'like', "%{$request->phone}%");
                                    });
                            });
                        })
                        ->paginate(20);
                    $data_sub = CandidateMedicalTest::orderby('updated_at','desc')->where('enrolled_by',auth()->user()->id)->whereIn('result', ['fit', 'unfit'])->with('country')->with('candidate')->with('candidate.designation')->with('user')->with('user.createdBy')
                        ->when($request->has('phone') || $request->phone != '', function ($query) use ($request) {
                            $query->where(function ($query) use ($request) {
                            $query->WhereHas('user', function ($query) use ($request) {
                                $query->where('phone', 'like', "%$request->phone%");})
                                ->orWhereHas('candidate', function ($query) use ($request) {
                                    $query->where('passport', 'like', "%$request->phone%");});
                            });
                        })
                        ->paginate(20);
                    $count = $data->count();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => $data,
                'data_sub' => $data_sub,
                'count' => $count,
                'id' => auth()->user()->id,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function count(){
        try {
            if (auth()->user()->role_id == 1){
                $enrolled = CandidateMedicalTest::orderby('id','desc')->count();
                $submitted = CandidateMedicalTest::orderby('id','desc')->where('result','!=',null)->count();
                $fit = CandidateMedicalTest::orderby('id','desc')->where('result','fit')->count();
                $unfit = CandidateMedicalTest::orderby('id','desc')->where('result','unfit')->count();
                $repeat = CandidateMedicalTest::orderby('id','desc')->where('max',1)->count();
                $pending = CandidateMedicalTest::orderby('id','desc')->where('result',null)->count();
                $partner = Partner::where('user_id',auth()->user()->id)->select('quota', 'quota_used')->first();
            }else{
                $enrolled = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->count();
                $submitted = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->where('result','!=',null)->count();
                $fit = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->where('result','fit')->count();
                $unfit = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->where('result','unfit')->count();
                $repeat = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->where('max',1)->count();
                $pending = CandidateMedicalTest::orderby('id','desc')->where('enrolled_by',auth()->user()->id)->where('result',null)->count();
                $partner = Partner::where('user_id',auth()->user()->id)->select('quota', 'quota_used')->first();
            }
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'enrolled' => $enrolled,
                'submitted' => $submitted,
                'fit' => $fit,
                'unfit' => $unfit,
                'repeat' => $repeat,
                'pending' => $pending,
                'partner' => $partner,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function filterMedicalReport(Request $request){
        try {
            if (auth()->user()->role_id == 1){
                if ($request->pg == ''){
                    $query = CandidateMedicalTest::with('user')
                        ->when($request->user_id != '', function($query) use ($request) { $query->where('enrolled_by', $request->user_id);})
                        ->when($request->result != '', function($query) use ($request) { $query->where('result', $request->result);})
                        ->with('enrolledBy')
                        ->with('country')
                        ->with(['candidate'=> function ($query) {
                            $query->select('designation_id', 'id', 'user_id', 'passport', 'photo', 'qr_code', 'firstName', 'lastName' );
                        }])
                        ->with('candidate.designation')
                        ->with('user.createdby')
                        ->orderBy('id','desc')
                        ->get();
                    $count = $query->count();
                }else{
                    $query = CandidateMedicalTest::with('user')
                    ->when($request->agent_id != '', function ($query) use ($request) {
                        $query->whereHas('user', function ($query) use ($request) {
                            $query->where('created_by', $request->agent_id);
                        });
                    })
                    ->when($request->user_id != '', function ($query) use ($request) {
                        $query->where('enrolled_by', $request->user_id);
                    })
                    ->when($request->result != '', function ($query) use ($request) {
                        $query->where('result', $request->result);
                    })
                    ->when($request->country != '', function ($query) use ($request) {
                        $query->where('country_id', $request->country); // Adjust 'country_id' if the column name is different
                    })
                    ->with('enrolledBy')
                    ->with('country')
                    ->with(['candidate' => function ($query) {
                        $query->select('designation_id', 'id', 'user_id', 'passport', 'photo', 'qr_code', 'firstName', 'lastName');
                    }])
                    ->when($request->passport != '', function ($query) use ($request) {
                        $query->whereHas('candidate', function ($query) use ($request) {
                            $query->where('passport', 'like', "%$request->passport%");
                        });
                    })
                    ->with('user.createdby')
                    ->orderBy('id', 'desc')
                    ->paginate(20);
                
                $count = $query->count();
                
                }
            }else{
                if ($request->pg == '') {
                    $query = CandidateMedicalTest::with('user')->whereHas('user', function ($query) use ($request) {
                        $query->where('created_by', auth()->user()->id);})
                        ->when($request->user_id != '', function($query) use ($request) { $query->where('enrolled_by', $request->user_id);})
                        ->when($request->result != '', function($query) use ($request) { $query->where('result', $request->result);})
                        ->with('country')
                        ->with(['candidate'=> function ($query) {
                            $query->select('designation_id', 'id', 'user_id', 'passport', 'photo', 'qr_code', 'firstName', 'lastName' );
                        }])
                        ->with('user.createdby')
                        ->orderBy('id','desc')
                        ->get();
                    $count = $query->count();
                }else{
                    $query = CandidateMedicalTest::with('user')->whereHas('user', function ($query) use ($request) {
                        $query->where('created_by', auth()->user()->id);})
                        ->when($request->user_id != '', function($query) use ($request) { $query->where('enrolled_by', $request->user_id);})
                        ->when($request->result != '', function($query) use ($request) { $query->where('result', $request->result);})
                        ->with('country')
                        ->with(['candidate'=> function ($query) {
                            $query->select('designation_id', 'id', 'user_id', 'passport', 'photo', 'qr_code', 'firstName', 'lastName' );
                        }])
                        ->with('candidate.designation')
                        ->with('user.createdby')
                        ->orderBy('id','desc')
                        ->get();
                    $count = $query->count();
                }
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
    public function reportData(Request $request){
        try {
            $data = CandidateMedicalTest::where('id', $request->id)->with('country')->with('candidate')->with('user')->get();
            $tests = TestByCountry::where('country_id',$request->country_id)->with('test')->get();
            return response()->json([
                'success' => true,
                'message' => 'Successful!',
                'data' => [
                    'data'=>$data,
                    'tests'=>$tests
                ]
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getImageUrl($request)
    {
        $image = $request->file('file');
        $imageName = time() . $image->getClientOriginalName();
        $path = 'medical_files/';
        $image->move($path, $imageName);
//        $imageUrl = $path.$imageName;
//        Storage::disk('s3')->put($path.$this->imageName, file_get_contents($request->photo));
        return $path.$imageName;
    }
}
