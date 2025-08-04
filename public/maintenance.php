<?php
// Script de maintenance Laravel
echo "<h1>Maintenance Laravel</h1>";

// Change to the Laravel directory
chdir(__DIR__ . '/..');

echo "<h2>Vidage des caches</h2>";

// Clear Laravel caches
$commands = [
    'php artisan config:clear' => 'Configuration cache',
    'php artisan route:clear' => 'Routes cache',
    'php artisan view:clear' => 'Views cache',
    'php artisan cache:clear' => 'Application cache'
];

foreach ($commands as $command => $description) {
    echo "🔄 {$description}...<br>";
    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);

    if ($return_var === 0) {
        echo "✅ {$description} vidé avec succès<br>";
    } else {
        echo "❌ Erreur lors du vidage de {$description}: " . implode('<br>', $output) . "<br>";
    }
    echo "<br>";
}

echo "<h2>Test de configuration</h2>";
echo "🔧 <a href='../'>Retourner au site</a><br>";
echo "🗑️ <a href='?delete_me=1'>Supprimer ce script</a><br>";

if (isset($_GET['delete_me'])) {
    if (unlink(__FILE__)) {
        echo "✅ Script supprimé avec succès !<br>";
        echo "<script>setTimeout(function(){ window.location.href = '../'; }, 2000);</script>";
    }
}
