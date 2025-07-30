<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Middleware\ApiEnsureFrontendRequestsAreStateful;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-email', function() {
    $details = [
        'title' => 'Test Email from Laravel',
        'body' => 'This is a test email to verify SMTP configuration.'
    ];

    \Mail::to('topeolotu75@gmail.com')->send(new \App\Mail\TestMail($details));

    return response()->json([
        'message' => 'Email sent successfully!'
    ]);
});

// Guest routes
Route::get("/products/list", [\App\Http\Controllers\Api\Products\ProductController::class, "list"])->name("list");
Route::get("/product-details/{id}", [\App\Http\Controllers\Api\Products\ProductController::class, "show"])->name("show");
Route::get("/categories/list", [\App\Http\Controllers\Api\Products\CategoryController::class, "list"])->name("list");
Route::post("/payment/webhook", [\App\Http\Controllers\Api\Payment\PaymentController::class, "webhook"])->name("paystack.webhook");

Route::prefix("auth")->as("auth.")->group(function () {
    Route::post("/register", [RegisterController::class, "register"])->name("register");
    Route::post("/oauth-login", [LoginController::class, "oauthLogin"]);
    Route::post("/login", [LoginController::class, "login"])->name("login");
    Route::post("/apple/signin", [LoginController::class, "signInWithApple"])->name("signInWithApple");


    Route::prefix("password")->as("password.")->group(function () {
        Route::post('/forgot', [PasswordController::class, 'forgotPassword'])->name("forgot_password");
        Route::post("/reset", [PasswordController::class, "resetPassword"])->name("reset_password");
    });
    Route::prefix("otp")->as("otp.")->group(function () {
        Route::post('/request', [VerificationController::class, 'request'])->name("request");
        Route::post("/verify", [VerificationController::class, "verify"])->name("verify");
    });
});

