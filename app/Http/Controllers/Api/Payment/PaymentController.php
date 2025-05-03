<?php

namespace App\Http\Controllers\Api\Payment;

use App\Helpers\ApiHelper;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\CartService;
use Exception;
use App\Constants\General\ApiConstants;
use App\Exceptions\Product\CartException;
use App\Http\Resources\Products\CartResource;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public $payment_service;
    public $order_service;

    public function __construct()
    {
        $this->payment_service = new PaymentService();
        $this->order_service = new OrderService();
    }


    public function initialize(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_id'] = $request->user()->id;
            // dd($data);
            $data = $this->payment_service->create($data);
            return ApiHelper::validResponse("Paystack Authorisation Url", $data);
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

    public function webhook(Request $request)
    {
        try {
            $data = $this->payment_service->handleWebhook($request->all());
            return ApiHelper::validResponse("success");
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
