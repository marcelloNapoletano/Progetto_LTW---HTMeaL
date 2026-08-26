<?php

require_once __DIR__ . '/db_users.php';
$envPath = __DIR__ . '/../.env';

// Avviamo la sessione una volta sola all'inizio per gestire le ricerche dell'utente loggato
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_ajax()) {
    if (isset($_POST["action"]) && !empty($_POST["action"])) {
        $action = $_POST["action"];
        switch ($action) {
            case "search-piatto":       ricercaPiatto(); break;
            case "search-ingredienti":  ricercaIngredienti(); break;
            case "search-all":          ricercaTutto(); break;
            case "get-user-recipes":    ricercaRicetteUtente(); break;
            case "get-user-favorites":  ricercaPreferitiUtente(); break; // <-- NUOVA AZIONE
            case "toggle-preferito":    gestisciTogglePreferito(); break;
        }
    }
}

function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/* =========================================================================
   FUNZIONI DI RICERCA SITO
   ========================================================================= */

function ricercaPiatto() {
    $pdo = getDB();
    $piatto = $_POST["piatto"] ?? '';
    $filtri = json_decode($_POST["filtri"], true) ?? [];
    $idUtente = $_SESSION['utente_id'] ?? 0; // 0 se non loggato

    // Query con LEFT JOIN su utenti e preferiti
    $sql = "SELECT r.*, 
                   COALESCE(u.username, 'HTMeaL') AS autore_username,
                   CASE WHEN p.id_utente IS NOT NULL THEN true ELSE false END AS is_preferito
            FROM Ricette r 
            LEFT JOIN utenti u ON r.id_autore = u.id 
            LEFT JOIN preferiti p ON r.id = p.id_ricetta AND p.id_utente = :id_utente
            WHERE r.nome ~* ('\m' || :piatto || '\M')";

    $params = [
        ':piatto' => $piatto,
        ':id_utente' => $idUtente
    ];

    if (!empty($filtri["tipo_piatto"])) {
        $sql .= " AND r.tipo_piatto = :tipo_piatto";
        $params[':tipo_piatto'] = $filtri["tipo_piatto"];
    }

    if (!empty($filtri["persone"])) {
        if ($filtri["persone"] == "10") {
            $sql .= " AND r.persone >= 10";
        } else {
            $sql .= " AND r.persone = :persone";
            $params[':persone'] = (int)$filtri["persone"];
        }
    }

    if (!empty($filtri["iniziale"]) && $filtri["iniziale"] != "none") {
        $sql .= " AND r.nome LIKE :iniziale";
        $params[':iniziale'] = $filtri["iniziale"] . '%';
    }

    $sql .= " ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ricette = $stmt->fetchAll();

    echo json_encode(formattaRispostaLegacy($ricette));
}

function ricercaIngredienti() {
    $pdo = getDB();
    $lista_ingredienti = json_decode($_POST["ingredienti"], true) ?? [];
    $filtri = json_decode($_POST["filtri"], true) ?? [];
    $idUtente = $_SESSION['utente_id'] ?? 0;

    $sql = "SELECT r.*, 
                   COALESCE(u.username, 'HTMeaL') AS autore_username,
                   CASE WHEN p.id_utente IS NOT NULL THEN true ELSE false END AS is_preferito
            FROM Ricette r 
            LEFT JOIN utenti u ON r.id_autore = u.id 
            LEFT JOIN preferiti p ON r.id = p.id_ricetta AND p.id_utente = :id_utente
            WHERE 1=1";

    $params = [':id_utente' => $idUtente];

    foreach ($lista_ingredienti as $idx => $ingrediente) {
        $paramKey = ":ing_$idx";
        $sql .= " AND r.ingredienti ~* $paramKey";
        $params[$paramKey] = '\m' . preg_quote(trim($ingrediente), '/') . '\M';
    }

    if (!empty($filtri["tipo_piatto"])) {
        $sql .= " AND r.tipo_piatto = :tipo_piatto";
        $params[':tipo_piatto'] = $filtri["tipo_piatto"];
    }

    if (!empty($filtri["persone"])) {
        if ($filtri["persone"] == "10+") {
            $sql .= " AND r.persone >= 10";
        } else {
            $sql .= " AND r.persone = :persone";
            $params[':persone'] = (int)$filtri["persone"];
        }
    }

    if (!empty($filtri["iniziale"])) {
        $sql .= " AND r.nome LIKE :iniziale";
        $params[':iniziale'] = $filtri["iniziale"] . '%';
    }

    $sql .= " ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ricette = $stmt->fetchAll();

    echo json_encode(formattaRispostaLegacy($ricette));
}

function ricercaTutto() {
    $ricette = getTutteLeRicette();
    echo json_encode(formattaRispostaLegacy($ricette));
}

/* =========================================================================
   FUNZIONI DI SUPPORTO E UTILITY
   ========================================================================= */

