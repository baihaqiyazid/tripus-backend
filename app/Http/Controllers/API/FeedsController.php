<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;
use App\Models\Feeds\FeedsImage;
use App\Models\Feeds\Feeds;
use App\Models\Feeds\FeedsLikes;
use App\Models\Feeds\FeedsSaves;
use App\Models\RequestCancelTrips;
use App\Models\RequestWithdrawTrips;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FeedsController extends Controller
{
    private $index = 0;

    public function create(Request $request)
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'description' => [],
                'images.*' => ['required', 'image' ,'max:20480'], // Menggunakan notasi wildcard untuk menerima multiple images
                'location' => [],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            if (!$request->hasFile('images')) {
                return ResponseFormatter::error([
                    'message' => 'something error',
                    'errors' => "image must be fill"
                ], 'no images', 400);
            }

            $feed = Feeds::create([
                'description' => $request->description,
                'location' => $request->location,
                'user_id' => $user->id,
                'type' => 'feed'
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {

                    $file_name = $user->id . $this->index . Carbon::now() . '.' . $image->getClientOriginalExtension();
                    try {
                        $storage_image = $image->move('images/feeds', $file_name);
                        FeedsImage::create([
                            'feed_id' => $feed->id,
                            'image_url' => "images/feeds/" . $file_name
                        ]);
                    } catch (\Exception $e) {
                        // Rollback the transaction if there is an error
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'authentication failed', 500);
                    }
                    $this->index++;
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            $feeds = $user->feeds;

            $feedData = [];
            foreach ($feeds as $feed) {
                $feedImages = $feed->feedImage->map(function ($image) {
                    return [
                        'image_url' => $image->image_url,
                    ];
                });

                $feedData[] = [
                    'description' => $feed->description,
                    'location' => $feed->location,
                    'images' => $feedImages,
                ];
            }

            return ResponseFormatter::success([
                $feed
            ], "create feeds success");
        } catch (\Exception $e) {
            // Rollback the transaction if there is an error
            DB::rollback();
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }
    
    public function createTrips(Request $request)
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'title' => ['required', 'max:50', 'string'],
                'meeting_point' => ['required'],
                'include' => ['required'],
                'exclude' => [],
                'others' => [],
                'category_id' => ['required'],
                'date_start' => ['required'],
                'date_end' => [],
                'fee' => ['required'],
                'max_person' => ['required'],
                'payment_account' => ['required'],
                'description' => ['required'],
                'images.*' => ['required', 'image' ,'max:20480'], // Menggunakan notasi wildcard untuk menerima multiple images
                'location' => ['required'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            if (!$request->hasFile('images')) {
                return ResponseFormatter::error([
                    'message' => 'something error',
                    'errors' => "image must be fill"
                ], 'no images', 400);
            }

            $feed = Feeds::create([
                'description' => $request->description,
                'location' => $request->location,
                'user_id' => $user->id,
                'meeting_point' => $request->meeting_point,
                'title' => $request->title,
                'include' => $request->include,
                'exclude' => $request->exclude,
                'others' => $request->others,
                'category_id' => $request->category_id,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
                'fee' => $request->fee,
                'max_person' => $request->max_person,
                'payment_account' => $request->payment_account,
                'type' => 'open trip'
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {

                    $file_name = $user->id . $this->index . Carbon::now() . '.' . $image->getClientOriginalExtension();
                    try {
                        $storage_image = $image->move('images/trips', $file_name);
                        FeedsImage::create([
                            'feed_id' => $feed->id,
                            'image_url' => "images/trips/" . $file_name
                        ]);
                    } catch (\Exception $e) {
                        // Rollback the transaction if there is an error
                        DB::rollback();
                        return ResponseFormatter::error([
                            'message' => 'something error',
                            'errors' => $e->getMessage()
                        ], 'authentication failed', 500);
                    }
                    $this->index++;
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            $feeds = $user->feeds;

            foreach ($feeds as $feed) {
                $feedImages = $feed->feedImage->map(function ($image) {
                    return [
                        'image_url' => $image->image_url,
                    ];
                });
            }

            return ResponseFormatter::success([
                $feed
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
    
    public function requestCancelTrips(Request $request)
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'feed_id' => ['required'],
                'file' => ['file'],
                'reason' => ['required', 'string'],
                'status' => ['required', 'string']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();
 
            if ($request->hasFile("file")) {
                $file = $request->file('file');
                $file_name = $user->email . Carbon::now() . '.' . $file->getClientOriginalExtension();
                $file->move('file/req_cancel', $file_name);
                
                $requestCancelTrips = RequestCancelTrips::create([
                    'feed_id' => $request->feed_id,
                    'file' => $file_name,
                    'reason' => $request->reason,
                    'status' => $request->status
                ]);
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return ResponseFormatter::success([
                $requestCancelTrips
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
    
    public function requestWithdrawTrips(Request $request)
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'feed_id' => ['required'],
                'file' => ['required','file'],
                'status' => ['required', 'string']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();
 
            if ($request->hasFile("file")) {
                $file = $request->file('file');
                $file_name = $user->email . Carbon::now() . '.' . $file->getClientOriginalExtension();
                $file->move('file/req_withdraw', $file_name);
                
                $requestWithdrawTrips = RequestWithdrawTrips::create([
                    'feed_id' => $request->feed_id,
                    'file' => $file_name,
                    'status' => $request->status
                ]);
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return ResponseFormatter::success([
                $requestWithdrawTrips
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

    public function getAll()
    {
        try {

            $feeds = Feeds::latest()->get();

            $feedData = [];
            foreach ($feeds as $feed) {
                $feedImages = $feed->feedImage->map(function ($image) {
                    return [
                        'image_url' => $image->image_url,
                    ];
                });

                $feedData[] = [
                    'description' => $feed->description,
                    'location' => $feed->location,
                    'images' => $feedImages,
                ];
            }

            return ResponseFormatter::success([
                "feeds" => $feeds,
            ], "success get all feeds");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'internal server error', 500);
        }
    }

    public function getAllLikes()
    {
        try {
            $user = Auth::user();
            $feeds = FeedsLikes::where('user_id', $user->id)->with('feed.feedImage')->get();

            return ResponseFormatter::success([
                "feeds" => $feeds,
            ], "success get all feeds likes");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => "something error",
            ], 'internal server error', 500);
        }
    }

    public function getAllSaves()
    {
        try {

            $user = Auth::user();
            $feeds = FeedsSaves::where('user_id', $user->id)->with('feed.feedImage')->get();

            return ResponseFormatter::success([
                "feeds" => $feeds,
            ], "success get all feeds likes");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => "something error",
            ], 'internal server error', 500);
        }
    }

    public function createLike(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'feed_id' => ['integer']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            $feed = FeedsLikes::updateOrCreate([
                'user_id' => $user->id,
                'feed_id' => $request->feed_id,
            ]);

            return ResponseFormatter::success([
                "feed" => $feed,
            ], "success");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function saveFeed(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'feed_id' => ['integer']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            $feed = FeedsSaves::updateOrCreate([
                'user_id' => $user->id,
                'feed_id' => $request->feed_id,
            ]);

            return ResponseFormatter::success([
                "feed" => $feed,
            ], "success");
        } catch (\Exception $e) {
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
                'feed_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            $feed = Feeds::where("id", $request->input('feed_id'))->first();
            if (!$feed) {
                return ResponseFormatter::error([
                    'message' => 'Feed not found',
                ], 'Not Found', 404);
            }

            $feedImages = FeedsImage::where("feed_id", $request->input('feed_id'))->get();
            foreach ($feedImages as $imageUrl) {
                $imagePath = public_path($imageUrl->image_url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            FeedsImage::where("feed_id", $request->input('feed_id'))->delete();

            $feedLikes = FeedsLikes::where("feed_id", $request->input('feed_id'))->delete();
            $feedSaves = FeedsSaves::where("feed_id", $request->input('feed_id'))->delete();

            $feed->delete();

            DB::commit();

            return ResponseFormatter::success(null, "Feed deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }


    public function deleteLike($feed_id)
    {
        try {
            $user = Auth::user();
            $feed = feedsLikes::where('feed_id', $feed_id)->where('user_id', $user->id) ->first();

            if (!$feed || $feed->user_id != $user->id) {
                return ResponseFormatter::error([
                    'message' => 'Post not found',
                    'errors' => 'Post not found'
                ], 'Not Found', 404);
            }

            $feed->delete();

            return ResponseFormatter::success(null, "like deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }

    public function deleteSave($feed_id)
    {
        try {
            $user = Auth::user();
            $feed = feedsSaves::where('feed_id', $feed_id)->where('user_id', $user->id) ->first();

            if (!$feed || $feed->user_id != $user->id) {
                return ResponseFormatter::error([
                    'message' => 'Post not found',
                    'errors' => 'Post not found'
                ], 'Not Found', 404);
            }

            $feed->delete();

            return ResponseFormatter::success(null, "like deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something error',
                'errors' => $e->getMessage()
            ], 'Internal Server Error', 500);
        }
    }
}
