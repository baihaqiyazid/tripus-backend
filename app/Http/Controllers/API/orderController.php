<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Feeds\Feeds;
use App\Models\Orders;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class orderController extends Controller
{
    public function charge(Request $request)
    {
        $payload = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $request->order_id,
                'gross_amount' => $request->total_price,
            ],
            'bank_transfer' => [
                'bank' => $request->bank,
            ],
            'item_details' => [
                [
                    'price' => $request->total_price / $request->qty,
                    'quantity' => $request->qty,
                    'name' => $request->name,
                ],
            ],
            'customer_details' => [
                'first_name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ],
            'custom_expiry' => [
                "expiry_duration"=> 4320,
                "unit"=> "minute"
            ],
            'feed' => $request->feed_id,
        ];


        try {
            DB::beginTransaction();

            try {
                $feeds = Feeds::where('id', $request->feed_id)->lockForUpdate()->first();
                if ($feeds->max_person == 0) {
                    DB::rollBack();
                    return ResponseFormatter::error([
                        'message' => 'something error',
                        'errors' => 'feed not found'
                    ], 'authentication failed', 500);
                } 
            } catch (\Exception $e) {
                DB::rollBack();
                return ResponseFormatter::error([
                    'message' => 'something error',
                    'errors' => $e->getMessage()
                ], 'authentication failed', 500);
            }

            $response = Http::withBasicAuth(config('midtrans.server_key'), '')->post(
                config('midtrans.base_url') . '/charge',
                $payload

            );

            $responseData = json_decode($response->getBody(), true);
            // dd( $responseData);
            try {
                Orders::create([
                    'id' => $request->order_id,
                    'feed_id' => $request->feed_id,
                    'email' => $request->email,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'bank' => $request->bank,
                    'va_number' => $responseData["va_numbers"][0]["va_number"],
                    'fee' => $request->fee,
                    'admin_price' => $request->admin_price,
                    'total_price' => $request->total_price,
                    'qty' => $request->qty,
                    'status' => 'pending',
                    'response_midtrans' => json_encode($responseData),
                    'expire_time' => $responseData["expiry_time"]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return ResponseFormatter::error([
                    'message' => 'something error',
                    'errors' => $e->getMessage()
                ], 'authentication failed', 500);
            }

            if ($response->failed()) {
                DB::rollBack();
                return ResponseFormatter::error([
                    'message' => 'midtrans server error',
                    'errors' => $responseData
                ], 'authentication failed', 500);
            }

            if ($response->json()['status_code'] != 201) {
                DB::rollBack();
                return ResponseFormatter::error([
                    'message' => 'midtrans server error',
                    'errors' => $responseData
                ], 'authentication failed', $response->json()['status_code']);
            }

            DB::commit();
            return ResponseFormatter::success([
                'data' => $responseData
            ], "success get all data");
        } catch (\Exception $e) {
            DB::rollBack();

            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function cancel($order_id)
    {
        try {
            $response = Http::withBasicAuth(config('midtrans.server_key'), '')->post(
                config('midtrans.base_url') . '/' . $order_id . '/cancel'
            );

            $responseData = json_decode($response->getBody(), true);
            // dd( $responseData);

            if ($response->failed()) {
                return ResponseFormatter::error([
                    'message' => 'midtrans server error',
                    'errors' => $responseData
                ], 'authentication failed', 500);
            }

            if ($response->json()['status_code'] != 200) {
                return ResponseFormatter::error([
                    'message' => 'midtrans server error',
                    'errors' => $responseData
                ], 'authentication failed', $response->json()['status_code']);
            }        
  
            return ResponseFormatter::success([
                'data' => $responseData
            ], "success get all data");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function callback(Request $request)
    {
        // dd($request->getBody());
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . config("midtrans.server_key"));
        if ($hashed == $request->signature_key) {
            $order = Orders::find($request->order_id);
            if (!$order) {
                return ResponseFormatter::error([
                    'message' => 'something error',
                    'errors' => 'feed not found'
                ], 'authentication failed', 500);
            }
            if ($request->transaction_status == 'settlement') {

                DB::beginTransaction();

                $order->update([
                    'status' => 'success',
                    'response_midtrans' => $request->getContent()
                ]);
                
                try {
                    $feeds = Feeds::where('id', $order->feed_id)->lockForUpdate()->first();
                    if ($feeds) {
                        $feeds->update(["max_person" => $feeds->max_person - 1]);
                    } else {
                        DB::rollBack();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => 'feed not found'
                        ], 'authentication failed', 500);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ResponseFormatter::error([
                        'message' => 'something error',
                        'errors' => $e->getMessage()
                    ], 'authentication failed', 500);
                }
               

            } elseif ($request->transaction_status == 'cancel') {
                $order->update([
                    'status' => 'cancel',
                    'response_midtrans' => $request->getContent()
                ]);

            } elseif ($request->transaction_status == 'expire') {
                $order->update([
                    'status' => 'expired',
                    'response_midtrans' => $request->getContent()
                ]);
            }
        }
    }
}
