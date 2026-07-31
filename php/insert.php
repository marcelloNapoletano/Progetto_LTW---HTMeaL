<?php

session_start();

require_once __DIR__ . '/db_users.php'; 

function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {
    if (isset($_POST["nome"]) && isset($_POST["tipo_piatto"]) && isset($_POST["ing_principale"]) && 
        isset($_POST["persone"]) && isset($_POST["ingredienti"]) && isset($_POST["preparazione"])) {
            
            // RECUPERO ID UTENTE: Se è loggato usa il suo ID, altrimenti 0 (HTMeal)
            $id_autore = isset($_SESSION['utente_id']) ? (int)$_SESSION['utente_id'] : 0;

            $nome           = $_POST["nome"];
            $tipo_piatto    = $_POST["tipo_piatto"];
            $ing_principale = $_POST["ing_principale"];
            $persone        = $_POST["persone"];
            $note           = $_POST["note"] ?? '';
            $ingredienti    = $_POST["ingredienti"];
            $preparazione   = $_POST["preparazione"];

            try {
                // QUERY CON PDO 
                $sql = "INSERT INTO Ricette (nome, tipo_piatto, ing_principale, persone, note, ingredienti, preparazione, id_autore) 
                        VALUES (:nome, :tipo_piatto, :ing_principale, :persone, :note, :ingredienti, :preparazione, :id_autore)";

                $stmt = getDB()->prepare($sql);
                $stmt->execute([
                    ':nome'           => $nome,
                    ':tipo_piatto'    => $tipo_piatto,
                    ':ing_principale' => $ing_principale,
                    ':persone'        => $persone,
                    ':note'           => $note,
                    ':ingredienti'    => $ingredienti,
                    ':preparazione'   => $preparazione,
                    ':id_autore'      => $id_autore
                ]);

                echo json_encode("success");

            } catch (PDOException $e) {
            
                echo json_encode("error");
            }
    }
}
?>