// Authenticated routes
Route::middleware([ApiEnsureFrontendRequestsAreStateful::class, "auth:sanctum"])->group(function () {
    Route::get("/me", [UserController::class, "me"])->name("me");

    Route::prefix("user")->as("user.")->group(function () {
         // logout
        Route::post("/logout", [LoginController::class, "logout"])->name("logout");
        Route::prefix("profile")->as("profile.")->group(function () {
            Route::post("/update", [UserController::class, "update"])->name("update");
            Route::post("/delete", [UserController::class, "delete"])->name("delete");
        });
    });

    Route::prefix("products")->as("products.")->group(function () {

        // products
        Route::get("/", [\App\Http\Controllers\Api\Products\ProductController::class, "list"])->name("list");
        Route::get("/search", [\App\Http\Controllers\Api\Products\ProductController::class, "search"])->name("search");
        Route::post("/create", [\App\Http\Controllers\Api\Products\ProductController::class, "create"])->name("create");
        Route::post("/update/{id}", [\App\Http\Controllers\Api\Products\ProductController::class, "update"])->name("update");
        Route::post("/delete/{id}", [\App\Http\Controllers\Api\Products\ProductController::class, "delete"])->name("delete");
        Route::get("/show/{id}", [\App\Http\Controllers\Api\Products\ProductController::class, "show"])->name("show");

        // categories
        Route::prefix("categories")->as("categories.")->group(function () {
            Route::get("/", [\App\Http\Controllers\Api\Products\CategoryController::class, "list"])->name("list");
            Route::post("/create", [\App\Http\Controllers\Api\Products\CategoryController::class, "create"])->name("create");
            Route::post("/update/{id}", [\App\Http\Controllers\Api\Products\CategoryController::class, "update"])->name("update");
            Route::post("/delete/{id}", [\App\Http\Controllers\Api\Products\CategoryController::class, "delete"])->name("delete");
            Route::get("/show/{id}", [\App\Http\Controllers\Api\Products\CategoryController::class, "show"])->name("show");
        });

        // sizes
        Route::prefix("sizes")->as("sizes.")->group(function () {
            Route::get("/", [\App\Http\Controllers\Api\Products\SizeController::class, "list"])->name("list");
            Route::post("/create", [\App\Http\Controllers\Api\Products\SizeController::class, "create"])->name("create");
            Route::post("/update/{id}", [\App\Http\Controllers\Api\Products\SizeController::class, "update"])->name("update");
            Route::post("/delete/{id}", [\App\Http\Controllers\Api\Products\SizeController::class, "delete"])->name("delete");
            Route::get("/show/{id}", [\App\Http\Controllers\Api\Products\SizeController::class, "show"])->name("show");
        });

        // images
        Route::prefix("images")->as("images.")->group(function () {
            Route::get("/", [\App\Http\Controllers\Api\Products\ImageController::class, "list"])->name("list");
            Route::post("/create", [\App\Http\Controllers\Api\Products\ImageController::class, "create"])->name("create");
            Route::post("/update/{id}", [\App\Http\Controllers\Api\Products\ImageController::class, "update"])->name("update");
            Route::post("/delete/{id}", [\App\Http\Controllers\Api\Products\ImageController::class, "delete"])->name("delete");
            Route::get("/show/{id}", [\App\Http\Controllers\Api\Products\ImageController::class, "show"])->name("show");
        });
    });

     // cart
     Route::prefix("cart")->as("cart.")->group(function () {
        Route::get("/", [\App\Http\Controllers\Api\Products\CartController::class, "list"])->name("list");
        Route::post("/add", [\App\Http\Controllers\Api\Products\CartController::class, "add"])->name("add");
        Route::post("/update/{id}", [\App\Http\Controllers\Api\Products\CartController::class, "update"])->name("update");
        Route::post("remove/{id}", [\App\Http\Controllers\Api\Products\CartController::class, "removeCartItem"])->name("remove");
        Route::post("/delete/{id}", [\App\Http\Controllers\Api\Products\CartController::class, "delete"])->name("delete");
    });

    // wishlist
    Route::prefix("wishlist")->as("wishlist.")->group(function () {
        Route::get("/", [\App\Http\Controllers\Api\Products\WishlistController::class, "list"])->name("list");
        Route::post("/add", [\App\Http\Controllers\Api\Products\WishlistController::class, "add"])->name("add");
        Route::post("/delete/{id}", [\App\Http\Controllers\Api\Products\WishlistController::class, "delete"])->name("delete");
    });

    // payment
    Route::prefix("payment")->as("payment.")->group(function () {
        Route::post("/initialize", [\App\Http\Controllers\Api\Payment\PaymentController::class, "initialize"])->name("initialize");
    });

    // orders
    Route::prefix("orders")->as("orders.")->group(function () {
        Route::get("/", [\App\Http\Controllers\Api\Order\OrderController::class, "list"])->name("list");
        Route::get("/show/{id}", [\App\Http\Controllers\Api\Order\OrderController::class, "show"])->name("show");
    });

    // logistics
    Route::prefix("logistics")->as("logistics.")->group(function () {
        Route::get("/package-types", [\App\Http\Controllers\Api\Logistics\LogisticController::class, "listPackageTypes"])->name("package_types");
        Route::get("/delivery-zones", [\App\Http\Controllers\Api\Logistics\LogisticController::class, "listDeliveryZones"])->name("delivery_zones");
        Route::post("/create-order", [\App\Http\Controllers\Api\Logistics\LogisticController::class, "createOrder"])->name("create_order");
        Route::get("/orders", [\App\Http\Controllers\Api\Logistics\LogisticController::class, "getOrders"])->name("orders");
        Route::get("/order/{id}", [\App\Http\Controllers\Api\Logistics\LogisticController::class, "getOrderDetails"])->name("order_details");
        Route::post("/test-zone-pricing", [\App\Http\Controllers\Api\Logistics\LogisticController::class, "testZonePricing"])->name("test_zone_pricing");
    });

    // location-based shipping
    Route::prefix("location")->as("location.")->group(function () {
        Route::get("/states", [\App\Http\Controllers\Api\Location\LocationController::class, "getStates"])->name("states");
        Route::get("/states/{stateId}/cities", [\App\Http\Controllers\Api\Location\LocationController::class, "getCitiesByState"])->name("cities_by_state");
        Route::get("/states/{stateId}/zones", [\App\Http\Controllers\Api\Location\LocationController::class, "getZonesByState"])->name("zones_by_state");
        Route::get("/cities/{cityId}/zones", [\App\Http\Controllers\Api\Location\LocationController::class, "getZonesByCity"])->name("zones_by_city");
        Route::post("/calculate-shipping", [\App\Http\Controllers\Api\Location\LocationController::class, "calculateShippingFee"])->name("calculate_shipping");
    });

});
