<?php

namespace App\Http\Controllers;

use App\Models\RequestCancelTrips;
use App\Models\RequestWithdrawTrips;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin/login');
    }

    public function login(Request $request)
    {

            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();

            if ($user && $password == $user->password) {
                return redirect('/dashboard');
            }

            return redirect('/');
   
    }

    public function dashboard()
    {
        return view('admin/dashboard');
    }

    public function acceptApprovalLetters($id)
    {
        try {
            User::find($id)->update([
                'status' => 'accept'
            ]);

            // dd(User::find($id));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
       
        return redirect('/dashboard');
    }

    public function rejectApprovalLetters($id)
    {
        try {
            User::find($id)->update([
                'status' => 'reject'
            ]);

            // dd(User::find($id));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
       
        return redirect('/dashboard');
    }
    
    public function acceptWithdrawTrips($id)
    {
        try {
            RequestWithdrawTrips::find($id)->update([
                'status' => 'accept'
            ]);

            // dd(User::find($id));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
       
        return redirect('/dashboard');
    }

    public function rejectWithdrawTrips($id)
    {
        try {
            RequestWithdrawTrips::find($id)->update([
                'status' => 'reject'
            ]);

            // dd(User::find($id));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
       
        return redirect('/dashboard');
    }

    public function acceptCancelTrips($id)
    {
        try {
            RequestCancelTrips::find($id)->update([
                'status' => 'accept'
            ]);

            // dd(User::find($id));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
       
        return redirect('/dashboard');
    }

    public function rejectCancelTrips($id)
    {
        try {
            RequestCancelTrips::find($id)->update([
                'status' => 'reject'
            ]);

            // dd(User::find($id));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
       
        return redirect('/dashboard');
    }
   
}
