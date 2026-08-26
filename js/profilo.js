$(document).ready(function () {
    $("body").fadeIn(1000);
    caricaRicetteUtente();
    caricaPreferitiUtente();

    $('#formAggiornaProfilo').on('submit', function (e) {
        e.preventDefault();

        var usernameInput = $('#editUsername').val().trim();
        var passwordInput = $('#editPassword').val();

        if (usernameInput === "") {
            mostraMessaggio("L'username non può essere vuoto.", "warning");
            return;
        }

        fetch('profilo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: usernameInput,
                password: passwordInput
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                mostraMessaggio(data.message, "success");
                $('#editPassword').val(''); // Svuota la password
            } else {
                mostraMessaggio(data.message, "danger");
            }
        })
        .catch(function (err) {
            console.error(err);
            mostraMessaggio("Errore di connessione al server.", "danger");
        });
    });
});

function mostraMessaggio(testo, tipo) {
    const feedback = document.getElementById('profiloFeedback');
    if (feedback) {
        feedback.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${testo}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
    }
}

function caricaRicetteUtente() {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "php/queries.php",
        data: { "action": "get-user-recipes" },
        success: function (data) {
            stampaRicette(data, "#mieRicette", "Non hai ancora inserito alcuna ricetta!");
        },
        error: function () {
            $("#mieRicette").html('<div class="alert alert-danger text-center">Errore nel caricamento delle ricette.</div>');
        }
    });
}

function caricaPreferitiUtente() {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "php/queries.php",
        data: { "action": "get-user-favorites" },
        success: function (data) {
            stampaRicette(data, "#preferiti", "Non hai ancora salvato alcuna ricetta tra i preferiti!");
        },
        error: function () {
            $("#preferiti").html('<div class="alert alert-danger text-center">Errore nel caricamento dei preferiti.</div>');
        }
    });
}

function stampaRicette(data, containerId, messaggioVuoto) {
    $(containerId).empty();

    if (!data || !data.nome) {
        $(containerId).append(`<div class="alert alert-warning text-center">${messaggioVuoto}</div>`);
        return;
    }

    var ricette = {};
    ricette["id"] = JSON.parse(data["id"]);
    ricette["nome"] = JSON.parse(data["nome"]);
    ricette["tipo_piatto"] = JSON.parse(data["tipo_piatto"]);
    ricette["ing_principale"] = JSON.parse(data["ing_principale"]);
    ricette["persone"] = JSON.parse(data["persone"]);
    ricette["note"] = JSON.parse(data["note"]);
    ricette["ingredienti"] = JSON.parse(data["ingredienti"]);
    ricette["preparazione"] = JSON.parse(data["preparazione"]);
    ricette["autore_username"] = data["autore_username"] ? JSON.parse(data["autore_username"]) : [];
    ricette["is_preferito"] = data["is_preferito"] ? JSON.parse(data["is_preferito"]) : [];

    var count = ricette.nome ? ricette.nome.length : 0;

    if (count === 0) {
        $(containerId).append(`<div class="alert alert-warning text-center">${messaggioVuoto}</div>`);
        return;
    }

    for (var i = 0; i < count; i++) {
        var idRicetta = ricette.id[i];
        var isPref = ricette.is_preferito[i];
        var stellaClasse = isPref ? "fas fa-star" : "far fa-star";
        
        var autore = (ricette.autore_username && ricette.autore_username[i]) ? ricette.autore_username[i] : "HTMeaL";
        var ingredientiFormatted = ricette.ingredienti[i].split('+').join('<br>');

        // Per evitare ID duplicati
        var prefix = containerId.replace('#', '') + '_' + i;

        var cardHtml = `
            <div class="card card-ricetta mb-3" id="card-${prefix}">
                <div class="card-header d-flex justify-content-between align-items-center" id="heading${prefix}">
                    <h6 class="mb-0 w-100 d-flex justify-content-between align-items-center">
                        <button class="btn collapsed btn-filtri filtri_text text-left" 
                                data-toggle="collapse" 
                                data-target="#sotto${prefix}" 
                                aria-expanded="false" 
                                aria-controls="sotto${prefix}">
                            ${ricette.nome[i]}
                        </button>
                        <div class="d-flex align-items-center">
                            <button class="btn-star" title="Aggiungi/Rimuovi preferito" onclick="togglePreferitoProfilo(event, ${idRicetta}, this)">
                                <i class="${stellaClasse}"></i>
                            </button>
                            <span class="badge badge-secondary p-2 ml-2" style="font-size: 0.8em; font-weight: normal;">
                                di <strong>${autore}</strong>
                            </span>
                        </div>
                    </h6>
                </div>
                <div id="sotto${prefix}" class="collapse" aria-labelledby="heading${prefix}" data-parent="${containerId}">
                    <div class="card-body">
                        <strong>Tipo piatto:</strong> <span>${ricette.tipo_piatto[i]}</span><br>
                        <strong>Persone:</strong> <span>${ricette.persone[i]}</span><br>
                        <strong>Ingrediente Principale:</strong> <span>${ricette.ing_principale[i]}</span><br><br>
                        <strong>Ingredienti:</strong><br>
                        <span>${ingredientiFormatted}</span><br><br>
                        <strong>Preparazione:</strong><br>
                        <span>${ricette.preparazione[i]}</span>
                    </div>
                </div>
            </div>
        `;

        $(containerId).append(cardHtml);
    }
}

function togglePreferitoProfilo(event, idRicetta, elemento) {
    togglePreferito(event, idRicetta, elemento);

    setTimeout(function() {
        caricaPreferitiUtente();
    }, 300);
}