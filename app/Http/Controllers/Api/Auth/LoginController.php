<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\PreviewResource;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\LoginService;
use App\Services\Auth\SanctumService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class LoginController extends Controller
{
    public $user_service;
    public $login_service;

    public function __construct()
    {
        $this->user_service = new UserService;
        $this->login_service = new LoginService;
    }

    public function login(Request $request)
    {
        try {
            $user = $this->login_service->authenticate($request->all());
            $data["user"] =  UserResource::make($user)->toArray($request);
            $data["token"] = $user->createToken(SanctumService::SESSION_KEY)->plainTextToken;
            LoginService::newLogin($user);
            return ApiHelper::validResponse("Logged in successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (AuthException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function oauthLogin(Request $request)
    {
        try {
            $data = $request->validate([
                'fcm_token' => 'nullable|string',
                "token" => "required|string",
                'provider' => 'required|in:google,apple,facebook,tiktok',
            ]);

            $payload = $this->login_service->ouath($data);

            $user = $payload["user"];
            $data = $payload["data"];

            $data["user"] =  UserResource::make($user)->toArray($request);
            $data["token"] = $user->createToken(SanctumService::SESSION_KEY)->plainTextToken;
            LoginService::newLogin($user);
            return ApiHelper::validResponse("Logged in successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (AuthException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $request, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }
}
