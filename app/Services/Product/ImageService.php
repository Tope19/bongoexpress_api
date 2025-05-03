<?php

namespace App\Services\Product;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImageService
{
    public ProductImage $image;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): ProductImage
    {
        $model = ProductImage::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Product Image not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "product_id" => "required|exists:products,id",
            "image_path" => "required|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "is_primary" => "nullable|boolean",
        ], [
            "product_id.exists" => "Product not found",

        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): ProductImage
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);
            $data["image_path"] = uploadImage($data["image_path"], "products");
            $data["status"] = 1;
            $image = ProductImage::create($data);
            DB::commit();
            return $image;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data, $id);

            $image = !empty($id) ? $this->getById($id) : $this->image;

            $data["image_path"] = uploadImage($data["image_path"], "products");
            $image->update($data);

            DB::commit();
            return $image->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $image = !empty($id) ? $this->getById($id) : $this->image;
            $image->status = 0;
            $image->save();
            // $product->delete();
            DB::commit();
            return $image;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = ProductImage::query()
                                ->where("status", 1);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
