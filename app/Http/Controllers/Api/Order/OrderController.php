<?php

namespace App\Http\Controllers\Api\Order;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Order\OrderService;
use App\Exceptions\Product\OrderException;
use App\Http\Resources\Orders\OrderResource;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public $order_service;

    public function __construct()
    {
        $this->order_service = new OrderService;
    }

    public function list(Request $request)
    {
        try {
            $orders = $this->order_service->getAll();
            // dd($orders);
            $data = OrderResource::collection($orders)->toArray($request);
            return ApiHelper::validResponse("Orders retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (OrderException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $order = $this->order_service->getById($id);
            return ApiHelper::validResponse("Order retrieved successfully", new OrderResource($order));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (OrderException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

}
