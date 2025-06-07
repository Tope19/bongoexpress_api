<?php

namespace App\Services\Product;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\ProductSize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SizeService
{
    public ProductSize $size;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): ProductSize
    {
        $model = ProductSize::where($column, $key)
        ->with(['product', 'product.category', 'product.user'])
        ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Product Size not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "product_id" => "required|exists:products,id",
            "size" => "required|string|max:255",
            "price" => "required|numeric",
            "stock_quantity" => "required|integer",
        ], [
            "product_id.exists" => "Product not found",
            "size.required" => "Size is required",
            "price.required" => "Price is required",
            "stock_quantity.required" => "Stock quantity is required",

        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): ProductSize
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);
            $data["status"] = 1;
            $size = ProductSize::create($data);
            DB::commit();
            return $size;
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

            $size = !empty($id) ? $this->getById($id) : $this->size;

            $size->update($data);

            DB::commit();
            return $size->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $size = !empty($id) ? $this->getById($id) : $this->size;
            $size->status = 0;
            $size->save();
            // $product->delete();
            DB::commit();
            return $size;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = ProductSize::query()
                                ->with(['product', 'product.category', 'product.user'])
                                ->where("status", 1)
                                ->orderBy("id", "DESC");
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
