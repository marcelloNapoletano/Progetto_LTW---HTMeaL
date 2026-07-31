<?php
require_once __DIR__ . '/php/db_users.php';

$messaggioErrore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confermaPassword = $_POST['conferma_password'] ?? '';

    if (empty($nome) || empty($cognome) || empty($username) || empty($email) || empty($password)) {
        $messaggioErrore = "Tutti i campi sono obbligatori!";
    } elseif ($password !== $confermaPassword) {
        $messaggioErrore = "Le due password non coincidono!";
    } else {
        // Cifro password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utenti (nome, cognome, username, email, password) 
                VALUES (:nome, :cognome, :username, :email, :password)";
        
        $stmt = $pdo->prepare($sql);    

        try {
            $stmt->execute([
                ':nome' => $nome,
            ':cognome' => $cognome,
            ':username' => $username,
            ':email' => $email,
            ':password' => $passwordHash
        ]);

    // Reindirizza alla home dopo il successo
        header('Location: index.html');
            exit;

        }catch (PDOException $e) {
    // 23505 è il codice specifico di PostgreSQL per violazione di vincolo UNIQUE
            if ($e->getCode() == '23505' || $e->getCode() == '23000') {
                $messaggioErrore = "Questa email o username è già in uso!";
            }else{
                $messaggioErrore = "Errore nel salvataggio: " . $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>HTMeaL</title>
    
    <!-- Bootstrap core CSS-->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Our CSS -->
    <link href="css/commons.css" rel="stylesheet">
    <link href="css/registrati.css" rel="stylesheet">
</head>
<body class="bg bg_body_registrati">

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
                <a class="nav-link" href="chi_siamo.html">Chi Siamo</a>
                
                <div class="auth-buttons"> 

                    <div class="dropdown d-inline-block">
                        <button class="login" type="button" id="dropdownLogin" data-toggle="collapse" data-target="#menuLogin" aria-haspopup="true" aria-expanded="false">
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
                                        <button type="submit" class="btn btn-dark btn-tendina">Accedi</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <?php if (!empty($messaggioErrore)): ?>
        <div id="error-toast" class="err-reg">
            <span><?php echo htmlspecialchars($messaggioErrore); ?></span>
        </div>

        <script>
            setTimeout(() => {dismissError();}, 5000);
        </script>

    <?php endif; ?>

    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card register-card">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-center font-weight-bold mb-4">Crea un account</h2>
                        
                        <form action="registrati.php" method="POST" id="formRegistrazione">
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="regNome">Nome</label>
                                    <input type="text" class="form-control" id="regNome" name="nome" placeholder="Mario" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="regCognome">Cognome</label>
                                    <input type="text" class="form-control" id="regCognome" name="cognome" placeholder="Rossi" required>
                                </div>
                            </div>
                                <div class="form-group">
                                    <label for="regUsername">Username</label>
                                    <input type="text" class="form-control" id="regUsername" name="username" placeholder="Super Mario" required>
                                </div>
                            <!-- Email -->
                            <div class="form-group">
                                <label for="regEmail">Indirizzo Email</label>
                                <input type="email" class="form-control" id="regEmail" name="email" placeholder="nome@esempio.com" required>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="regPassword">Password</label>
                                <input type="password" class="form-control" id="regPassword" name="password" placeholder="Minimo 8 caratteri" minlength="8" required>
                            </div>

                            <!-- Conferma Password -->
                            <div class="form-group">
                                <label for="regConfirmPassword">Conferma Password</label>
                                <input type="password" class="form-control" id="regConfirmPassword" name="conferma_password" placeholder="Ripeti la password" minlength="8" required>
                            </div>

                            <!-- Checkbox Termini e Condizioni -->
                            <div class="form-group form-check mt-3">
                                <input type="checkbox" class="form-check-input" id="checkTermini" required>
                                <label class="form-check-label text-muted" for="checkTermini">
                                    Accetto i <a href="#" class="terms">Termini e le Condizioni</a>
                                </label>
                            </div>

                            <!-- Pulsanti di Azione -->
                            <div class="row mt-4">
                                <div class="col-6">
                                    <a class="btn btn-outline-light w-100" href="index.html">Annulla</a>
                                </div>
                                <div class="col-6">
                                    <button type="submit" class="btn btn-dark w-100 font-weight-bold">Registrati</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="assets/js/vendor/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/commons.js"></script>
    <script type="text/javascript" language="javascript" src="js/registrati.js"></script>
</body>
</html>