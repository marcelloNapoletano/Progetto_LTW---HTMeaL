<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['utente_id'])) {
    header("Location: index.html?error=non_autorizzato");
    exit();
}

require_once 'php/db_users.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $utente_id = $_SESSION['utente_id'];

    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => "L'username non può essere vuoto."]);
        exit();
    }

    try {
        // Verifica username duplicato  
        $stmtCheck = $pdo->prepare("SELECT id FROM utenti WHERE username = :username AND id != :id");
        $stmtCheck->execute([':username' => $username, ':id' => $utente_id]);
        
        if ($stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username già in uso.']);
            exit();
        }

        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("UPDATE utenti SET username = :username, password = :password WHERE id = :id");
            $stmtUpdate->execute([':username' => $username, ':password' => $passwordHash, ':id' => $utente_id]);
        } else {
            $stmtUpdate = $pdo->prepare("UPDATE utenti SET username = :username WHERE id = :id");
            $stmtUpdate->execute([':username' => $username, ':id' => $utente_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Profilo aggiornato con successo!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio: ' . $e->getMessage()]);
    }
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT nome, cognome, email, username FROM utenti WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['utente_id']]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utente) {
        session_destroy();
        header("Location: index.html?error=utente_non_trovato");
        exit();
    }
} catch (PDOException $e) {
    die("Errore di connessione al database.");
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>HTMeaL</title>

    <link rel="icon" type="image/png" href="/images/logo.png">

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/commons.css" rel="stylesheet">
    <link href="css/risultati.css" rel="stylesheet">
    <link href="css/profilo.css" rel="stylesheet">

    <!-- Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg bg_body_profilo">

    <!--Menù-->
<nav class="navbar navbar-expand-lg navbar-dark menu" id="menu">
    <a class="navbar-brand marchio">HTMeaL</a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse text-center" id="navbarNav">
        <div class="navbar-nav ml-auto">
            <a class="nav-link" href="index.html">Home</a>
            <a class="nav-link" href="piatto.html">Cerca Per Piatto</a>
            <a class="nav-link" href="ingredienti.html">Cerca Per Ingredienti</a>
            <a class="nav-link d-none" id="navCondividi" href="condividi.php">Condividi Ricetta</a>
            <a class="nav-link active d-none" id="navProfilo" href="profilo.php">Profilo</a>
            <a class="nav-link" href="chi_siamo.html">Chi Siamo</a>
            <div>
                <div class="dropdown d-inline-block">
                <button class="login dropdown-toggle" type="button" id="dropdownLogin" data-toggle="collapse" data-target="#menuLogin" aria-haspopup="true" aria-expanded="false">
                    <span class="login-label">Accedi</span>
                </button>

                    <div class="dropdown-menu dropdown-menu-right p-4" id="menuLogin" aria-labelledby="dropdownLogin">
                        <form class="login-form" onsubmit="inviaLogin(event)">
                            <div class="form-group mb-3">
                                <label for="loginEmail" class="form-label text-light">Email</label>
                                <input type="email" class="form-control input-login" id="loginEmail" placeholder="email@esempio.com" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="loginPassword" class="form-label text-light">Password</label>
                                <input type="password" class="form-control input-login" id="loginPassword" placeholder="••••••••" required>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-secondary btn-tendina" data-toggle="collapse" data-target="#menuLogin">
                                    Indietro
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="submit" class="btn btn-dark btn-tendina">
                                    Accedi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <a class="login" href="registrati.php">
                    <span class="login-label">Registrati</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid" id="profiloContainer">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-3">
            <div class="id_utenteCard">
                <h2 class="text-center mb-4">Dati Personali</h2>
                
                <div id="profiloFeedback"></div>

                <div class="mb-4">
                    <p class="mb-1 text-muted small">Nome</p>
                    <p class="mb-2 fw-bold" id="profiloNome"><?php echo htmlspecialchars($utente['nome']); ?></p>
                    
                    <p class="mb-1 text-muted small">Cognome</p>
                    <p class="mb-2 fw-bold" id="profiloCognome"><?php echo htmlspecialchars($utente['cognome']); ?></p>

                    <p class="mb-1 text-muted small">Email</p>
                    <p class="mb-0 fw-bold" id="profiloEmail"><?php echo htmlspecialchars($utente['email']); ?></p>
                </div>

                <hr>

                <form id="formAggiornaProfilo">
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" value="<?php echo htmlspecialchars($utente['username']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="editPassword" class="form-label">Nuova Password</label>
                        <input type="password" class="form-control" id="editPassword" placeholder="Lascia vuoto per non modificare">
                        <div class="form-text">Inserisci una password solo se desideri cambiarla.</div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-profilo-salva w-100 py-2">
                            Salva Modifiche
                        </button>
                    </div>
                </form>

            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="id_utenteCard scheda-profilo">
                
                <ul class="nav nav-tabs justify-content-center mb-4" id="profiloTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active text-light font-weight-bold" id="ricette-tab" data-toggle="tab" href="#mie-ricette" role="tab" aria-controls="mie-ricette" aria-selected="true">
                            Le Mie Ricette
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light font-weight-bold" id="preferiti-tab" data-toggle="tab" href="#miei-preferiti" role="tab" aria-controls="miei-preferiti" aria-selected="false">
                            <i class="fas fa-star text-warning mr-1"></i> I Miei Preferiti
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="profiloTabsContent">
                    
                    <div class="tab-pane fade show active" id="mie-ricette" role="tabpanel" aria-labelledby="ricette-tab">
                        <div id="mieRicette" class="ricette2 w-100">
                            <div class="text-center text-light p-3">Caricamento ricette in corso...</div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="miei-preferiti" role="tabpanel" aria-labelledby="preferiti-tab">
                        <div id="preferiti" class="ricette2 w-100">
                            <div class="text-center text-light p-3">Caricamento preferiti in corso...</div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="assets/js/vendor/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/commons.js"></script>
    <script src="js/profilo.js"></script> 
</body>
</html>