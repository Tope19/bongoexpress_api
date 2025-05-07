<?php

namespace App\Http\Controllers\Api\Products;

use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\CartService;
use Exception;
use App\Constants\General\ApiConstants;
use App\Exceptions\Product\CartException;
use App\Http\Resources\Products\CartResource;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public $cart_service;

    public function __construct()
    {
        $this->cart_service = new CartService;
    }

    public function list(Request $request)
    {
        try {
            $carts = $this->cart_service->getAll();
            // dd($Carts);
            $data = CartResource::collection($carts);
            return ApiHelper::validResponse("Carts retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CartException $e) {
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
            $data = $this->cart_service->create($data);
            return ApiHelper::validResponse("Added to Cart", new CartResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CartException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->cart_service->addCartItem($request->all(), $id);
            return ApiHelper::validResponse("Cart updated", new CartResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CartException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function removeCartItem(Request $request, $id)
    {
        try {
            $data = $this->cart_service->removeCartItem($request->all(), $id);
            return ApiHelper::validResponse("Cart item reduced", new CartResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CartException $e) {
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
            $this->cart_service->delete($id);
            return ApiHelper::validResponse("Removed from Cart");
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CartException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

}
