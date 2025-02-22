<?php

namespace App\Http\Controllers;

use App\Constants\ClientResponse;
use App\Infrastructure\Services\AsAccountantService;
use App\Jobs\AsAccountantProductSyncJob;

class AsAccountantController extends Controller
{
    /**
     * @param AsAccountantService $asAccountantService
     * @return void
     */
    public function syncProducts(AsAccountantService $asAccountantService): void
    {
        $response = $asAccountantService->getProducts();
        if ($response['status'] == ClientResponse::STATUS['success']) {
            foreach ($response['data'] as $key =>  $row) {
                if($key == '001111') {
                    dd($row);
                }
                // AsAccountantProductSyncJob::dispatch($row)->onQueue('high');
            }
        }
    }
}
