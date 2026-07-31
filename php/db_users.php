<?php

$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die("Errore: Il file di configurazione .env non è stato trovato!");
}

$env = parse_ini_file($envPath);

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '5432';
$db   = $env['DB_NAME'] ?? 'Ricette';
$user = $env['DB_USER'] ?? 'marcio';
$pass = $env['DB_PASS'] ?? '';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Errore di connessione al database.' // Non mostriamo $e->getMessage() in produzione per sicurezza!
    ]);
    exit;
}
?>


<?php

function getDB() {
    static $pdo = null;

    if ($pdo === null) {

        $envPath = __DIR__ . '/../.env';

        if (!file_exists($envPath)) {
            throw new Exception("Errore: Il file di configurazione .env non è stato trovato!");
        }

        $env = parse_ini_file($envPath);

        $host = $env['DB_HOST'] ?? '127.0.0.1';
        $port = $env['DB_PORT'] ?? '5432';
        $db   = $env['DB_NAME'] ?? 'Ricette';
        $user = $env['DB_USER'] ?? 'marcio';
        $pass = $env['DB_PASS'] ?? '';

        $dsn = "pgsql:host=$host;port=$port;dbname=$db";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            //Crea la connessione PDO
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            
            throw new Exception("Errore di connessione al database: " . $e->getMessage());
        }
    }

    return $pdo;
}

?>