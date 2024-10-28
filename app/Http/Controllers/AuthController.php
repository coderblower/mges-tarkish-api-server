<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','optimize','passResetOtp','verifyOtp']]);
    }

    public function login(Request $request)
    {
        $req = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string',
        ]);
        if ($req->fails()) {
            return response()->json($req->errors(), 422);
        }
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);
        $error = [];

        if (!$token) {
            $req->errors()->add('password', 'Password is Invalid!');
            return response()->json($req->errors(), 422);

//            return response()->json([
//                'success' => false,
//                'message' => 'failed!',
//                'error' => "Email or Password is Invalid!",
//            ]);
        }

        $user = User::where('id', Auth::user()->id)->with('role')->with('candidate')->with('partner')->first();
        return response()->json([
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
    public function optimize()
    {
        Artisan::call('optimize');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        return "Cleared!";
    }
    public function passResetOTP(Request $request)
    {
        $req = Validator::make($request->all(), [
            'phoneNumber' => 'required|max:17|exists:users,phone',
        ]);
        if($req->fails()){
            return response()->json($req->errors()->toJson(), 400);
        }else{
            $code = Verification::create([
                'phoneNumber' => $request->phoneNumber,
                'otp' => rand(1111, 9999),
                'expire_at' => Carbon::now()->addMinutes(10)
            ]);

            $user = Verification::where('phoneNumber', $request->phoneNumber)->latest()->first();
//        Notification::send($user, new SuccessfulRegistration());
            try {
                $this->send_sms($user);
                return response()->json([
                    'success' => true,
                    'message' => 'OTP Sent Successfully',
                    'data' => $code,
                ]);
            }catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Otp Send failed!',
                    'error' => $e->getMessage(),
                ]);
            }
        }

    }
    public function verifyOTP(Request $request)
    {
        $user = Verification::where('otp', $request->otp)->first();
        $now = Carbon::now();
        if ($user){
            if ($now->isAfter($user->expire_at)){
                $user->delete();
                return \response('Expired OTP!'); //cannot proceed to next page
            }else{
                $user->is_verified = 1;
                $user->update();
                return \response('Verified!'); //You can redirect to next page for registration
            }
        }else{
            return \response('send OTP again!');
        }
    }
    public function send_sms($user)
    {
        $url = "http://bulksmsbd.net/api/smsapi";
        $api_key = "OyrexM3Rft3HiP3IfZ8C";
        $senderid = "8809617613568";
        $number = $user->phoneNumber;
        $message = 'Your OTP: '.$user->otp;

        $data = [
            "api_key" => $api_key,
            "senderid" => $senderid,
            "number" => $number,
            "message" => $message
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
