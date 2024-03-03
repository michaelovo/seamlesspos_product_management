<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Status;
use Illuminate\Support\Str;

trait Helpers
{

    /**
     * This endpoint fetches a status ID through the status name.
     * @param string $statusName
     * @return int
     *
     */
    public static function getStatusId(string $statusName): int
    {
        $status_id = Status::where('name', $statusName)->pluck('id')->first();
        if (is_null($status_id)) {
            throw new \Exception('Failed To Fetch Status ID');
        }

        return $status_id;
    }

    /**
     * This endpoint fetches a status Name through the status ID.
     * @param int $statusUD
     * @return string
     *
     */

    public static function getStatusName(int $statusID): string
    {
        $status = Status::find($statusID);
        if (is_null($status)) {
            throw new \Exception('Failed To Fetch Status Name');
        }

        return $status->name;
    }

    /**
     * This endpoint fetches a category ID through the category name.
     * @param string $name
     * @return int
     *
     */
    public static function getCategoryId(string $categoryName): int
    {
        $category_id = Category::where('name', $categoryName)->pluck('id')->first();
        if (is_null($category_id)) {
            throw new \Exception('Failed To Fetch Category ID');
        }

        return $category_id;
    }

    /**
     * This endpoint fetches a Category Name through the Category ID.
     * @param int $categoryID
     * @return string
     *
     */
    public static function getCategoryName(int $categoryID): string
    {
        $category = Category::find($categoryID);
        if (is_null($category)) {
            throw new \Exception('Failed To Fetch Category Name');
        }

        return $category->name;
    }
}
