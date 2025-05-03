<?php

namespace App\Http\Controllers\Api\Products;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Product\CategoryService;
use App\Exceptions\Product\CategoryException;
use App\Http\Resources\Products\CategoryResource;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public $category_service;

    public function __construct()
    {
        $this->category_service = new CategoryService;
    }

    public function list(Request $request)
    {
        try {
            $categories = $this->category_service->getAll();
            $data = CategoryResource::collection($categories)->toArray($request);
            return ApiHelper::validResponse("Categories retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CategoryException $e) {
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
            $data = $this->category_service->create($request->all());
            return ApiHelper::validResponse("Category created successfully", new CategoryResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CategoryException $e) {
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
            $category = $this->category_service->getById($id);
            return ApiHelper::validResponse("Category retrieved successfully", new CategoryResource($category));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CategoryException $e) {
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
            $data = $this->category_service->update($request->all(), $id);
            return ApiHelper::validResponse("Category updated successfully", new CategoryResource($data));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CategoryException $e) {
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
            $this->category_service->delete($id);
            return ApiHelper::validResponse("Category deleted successfully");
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (CategoryException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

}
