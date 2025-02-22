<?php

namespace App\Jobs;

use App\Constants\Logging;
use Botble\Ecommerce\Models\Product;
use App\Infrastructure\Services\AsAccountantService;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AsAccountantProductSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const ACCOUNTANT_LAST_MTID_KEY = 'accountant_last_mtid';

    public $tries = 1;

    public int $retryAfter = 600;

    public int $timeout = 1000000000;

    private array $data;

    public AsAccountantService $AsAccountantService;

    public StoreProductService $storeProductService;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->AsAccountantService = new AsAccountantService();
        $this->storeProductService = new StoreProductService();
    }

    public function handle(): void
    {
        // set_time_limit(0);
        try {
            $product = Product::where('sku', $this->data['MTCode'])->first() ?? new Product();
            $request = $this->AsAccountantService->setProductRequest($product, $this->data);
            $data = $this->storeProductService->execute($request, $product);
            if ($data instanceof Product) {
                // echo 'MTID: ' . $this->data['MTID'] . ' SKU: ' . $data['sku'] . ' Quantity: (' . $data['quantity'] . ')';
                setting()->set([
                    self::ACCOUNTANT_LAST_MTID_KEY => $this->data['MTID']
                ])
                    ->save();
            }
        } catch (\Exception|\Throwable $e) {
            Log::error(Logging::ACCOUNTANT_PRODUCT_SYNC_JOB_FAIL, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
