<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        // Retrieve all payments
        $payments = Payment::with('user','receiver')->get();

        return response()->json($payments);
    }

    public function store(Request $request){
        $req = Validator::make($request->all(), [
            'user_id' => 'required',
            'receiver_id'=> 'required'
        ]);
        try {
            if ($req->fails()) {
                return response()->json($req->errors(), 422);
            }
            $data = new Payment();
            $data->user_id = $request->user_id;
            $data->receiver_id = $request->receiver_id;
            $data->amount = $request->amount;

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


}
