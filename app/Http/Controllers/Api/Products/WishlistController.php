<?php

namespace App\Http\Controllers\Api\Products;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Exceptions\Product\WishlistException;
use App\Services\Product\WishlistService;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Products\WishlistResource;

class WishlistController extends Controller
{
    public $wishlist_service;

    public function __construct()
    {
        $this->wishlist_service = new WishlistService();
    }

    public function list(Request $request)
    {
        try {
            $wishlists = $this->wishlist_service->getAll();
            $data = WishlistResource::collection($wishlists);
            return ApiHelper::validResponse("Wishlist retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (WishlistException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function add(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_id'] = $request->user()->id;
            // dd($data);
            $data = $this->wishlist_service->create($data);
            return ApiHelper::validResponse("Added to Wishlist", new WishlistResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (WishlistException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $this->wishlist_service->delete($id);
            return ApiHelper::validResponse("Removed from Wishlist");
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (WishlistException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

}
