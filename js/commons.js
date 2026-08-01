/*###########################################################################################################################*/

//READY (GESTIONE MENU)

$(document).ready(function () {

    $(window).on("scroll", function () {
        var currentScrollPos = window.pageYOffset;
        if (currentScrollPos != 0) {
            document.getElementById("menu").style.top = "-4rem";
        }
        else {
            document.getElementById("menu").style.top = "0";
        }
    });

    $("#menu").on("mouseover", function () {
        if (window.pageYOffset != 0) {
            document.getElementById("menu").style.top = "0";
            $("#menu").css("background-color", "rgba(0, 0, 0, 0.8)");
        }
    });

    $("#menu").on("mouseleave", function () {
        if (window.pageYOffset != 0) {
            document.getElementById("menu").style.top = "-4rem";
            $("#menu").css("background-color", "rgba(0, 0, 0, 0.3)");
        }
    });
})

// Controlla sessione
document.addEventListener("DOMContentLoaded", function() {
    checkUserSession();
});

function inviaLogin(e) {
    e.preventDefault(); // Blocca il ricaricamento standard della pagina

    const emailInput = document.getElementById('loginEmail').value;
    const passwordInput = document.getElementById('loginPassword').value;

    // Invia i dati a login.php via fetch (AJAX)
    fetch('php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            login_input: emailInput,
            password: passwordInput
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Login riuscito! Aggiorna la navbar e chiudi la tendina
            updateNavbarLoggedIn(data.user.nome);
            $('#menuLogin').collapse('hide');
        } else {

            showToastError(data.message);
        }
    })
    .catch(err => {
        showToastError("Errore durante la connessione al server.");
    });
}

//Verifica se l'utente è già loggato
function checkUserSession() {
    fetch('php/check_session.php')
        .then(res => res.json())
        .then(data => {
            if (data.loggedIn) {
                updateNavbarLoggedIn(data.nome);
            }
        })
        .catch(err => console.log("Nessuna sessione attiva"));
}

//Modifica la Navbar via DOM (sostituisce Accedi/Registrati con il saluto)
function updateNavbarLoggedIn(nome) {
    const linkCondividi = document.getElementById('navCondividi');
    if (linkCondividi) {
        linkCondividi.classList.remove('d-none');
    }

    const loginContainer = document.querySelector('#navbarNav .ml-auto > div');
    if (loginContainer) {
        loginContainer.innerHTML = `
            <span class="nav-post-access">Ciao, <strong>${nome}</strong>!</span>
            <button onclick="logout()" class="btn btn-dark login">
                <span class="login-label">Esci</span>
            </button>
        `;
    }
}

function logout() {
    fetch('php/logout.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.location.pathname.includes("condividi.php")) {
                    window.location.href = "index.html";
                } else {
                    window.location.reload();
                }
            }
        })
        .catch(err => {
            window.location.reload();
        });
}

// Mostra errore
function showToastError(msg) {
    let toast = document.getElementById('login-error-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'login-error-toast';
        toast.className = 'error-banner';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<span>${msg}</span><button onclick="this.parentElement.remove()" class="close-btn">&times;</button>`;
    
    setTimeout(() => { 
        if (toast) toast.remove(); 
    }, 5000);
}

/*###########################################################################################################################*/
