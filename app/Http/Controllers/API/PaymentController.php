<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Trips\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function Create(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'payment_method_id' => ['required', 'string'],
                'number' => ['required', 'numeric'],
            ]);


            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $payment = PaymentAccount::create([
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'number' => $request->number,
            ]);

            return ResponseFormatter::success([
                'payment' => $payment
            ], "Payment account successfully created");

        } catch (\Exception $e) {
            return ResponseFormatter::error([
                "message" => " something error",
                "errors" => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function delete($payment_account_id)
    {
        try {
            $user = Auth::user();
            $payment = PaymentAccount::where('id', $payment_account_id)->first();

            if (!$payment || $payment->user_id != $user->id) {
                return ResponseFormatter::error([
                    'message' => 'Payment account not found',
                    'errors' => 'Payment account not found'
                ], 'Not Found', 404);
            }

            $payment->delete();

            return ResponseFormatter::success(null, "Payment account deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }

    public function getAll()
    {
        try {
            $payment = PaymentAccount::get();

            return ResponseFormatter::success([
                'payment' => $payment
            ], "Get all payment account success");

        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }
}
