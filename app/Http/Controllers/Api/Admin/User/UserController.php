<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\QueryBuilders\User\UserQueryBuilder;
use App\Services\User\UserService;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $user_service;

    function __construct()
    {
        $this->user_service = new UserService;
    }

    public function index(Request $request)
    {
        try {
            $users = UserQueryBuilder::filterList($request)->latest()->get();
            $data = UserResource::collection($users);
            return ApiHelper::validResponse("Users returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::throwableResponse($th, $request);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->user_service->getById($id);
            $data = UserResource::make($user);
            return ApiHelper::validResponse("User details returned successfully", $data);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $th);
        } catch (Exception $th) {
            return ApiHelper::throwableResponse($th, null);
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->user_service->getById($id);
            $user->delete();
            return ApiHelper::validResponse("User deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $th);
        } catch (Exception $th) {
            return ApiHelper::throwableResponse($th, null);
        }
    }
}
