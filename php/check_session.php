<?php
// JSON
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifico se l'utente è loggato
if (isset($_SESSION['utente_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'nome'     => $_SESSION['nome'] ?? $_SESSION['username']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
exit;