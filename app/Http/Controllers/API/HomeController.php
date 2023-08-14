<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Feeds\Feeds;
use App\Models\Feeds\FeedsLikes;
use App\Models\Feeds\FeedsSaves;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function getData()
    {
        try {
            $feeds = Feeds::latest()->get();
            
            foreach ($feeds as $feed) {
                $feed->created_at = $feed->created_at->toIso8601String();
                $feed->updated_at = $feed->updated_at->toIso8601String();

                $feedImages = $feed->feedImage->map(function ($image) {
                    return [
                        'image_url' => $image->image_url,
                    ];
                });

                $feed->user;
                $feed->feedsLikes;
                $feed->feedsSaves;
            }

            return ResponseFormatter::success([
                'feeds' => $feeds
            ], "success get all data");

        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }
}
