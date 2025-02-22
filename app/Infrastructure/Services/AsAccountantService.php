<?php

namespace App\Infrastructure\Services;

use App\Constants\AsAccountant;
use App\Constants\ClientResponse;
use App\Constants\Logging;
use App\Infrastructure\API\AsAccountantAPI;
use Botble\Ecommerce\Models\Product;
use Botble\Slug\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AsAccountantService
{
    use AsAccountantAPI;

    const ACCOUNTANT_ACCESS_TOKEN_KEY = 'accountant_access_token';

    /**
     * @return bool
     */
    public function refreshAccessToken(): bool
    {
        $status = ClientResponse::STATUS['error'];
        try {
            $response = $this->refreshAccessTokenAPI();
            if ($response['status'] == ClientResponse::STATUS['success']) {

                $save = setting()->forceSet([
                    self::ACCOUNTANT_ACCESS_TOKEN_KEY => $response['data']['accessToken']
                ])
                    ->save();

                if ($save) {
                    $status = ClientResponse::STATUS['success'];
                }
            }
        } catch (\Exception $e) {
            Log::error(Logging::ACCOUNTANT_REFRESH_ACCESS_TOKEN_FAIL, [
                'msg' => $e->getMessage(),
                'tress' => $e->getTraceAsString()
            ]);
        }

        return $status;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        $data = [];
        $status = ClientResponse::STATUS['error'];
        $productData = $this->getProductsAPI();
        $productsQtyData = $this->getProductsByStorageAPI();
        if ($productData['status'] == ClientResponse::STATUS['success']
            && $productsQtyData['status'] == ClientResponse::STATUS['success']
        ) {
            $status = ClientResponse::STATUS['success'];
            foreach ($productData['data'] as $productId => $product) {
                $product['Qty'] = $productsQtyData['data'][$productId]['Qty'] ?? 0;
                $product['CostSumm'] = $productsQtyData['data'][$productId]['CostSumm'] ?? 0;
                $product['CostSummVAT'] = $productsQtyData['data'][$productId]['CostSummVAT'] ?? 0;
                $product['CurrCostSumm'] = $productsQtyData['data'][$productId]['CurrCostSumm'] ?? 0;
                $product['CurrCost'] = $productsQtyData['data'][$productId]['CurrCost'] ?? 0;
                $product['Price'] = $productsQtyData['data'][$productId]['Price'] ?? 0;
                $product['Summ'] = $productsQtyData['data'][$productId]['Summ'] ?? 0;
                $product['Col0'] = $productsQtyData['data'][$productId]['Col0'] ?? 0;

                $data[$productId] = $product;
            }
        }

        return [
            'status' => $status,
            'data' => $data
        ];
    }

    /**
     * @param Product $product
     * @param array $data
     * @return array|Request
     */
    public function setProductRequest(Product $product, array $data): array|Request
    {
        $model = 'Botble\Ecommerce\Models\Product';
        $slug = $this->generateProductSlug(); // 'product-1734983600';

        $sku = $data['MTCode'];
        $groupPath = explode('!', $data['GroupPath']);
        $categories = [];
        if (!empty($groupPath)) {
            foreach ($groupPath as $groupId) {
                $categories[] = AsAccountant::CATEGORIES_ID_MAP[$groupId] ?? [];
            }
        }

        $requestData = [
            'model' => $model,
            'sku' => $sku,
            'name' => $data['Caption'],
            'quantity' => $data['Qty'],
            'price' => $data['Summ'],
            'categories' => $categories,
            'brand_id' => null,
            'is_slug_editable' => 1,
            'description' => $data['Description'],
            'content' => $data['FullCaption'],
            'status' => 'pending',
            'images' => [],
            'product_type' => 'physical',
            'specification_table_id' => 1,
            'specification_attributes' => [],
            'sale_type' => '0',
            'sale_price' => null,
            'start_date' => null,
            'end_date' => null,
            'cost_per_item' => "0",
            'barcode' => $data['BarCode'],
            'with_storehouse_management' => '1',
            'allow_checkout_when_out_of_stock' => '0',
            'stock_status' => 'in_stock',
            'weight' => 0,
            'length' => 0,
            'wide' => 0,
            'height' => 0,
            'is_added_attributes' => 0,
            'has_product_options' => 1,
            'related_products' => null,
            'cross_sale_products' => [],
            'faq_schema_config' => '[]',
            'selected_existing_faqs' => [],
            'seo_meta' => [
                'seo_title' => null,
                'seo_description' => null,
                'index' => 'index'
            ],
            'seo_meta_image' => null,
            'layout' => null,
            'is_popular' => 0,
            'is_featured' => 0,
            'image' => null,
            'product_collections' => [],
            'product_labels' => [],
            'minimum_order_quantity' => 0,
            'maximum_order_quantity' => 0,
            'tag' => null, // '[{"value":"tag1"},{"value":"tag2"}]'
            'submitter' => 'apply',
            'options' => null,
            'notify_attachment_updated' => false,
        ];

        if ($product->id) {
            $requestData['product_id'] = $product->id;
            $slugData = Slug::where('key', $slug)
                ->where('reference_type', $model)
                ->where('reference_id', $product->id)
                ->first();

            if (!empty($slugData)) {
                $requestData['slug_id'] = $slugData->id;
                $requestData['slug'] = $slugData->key;
            } else {
                $requestData['slug_id'] = 0;
                $requestData['slug'] = $slug;
            }
        } else {
            $requestData['slug_id'] = 0;
            $requestData['slug'] = $slug;
        }

        return new Request($requestData);
    }

    /**
     * @param bool $random
     * @param int $length
     * @return string
     */
    private function generateProductSlug(bool $random = false, int $length = 150): string
    {
        if ($random) {
            return substr(Str::uuid()->toString() . Str::random($length), 0, $length);
        } else {
            return 'product-' . Carbon::now()->timestamp;
        }
    }
}
