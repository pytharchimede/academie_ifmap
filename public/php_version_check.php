<?php
echo "<h1>Vérification Version PHP</h1>";
echo "<h2>Version PHP Actuelle</h2>";
echo "Version: <strong>" . PHP_VERSION . "</strong><br>";
echo "Version majeure.mineure: <strong>" . PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION . "</strong><br>";
echo "SAPI: " . php_sapi_name() . "<br>";
echo "Date: " . date('Y-m-d H:i:s') . "<br>";

echo "<h2>Statut pour Laravel</h2>";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "✅ <strong>PHP " . PHP_VERSION . " est compatible Laravel 9+</strong><br>";
    echo "✅ Vous pouvez utiliser toutes les fonctionnalités modernes<br>";
} elseif (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo "⚠️ <strong>PHP " . PHP_VERSION . " est l'ancien minimum</strong><br>";
    echo "❌ Laravel moderne nécessite PHP 8.1+<br>";
    echo "🔧 <strong>Vous devez passer à PHP 8.1 ou 8.2</strong><br>";
} else {
    echo "❌ <strong>PHP " . PHP_VERSION . " est trop ancien</strong><br>";
    echo "🚨 <strong>Mise à jour critique nécessaire !</strong><br>";
}

echo "<h2>Actions à faire dans cPanel</h2>";
echo "1. 🔧 Allez dans <strong>MultiPHP Manager</strong><br>";
echo "2. 📋 Sélectionnez votre domaine <strong>academie.ifmap.ci</strong><br>";
echo "3. 🔄 Changez vers <strong>PHP 8.1</strong> ou <strong>PHP 8.2</strong><br>";
echo "4. ✅ Cliquez sur <strong>Appliquer</strong><br>";
echo "5. ⏳ Attendez 2-3 minutes puis rechargez cette page<br>";

echo "<h2>Vérification MultiPHP</h2>";
echo "📍 Si cPanel affiche PHP 8.2 mais ce script montre PHP 8.0.30 :<br>";
echo "- Le changement n'est pas encore effectif<br>";
echo "- Contactez votre hébergeur<br>";
echo "- Ou attendez quelques minutes et rechargez<br>";

echo "<br><a href='?refresh=1'>🔄 Recharger la page</a>";
echo " | <a href='../'>🏠 Retour au site</a>";
