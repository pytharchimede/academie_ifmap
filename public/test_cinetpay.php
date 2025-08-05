<?php
// Test CinetPay détaillé
echo "<h1>Test CinetPay Détaillé</h1>";

// Charger l'autoloader Laravel si possible
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoloader Laravel chargé<br>";
} else {
    echo "❌ Autoloader Laravel non trouvé<br>";
}

echo "<h2>1. Configuration PHP</h2>";
echo "Version PHP: " . PHP_VERSION . "<br>";
echo "cURL disponible: " . (extension_loaded('curl') ? '✅ Oui' : '❌ Non') . "<br>";
echo "JSON disponible: " . (extension_loaded('json') ? '✅ Oui' : '❌ Non') . "<br>";
echo "OpenSSL disponible: " . (extension_loaded('openssl') ? '✅ Oui' : '❌ Non') . "<br>";

echo "<h2>2. Test de connectivité CinetPay</h2>";

// Test de connectivité à l'API CinetPay
$cinetpayUrl = 'https://api-checkout.cinetpay.com/v2/payment';
echo "URL API CinetPay: {$cinetpayUrl}<br>";

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cinetpayUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour test seulement

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ Erreur cURL: {$error}<br>";
    } else {
        echo "✅ Connectivité API: HTTP {$httpCode}<br>";
        if ($httpCode == 405 || $httpCode == 200) {
            echo "✅ API CinetPay accessible (HTTP {$httpCode} est normal pour HEAD request)<br>";
        }
    }
} else {
    echo "❌ cURL non disponible<br>";
}

echo "<h2>3. Test de configuration Laravel (si disponible)</h2>";

// Essayer de charger les configurations Laravel
if (function_exists('env') || file_exists(__DIR__ . '/../.env')) {
    echo "✅ Fichier .env détecté<br>";

    // Simulation de lecture du .env
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $cinetpayKeyFound = strpos($envContent, 'CINETPAY_API_KEY') !== false;
        $cinetpaySiteFound = strpos($envContent, 'CINETPAY_SITE_ID') !== false;

        echo "Configuration CinetPay dans .env:<br>";
        echo "- CINETPAY_API_KEY: " . ($cinetpayKeyFound ? '✅ Présent' : '❌ Manquant') . "<br>";
        echo "- CINETPAY_SITE_ID: " . ($cinetpaySiteFound ? '✅ Présent' : '❌ Manquant') . "<br>";
    }
} else {
    echo "⚠️ Configuration Laravel non accessible depuis ce script<br>";
}

echo "<h2>4. Test CinetPay avec données factices</h2>";

// Test avec des données factices pour voir le format de réponse
function testCinetPayAPI()
{
    $url = 'https://api-checkout.cinetpay.com/v2/payment';

    // Données de test (qui vont échouer mais montrer le format)
    $data = [
        'apikey' => 'test_key_12345',
        'site_id' => 'test_site_12345',
        'transaction_id' => 'test_' . time(),
        'amount' => 1000,
        'currency' => 'XOF',
        'alternative_currency' => 'XOF',
        'description' => 'Test transaction',
        'customer_name' => 'Test User',
        'customer_email' => 'test@example.com',
        'return_url' => 'https://academie.ifmap.ci/payment/success',
        'notify_url' => 'https://academie.ifmap.ci/payment/notify',
        'metadata' => 'test_metadata'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "Test API avec données factices:<br>";
    echo "HTTP Code: {$httpCode}<br>";

    if ($error) {
        echo "❌ Erreur cURL: {$error}<br>";
        return false;
    }

    echo "Réponse brute: <pre>" . htmlspecialchars($response) . "</pre>";

    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        echo "Réponse JSON décodée: <pre>" . print_r($jsonResponse, true) . "</pre>";

        if (isset($jsonResponse['code'])) {
            echo "Code réponse CinetPay: " . $jsonResponse['code'] . "<br>";
        }
        if (isset($jsonResponse['message'])) {
            echo "Message CinetPay: " . $jsonResponse['message'] . "<br>";
        }
    } else {
        echo "❌ Réponse non-JSON ou invalide<br>";
    }

    return true;
}

if (function_exists('curl_init')) {
    testCinetPayAPI();
} else {
    echo "❌ Impossible de tester - cURL non disponible<br>";
}

echo "<h2>5. Diagnostic complet</h2>";
echo "Si vous voyez 'UNKNOWN ERROR' dans votre application:<br>";
echo "1. ✅ Vérifiez que les clés CinetPay dans l'admin Laravel sont correctes<br>";
echo "2. ✅ Assurez-vous que le montant est > 0<br>";
echo "3. ✅ Vérifiez que la devise est 'XOF'<br>";
echo "4. ✅ Vérifiez que les URLs de retour sont accessibles<br>";
echo "5. ✅ Consultez les logs Laravel dans storage/logs/<br>";

echo "<br><a href='../'>🏠 Retour au site</a>";
echo " | <a href='?delete=1'>🗑️ Supprimer ce script</a>";

if (isset($_GET['delete'])) {
    if (unlink(__FILE__)) {
        echo "<br>✅ Script supprimé !";
        echo "<script>setTimeout(function(){ window.location.href = '../'; }, 2000);</script>";
    }
}
