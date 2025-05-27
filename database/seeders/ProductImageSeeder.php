<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageSeeder extends Seeder
{
    public function run()
    {
        $imageMap = [
            'Egusi' => 'https://c8.alamy.com/comp/F6RM42/egusi-seeds-without-shells-african-watermelon-seeds-egusi-soup-ingredient-F6RM42.jpg',
            'Palm Oil' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRzo5EQkmREh_pqUAwolpLyWSciXIpkbiJT9Q&s',
            'Dry Fish' => 'https://media.istockphoto.com/id/1499928964/photo/little-pile-of-dried-small-fish-laid-out-in-slide-at-market-in-india-cooked-seafood-sold-at.jpg?s=612x612&w=0&k=20&c=Cj6XRZKNXf74iSv0BvZegpJ74SyGRWWKXr2hV89EIxA=',
            'Cray Fish' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVOh861JpSG7Km97baABRT5Na1TzFFNcohBg&s',
            'Spices' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTgU7cvjyfv2wGGeieYscfmBKm9nm0dK_BrsA&s',
            'Ugu' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTDYcsjHGV9gB3UUZ2fvXcO1ArFDm5RtXgFeQ&s',
            'Bitter Leaves' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRZeosDfU0wSZohCK1C08Lg_GXR3X61NaFwqQ&s',
            'Dry Ponmo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2kBoeRnJ1FzIRTZpZp-jX_qJ-j2AIMBkXRA&s',
            'Goat Meat' => 'https://nigeria.foodkravings.com/wp-content/uploads/2022/08/half-goat-meat-.jpg',
            'Cow Meat' => 'http://media.premiumtimesng.com/wp-content/files/2021/07/WhatsApp-Image-2021-07-04-at-18.02.41-1.jpeg',
            'Tiko Meat' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXeTbdrHKm0TVasgcAE5Dm4_Ws0Ad0tEg9zQ&s',
            'Lucys Bean' => 'https://www.blessthismessplease.com/wp-content/uploads/2019/06/grandma-lucys-baked-beans-recipe-1-of-5-856x1024.jpg',
            'Tin Tomatoes' => 'https://image.made-in-china.com/2f0j00FRVbUmCGhLkf/Healthy-Tin-Tomato-Double-Concentrated-Tomato-Paste-28-30-Canned-or-Sachet-to-Mali.webp',
            'Banga' => 'https://www.shutterstock.com/image-photo/close-palm-oil-seeds-on-600nw-2255066329.jpg',
            'Periwinkle' => 'https://mile12market.com/wp-content/uploads/2014/12/mile12_market_online-periwinkles-shellfish.png',
            'Garlic' => 'https://tildaricelive.s3.eu-central-1.amazonaws.com/wp-content/uploads/2022/04/25115428/Full-garlics-edit-1440x970.jpg',
            'Ginger' => 'https://media.post.rvohealth.io/wp-content/uploads/2023/09/ginger-root-still-life-1296x728-header.jpg',
            'Scent Leave' => 'https://www.envynature.org/wp-content/uploads/2022/07/IMG_20220722_092244-scaled.jpg',
            'Uzize Seeds' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS8Aoru_unVusoNKwjRcLgSKNVfi2Wn4RxFTQ&s',
            'Utazi Leave' => 'https://cdn.guardian.ng/wp-content/uploads/2015/09/Utazi.jpg',
            'Dry Pepper' => 'https://live.staticflickr.com/65535/48308492231_93039d4731_h.jpg',
            'Grounded pepper' => 'https://www.jannymart.com/wp-content/uploads/2024/08/FMA-Grounded-Pepper-%E2%80%93-Ata-gigun.png',
            'Stock Fish' => 'https://image.api.sportal365.com/process/smp-images-production/pulse.ng/26072024/1b9ee980-452f-4e6e-8fbf-7223b0e981d3',
            'Prawn' => 'https://t4.ftcdn.net/jpg/00/96/12/09/360_F_96120998_D8ZMfxIvQiExJK1rRmOwmyGnhP3eS1OW.jpg',
            'Ogbolo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4QC6L2QdNk-6jccVzw6-l5dls2WYOOrkBxQ&s',
            'Scent Pepper' => 'https://nenagroceries.com/wp-content/uploads/2024/03/grounded-cameroon-pepper-270x270.jpg',
        ];

        foreach (Product::all() as $product) {
            // Try to match exact name
            $matchedImage = $imageMap[$product->name] ?? null;

            // Try fallback using partial matching
            if (!$matchedImage) {
                foreach ($imageMap as $key => $url) {
                    if (stripos($product->name, $key) !== false) {
                        $matchedImage = $url;
                        break;
                    }
                }
            }

            // Default fallback
            $matchedImage = $matchedImage ?? 'https://locksmithstore.com/media/catalog/product/placeholder/websites/1/Product-Image-Coming-Soon.png';

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $matchedImage,
                'is_primary' => true,
            ]);
        }
    }
}
