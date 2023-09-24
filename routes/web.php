<?php

use App\Http\Controllers\AdminController;
use App\Models\Feeds\Feeds;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        $feedsWithdraw = Feeds::select('feeds.*', 'request_withdraw_trips.*', 'users.name as name', DB::raw('SUM(orders.total_price) AS total_price'))
             ->join('request_withdraw_trips', 'feeds.id', '=', 'request_withdraw_trips.feed_id')
             ->join('users', 'feeds.user_id', 'users.id')
             ->join('orders', 'feeds.id', 'orders.feed_id')
             ->where('orders.status', 'success')
             ->groupBy('feeds.id', 'request_withdraw_trips.id');
        

        $feedsCancel = Feeds::select('feeds.*', 'request_cancel_trips.*', 'users.name as name', )
             ->join('request_cancel_trips', 'feeds.id', '=', 'request_cancel_trips.feed_id')
             ->join('users', 'feeds.user_id', 'users.id')
             ->get();

        $users = User::where('role', 'open trip')->get();

        return view('dashboard', [
            'feedsWithdraw' => $feedsWithdraw, 
            'feedsCancel' => $feedsCancel, 
            'users' => $users
        ]);
    })->name('dashboard');

    Route::get('trips/approval-letters/{id}/accept', [AdminController::class, 'acceptApprovalLetters'])->name('acceptApproval');
    Route::get('trips/approval-letters/{id}/reject', [AdminController::class, 'rejectApprovalLetters'])->name('rejectApproval');
});
