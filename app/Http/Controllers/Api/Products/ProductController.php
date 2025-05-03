<?php

namespace App\Http\Controllers\Api\Products;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Product\ProductService;
use App\Exceptions\Product\ProductException;
use App\Http\Resources\Products\ProductResource;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public $product_service;

    public function __construct()
    {
        $this->product_service = new ProductService;
    }

    public function list(Request $request)
    {
        try {
            $products = $this->product_service->getAll();
            // dd($products);
            $data = ProductResource::collection($products);
            return ApiHelper::validResponse("Products retrieved successfully", $data);
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
            $data = $this->product_service->create($request->all());
            return ApiHelper::validResponse("Product created successfully", new ProductResource($data));
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
            $Product = $this->product_service->getById($id);
            return ApiHelper::validResponse("Product retrieved successfully", new ProductResource($Product));
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
            $data = $this->product_service->update($request->all(), $id);
            return ApiHelper::validResponse("Product updated successfully", new ProductResource($data));
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
            $this->product_service->delete($id);
            return ApiHelper::validResponse("Product deleted successfully");
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
