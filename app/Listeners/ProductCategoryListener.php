<?php

namespace App\Listeners;

use App\Events\ProductCategoryEvent;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryListener
{
    /**
     * Create the event listener.
     */
    public $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(ProductCategoryEvent $event): void
    {
        foreach ($event->request->category as $category) {
            ProductCategory::create([
                'product_id' => $event->product->id,
                'category_id' => $category,
            ]);
        }

    }
}
