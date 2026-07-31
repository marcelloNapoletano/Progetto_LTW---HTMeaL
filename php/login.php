<?php
session_start();
header('Content-Type: application/json'); //JSON
require_once __DIR__ . '/db_users.php';

// Legge i dati inviati da JavaScript
$inputData = json_decode(file_get_contents('php://input'), true);
$loginInput = trim($inputData['login_input'] ?? '');
$password = $inputData['password'] ?? '';

if (empty($loginInput) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Inserisci tutte le credenziali!']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE username = :input OR email = :input LIMIT 1");
    $stmt->execute([':input' => $loginInput]);
    $utente = $stmt->fetch();

    if ($utente && password_verify($password, $utente['password'])) {
        $_SESSION['utente_id'] = $utente['id'];
        $_SESSION['username'] = $utente['username'];
        $_SESSION['nome'] = $utente['nome'];

        echo json_encode([
            'success' => true,
            'user' => ['nome' => $utente['nome'], 'username' => $utente['username']]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email/Username o Password errati!']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Errore del server!']);
}