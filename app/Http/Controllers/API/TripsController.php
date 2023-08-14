<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;
use App\Models\Trips\PaymentAccount;
use App\Models\Trips\Trips;
use App\Models\Trips\TripsCategories;
use App\Models\Trips\TripsImages;
use App\Models\Trips\TripsLikes;
use App\Models\Trips\TripsSaves;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TripsController extends Controller
{
    private $index = 0;

    public function create(Request $request)
    {
        try {

            $user = Auth::user();

            if ($user->role != 'open trip') {
                return ResponseFormatter::error([
                    'message' => "Sorry you can't access this feature",
                    'errors' => "Sorry you can't access this feature"
                ], 'authentication failed', 400);
            }
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'description' => ['string'],
                'location' => ['string'],
                'fee' => ['required', 'numeric'],
                'limit' => ['required', 'numeric'],
                'date_from' => ['required', 'date'],
                'date_end' => ['required', 'date'],
                'transportation' => ['string', Rule::in(['yes', 'no'])],
                'guide' => ['string', Rule::in(['yes', 'no'])],
                'porter' => ['string', Rule::in(['yes', 'no'])],
                'eat' => ['string', Rule::in(['yes', 'no'])],
                'breakfast' => ['string', Rule::in(['yes', 'no'])],
                'lunch' => ['string', Rule::in(['yes', 'no'])],
                'permit' => ['string', Rule::in(['yes', 'no'])],
                'others' => ['string'],
                'exclude' => ['string'],
                'images.*' => ['required', 'image'],
                'category_id' => ['array'],
                'payment_account' => ['required', 'array'],
                'payment_account.*.number' => ['required', 'string'], // Use dot notation here
                'payment_account.*.payment_method_id' => ['required', 'numeric'], // Use dot notation here
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            if (!$request->hasFile('images')) {
                return ResponseFormatter::error([
                    'message' => 'something error',
                    'errors' => "image must be fill"
                ], 'no images', 400);
            }

            $trip = Trips::create([
                'user_id' => $user->id,
                'description' => $request->description,
                'location' => $request->location,
                'fee' => $request->fee,
                'limit' =>  $request->limit,
                'date_from' => $request->date_from,
                'date_end' => $request->date_end,
                'transportation' => $request->transportation,
                'guide' => $request->guide,
                'porter' => $request->porter,
                'eat' => $request->eat,
                'breakfast' => $request->breakfast,
                'lunch' => $request->lunch,
                'permit' => $request->permit,
                'others' => $request->others,
                'exclude' => $request->exclude,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {

                    $file_name = $user->id . $this->index . Carbon::now() . '.' . $image->getClientOriginalExtension();
                    try {
                        $storage_image = $image->move('images/trips', $file_name);
                        TripsImages::create([
                            'trip_id' => $trip->id,
                            'image_url' => "images/trips/" . $file_name
                        ]);
                    } catch (\Exception $e) {
                        // Rollback the transaction if there is an error
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'something error', 500);
                    }
                    $this->index++;
                }
            }

            if ($request->input('category_id')) {
                foreach ($request->input('category_id') as $categoryId) {
                    try {
                        TripsCategories::create([
                            'trip_id' => $trip->id,
                            'category_id' => $categoryId
                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'something error', 500);
                    }
                }
            }

            // dd($request->input('payment_account'));

            if ($request->input('payment_account')) {
                foreach ($request->input('payment_account') as $payment) {
                    try {
                        PaymentAccount::create([
                            'trip_id' => $trip->id,
                            'number' => $payment["number"],
                            'payment_method_id' => $payment["payment_method_id"],

                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'something error', 500);
                    }
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            $trip->tripsImages;
            $trip->tripsCategories;
            $trip->paymentAccounts;

            return ResponseFormatter::success([
                $trip
            ], "create trips success");
        } catch (\Exception $e) {
            // Rollback the transaction if there is an error
            DB::rollback();
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function createLike(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trip_id' => ['required', 'integer']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            $trips = Trips::where('id', $request->input('trip_id'))->first();
            if (!$trips) {
                return ResponseFormatter::error([
                    'message' => 'not found',
                    'errors' => 'trips not found'
                ], 'not found', 404);
            }

            $trip = TripsLikes::updateOrCreate([
                'user_id' => $user->id,
                'trip_id' => $request->trip_id,
            ]);

            return ResponseFormatter::success([
                "trip" => $trip,
            ], "success");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function createSave(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trip_id' => ['required', 'integer']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            $trips = Trips::where('id', $request->input('trip_id'))->first();
            if (!$trips) {
                return ResponseFormatter::error([
                    'message' => 'not found',
                    'errors' => 'trips not found'
                ], 'not found', 404);
            }

            $trip = TripsSaves::updateOrCreate([
                'user_id' => $user->id,
                'trip_id' => $request->trip_id,
            ]);

            return ResponseFormatter::success([
                "trip" => $trip,
            ], "success");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function getAll()
    {
        try {
            $trips = Trips::latest()->get();

            // $tripData = [];
            foreach ($trips as $trip) {
                $trip->tripsImages;
                $trip->tripsCategories;
                $trip->paymentAccounts;
                $trip->tripsJoins;
            }


            return ResponseFormatter::success([
                "trips" => $trips,
            ], "success");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function getAllLikes()
    {
        try {

            $user = Auth::user();
            $tripsLikes = TripsLikes::where('user_id', $user->id)->with('trips.tripsImages', 'trips.tripsCategories', 'trips.paymentAccounts', 'trips.tripsJoins')->get();


            return ResponseFormatter::success([
                "trips" => $tripsLikes,
            ], "success");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function getAllSaves()
    {
        try {

            $user = Auth::user();
            $tripsSaves = TripsSaves::where('user_id', $user->id)->with('trips.tripsImages', 'trips.tripsCategories', 'trips.paymentAccounts', 'trips.tripsJoins')->get();


            return ResponseFormatter::success([
                "trips" => $tripsSaves,
            ], "success");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function update(Request $request)
    {
        try {

            $user = Auth::user();
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'trip_id' => ['required', 'numeric'],
                'description' => ['string'],
                'location' => ['string'],
                'fee' => ['numeric'],
                'limit' => ['numeric'],
                'date_from' => ['date'],
                'date_end' => ['date'],
                'transportation' => ['string', Rule::in(['yes', 'no'])],
                'guide' => ['string', Rule::in(['yes', 'no'])],
                'porter' => ['string', Rule::in(['yes', 'no'])],
                'eat' => ['string', Rule::in(['yes', 'no'])],
                'breakfast' => ['string', Rule::in(['yes', 'no'])],
                'lunch' => ['string', Rule::in(['yes', 'no'])],
                'permit' => ['string', Rule::in(['yes', 'no'])],
                'others' => ['string'],
                'exclude' => ['string'],
                'images.*' => ['image'],
                'category_id' => ['array'],
                'payment_account' => ['array'],
                'payment_account.*.number' => ['string'], // Use dot notation here
                'payment_account.*.payment_method_id' => ['numeric'], // Use dot notation here
            ]);

            $trip = Trips::find($request->trip_id);
            if (!$trip || $trip->user_id != $user->id) {
                return ResponseFormatter::error([
                    'message' => 'Post not found',
                    'errors' => 'Post not found'
                ], 'Not Found', 404);
            }

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }


            $trip->description = $request->description;
            $trip->location = $request->location;
            $trip->fee = $request->fee;
            $trip->limit = $request->limit;
            $trip->date_from = $request->date_from;
            $trip->date_end = $request->date_end;
            $trip->transportation = $request->transportation;
            $trip->guide = $request->guide;
            $trip->porter = $request->porter;
            $trip->eat = $request->eat;
            $trip->breakfast = $request->breakfast;
            $trip->lunch = $request->lunch;
            $trip->permit = $request->permit;
            $trip->others = $request->others;
            $trip->exclude = $request->exclude;

            // Save the updated trip
            $trip->save();

            if ($request->hasFile('images')) {
                $tripImages = TripsImages::where('trip_id', $trip->id)->get();
                foreach ($tripImages as $imageUrl) {
                    $imagePath = public_path($imageUrl->image_url);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                TripsImages::where('trip_id', $trip->id)->delete();

                foreach ($request->file('images') as $image) {

                    $file_name = $user->id . $this->index . Carbon::now() . '.' . $image->getClientOriginalExtension();
                    try {
                        $storage_image = $image->move('images/trips', $file_name);
                        TripsImages::create([
                            'trip_id' => $trip->id,
                            'image_url' => "images/trips/" . $file_name
                        ]);
                    } catch (\Exception $e) {
                        // Rollback the transaction if there is an error
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'something error', 500);
                    }
                    $this->index++;
                }
            }

            if ($request->input('category_id')) {

                TripsCategories::where('trip_id', $trip->id)->delete();

                foreach ($request->input('category_id') as $categoryId) {
                    try {
                        TripsCategories::create([
                            'trip_id' => $trip->id,
                            'category_id' => $categoryId
                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'something error', 500);
                    }
                }
            }

            // dd($request->input('payment_account'));

            if ($request->input('payment_account')) {

                PaymentAccount::where('trip_id', $trip->id)->delete();

                foreach ($request->input('payment_account') as $payment) {
                    try {
                        PaymentAccount::create([
                            'trip_id' => $trip->id,
                            'number' => $payment["number"],
                            'payment_method_id' => $payment["payment_method_id"],

                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'something error', 500);
                    }
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            $trip->tripsImages;
            $trip->tripsCategories;
            $trip->paymentAccounts;

            return ResponseFormatter::success([
                $trip
            ], "update trips success");
        } catch (\Exception $e) {
            // Rollback the transaction if there is an error
            DB::rollback();
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'trip_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $trip = Trips::where("id", $request->input('trip_id'))->first();
            if (!$trip) {
                return ResponseFormatter::error([
                    'message' => 'trip not found',
                ], 'Not Found', 404);
            }

            $tripImages = TripsImages::where("trip_id", $request->input('trip_id'))->get();
            foreach ($tripImages as $imageUrl) {
                $imagePath = public_path($imageUrl->image_url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            TripsImages::where("trip_id", $request->input('trip_id'))->delete();

            $tripLikes = TripsLikes::where("trip_id", $request->input('trip_id'))->delete();
            $tripSaves = TripsSaves::where("trip_id", $request->input('trip_id'))->delete();
            $tripSaves = TripsCategories::where("trip_id", $request->input('trip_id'))->delete();
            $tripPayment = PaymentAccount::where("trip_id", $request->input('trip_id'))->delete();

            $trip->delete();

            DB::commit();

            return ResponseFormatter::success(null, "trip deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }

    public function deleteLike(Request $request)
    {
        try {
            $user = Auth::user();
            $trip = TripsLikes::where('trip_id', $request->trip_id)->first();

            if (!$trip || $trip->user_id != $user->id) {
                return ResponseFormatter::error([
                    'message' => 'Post not found',
                    'errors' => 'Post not found'
                ], 'Not Found', 404);
            }

            $trip->delete();

            return ResponseFormatter::success(null, "like deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }

    public function deleteSave(Request $request)
    {
        try {
            $user = Auth::user();
            $trip = TripsSaves::where('trip_id', $request->trip_id)->first();

            if (!$trip || $trip->user_id != $user->id) {
                return ResponseFormatter::error([
                    'message' => 'Post not found',
                    'errors' => 'Post not found'
                ], 'Not Found', 404);
            }

            $trip->delete();

            return ResponseFormatter::success(null, "like deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }
}
