<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Traits\Helpers;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use Helpers;
    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter ProductTest::test_verified_user_fetch_product_list
     */

    public function test_verified_user_fetch_product_list()
    {
        Sanctum::actingAs(
            User::where('email', 'emikeovo@yopmail.com')->where('status_id', ProductTest::getStatusId('Active'))->first(),
        );

        $this->get('/api/v1/product')->assertStatus(200);
    }

    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter ProductTest::test_verified_user_fetch_product_by_id
     */

    public function test_verified_user_fetch_product_by_id()
    {
        Sanctum::actingAs(
            User::where('email', 'emikeovo@yopmail.com')->where('status_id', ProductTest::getStatusId('Active'))->first(),
        );

        $product = Product::where('id', 1)->where('status_id', ProductTest::getStatusId('Active'))->first();
        $this->get("/api/v1/product/" . $product->id . "/fetch")
            ->assertStatus(200);
    }

    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter ProductTest::test_unverified_user_fetch_product_list
     */
    public function test_unverified_user_fetch_product_list()
    {
        Sanctum::actingAs(
            User::where('email', 'emikeovo@yopmail.com')->where('status_id', ProductTest::getStatusId('Inactive'))->first(),
        );
        $response = $this->get('/api/v1/product');
        $response->assertStatus(403);
    }
}
