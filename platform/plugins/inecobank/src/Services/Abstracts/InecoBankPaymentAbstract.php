<?php

namespace Botble\InecoBank\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\InecoBank\Supports\InecoBankHelper;
use Exception;
use Illuminate\Http\Request;

abstract class InecoBankPaymentAbstract
{
    use PaymentErrorTrait;

    protected ?string $token = null;

    protected float $amount;

    protected string $currency;

    protected string $description;
    protected string $returnUrl;

    protected string $cancelUrl;

    protected string $customer;

    public function __construct()
    {
        $this->amount = 0;
        $this->currency = INECOBANK_PAYMENT_DEFAULT_CURRENCY;
        $this->setClient();
    }

    /**
     * @param array $data
     * @return string|null
     */
    abstract public function makePayment(array $data): ?string;

    /**
     * @param string $chargeId
     * @param array $data
     * @return mixed
     */
    abstract public function afterMakePayment(string $chargeId, array $data);

    /**
     * @param array $data
     * @return string|null
     */
    public function execute(array $data): ?string
    {
        try {
            return $this->makePayment($data);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }
    }

    /**
     * @param string $transactionDescription
     * @return string|bool|null
     */
    public function createPayment(): string|null|bool
    {

    }

    public function getPaymentDetails(string $chargeId): ?Charge
    {
        if (!$this->setClient()) {
            return null;
        }

        try {
            // TODO HT need implement getDetails API
            return Charge::retrieve($chargeId);
        } catch (Exception) {
            return null;
        }
    }

    public function refundOrder(string $paymentId, float|string $totalAmount, array $options = []): array
    {
        if (!$this->setClient()) {
            return [
                'error' => true,
                'message' => trans('plugins/payment::payment.invalid_settings', ['name' => 'InecoBank']),
            ];
        }

        $multiplier = InecoBankHelper::getInecoBankCurrencyMultiplier($this->currency);

        if ($multiplier > 1) {
            $totalAmount = (int)(round((float)$totalAmount, 2) * $multiplier);
        }

        try {
            $response = Refund::create([
                'charge' => $paymentId,
                'amount' => $totalAmount,
                'metadata' => $options,
            ]);

            if ($response->status == 'succeeded') {
                return [
                    'error' => false,
                    'message' => $response->status,
                    'data' => $response->toArray(),
                ];
            }

            return [
                'error' => true,
                'message' => trans('plugins/payment::payment.status_is_not_completed'),
            ];
        } catch (Exception $exception) {
            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function setClient(): self
    {
        $this->client = $this->environment();

        return $this;
//        $secret = get_payment_setting('secret', 'inecobank');
//        $clientId = get_payment_setting('client_id', 'inecobank');
//
//        if (!$secret || !$clientId) {
//            return false;
//        }
//
//        InecoBank::setApiKey($secret);
//        InecoBank::setClientId($clientId);
//
//        return true;
    }

    public function setCurrency($currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCancelUrl(string $url): self
    {
        $this->cancelUrl = $url;

        return $this;
    }

    public function setReturnUrl(string $url): self
    {
        $this->returnUrl = $url;

        return $this;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setItem(array $itemData): self
    {
        /*if (count($itemData) === count($itemData, COUNT_RECURSIVE)) {
            $itemData = [$itemData];
        }

        foreach ($itemData as $data) {
            $amount = $data['price'] * $data['quantity'];

            $item = [
                'name' => $data['name'],
                'sku' => $data['sku'],
                'unit_amount' => [
                    'currency_code' => $this->paymentCurrency,
                    'value' => $amount,
                ],
                'quantity' => $data['quantity'],
            ];

            if ($description = Arr::get($data, 'description')) {
                $item['description'] = $description;
            }

            if ($tax = Arr::get($data, 'tax')) {
                $item['tax'] = [
                    'currency_code' => $this->paymentCurrency,
                    'value' => $tax,
                ];
            }

            if ($category = Arr::get($data, 'category')) {
                $item['category'] = $category;
            }

            $this->itemList[] = $item;
            $this->totalAmount += $amount;
        }

        // issue https://developer.paypal.com/docs/api/orders/v2/#error-DECIMAL_PRECISION
        $this->totalAmount = round((float)$this->totalAmount, $this->isSupportedDecimals() ? 2 : 0);*/

        return $this;
    }

    /**
     * @return array
     */
    protected function buildRequestBody(): array
    {
        return [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl ?: $this->returnUrl,
                'brand_name' => theme_option('site_title'),
            ],
            'purchase_units' => [
                0 => [
                    'description' => $this->transactionDescription,
                    'custom_id' => $this->customer,
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => (string)$this->amount,
                    ],
                ],
            ],
        ];
    }

    public function isSupportedDecimals(): bool
    {
        return !in_array($this->getCurrency(), [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ]);
    }

    public function getPaymentStatus(Request $request)
    {
        if (empty($request->input('PayerID')) || empty($request->input('token'))) {
            return false;
        }

        $paymentId = session('inecobank_payment_id');

        try {
            $orderRequest = [];

            do_action('payment_before_making_api_request', INECOBANK_PAYMENT_METHOD_NAME, $orderRequest);

            $response = $this->client->execute($orderRequest);

            do_action('payment_after_api_response', INECOBANK_PAYMENT_METHOD_NAME, (array)$orderRequest, (array)$response);

            // @phpstan-ignore-next-line
            if ($response && $response->statusCode == 201 && $response->result->status == 'COMPLETED') {
                // @phpstan-ignore-next-line
                return $response->result->status;
            }
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);
        }

        return false;
    }

    public function environment()
    {
        $password = InecoBankHelper::getPaymentSetting('password', INECOBANK_PAYMENT_METHOD_NAME);
        $userName = InecoBankHelper::getPaymentSetting('user_name', INECOBANK_PAYMENT_METHOD_NAME);
        $paymentMode = InecoBankHelper::getPaymentSetting('payment_mode', INECOBANK_PAYMENT_METHOD_NAME);

        if ($paymentMode) {
            return 'https://ipay.arca.am/payment/rest';
        }

        return 'https://ipaytest.arca.am:8445/payment/rest';
    }

    public function supportedCurrencyCodes(): array
    {
        return [
            'AMD',
            'EUR',
            'USD'
        ];
    }
}
