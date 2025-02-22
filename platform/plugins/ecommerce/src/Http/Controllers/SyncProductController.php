<?php

namespace Botble\Ecommerce\Http\Controllers;

use App\Constants\ClientResponse;
use App\Constants\Logging;

use App\Infrastructure\Services\AsAccountantService;
use App\Jobs\AsAccountantProductSyncJob;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Syncers\Syncer;
use Botble\Ecommerce\Syncers\Controllers\SyncController;
use Botble\Ecommerce\Traits\ProductActionsTrait;
use Illuminate\Http\Request;
use Botble\Ecommerce\Syncers\ProductSyncer;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncProductController extends SyncController
{
    use ProductActionsTrait;
    public AsAccountantService $AsAccountantService;

    public function __construct()
    {
        $this->AsAccountantService = new AsAccountantService();
    }
    protected function getSyncer(): Syncer
    {
        return ProductSyncer::make();
    }

    public function index()
    {
        $this->pageTitle($this->getSyncer()->getHeading());

        return $this->getSyncer()->render();
    }

    public function store(Request $request, AsAccountantService $asAccountantService)
    {
        $messageKey = 'core/base::notices.sync_failed_message';

        try {
            $response = $this->AsAccountantService->getProducts();
            if ($response['status'] == ClientResponse::STATUS['success']) {
                foreach ($response['data'] as $row) {
                    AsAccountantProductSyncJob::dispatch($row)->onQueue('high');
                }
                $messageKey = 'core/base::notices.sync_success_message';
            }
        } catch (\Exception|\Throwable $e) {
            Log::error(Logging::ACCOUNTANT_PRODUCT_SYNC_COMMAND_FAIL, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('products.index'))
            ->setNextUrl(route('products.index'))
            ->setMessage(
                trans($messageKey)
            );
            //->withSyncSuccessMessage();
    }
}
