/*###########################################################################################################################*/

// VARIABILI GLOBALI IN MEMORIA RAM
var lista_ingredienti_ = [];
var filtri_ = { "tipo_piatto": "", "persone": "", "iniziale": "" };
var isNaviga = false;

function ingrediente_esistente(ingrediente) {
    var ingLower = ingrediente.toUpperCase();
    return lista_ingredienti_.some(function(item) {
        return item.toUpperCase() === ingLower;
    });
}

function controlloSearchBarIngredienti(event) {
    if (event.which === 13 || event.keyCode === 13) {
        var ingrediente = $.trim($("#searchBarIngredienti").val());

        if (!isNaN(parseInt(ingrediente)) && ingrediente !== "") {
            window.alert("Errore: Gli ingredienti non possono essere numeri!");
        }
        else if (ingrediente === "") {
            window.alert("Errore: Inserire un ingrediente!");
        }
        else {
            var $listaScelteDiv = $("#lista_scelte_div");

            // Se è il primo ingrediente, crea il titolo e il tag <ul>
            if ($("#lista_scelte").length === 0) {
                $listaScelteDiv.append('<h4 id="titolo_ingredienti">INGREDIENTI:</h4>');
                $listaScelteDiv.append('<ul id="lista_scelte"></ul>');
            }

            if (!ingrediente_esistente(ingrediente)) {
            
                lista_ingredienti_.push(ingrediente);

                // LISTA
                var liHtml = `
                    <li class="li" data-ingrediente="${ingrediente}">
                        <label>${ingrediente}</label>
                        <label class="remove" data-ingrediente="${ingrediente}">−</label>
                    </li>
                `;
                $("#lista_scelte").append(liHtml);
            } else {
                window.alert("Ingrediente già inserito nella lista!");
            }
        }
        $("#searchBarIngredienti").val("");
    }
}

/*###########################################################################################################################*/

// READY

$(document).ready(function () {
    $("body").fadeIn(1000);

    caricaStatoDaUrl();

    window.onpopstate = function() {
        isNaviga = true;
        caricaStatoDaUrl();
    };

    // CLICK VAI
    $("#searchIngredienti").click(function (e) {
        e.preventDefault();

        if (lista_ingredienti_.length === 0 && $("#searchBarIngredienti").val() === "") {
            window.alert("Errore: Inserire almeno un ingrediente!");
            $("#searchBarIngredienti").val("");
        }
        else if (lista_ingredienti_.length === 0) {
            window.alert("Errore: Premere Invio per inserire un ingrediente!");
        }
        else {
            filtri_ = { "tipo_piatto": "", "persone": "", "iniziale": "" };
            searchForIngredienti();
        }
    });

    $("input[name='tp']").prop("checked", false);
    $("input[name='np']").prop("checked", false);

    // FILTRO TIPO PIATTO
    $("input[name='tp']").on("change", function () {
        if (this.checked) {
            filtri_["tipo_piatto"] = $(this).val();
            searchForIngredienti();
        }
    });

    // FILTRO NUMERO PERSONE
    $("input[name='np']").on("change", function () {
        if (this.checked) {
            filtri_["persone"] = $(this).val();
            searchForIngredienti();
        }
    });

    // FILTRO INIZIALE 
    $("#lettere").on("change", function () {
        var lettera = $(this).val();
        filtri_["iniziale"] = (lettera === "none") ? "" : lettera;
        searchForIngredienti();
    });

    // RIMUOVE INGREDIENTE
    $(document).on("click", ".remove", function () {
        var ingredienteDaRimuovere = $(this).attr("data-ingrediente");

        $(this).closest("li").remove();

        lista_ingredienti_ = lista_ingredienti_.filter(function(item) {
            return item !== ingredienteDaRimuovere;
        });

        if (lista_ingredienti_.length === 0) {
            $("#lista_scelte_div").empty();
        }
    });

    // EVENTO: Aggiorna l'hash dell'URL quando si apre/chiude una ricetta
    $(document).on("click", ".btn-toggle-ricetta", function () {
        var idRicetta = $(this).data("id");
        var targetCollapse = $(this).attr("data-target");

        // Se la scheda sta per essere aperta
        if (!$(targetCollapse).hasClass("show")) {
            history.replaceState(null, null, "#ricetta_" + idRicetta);
        } else {
            // Se si sta chiudendo, rimuovi l'hash mantenendo i filtri nell'URL
            var cleanUrl = window.location.pathname + window.location.search;
            history.replaceState(null, null, cleanUrl);
        }
    });
});

