<?php

namespace App\Http\Services\Payment;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CinetPayService
{
    private $apiUrl;
    private $apiKey;
    private $siteId;
    private $currency;
    private $successUrl;
    private $cancelUrl;

    public function __construct($object)
    {
        // Fetch settings dynamically from configuration (get_option or similar)
        $this->apiUrl = 'https://api-checkout.cinetpay.com/v2/payment';
        $this->apiKey = get_option('cinetpay_key');  // CinetPay's API Key
        $this->siteId = get_option('cinetpay_secret');  // CinetPay's Site ID
        $this->currency = isset($object['currency']) ? $object['currency'] : 'XOF';  // Set default currency as XOF (CFA Franc)

        // Set successUrl and cancelUrl from the object, or use default routes
        if (isset($object['id'])) {
            $this->successUrl = isset($object['successUrl']) ? $object['successUrl'] : route('paymentNotify', $object['id']);
            $this->cancelUrl = isset($object['cancelUrl']) ? $object['cancelUrl'] : route('paymentCancel', $object['id']);
        }
    }

    public function makePayment($amount)
    {
        $transaction_id = 'txn_' . time() . '_' . uniqid();  // Generate a more unique transaction ID

        // Validate required configuration
        if (empty($this->apiKey) || empty($this->siteId)) {
            Log::error('CinetPay Configuration Error: API Key or Site ID is missing', [
                'api_key_present' => !empty($this->apiKey),
                'site_id_present' => !empty($this->siteId),
            ]);
            return [
                'success' => false,
                'redirect_url' => '',
                'payment_id' => '',
                'message' => 'CinetPay configuration is incomplete. Please check API credentials.',
            ];
        }

        // Prepare the payload according to CinetPay's API requirements
        $payload = [
            'amount' => (int)($amount),  // CinetPay expects amount in major units for XOF
            'currency' => $this->currency,  // Currency for the payment
            'transaction_id' => $transaction_id,  // Unique transaction ID
            'customer_name' => Auth::user()->name ?? 'Guest',  // Customer's name
            'customer_email' => Auth::user()->email ?? 'guest@example.com',  // Customer's email
            'description' => 'Purchase from ' . get_option('app_name', 'LMSzai'),  // Description of the payment
            'return_url' => $this->successUrl,  // URL to redirect after successful payment
            'cancel_url' => $this->cancelUrl,  // URL to redirect if payment is canceled
            'site_id' => $this->siteId,  // CinetPay's site ID
            'apikey' => $this->apiKey,  // CinetPay's API key
        ];

        // Log the payload for debugging (without sensitive info)
        Log::info('CinetPay makePayment Request:', [
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'transaction_id' => $payload['transaction_id'],
            'api_url' => $this->apiUrl,
            'customer_email' => $payload['customer_email'],
        ]);

        $data = [
            'success' => false,
            'redirect_url' => '',
            'payment_id' => '',
            'message' => __('Something went wrong'),
        ];

        try {
            // Make the request to CinetPay API
            $response = Http::timeout(30)->post($this->apiUrl, $payload);

            // Log the response for debugging
            Log::info('CinetPay makePayment Response:', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'response' => $response->json()
            ]);

            $responseData = $response->json();

            if ($response->status() == 201 && isset($responseData['data']['payment_url'])) {
                $data['redirect_url'] = $responseData['data']['payment_url'];
                $data['payment_id'] = $transaction_id;
                $data['success'] = true;
                $data['message'] = 'Payment initiated successfully';
            } elseif ($response->status() == 200 && isset($responseData['data']['payment_url'])) {
                // Some APIs return 200 instead of 201
                $data['redirect_url'] = $responseData['data']['payment_url'];
                $data['payment_id'] = $transaction_id;
                $data['success'] = true;
                $data['message'] = 'Payment initiated successfully';
            } else {
                $errorMessage = 'Payment initialization failed';
                if (isset($responseData['message'])) {
                    $errorMessage = $responseData['message'];
                } elseif (isset($responseData['description'])) {
                    $errorMessage = $responseData['description'];
                } elseif (isset($responseData['error'])) {
                    $errorMessage = is_array($responseData['error']) ? json_encode($responseData['error']) : $responseData['error'];
                }

                $data['message'] = $errorMessage;
                Log::error('CinetPay Payment Error:', [
                    'status' => $response->status(),
                    'response' => $responseData,
                    'payload' => array_merge($payload, ['apikey' => '[HIDDEN]']) // Hide API key in logs
                ]);
            }

            return $data;
        } catch (\Exception $ex) {
            Log::error('CinetPay Exception: ' . $ex->getMessage(), [
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTraceAsString()
            ]);
            $data['message'] = 'Network error: ' . $ex->getMessage();
            return $data;
        }
    }

    public function paymentConfirmation($payment_id)
    {
        $data = [
            'success' => false,
            'data' => null,
        ];

        // Validate required configuration
        if (empty($this->apiKey) || empty($this->siteId)) {
            Log::error('CinetPay Configuration Error in paymentConfirmation: API Key or Site ID is missing');
            $data['data'] = [
                'payment_status' => 'failed',
                'payment_method' => 'cinetpay',
                'error' => 'Configuration error'
            ];
            return $data;
        }

        // Prepare the payload for verifying the payment
        $payload = [
            'transaction_id' => $payment_id,
            'site_id' => $this->siteId,
            'apikey' => $this->apiKey,
        ];

        $url = "https://api-checkout.cinetpay.com/v2/payment/check";

        Log::info('CinetPay Payment Confirmation Request:', [
            'transaction_id' => $payment_id,
            'url' => $url
        ]);

        try {
            // Make the request to CinetPay to verify the payment status
            $response = Http::timeout(30)->post($url, $payload);

            Log::info('CinetPay Payment Confirmation Response:', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            // Check if the API response is successful
            if ($response->successful()) {
                $responseData = $response->json();

                // Log the detailed response for debugging
                Log::info('CinetPay Confirmation Details:', [
                    'code' => $responseData['code'] ?? 'no_code',
                    'status' => $responseData['data']['status'] ?? 'no_status',
                    'full_response' => $responseData
                ]);

                if (
                    isset($responseData['code']) && $responseData['code'] == '00' &&
                    isset($responseData['data']['status']) && $responseData['data']['status'] == 'ACCEPTED'
                ) {
                    // Payment was successful
                    $data['success'] = true;
                    $data['data'] = [
                        'amount' => isset($responseData['data']['amount']) ? ($responseData['data']['amount'] / 100) : 0,
                        'currency' => $responseData['data']['currency'] ?? $this->currency,
                        'payment_status' => 'success',
                        'payment_method' => 'cinetpay',
                        'transaction_id' => $payment_id,
                    ];
                } else {
                    $data['success'] = false;
                    $data['data'] = [
                        'payment_status' => 'failed',
                        'payment_method' => 'cinetpay',
                        'error' => $responseData['message'] ?? 'Payment not accepted',
                        'code' => $responseData['code'] ?? 'unknown',
                        'transaction_id' => $payment_id,
                    ];
                }
            } else {
                Log::error('CinetPay Confirmation HTTP Error:', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                $data['success'] = false;
                $data['data'] = [
                    'payment_status' => 'failed',
                    'payment_method' => 'cinetpay',
                    'error' => 'HTTP Error: ' . $response->status(),
                    'transaction_id' => $payment_id,
                ];
            }
        } catch (\Exception $ex) {
            Log::error('CinetPay Payment Confirmation Exception: ' . $ex->getMessage(), [
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'transaction_id' => $payment_id
            ]);

            $data['success'] = false;
            $data['data'] = [
                'payment_status' => 'failed',
                'payment_method' => 'cinetpay',
                'error' => 'Exception: ' . $ex->getMessage(),
                'transaction_id' => $payment_id,
            ];
        }

        return $data;
    }
}
