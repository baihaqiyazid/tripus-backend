<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Laravel\Fortify\Rules\Password;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;


class UserController extends Controller
{
    function generateOtpCode()
    {
        $previousOtpCode = session('previous_otp_code'); // Retrieve the previously generated OTP code from session

        do {
            $otpCode = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit OTP code
        } while ($otpCode === $previousOtpCode); // Repeat generation if the new code is the same as the previous one

        session(['previous_otp_code' => $otpCode]); // Store the newly generated OTP code in session

        return $otpCode;
    }

    public function register(Request $request)
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'string', 'max:255', 'email', 'unique:users'],
                'role' => ['required', 'string', Rule::in(['open trip', 'user'])],
                'password' => ['required', 'string', 'min:8', new Password],
                'file' => ['nullable','file', 'mimes:pdf'],
            ]);


            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }

            if ($request->input("role") == 'open trip') {
                if (!$request->hasFile("file")) {
                    return ResponseFormatter::error([
                        'message' => 'Bad Request',
                        'errors' => "field file required"
                    ], 'Bad Request', 400);
                }
            }

            $otp = $this->generateOtpCode();

            try {
                // Send OTP code to user's email
                Mail::to($request->email)->send(new OtpMail($otp));
            } catch (\Exception $error) {
                // Rollback the transaction if there is an error sending the email
                DB::rollback();
                return ResponseFormatter::error([
                    "message" => "something erorr",
                    "errors" => $error->getMessage()
                ], 'something error', 500);
            }
            
            if ($request->hasFile("file")) {
                $file = $request->file('file');
                $file_name = $request->input('email') . Carbon::now() . '.' . $file->getClientOriginalExtension();
                $file->move('file', $file_name);

                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'otp_code' => $otp,
                    'role' => $request->role,
                    'file' => $file_name
                ]);
            }else{
                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'otp_code' => $otp,
                    'role' => $request->role,
                ]);
            }


            // Commit the transaction if everything is successful
            DB::commit();

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => "Bearer",
                'user' => $user
            ], "user registered");
        } catch (Exception $error) {
            // Rollback the transaction if there is an error
            DB::rollback();
            return ResponseFormatter::error([
                "message" => " something error",
                "errors" => $error->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, "token revoked");
    }

    public function verify(Request $request)
    {
        try {
            // Start the database transaction
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'otp_code' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors(),
                    'user' => Auth::user()->email,
                ], 'Bad Request', 400);
            }

            $user = Auth::user();

            if ($request->otp_code != $user->otp_code) {
                // Rollback the transaction if OTP code is wrong
                DB::rollback();
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => "OTP code is wrong!"
                ], 'Bad Request', 400);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            // Commit the transaction if everything is successful
            DB::commit();

            return ResponseFormatter::success([
                'user' => $user
            ], "user verified");
        } catch (Exception $error) {
            // Rollback the transaction if there is an error
            DB::rollback();
            return ResponseFormatter::error([
                "message" => "something error",
                "errors" => $error->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required_without_all:username,phone_number', 'string', 'email'],
                'password' => ['required', 'string'],

            ], [
                'email.required_without_all' => 'The email field must be filled when the username or telephone number does not exist.',
                'password.required' => 'Password field is required',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }


            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();

            if (!empty($email)) {
                if (!$user) {
                    return ResponseFormatter::error([
                        'message' => 'User not found',
                        'errors' => 'Email Unregistered'
                    ], 'Authentication failed', 404);
                }
            }

            if ($user->email_verified_at == null) {
                return ResponseFormatter::error([
                    'message' => 'Oops! Your email has not been verified',
                    'errors' => 'Oops! Your email has not been verified'
                ], 'Authentication failed', 400);
            }

            if (!Hash::check($password, $user->password)) {
                return ResponseFormatter::error([
                    'message' => 'Oops! The password you entered is incorrect.',
                    'errors' => 'Oops! The password you entered is incorrect.'
                ], 'Authentication failed', 401);
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], "Congratulations, you have successfully logged in!");
        } catch (Exception $error) {
            return ResponseFormatter::error([
                "message" => "Something error",
                "errors" => $error
            ], 'Authentication failed', 500);
        }
    }

    public function getAllUsers()
    {
        try {
            $user = User::get();

            return ResponseFormatter::success([
                'user' => $user
            ], "success get users");

        } catch (\Exception $e) {
            return ResponseFormatter::error([
                "message" => " something error",
                "errors" => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'name' => [],
                'links' => [],
                'bio' => [],
                'phone' => [],
                'birthdate' => ['date'],
                'email' => ['email', 'unique:users,email,'.$user->id],
                'background_image_url' => ['image'],
                'profile_photo_path' => ['image'],
            ]);
        
            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }
        
            // Update user fields
            $user->name = $request->input('name', $user->name); // Use current value if not provided
            $user->links = $request->input('links', $user->links);
            $user->bio = $request->input('bio', $user->bio);
            $user->phone_number = $request->input('phone', $user->phone);
            $user->birthdate = $request->input('birthdate', $user->birthdate);
            $user->email = $request->input('email', $user->email);
        
            if ($request->hasFile('profile_photo_path')) {
                $file_name_profile = $user->id . $user->email . Str::random(3) . '.' . $request->file('profile_photo_path')->getClientOriginalExtension();
                $storage_path = $user->profile_photo_path;
                $full_storage_path = public_path($storage_path);
            
                // Delete old profile photo if exists
                if (File::exists($full_storage_path)) {
                    File::delete($full_storage_path);
                }
                
                // Move and update the photo path in the user model
                $request->file('profile_photo_path')->move('images/profile', $file_name_profile);
                $user->profile_photo_path = 'images/profile/' . $file_name_profile;
            }
            
            if ($request->hasFile('background_image_url')) {
                $file_name = $user->id . $user->email . '.' . $request->file('background_image_url')->getClientOriginalExtension();
                $storage_path = 'images/background/' . $file_name;
                $full_storage_path = public_path($storage_path);
            
                // Delete old background image if exists
                if (File::exists($full_storage_path)) {
                    File::delete($full_storage_path);
                }
            
                // Move and update the image path in the user model
                $request->file('background_image_url')->move('images/background', $file_name);
                $user->background_image_url = $storage_path;
            }
        
            $user->save(); // Save the updated user model
        
            return ResponseFormatter::success([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'new_password' => ['required', 'string', 'min:8'],
                'old_password' => ['required', 'string'],
            ]);
        
            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => $validator->errors()
                ], 'Bad Request', 400);
            }
            
            // dd(Hash::check($request->old_password, $user->password));

            if (!Hash::check($request->old_password, $user->password)){
                return ResponseFormatter::error([
                    'message' => 'Bad Request',
                    'errors' => 'old password is wrong!'
                ], 'Bad Request', 400);
            }
        
            // Update user fields
            $user->password = Hash::make($request->new_password);

            $user->save(); // Save the updated user model
        
            return ResponseFormatter::success([
                'message' => 'Password updated successfully',
            ]);

        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'something error',
                'errors' => $e->getMessage()
            ], 'authentication failed', 500);
        }
    }
}