/*###########################################################################################################################*/

// FUNZIONE AJAX PER INVIARE L'ARRAY DI INGREDIENTI AL PHP
function searchForIngredienti() {
    aggiornaUrlStato();
    console.log("Invio ricerca per ingredienti:", { 
        ingredienti: lista_ingredienti_, 
        filtri: filtri_ 
    });

    var data = {
        "action": "search-ingredienti",
        "ingredienti": JSON.stringify(lista_ingredienti_), // Invia l'array serializzato
        "filtri": JSON.stringify(filtri_)
    };

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../php/queries.php",
        data: data,
        success: function (response) {
            stampaRisultati(response);

            $("#accordion").removeClass("d-none").fadeIn();
            $("#ricette").removeClass("d-none").hide().fadeIn();
        },
        error: function (xhr, status, error) {
            console.error("Errore AJAX:", error);
        }
    });
}

function stampaRisultati(data) {
    $("#ricette").empty();

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

    for (var i = 0; i < count; i++) {
        var idRicetta = ricette.id[i];
        var isPref = ricette.is_preferito[i];
        var stellaClasse = isPref ? "fas fa-star" : "far fa-star";

        var autore = (ricette.autore_username && ricette.autore_username[i]) ? ricette.autore_username[i] : "HTMeaL";
        var ingredientiFormatted = ricette.ingredienti[i].split('+').join('<br>');

        var cardHtml = `
            <div class="card card-ricetta mb-3" id="ricetta_${idRicetta}">
                <div class="card-header d-flex justify-content-between align-items-center" id="heading_ing_${i}">
                    <h6 class="mb-0 w-100 d-flex justify-content-between align-items-center">
                        <button class="btn collapsed btn-filtri filtri_text text-left btn-toggle-ricetta" 
                                data-toggle="collapse" 
                                data-target="#sotto_ing_${i}" 
                                data-id="${idRicetta}"
                                aria-expanded="false" 
                                aria-controls="sotto_ing_${i}">
                            ${ricette.nome[i]}
                        </button>
                        <div class="d-flex align-items-center">
                            <button class="btn-star" title="Aggiungi/Rimuovi preferito" onclick="togglePreferito(event, ${idRicetta}, this)">
                                <i class="${stellaClasse}"></i>
                            </button>
                            <span class="badge badge-secondary p-2 ml-2" style="font-size: 0.8em; font-weight: normal;">
                                di <strong>${autore}</strong>
                            </span>
                        </div>
                    </h6>
                </div>
                <div id="sotto_ing_${i}" class="collapse" aria-labelledby="heading_ing_${i}" data-parent="#ricette">
                    <div class="card-body">
                        <strong>Tipo piatto:</strong> ${ricette.tipo_piatto[i]}<br>
                        <strong>Persone:</strong> ${ricette.persone[i]}<br>
                        <strong>Ingrediente Principale:</strong> ${ricette.ing_principale[i]}<br><br>
                        <strong>Ingredienti:</strong><br>
                        ${ingredientiFormatted}<br><br>
                        <strong>Preparazione:</strong><br>
                        ${ricette.preparazione[i]}
                    </div>
                </div>
            </div>
        `;

        $("#ricette").append(cardHtml);
    }

    // 0 Risultati
    if (count === 0) {
        var ingredientiCercati = lista_ingredienti_.join(", ");
        var messaggioZero = `
            <div id="ricerca_fallita" class="alert alert-warning text-center">
                Ci dispiace, ma nessuna ricetta contiene tutti gli ingredienti selezionati: 
                <strong>${ingredientiCercati}</strong>.
            </div>
        `;
        $("#ricette").append(messaggioZero);
    }

    $("#num_risultati").html(count + " risultati trovati");

    // Scroll automatico ed espansione se presente l'hash nell'URL
    gestisciHashUrl();
}

