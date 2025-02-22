<?php

namespace Botble\Ecommerce\Syncers;

use App\Constants\ClientResponse;
use App\Infrastructure\Services\AsAccountantService;
use Botble\Ecommerce\Syncers\Syncer;
use Botble\Ecommerce\Syncers\SyncCounter;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Language\Facades\Language;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductSyncer extends Syncer
{
    protected bool $isMarketplaceActive;

    protected bool $isEnabledDigital;

    protected array $supportedLocales = [];

    protected string $defaultLanguage;

    public function __construct()
    {
        $this->isMarketplaceActive = is_plugin_active('marketplace');
        $this->isEnabledDigital = EcommerceHelper::isEnabledSupportDigitalProducts();

        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            $this->supportedLocales = Language::getSupportedLocales();
            $this->defaultLanguage = Language::getDefaultLanguage(['lang_code'])->lang_code;
        }
    }

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::products.name');
    }

    public function counters(): array
    {
        $products = $this->getProductsCount();
        $syncProducts = $this->getSyncProductsCount();

        return [
            SyncCounter::make()
                ->label(trans('plugins/ecommerce::products.sync.total_products'))
                ->value(number_format($products)),
            SyncCounter::make()
                ->label(trans('plugins/ecommerce::products.sync.total_sync_products'))
                ->value(number_format($syncProducts)),
        ];
    }

    /**
     * @return int
     */
    protected function getProductsCount(): int
    {
        return Product::query()->where('is_variation', false)->count();
    }

    /**
     * @return int
     */
    protected function getSyncProductsCount(): int
    {
        return Product::query()->where('is_variation', false)->count();
    }

    public function getAsProducts()
    {
        $resource = (new AsAccountantService())->getProducts();
        $paginator = '';
        if ($resource['status'] == ClientResponse::STATUS['success']) {
            $data = $resource['data'];
            // Current page number
            $currentPage = request()->get('page', 1);
            // Items per page
            $perPage = 100;

            if ($currentPage > round(count($data) / $perPage)) {
                $currentPage = 1;
            }

            // Slice the array for the current page
            $currentPageItems = array_slice($data, ($currentPage - 1) * $perPage, $perPage);

            $paginator = new LengthAwarePaginator(
                $currentPageItems,
                count($data),
                $perPage,
                $currentPage,
                ['path' => route('tools.data-synchronize.sync.products.index')] // request()->url()
            );
        }

        return $paginator;
    }
}
