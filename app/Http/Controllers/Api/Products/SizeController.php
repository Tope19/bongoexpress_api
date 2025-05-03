<?php

namespace App\Http\Controllers\Api\Products;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Product\SizeService;
use App\Exceptions\Product\ProductException;
use App\Http\Resources\Products\SizeResource;
use Illuminate\Validation\ValidationException;

class SizeController extends Controller
{
    public $size_service;

    public function __construct()
    {
        $this->size_service = new SizeService;
    }

    public function list(Request $request)
    {
        try {
            $products = $this->size_service->getAll();
            $data = SizeResource::collection($products)->toArray($request);
            return ApiHelper::validResponse("Product Size(s) retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $this->size_service->create($request->all());
            return ApiHelper::validResponse("Product size created successfully", new SizeResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
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
            $Product = $this->size_service->getById($id);
            return ApiHelper::validResponse("Product size retrieved successfully", new SizeResource($Product));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
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
            $data = $this->size_service->update($request->all(), $id);
            return ApiHelper::validResponse("Product size updated successfully", new SizeResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
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
            $this->size_service->delete($id);
            return ApiHelper::validResponse("Product size deleted successfully");
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

}
