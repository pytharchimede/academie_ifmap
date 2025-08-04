<?php
// Script de diagnostic Laravel et PHP
echo "<h1>Diagnostic Laravel et PHP</h1>";

echo "<h2>Version PHP actuelle</h2>";
echo "Version PHP du script: " . PHP_VERSION . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";

echo "<h2>Version Laravel</h2>";
// Essayons de lire le fichier composer.json pour voir la version Laravel
$composerPath = __DIR__ . '/../composer.json';
if (file_exists($composerPath)) {
    $composer = json_decode(file_get_contents($composerPath), true);
    if (isset($composer['require']['laravel/framework'])) {
        echo "Laravel framework version: " . $composer['require']['laravel/framework'] . "<br>";
    }
    if (isset($composer['require']['php'])) {
        echo "PHP requirement: " . $composer['require']['php'] . "<br>";
    }
} else {
    echo "❌ Fichier composer.json non trouvé<br>";
}

echo "<h2>Configuration PHP recommandée</h2>";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "✅ Votre PHP " . PHP_VERSION . " est excellent pour Laravel 9+<br>";
} elseif (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo "⚠️ Votre PHP " . PHP_VERSION . " fonctionne mais PHP 8.1+ est recommandé<br>";
} else {
    echo "❌ Votre PHP " . PHP_VERSION . " est trop ancien pour Laravel moderne<br>";
}

echo "<h2>Extensions critiques</h2>";
$criticalExtensions = ['pdo', 'pdo_mysql', 'openssl', 'tokenizer', 'mbstring'];
foreach ($criticalExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ {$ext}<br>";
    } else {
        echo "❌ {$ext} - MANQUANT !<br>";
    }
}

echo "<h2>Test de résolution</h2>";
echo "<strong>Recommandation:</strong><br>";
if (!extension_loaded('pdo_mysql')) {
    echo "🚨 <strong>Activez l'extension pdo_mysql dans cPanel</strong><br>";
    echo "📍 Allez dans cPanel → MultiPHP Extensions → Cochez pdo_mysql<br>";
} else {
    echo "✅ pdo_mysql est disponible - Le problème peut être ailleurs<br>";
}

echo "<h2>Actions à faire</h2>";
echo "1. 🔧 Dans cPanel, assurez-vous que votre domaine utilise <strong>PHP 8.1 ou 8.2</strong><br>";
echo "2. 📦 Dans MultiPHP Extensions, activez <strong>pdo_mysql</strong><br>";
echo "3. 🔄 Rechargez votre site après les changements<br>";
