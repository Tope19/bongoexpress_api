<?php

namespace App\Services\Logistic;

use App\Models\PriceSetting;
use App\Models\PackageType;

class PriceCalculationService
{
    /**
     * Calculate the delivery price based on distance, weight, and package type
     *
     * @param float $distance Distance in kilometers
     * @param float $weight Weight in kilograms
     * @param int $packageTypeId ID of the package type
     * @return array Price calculation details
     */
    public function calculatePrice(float $distance, float $weight, int $packageTypeId): array
    {
        // Get pricing settings
        $settings = PriceSetting::getSettings();

        if (!$settings) {
            throw new \Exception('Pricing settings not found');
        }

        // Get package type
        $packageType = PackageType::findOrFail($packageTypeId);

        // Calculate base price
        $baseFare = $settings->base_fare;
        $distancePrice = $distance * $settings->price_per_km;
        $weightPrice = $weight * $settings->price_per_kg;

        // Calculate subtotal
        $subtotal = $baseFare + $distancePrice + $weightPrice;

        // Apply package type multiplier
        $totalBeforeMin = $subtotal * $packageType->price_multiplier;

        // Ensure minimum price
        $totalPrice = max($totalBeforeMin, $settings->min_price);

        return [
            'base_fare' => $baseFare,
            'distance_price' => $distancePrice,
            'weight_price' => $weightPrice,
            'subtotal' => $subtotal,
            'package_type_multiplier' => $packageType->price_multiplier,
            'price_before_min' => $totalBeforeMin,
            'minimum_price' => $settings->min_price,
            'total_price' => round($totalPrice, 2),
            'distance' => $distance,
            'weight' => $weight,
            'package_type' => $packageType->name,
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Earth's radius in kilometers
        $radius = 6371;

        // Distance in kilometers
        $distance = $radius * $c;

        return round($distance, 2);
    }

    /**
     * Calculate total distance for multiple dropoff points
     *
     * @param array $points Array of [lat, lon] coordinates starting with pickup location
     * @return float Total distance in kilometers
     */
    public function calculateTotalDistance(array $points): float
    {
        $totalDistance = 0;

        // Calculate distance between pickup and each dropoff
        for ($i = 0; $i < count($points) - 1; $i++) {
            $currentPoint = $points[$i];
            $nextPoint = $points[$i + 1];

            $distance = $this->calculateDistance(
                $currentPoint['lat'],
                $currentPoint['lon'],
                $nextPoint['lat'],
                $nextPoint['lon']
            );

            $totalDistance += $distance;
        }

        return $totalDistance;
    }

    /**
     * Calculate price for a delivery with multiple dropoffs
     *
     * @param array $pickupLocation [lat, lon] of pickup location
     * @param array $dropoffLocations Array of [lat, lon] dropoff locations
     * @param float $weight Weight in kilograms
     * @param int $packageTypeId ID of package type
     * @return array Price calculation details with individual dropoff prices
     */
    public function calculateMultiDropoffPrice(
        array $pickupLocation,
        array $dropoffLocations,
        float $weight,
        int $packageTypeId
    ): array {
        $points = [$pickupLocation, ...$dropoffLocations];
        $totalDistance = $this->calculateTotalDistance($points);

        // Get the overall price calculation
        $priceDetails = $this->calculatePrice($totalDistance, $weight, $packageTypeId);

        // Calculate individual dropoff prices
        $dropoffPrices = [];
        $settings = PriceSetting::getSettings();
        $packageType = PackageType::findOrFail($packageTypeId);

        foreach ($dropoffLocations as $index => $dropoff) {
            $distance = $this->calculateDistance(
                $pickupLocation['lat'],
                $pickupLocation['lon'],
                $dropoff['lat'],
                $dropoff['lon']
            );

            // Simple allocation based on distance proportion
            $proportion = $distance / $totalDistance;
            $price = $priceDetails['total_price'] * $proportion;
            $dropoffPrice = round($price, 2);

            $dropoffPrices[] = [
                'index' => $index,
                'distance' => $distance,
                'price' => round($dropoffPrice, 2)
            ];
        }

        $priceDetails['dropoff_prices'] = $dropoffPrices;

        return $priceDetails;
    }

    /**
     * Get available package types
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPackageTypes()
    {
        $packageTypes = PackageType::where('is_active', true)->get();
        if ($packageTypes->isEmpty()) {
            throw new \Exception('No active package types found');
        }
        return $packageTypes;
    }
}
