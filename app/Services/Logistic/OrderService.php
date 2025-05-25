<?php

namespace App\Services\Logistic;

use App\Models\LogisticOrder;
use App\Exceptions\General\ModelNotFoundException;

class OrderService
{
    public LogisticOrder $order;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): LogisticOrder
    {
        $user = auth()->user();
        if (empty($user)) {
            throw new ModelNotFoundException("User not found");
        }
        $model = LogisticOrder::where($column, $key)
                    ->where('user_id', $user->id)
                    ->with(['pickupLocation', 'user', 'packageType', 'dropoffs'])
                    ->orderBy('created_at', 'desc')
                    ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Order not found");
        }
        return $model;
    }




    public function getAll($per_page = null)
    {
        $user = auth()->user();
        if (empty($user)) {
            throw new ModelNotFoundException("User not found");
        }
        $query = LogisticOrder::query()
            ->where('user_id', $user->id)
            ->with(['pickupLocation', 'user', 'packageType', 'dropoffs'])
            ->orderBy('created_at', 'desc');
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
