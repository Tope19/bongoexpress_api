<?php

namespace App\Services\Product;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public Product $product;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): Product
    {
        $model = Product::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Product not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            'category_id' => "required|exists:product_categories,id",
            "name" => "required|string|max:255",
            "description" => "required|string",
        ], [

        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): Product
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);
            $data['user_id'] = auth()->user()->id;
            $data['sku'] = 'SKU-' . strtoupper(uniqid());
            $data['barcode'] = 'BAR-' . strtoupper(uniqid());
            $data["status"] = 1;
            // user role is User, then don't allow them to create a product
            if (auth()->user()->role == 'User') {
                throw new \Exception("You are not allowed to create a product");
            }
            $product = Product::create($data);
            DB::commit();
            return $product;
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

            $product = !empty($id) ? $this->getById($id) : $this->product;

            $product->update($data);

            DB::commit();
            return $product->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $product = !empty($id) ? $this->getById($id) : $this->product;
            $product->status = 0;
            $product->save();
            // $product->delete();
            DB::commit();
            return $product;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = Product::query()
                            ->with(["category", "images", "sizes", "user"])
                                ->where("status", 1)
                                ->orderBy("id", "DESC");
        // dd($query->get());
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }

    // search
    public function search($query, $per_page = null)
    {
        $product = Product::query()
                            ->with(["category", "images", "sizes"])
                                ->where("status", 1)
                                    ->where("name", "LIKE", "%$query%")
                                        ->orWhere("description", "LIKE", "%$query%");
        if (!empty($per_page)) {
            return $product->paginate($per_page);
        }
        return $product->get();
    }
}
