<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //to create categories and products
        Category::factory()->count(6)->create()->each(function($ctgry) {
            Product::factory()->count(8)->create(['category_id' => $ctgry->id]);
        });

        //to create customers
        Customer::factory()->count(40)->create();

        $customers = Customer::all();
        $products = Product::all();

        //to create 200 orders with 1-5 items each order
        for ($i = 0; $i <200; $i++) {
            $customer = $customers->random();
            $orderDate = now()->subDays(rand(0, 120))->toDateString();
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_date' => $orderDate,
                'total_amount' => 0
            ]);

            $itemsCount = rand(1, 5);
            $total = 0;
            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products->random();
                $qty = rand(1, 6);
                $unit = $product->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                ]);
                $total += $qty * $unit;
            }
            $order->update(['total_amount' => $total]);
        }
    }
}
