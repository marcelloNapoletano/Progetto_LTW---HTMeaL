<?php

require_once __DIR__ . '/db_users.php';
$envPath = __DIR__ . '/../.env';

if (is_ajax()) {
    if (isset($_POST["action"]) && !empty($_POST["action"])) {
        $action = $_POST["action"];
        switch ($action) {
            case "search-piatto":       ricercaPiatto(); break;
            case "search-ingredienti":  ricercaIngredienti(); break;
            case "search-all":          ricercaTutto(); break;
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

    // Query base con JOIN per leggere l'autore
    $sql = "SELECT r.*, COALESCE(u.username, 'HTMeaL') AS autore_username 
            FROM Ricette r 
            LEFT JOIN utenti u ON r.id_autore = u.id 
            WHERE r.nome ~* ('\m' || :piatto || '\M')";

    $params = [':piatto' =>  $piatto ];

    // Filtri dinamici con Prepared Statements
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

    $sql = "SELECT r.*, COALESCE(u.username, 'HTMeaL') AS autore_username 
            FROM Ricette r 
            LEFT JOIN utenti u ON r.id_autore = u.id 
            WHERE 1=1";

    $params = [];

    // Filtro per ogni ingrediente
    foreach ($lista_ingredienti as $idx => $ingrediente) {
        $paramKey = ":ing_$idx";
        $sql .= " AND r.ingredienti ~* $paramKey";
        $params[$paramKey] = '\m' . preg_quote(trim($ingrediente), '/') . '\M';
    }

    // Filtri aggiuntivi
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
    $sql = "SELECT r.*, COALESCE(u.username, 'HTMeal') AS autore_username
            FROM Ricette r
            LEFT JOIN utenti u ON r.id_autore = u.id
            ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getRicetteUtente($idAutore) {
    $pdo = getDB();
    $sql = "SELECT r.*, COALESCE(u.username, 'HTMeal') AS autore_username
            FROM Ricette r
            LEFT JOIN utenti u ON r.id_autore = u.id
            WHERE r.id_autore = :id_autore
            ORDER BY r.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_autore' => $idAutore]);
    return $stmt->fetchAll();
}

function formattaRispostaLegacy($rows) {
    $return = [
        "nome"            => [],
        "tipo_piatto"     => [],
        "ing_principale"  => [],
        "persone"         => [],
        "note"            => [],
        "ingredienti"     => [],
        "preparazione"    => [],
        "autore_username" => [] 
    ];

    foreach ($rows as $row) {
        $return["nome"][]            = $row["nome"];
        $return["tipo_piatto"][]     = $row["tipo_piatto"];
        $return["ing_principale"][]  = $row["ing_principale"];
        $return["persone"][]         = $row["persone"];
        $return["note"][]            = $row["note"];
        $return["ingredienti"][]     = $row["ingredienti"];
        $return["preparazione"][]    = $row["preparazione"];
        $return["autore_username"][] = $row["autore_username"];
    }

    // Converte le singole colonne in stringhe JSON
    foreach ($return as $key => $val) {
        $return[$key] = json_encode($val);
    }

    return $return;
}
?>

