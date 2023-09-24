<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Feeds\Feeds;
use App\Models\Trips\TripsJoins;
use App\Models\Orders;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class orderController extends Controller
{
    
    public function getByUserId(){
        
        try{
            $user = Auth::user();
           
              $orders = DB::table('orders')
                        ->join('feeds', 'orders.feed_id', '=', 'feeds.id')
                        ->join('users', 'feeds.user_id', '=', 'users.id')
                        ->select(DB::raw('orders.*,  users.name as agent_name, feeds.id as feeds_id, feeds.user_id as feed_user_id,  feeds.description, feeds.location, feeds.meeting_point, feeds.type, feeds.title, feeds.payment_account, feeds.max_person, feeds.date_end, feeds.date_start, feeds.category_id, feeds.others, feeds.exclude, feeds.include'))
                        ->where('users.id', $user->id)
                        ->get();
                        
                $formattedOrders = [];
                foreach ($orders as $order) {
                    $formattedOrders[] = [
                        'id' => $order->id,
                        'feed_id' => $order->feed_id,
                        'name' => $order->name,
                         "email" => $order->email,
                         "address"=> $order->address,
                        "phone"=> $order->phone,
                        "qty"=> $order->qty,
                        "bank"=> $order->bank,
                        "va_number"=> $order->va_number,
                        "fee"=>$order->fee,
                        "admin_price"=> $order->admin_price,
                        "total_price"=> $order->total_price,
                        "status" => $order->status,
                        "response_midtrans"=> $order->response_midtrans,
                         "expire_time"=> $order->expire_time,
                        "created_at"=> $order->created_at,
                        "updated_at"=> $order->updated_at,
                        'feeds' => [
                            'id' => $order->feed_id,
                            "name" => $order->agent_name,
                            "user_id" => $order->feed_user_id,
                            "description"=> $order->description,
                            "location"=> $order->location,
                            "meeting_point"=> $order->meeting_point,
                            "type"=> $order->type,
                            "title"=> $order->title,
                            "payment_account"=> $order->payment_account,
                            "max_person"=> $order->max_person,
                            "date_end"=> $order->date_end,
                            "date_start"=> $order->date_start,
                            "category_id"=> $order->category_id,
                            "others"=> $order->others,
                            "exclude"=> $order->exclude,
                            "include"=> $order->include,
                        ],
                        
                       
                        
                    ];
                }
            
            return ResponseFormatter::success(
               $formattedOrders,
                "Success get all data"
            );
            
        }catch(\Exception $e){
              return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Authentication failed', 500);
        }
    }
    public function getByEmail($email)
    {
        try {
           $orders = DB::table('orders')
            ->join('feeds', 'orders.feed_id', '=', 'feeds.id')
            ->join('users', 'feeds.user_id', '=', 'users.id')
            ->select(DB::raw('orders.*, users.name as agent_name, feeds.id as feeds_id, feeds.user_id as feed_user_id,  feeds.description, feeds.location, feeds.meeting_point, feeds.type, feeds.title, feeds.payment_account, feeds.max_person, feeds.date_end, feeds.date_start, feeds.category_id, feeds.others, feeds.exclude, feeds.include'))
            ->where('orders.email', $email)
            ->orderBy('orders.created_at', 'desc')
            ->get();
                
                // Ubah struktur respons
                $formattedOrders = [];
                foreach ($orders as $order) {
                    $formattedOrders[] = [
                        'id' => $order->id,
                        'feed_id' => $order->feed_id,
                        'name' => $order->name,
                         "email" => $order->email,
                         "address"=> $order->address,
                        "phone"=> $order->phone,
                        "qty"=> $order->qty,
                        "bank"=> $order->bank,
                        "va_number"=> $order->va_number,
                        "fee"=>$order->fee,
                        "admin_price"=> $order->admin_price,
                        "total_price"=> $order->total_price,
                        "status" => $order->status,
                        "response_midtrans"=> $order->response_midtrans,
                         "expire_time"=> $order->expire_time,
                        "created_at"=> $order->created_at,
                        "updated_at"=> $order->updated_at,
                        'feeds' => [
                            'id' => $order->feeds_id,
                            "name" => $order->agent_name,
                            "user_id" => $order->feed_user_id,
                            "description"=> $order->description,
                            "location"=> $order->location,
                            "meeting_point"=> $order->meeting_point,
                            "type"=> $order->type,
                            "title"=> $order->title,
                            "payment_account"=> $order->payment_account,
                            "max_person"=> $order->max_person,
                            "date_end"=> $order->date_end,
                            "date_start"=> $order->date_start,
                            "category_id"=> $order->category_id,
                            "others"=> $order->others,
                            "exclude"=> $order->exclude,
                            "include"=> $order->include
                        ],
                    ];
                }
            
            return ResponseFormatter::success(
               $formattedOrders,
                "Success get all data"
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Authentication failed', 500);
        }
    }
    
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
                
            $order = DB::table('orders')
            ->select(DB::raw('*'))->where('id', $request->order_id)
            ->first();

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
                'data' => $responseData,
                'order' => $order
            ], "success charge data");
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
                'data' => $responseData,
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
        
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . config("midtrans.server_key"));
        if ($hashed == $request->signature_key) {
            $order = Orders::find($request->order_id);
            $user = User::where('email', $order->email)->first();
            
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
                
                // dd($order);
                
                try {
                    $feeds = Feeds::where('id', $order->feed_id)->lockForUpdate()->first();
                   
                    if ($feeds) {
                        $feeds->update(["max_person" => $feeds->max_person - 1]);
                        
                        for($i = 0; $i < $order->qty; $i++){
                             TripsJoins::create([
                                'feed_id' => $feeds->id, 
                                'user_id' => $user->id
                            ]);
                        }
                
                        
                        
                         DB::commit();

                        
                         return ResponseFormatter::success([
                            'data' => "success",
                        ], "success update");
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
                try{
                    $order->update([
                    'status' => 'cancel',
                    'response_midtrans' => $request->getContent()
                ]);
                
                DB::commit();
                 return ResponseFormatter::success([
                            'data' => "success",
                        ], "success update");
                }catch(\Exception $e){
                    DB::rollBack();
                    return ResponseFormatter::error([
                        'message' => 'something error',
                        'errors' => $e->getMessage()
                    ], 'authentication failed', 500);
                }
                
                
                

            } elseif ($request->transaction_status == 'expire') {
                 try{
                    $order->update([
                    'status' => 'expired',
                    'response_midtrans' => $request->getContent()
                ]);
                
                DB::commit();
                
                return ResponseFormatter::success([
                            'data' => "success",
                        ], "success update");
                }catch(\Exception $e){
                    DB::rollBack();
                    return ResponseFormatter::error([
                        'message' => 'something error',
                        'errors' => $e->getMessage()
                    ], 'authentication failed', 500);
                }
                
            }
        }
    }
}
