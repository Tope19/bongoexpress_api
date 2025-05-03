<?php

namespace App\Http\Controllers\Api\Products;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Product\ImageService;
use App\Exceptions\Product\ProductException;
use App\Http\Resources\Products\ImageResource;
use Illuminate\Validation\ValidationException;

class ImageController extends Controller
{
    public $image_service;

    public function __construct()
    {
        $this->image_service = new ImageService();
    }

    public function list(Request $request)
    {
        try {
            $images = $this->image_service->getAll();
            $data = ImageResource::collection($images)->toArray($request);
            return ApiHelper::validResponse("Product Images retrieved successfully", $data);
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
            $data = $this->image_service->create($request->all());
            return ApiHelper::validResponse("Product Image created successfully", new ImageResource($data));
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
            $image = $this->image_service->getById($id);
            return ApiHelper::validResponse("Product Image retrieved successfully", new ImageResource($image));
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
            $data = $this->image_service->update($request->all(), $id);
            return ApiHelper::validResponse("Product Image updated successfully", new ImageResource($data));
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
            $this->image_service->delete($id);
            return ApiHelper::validResponse("Product Image deleted successfully");
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
