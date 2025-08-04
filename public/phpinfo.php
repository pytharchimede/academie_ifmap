<?php
// Script de diagnostic PHP
echo "<h1>Diagnostic PHP pour Laravel</h1>";

echo "<h2>Version PHP</h2>";
echo "Version: " . PHP_VERSION . "<br>";

echo "<h2>Extensions PDO</h2>";
if (extension_loaded('pdo')) {
    echo "✅ PDO est installé<br>";
    echo "Drivers PDO disponibles: " . implode(', ', PDO::getAvailableDrivers()) . "<br>";
} else {
    echo "❌ PDO n'est pas installé<br>";
}

echo "<h2>Extensions MySQL</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "✅ pdo_mysql est installé<br>";
} else {
    echo "❌ pdo_mysql n'est PAS installé - C'est le problème !<br>";
}

if (extension_loaded('mysqli')) {
    echo "✅ mysqli est installé<br>";
} else {
    echo "❌ mysqli n'est pas installé<br>";
}

if (extension_loaded('mysql')) {
    echo "✅ mysql (déprécié) est installé<br>";
} else {
    echo "ℹ️ mysql (déprécié) n'est pas installé - Normal<br>";
}

echo "<h2>Autres extensions importantes pour Laravel</h2>";
$requiredExtensions = [
    'openssl',
    'tokenizer',
    'mbstring',
    'xml',
    'ctype',
    'json',
    'bcmath',
    'curl',
    'fileinfo',
    'gd'
];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ {$ext} est installé<br>";
    } else {
        echo "❌ {$ext} n'est PAS installé<br>";
    }
}

echo "<h2>Test de connexion MySQL (si pdo_mysql disponible)</h2>";
if (extension_loaded('pdo_mysql')) {
    $databases_to_test = [
        'ifmapci_academie_db',  // Nom du .env local
        'ifmapci_academie',     // Nom du log d'erreur
        'academie_ifmap',       // Variante possible
        'ifmapci_lmszai'        // Autre variante possible
    ];

    $host = '127.0.0.1';
    $username = 'ifmapci_ulrich';
    $password = '@Succes2019';

    foreach ($databases_to_test as $dbname) {
        try {
            $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
            echo "✅ Connexion MySQL réussie avec la base: <strong>{$dbname}</strong> !<br>";

            // Test d'une requête simple
            $stmt = $pdo->query("SHOW TABLES LIMIT 5");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "📋 Quelques tables trouvées: " . implode(', ', $tables) . "<br>";
            break;
        } catch (PDOException $e) {
            echo "❌ Erreur avec la base '{$dbname}': " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "❌ Impossible de tester - pdo_mysql non disponible<br>";
}
echo "<h2>Informations complètes PHP</h2>";
echo '<a href="?full=1">Voir phpinfo() complet</a><br>';

if (isset($_GET['full'])) {
    phpinfo();
}
