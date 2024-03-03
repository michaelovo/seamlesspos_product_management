<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\AccountVerificationNotification;
use App\Traits\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use Helpers;

    public function register(RegistrationRequest $request): JsonResponse
    {
        try {

            /* Create The OTP Variables */
            $otp = mt_rand(100000, 999999);
            $otp_expires_at = now()->addMinutes(10);

            /* Create the user account */
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status_id' => AuthController::getStatusId('Inactive'),
                'otp' => encrypt($otp),
                'otp_expires_at' => $otp_expires_at,
                'password' => Hash::make($request->password),

            ]);

            Log::info($otp); //log otp

            /* Notify the user via email of their account creation and verification */
            Notification::send($user, (new AccountVerificationNotification($otp, $user))->delay(5));

            /* Send Back The Success Response */
            return $this->sendSuccessResponse(new UserResource($user), 'Account created successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function resendOtp(string $identifier): JsonResponse
    {
        try {
            $field = Str::contains($identifier, '@') ? 'email' : 'phone';
            $user = User::where($field, $identifier)
                ->where('status_id', AuthController::getStatusId('Inactive'))
                ->first();

            /* Check the model status */
            if (!($user)) {
                $errors = new \stdClass();
                $errors->identifier = ['Please, check the email address or phone number and try again.'];

                return $this->sendErrorResponse($errors, 'Account could not be retrieved!', 400);
            }

            /* prepare the otp && resend */
            $otp = mt_rand(100000, 999999);
            $otp_expires_at = now()->addMinutes(10);

            /* Update The User Model */
            $user->update([
                'otp' => encrypt($otp),
                'otp_expires_at' => $otp_expires_at,
            ]);

            /**
             * Just incase you do not want to test without SMTP credentials
             * Uncomment line 91 and comment line 94
             * the OTP will be log in the "laravel.log" file
             **/

            Log::info($otp); //log otp

            /* Notify the user via email of their account creation and verification */
            Notification::send($user, (new AccountVerificationNotification($otp, $user))->delay(5));

            /* Send Back The Success Response */
            return $this->sendSuccessResponse(new \stdClass(), 'OTP resend successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function verifyAccount(Request $request): JsonResponse
    {
        try {
            if (is_null($request->otp)) {
                $errors = new \stdClass();
                $errors->otp = ['Sorry, The OTP field is required in order to proceed!'];

                return $this->sendErrorResponse($errors, 'OTP is required', 400);
            }

            /* validate the fields */
            $field = Str::contains($request->identifier, '@') ? 'email' : 'phone';

            $user = User::where($field, $request->identifier)
                ->where('status_id', AuthController::getStatusId('Inactive'))
                ->where('otp_expires_at', '>=', now())
                ->first();

            /* Check the model status */
            if (!($user)) {
                $errors = new \stdClass();
                $errors->identifier = ['Sorry, Your OTP has either expired or an account with these ID does not exist.'];

                return $this->sendErrorResponse($errors, 'Sorry, Your OTP has expired or your ID is invalid!', 400);
            }

            /* Confirm the OTP matches */
            if (decrypt($user->otp) != $request->otp) {
                $errors = new \stdClass();
                $errors->otp = ['OTP mismatch! Please, check the OTP and try again!'];

                return $this->sendErrorResponse($errors, 'OTP mismatch!', 400);
            }

            /* Update the user account status */
            $user->update(['status_id' => AuthController::getStatusId('Active'), 'email_verified_at' => now()]);

            /* Prepare the response data object */
            $data = new \stdClass();
            $data->user = new UserResource($user);
            $data->access_token = $user->createToken('authToken')->plainTextToken;

            return $this->sendSuccessResponse(new UserResource($user), 'Account verified successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {

            /* confirm the user account exists */
            $user = User::where('email', $request->email)
                ->with('status:id,name')
                ->first();

            if (is_null($user)) {
                $errors = new \stdClass();
                $errors->email = ['Sorry, Invalid Email/Password combination!'];

                return $this->sendErrorResponse($errors, 'Invalid credentials supplied!', 400);
            }

            /* confirm the user account is active */
            if ($user->status_id == AuthController::getStatusId('Inactive')) {
                $errors = new \stdClass();
                $errors->inactive = [true];
                $errors->email = ['Sorry, Your account is inactive! Verify your account with the OTP sent to your account and try again'];

                /* send out an OTP to the user */
                $otp = mt_rand(100000, 999999);
                $otp_expires_at = now()->addMinutes(5);

                /* update the user account */
                $user->update(['otp' => encrypt($otp), 'otp_expires_at' => $otp_expires_at]);

                /**
                 * Just incase you do not want to test without SMTP credentials
                 * Uncomment line 188 and comment line 191
                 * the OTP will be log in the "laravel.log" file
                 **/

                Log::info($otp);

                /* Notify the user via email of their account creation and verification */
                Notification::send($user, (new AccountVerificationNotification($otp, $user))->delay(5));

                return $this->sendErrorResponse($errors, 'Login failed! Account is inactive', 400);
            }

            if ($user->status_id == AuthController::getStatusId('Suspended')) {
                $errors = new \stdClass();
                $errors->email = ['Sorry, Your account has been suspended! Please, contact support!'];

                return $this->sendErrorResponse($errors, 'Suspended account! Please, contact support!', 400);
            }

            /* Check the login credentials match and generate auth token */

            $params = [];
            if (Str::contains($request->email, '@')) {
                $params = ['email' => $request->email, 'password' => $request->password];
            }

            if (Auth::attempt($params)) {
                $data = new \stdClass();
                $data->user = new UserResource($user);
                $data->access_token = $user->createToken('authToken')->plainTextToken;

                return $this->sendSuccessResponse($data, 'Login successful!', 200);
            }

            $errors = new \stdClass();
            $errors->email = ['Sorry, Invalid Email/Password combination!'];
            return $this->sendErrorResponse($errors, 'Invalid credentials supplied!', 400);

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }
}