function getTutteLeRicette() {
    $pdo = getDB();
    $idUtente = $_SESSION['utente_id'] ?? 0;

    $sql = "SELECT r.*, 
                   COALESCE(u.username, 'HTMeal') AS autore_username,
                   CASE WHEN p.id_utente IS NOT NULL THEN true ELSE false END AS is_preferito
            FROM Ricette r
            LEFT JOIN utenti u ON r.id_autore = u.id
            LEFT JOIN preferiti p ON r.id = p.id_ricetta AND p.id_utente = :id_utente
            ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_utente' => $idUtente]);
    return $stmt->fetchAll();
}

function getRicetteUtente($idAutore) {
    $pdo = getDB();
    $sql = "SELECT r.*, 
                   COALESCE(u.username, 'HTMeal') AS autore_username,
                   CASE WHEN p.id_utente IS NOT NULL THEN true ELSE false END AS is_preferito
            FROM Ricette r
            LEFT JOIN utenti u ON r.id_autore = u.id
            LEFT JOIN preferiti p ON r.id = p.id_ricetta AND p.id_utente = :id_utente
            WHERE r.id_autore = :id_autore
            ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_autore' => $idAutore, ':id_utente' => $idAutore]);
    return $stmt->fetchAll();
}

function formattaRispostaLegacy($rows) {
    $return = [
        "id"              => [], 
        "nome"            => [],
        "tipo_piatto"     => [],
        "ing_principale"  => [],
        "persone"         => [],
        "note"            => [],
        "ingredienti"     => [],
        "preparazione"    => [],
        "autore_username" => [],
        "is_preferito"    => []  
    ];

    foreach ($rows as $row) {
        $return["id"][]              = $row["id"];
        $return["nome"][]            = $row["nome"];
        $return["tipo_piatto"][]     = $row["tipo_piatto"];
        $return["ing_principale"][]  = $row["ing_principale"];
        $return["persone"][]         = $row["persone"];
        $return["note"][]            = $row["note"];
        $return["ingredienti"][]     = $row["ingredienti"];
        $return["preparazione"][]    = $row["preparazione"];
        $return["autore_username"][] = $row["autore_username"];
        $return["is_preferito"][]    = (bool)($row["is_preferito"] ?? false);
    }

    foreach ($return as $key => $val) {
        $return[$key] = json_encode($val);
    }

    return $return;
}

function ricercaRicetteUtente() {
    if (!isset($_SESSION['utente_id'])) {
        echo json_encode(['error' => 'Non autorizzato']);
        exit();
    }

    $idAutore = $_SESSION['utente_id'];
    $ricette = getRicetteUtente($idAutore); 
    echo json_encode(formattaRispostaLegacy($ricette)); 
}

// NUOVA: Permette di recuperare solo le ricette preferite dell'utente (utile per il profilo)
function ricercaPreferitiUtente() {
    if (!isset($_SESSION['utente_id'])) {
        echo json_encode(['error' => 'Non autorizzato']);
        exit();
    }

    $pdo = getDB();
    $idUtente = $_SESSION['utente_id'];

    $sql = "SELECT r.*, 
                   COALESCE(u.username, 'HTMeal') AS autore_username,
                   true AS is_preferito
            FROM preferiti p
            JOIN Ricette r ON p.id_ricetta = r.id
            LEFT JOIN utenti u ON r.id_autore = u.id
            WHERE p.id_utente = :id_utente
            ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_utente' => $idUtente]);
    $ricette = $stmt->fetchAll();

    echo json_encode(formattaRispostaLegacy($ricette));
}

function gestisciTogglePreferito() {
    if (!isset($_SESSION['utente_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Devi effettuare l\'accesso per salvare i preferiti!'
        ]);
        exit();
    }

    $idUtente = $_SESSION['utente_id'];
    $idRicetta = $_POST['id_ricetta'] ?? null;

    if (!$idRicetta) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID ricetta non valido.'
        ]);
        exit();
    }

    $isPreferito = togglePreferitoDB($idUtente, (int)$idRicetta);

    echo json_encode([
        'status' => 'success',
        'is_preferito' => $isPreferito
    ]);
    exit();
}

function togglePreferitoDB($idUtente, $idRicetta) {
    $pdo = getDB();

    $checkSql = "SELECT 1 FROM preferiti WHERE id_utente = :id_utente AND id_ricetta = :id_ricetta";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([
        ':id_utente' => $idUtente,
        ':id_ricetta' => $idRicetta
    ]);

    if ($stmt->fetch()) {
        $deleteSql = "DELETE FROM preferiti WHERE id_utente = :id_utente AND id_ricetta = :id_ricetta";
        $delStmt = $pdo->prepare($deleteSql);
        $delStmt->execute([
            ':id_utente' => $idUtente,
            ':id_ricetta' => $idRicetta
        ]);
        return false;
    } else {
        $insertSql = "INSERT INTO preferiti (id_utente, id_ricetta) VALUES (:id_utente, :id_ricetta)";
        $insStmt = $pdo->prepare($insertSql);
        $insStmt->execute([
            ':id_utente' => $idUtente,
            ':id_ricetta' => $idRicetta
        ]);
        return true;
    }
}   
?>