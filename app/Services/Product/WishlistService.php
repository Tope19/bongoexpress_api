<?php

namespace App\Services\Product;

use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\General\ModelNotFoundException;

class WishlistService
{
    public Wishlist $wishlist;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): Wishlist
    {
        $model = Wishlist::where($column, $key)
                    ->with(['size.product', 'user'])
                    ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Wishlist not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        // dd($data);
        $validator = Validator::make($data, [
            'product_size_id' => 'required|exists:product_sizes,id',
            'user_id' => 'required|exists:users,id'
        ], [
            'product_size_id.required' => 'Product is required',
            'product_size_id.exists' => 'Product does not exist',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // dd($data);


        return $validator->validated();
    }


    public function create(array $data): Wishlist
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);

            $wishlist = Wishlist::firstOrCreate($data);
            // dd($wishlist);
            DB::commit();
            return $wishlist;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $wishlist = !empty($id) ? $this->getById($id) : $this->wishlist;
            $wishlist->delete();
            DB::commit();
            return $wishlist;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = Wishlist::query()
            ->with(['size.product', 'user']);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
