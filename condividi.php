<?php
session_start();

// Controllo di sicurezza: se l'utente non è loggato, reindirizza alla home
if (!isset($_SESSION['utente_id'])) { // Usa il nome della variabile di sessione che imposti al login
    header("Location: index.html?error=non_autorizzato");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>HTMeaL</title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/commons.css" rel="stylesheet">
    <link href="css/condividi.css" rel="stylesheet">
</head>

<body class="bg bg_body_condividi">

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
            <a class="nav-link active d-none" id="navCondividi" href="condividi.php">Condividi Ricetta</a>
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
    <!-- Form per inviare la ricetta -->

    <div class="container" id="invio_ricette">
        <div id="form_ricetta" name="form_ricetta" class="form-group">
            <h1 style="text-align: center;">Condividi con noi la tua ricetta!</h1>

            <label for="nome">Nome Ricetta:</label>
            <input id="nome" type="text" class="form-control" placeholder="Inserisci il nome della Ricetta...">

            <label for="tipo_piatto">Tipo Piatto:</label>
            <div>
                <select id="tipo_piatto">
                    <option value="none" selected>-- tipo piatto --</option>
                    <option value="Antipasto">Antipasto</option>
                    <option value="Primo">Primo</option>
                    <option value="Salsa">Salsa</option>
                    <option value="Carne">Carne</option>
                    <option value="Pesce">Pesce</option>
                    <option value="Pollame">Pollame</option>
                    <option value="Contorno">Contorno</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Bevande">Bevande</option>
                </select>
            </div>

            <label for="ing_principale">Ingrediente Principale:</label>
            <input id="ing_principale" type="text" class="form-control" placeholder="Inserisci l'ingrediente principale...">

            <label for="persone">Persone:</label>
            <input id="persone" type="text" class="form-control" placeholder="Inserisci il numero di persone...">

            <label for="note">Note:</label>
            <input id="note" type="text" class="form-control" placeholder="Inserisci eventuali note...">
            
            <label for="ingredienti">Ingredienti:</label>
            <input id ="ingredienti" type="text" class="form-control" placeholder="Inserisci ingrediente..."><br>
            <br>
            
            <label for="preparazione">Preparazione:</label>
            <textarea id="preparazione" rows="15" class="form-control" placeholder="Inserisci la preparazione..."></textarea>

            <div id="btn_container"><button id="invia" onclick="return validaForm()">Condividi!</button></div>
        </div>

        <div id="invio_result"></div>
        <button id="back" class="button back">Indietro</button>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="assets/js/vendor/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/commons.js"></script>
    <script src="js/chisiamo.js"></script> 
</body>

</html>
