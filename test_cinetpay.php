<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

echo "=== Test de configuration CinetPay ===\n\n";

// Test 1: Vérifier les clés de configuration
echo "1. Vérification des clés de configuration:\n";
$apiKey = get_option('cinetpay_key');
$siteId = get_option('cinetpay_secret');

echo "   - API Key: " . ($apiKey ? "Configurée (" . substr($apiKey, 0, 10) . "...)" : "NON CONFIGURÉE") . "\n";
echo "   - Site ID: " . ($siteId ? "Configurée (" . substr($siteId, 0, 10) . "...)" : "NON CONFIGURÉE") . "\n\n";

if (empty($apiKey) || empty($siteId)) {
    echo "❌ ERREUR: Les clés CinetPay ne sont pas configurées!\n";
    echo "   Veuillez configurer 'cinetpay_key' et 'cinetpay_secret' dans votre panneau d'administration.\n\n";
    exit;
}

// Test 2: Test de connexion à l'API CinetPay
echo "2. Test de connexion à l'API CinetPay:\n";

$testPayload = [
    'amount' => 1000, // 1000 XOF
    'currency' => 'XOF',
    'transaction_id' => 'test_' . time(),
    'customer_name' => 'Test User',
    'customer_email' => 'test@example.com',
    'description' => 'Test de connexion API',
    'return_url' => 'http://localhost/success',
    'cancel_url' => 'http://localhost/cancel',
    'site_id' => $siteId,
    'apikey' => $apiKey,
];

try {
    $response = Http::timeout(30)->post('https://api-checkout.cinetpay.com/v2/payment', $testPayload);

    echo "   - Status HTTP: " . $response->status() . "\n";
    echo "   - Response:\n";

    $responseData = $response->json();
    if ($responseData) {
        echo "     " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        if ($response->status() == 201 && isset($responseData['data']['payment_url'])) {
            echo "✅ SUCCESS: API CinetPay fonctionne correctement!\n";
            echo "   URL de paiement générée: " . $responseData['data']['payment_url'] . "\n";
        } elseif ($response->status() == 200 && isset($responseData['data']['payment_url'])) {
            echo "✅ SUCCESS: API CinetPay fonctionne correctement! (Status 200)\n";
            echo "   URL de paiement générée: " . $responseData['data']['payment_url'] . "\n";
        } else {
            echo "❌ ERREUR: Réponse inattendue de l'API CinetPay\n";
            if (isset($responseData['message'])) {
                echo "   Message: " . $responseData['message'] . "\n";
            }
            if (isset($responseData['description'])) {
                echo "   Description: " . $responseData['description'] . "\n";
            }
        }
    } else {
        echo "❌ ERREUR: Réponse vide de l'API CinetPay\n";
        echo "   Body: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: Exception lors de l'appel API\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Fin du test ===\n";
