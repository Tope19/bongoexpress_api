<?php

namespace App\Services\Product;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public ProductCategory $category;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): ProductCategory
    {
        $model = ProductCategory::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Category not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string',
            "description" => "nullable|string",
            "icon" => "nullable|mimes:jpeg,png,jpg,gif,svg|max:2048",
        ], [

        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): ProductCategory
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);

            // if request has file store it in the storage folder named categories images and return the storage link
            $data["icon"] = uploadImage($data["icon"], "categories");
            $data["status"] = 1;
            $category = ProductCategory::create($data);
            DB::commit();
            return $category;
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

            $category = !empty($id) ? $this->getById($id) : $this->category;

           // if request has file store it in the storage folder named categories images and return the storage link, delete the previous record from the storage and store
            if (isset($data["icon"])) {
                // delete the previous file from the storage
                if (isset($category->icon)) {
                    $previous_file = str_replace("storage/", "", $category->icon);
                    if (file_exists($previous_file)) {
                        unlink($previous_file);
                    }
                }
                $file = $data["icon"];
                $file_name = time() . "_" . $file->getClientOriginalName();
                $file->storeAs("categories", $file_name, "public");
                $data["icon"] = "storage/categories/" . $file_name;
            }

            $category->update($data);

            DB::commit();
            return $category->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $category = !empty($id) ? $this->getById($id) : $this->category;
            $category->status = 0;
            $category->save();
            // $category->delete();
            DB::commit();
            return $category;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = ProductCategory::query()
                                ->where("status", 1);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