function aggiornaUrlStato() {
    if (!isNaviga) {
        var params = new URLSearchParams();

        if (lista_ingredienti_.length > 0) {
            params.set("ing", lista_ingredienti_.join(","));
        }

        if (filtri_["tipo_piatto"]) params.set("tp", filtri_["tipo_piatto"]);
        if (filtri_["persone"]) params.set("np", filtri_["persone"]);
        if (filtri_["iniziale"]) params.set("ini", filtri_["iniziale"]);

        // Preserva l'hash corrente se presente
        var currentHash = window.location.hash;
        var newUrl = window.location.pathname + (params.toString() ? "?" + params.toString() : "") + currentHash;

        window.history.pushState({ path: newUrl }, '', newUrl);
    }
    isNaviga = false;
}

function caricaStatoDaUrl() {
    var params = new URLSearchParams(window.location.search);

    var ingParam = params.get("ing");
    var tp = params.get("tp");
    var np = params.get("np");
    var ini = params.get("ini");

    if (ingParam) {
        var ingPuliti = decodeURIComponent(ingParam.replace(/\+/g, ' '));
        lista_ingredienti_ = ingPuliti.split(",").map(function(item) {
            return item.trim();
        }).filter(function(item) {
            return item !== "";
        });
    } else {
        lista_ingredienti_ = [];
    }

    var $listaScelteDiv = $("#lista_scelte_div");
    $listaScelteDiv.empty();

    if (lista_ingredienti_.length > 0) {
        $listaScelteDiv.append('<h4 id="titolo_ingredienti">INGREDIENTI:</h4><ul id="lista_scelte"></ul>');
        lista_ingredienti_.forEach(function(ingrediente) {
            var liHtml = `
                <li class="li" data-ingrediente="${ingrediente}">
                    <label>${ingrediente}</label>
                    <label class="remove" data-ingrediente="${ingrediente}">−</label>
                </li>
            `;
            $("#lista_scelte").append(liHtml);
        });
    }

    filtri_ = {
        "tipo_piatto": tp || "",
        "persone": np || "",
        "iniziale": ini || ""
    };

    $("input[name='tp']").prop("checked", false);
    if (tp) $(`input[name='tp'][value='${tp}']`).prop("checked", true);

    $("input[name='np']").prop("checked", false);
    if (np) $(`input[name='np'][value='${np}']`).prop("checked", true);

    $("#lettere").val(ini ? ini : "none");

    if (lista_ingredienti_.length > 0) {
        searchForIngredienti();
    } else {
        $("#ricette").empty().addClass("d-none");
        $("#accordion").addClass("d-none");
        
        isNaviga = false;
    }
}

// FUNZIONE PER APRIRE E SCORRERE FINO ALLA RICETTA SPECIFICATA NELL'HASH
function gestisciHashUrl() {
    var hash = window.location.hash; // Es: "#ricetta_12"

    if (hash && $(hash).length > 0) {
        var $cardTarget = $(hash);
        var $btn = $cardTarget.find(".btn-toggle-ricetta");
        var targetCollapse = $btn.attr("data-target");

        // Apre il collapse Bootstrap
        $(targetCollapse).collapse('show');

        // Scroll fluido verso la card target
        setTimeout(function () {
            $('html, body').animate({
                scrollTop: $cardTarget.offset().top - 30
            }, 500);
        }, 300);
    }
}