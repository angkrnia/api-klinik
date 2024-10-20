<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            "statusCode" => 200,
            "message" => "Error mendapatkan data",
            "data" => [
                "SUCCESS_FLAG" => 1,
                "TOTAL_ROWS" => 4,
                "TOTAL_PAGES" => 1,
                "DATA_PER_PAGE" => 12,
                "CURRENT_PAGE" => 1,
                "list" => [
                    [
                        "ROW_NUM" => "1",
                        "ITEM_ID" => "96",
                        "ITEM_NAME" => "Tas Viral",
                        "ITEM_IMAGE_ID" => "509",
                        "ITEM_SLUG" => "tas-viral",
                        "ITEM_SELL_PRICE" => 500000,
                        "ITEM_NEW_PRICE" => 449000,
                        "ITEM_DISCOUNT_SOURCE_TYPE" => "SPECIAL",
                        "ITEM_DISCOUNT_EXPIRED_DATE" => "2024-08-31T00:00:00.000Z",
                        "ITEM_ISAVAILABLE" => true,
                        "ITEM_ONHAND_QTY" => 56,
                        "ITEM_SALE_TOTAL" => 26,
                        "ITEM_ISNEW_PRODUCT" => true,
                        "ITEM_REVIEW_COUNT" => 4,
                        "ITEM_REVIEW_STAR" => 5,
                        "ITEM_IS_CUSTOMER_WISHLIST" => false,
                        "ITEM_IMAGE_URL" => "https://storage.googleapis.com/dev-ama-nexa/lamonte.id/2024/08/07/product-2-500x500.webp",
                        "ITEM_ID_CODE" => "893127ee098306390cd10ade41af71c3"
                    ]
                ]
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $statusCode = 200;
        return response()->json([
            "statusCode" => $statusCode,
            "message" => "Data tidak ditemukan",
            "data" => []
        ], $statusCode);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * App Version
     */
    public function appVersion(Request $request)
    {
        return response()->json([
            "statusCode" => 200,
            "message" => "Success",
            "data" => DB::table('app_config')->first(['version', 'description', 'url', 'is_force_update']),
        ]);
    }
}
