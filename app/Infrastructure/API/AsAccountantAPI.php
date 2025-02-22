<?php

namespace App\Infrastructure\API;

use App\Constants\AsAccountant;
use App\Constants\Logging;
use App\Constants\ClientResponse;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

trait AsAccountantAPI
{
    /**
     * @param array $options
     * @return array
     */
    protected function getProductsAPI(array $options = []): array
    {
        $data = [];
        $status = ClientResponse::STATUS['error'];
        try {
            $config = config('accountant.getGoods');
            $headers = array_merge($config['headers'], [
                'Authorization' => $this->getAuthorizationToken()
            ]);
            $body = [
                'settings' => [
                    'ShowProgress' => false,
                    'ShowColumns' => false
                ],
                'parameters' => [
                    'MTID' => '',
                    'MTCode' => '',
                    'Group' => '',
                    'ItemType' => '',
                    'Show' => '',
                    'PriceType' => '',
                    'ShowExtended' => true
                ]
            ];

            $request = client()->{$config['method']}($config['url']) // ['verify' => false]
            ->setHeaders($headers)
                ->setBody($body)
                ->setLogMessage(Logging::ACCOUNTANT_API_GET_PRODUCTS_REQUEST)
                ->send();
            if ($request->getStatusCode() === Response::HTTP_OK) {
                $response = $request->getBody();
                if (!empty($response['rows'])) {
                    $status = ClientResponse::STATUS['success'];
                    foreach ($response['rows'] as $row) {
                        $mtid = $row['MTID'];
                        $data[$mtid] = $row;

                    }
                }
            }
        } catch (\Exception $e) {
            Log::error(Logging::ACCOUNTANT_API_GET_PRODUCTS_REQUEST_FAIL, [
                'msg' => $e->getMessage(),
                'tress' => $e->getTraceAsString()
            ]);
        }

        return [
            'status' => $status,
            'data' => $data
        ];
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getProductsByStorageAPI(array $options = []): array
    {
        $data = [];
        $status = ClientResponse::STATUS['error'];
        try {
            $config = config('accountant.getGoodsRemsDetailedByStorage');
            $headers = array_merge($config['headers'], [
                'Authorization' => $this->getAuthorizationToken()
            ]);
            $body = [
                'settings' => [
                    'ShowProgress' => false,
                    'ShowColumns' => false
                ],
                'parameters' => [
                    'RemDate' => Carbon::now()->toDateString(),
                    'Storage' => '',
                    'MTGroup' => '',
                    'MTCode' => '',
                    'PriceListTypes' => '02',
                    'ShowAltUnits' => true,
                    'ShowZeroRows' => true,
                ]
            ];

            $request = client()->{$config['method']}($config['url']) // ['verify' => false]
            ->setHeaders($headers)
                ->setBody($body)
                ->setLogMessage(Logging::ACCOUNTANT_API_GET_PRODUCTS_BY_STORAGE_REQUEST)
                ->send();
            if ($request->getStatusCode() === Response::HTTP_OK) {
                $response = $request->getBody();
                if (!empty($response['rows'])) {
                    $status = ClientResponse::STATUS['success'];
                    $rows = [];
                    $qtys = [];
                    foreach ($response['rows'] as $row) {
                        $value = $row;
                        $mtid = $row['MTID'];
                        $qty = $row['Qty'];
                        // If the MTID already exists, add the Qty; otherwise, initialize it
                        if (isset($qtys[$mtid])) {
                            $qtys[$mtid] += $qty;
                        } else {
                            $qtys[$mtid] = $qty;
                        }
                        $value['Qty'] = $qtys[$mtid];

                        if (AsAccountant::STORAGES['tradeHall'] == $row['Storage']) {
                            $value['CostSumm'] = $row['CostSumm'];
                            $value['CostSummVAT'] = $row['CostSummVAT'];
                            $value['CurrCostSumm'] = $row['CurrCostSumm'];
                            $value['CurrCost'] = $row['CurrCost'];
                            $value['Price'] = $row['Price'];
                            $value['Summ'] = $row['Summ'];
                            $value['Col0'] = $row['Col0'];
                        }

                        $rows[$mtid] = $value;
                    }

                    $data = $rows;
                }
            }
        } catch (\Exception $e) {
            Log::error(Logging::ACCOUNTANT_API_GET_PRODUCTS_BY_STORAGE_REQUEST_FAIL, [
                'msg' => $e->getMessage(),
                'tress' => $e->getTraceAsString()
            ]);
        }

        return [
            'status' => $status,
            'data' => $data
        ];
    }

    /**
     * @return string
     */
    private function getAuthorizationToken(): string
    {
        return 'Bearer ' . Setting::get('accountant_access_token');
    }

    /**
     * @return array
     */
    protected function refreshAccessTokenAPI(): array
    {
        $data = [];
        $status = ClientResponse::STATUS['error'];
        try {
            $config = config('accountant.refreshAccessToken');
            $body = [
                'ClientId' => config('accountant.ClientId'),
                'Secret' => config('accountant.Secret'),
                'DBId' => config('accountant.DBId')
            ];

            $request = client()->{$config['method']}($config['url']) // ['verify' => false]
            ->setHeaders($config['headers'])
                ->setBody($body)
                ->setLogMessage(Logging::ACCOUNTANT_API_REFRESH_ACCESS_TOKEN_REQUEST)
                ->send();

            if ($request->getStatusCode() === Response::HTTP_OK) {
                $data = $request->getBody();
                if (!empty($data['accessToken'])) {
                    $status = ClientResponse::STATUS['success'];
                }
            }
        } catch (\Exception $e) {
            Log::error(Logging::ACCOUNTANT_API_REFRESH_ACCESS_TOKEN_REQUEST_FAIL, [
                'msg' => $e->getMessage(),
                'tress' => $e->getTraceAsString()
            ]);
        }

        return [
            'status' => $status,
            'data' => $data
        ];
    }
}
