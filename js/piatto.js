/*###########################################################################################################################*/

var filtri_ = { "tipo_piatto": "", "persone": "", "iniziale": "" };
var isNaviga = false;

//READY

$(document).ready(function () {
    $("body").fadeIn(1000);

    caricaStatoDaUrlPiatto();

    // Sincronizza l'interfaccia se l'utente usa le frecce "Avanti" / "Indietro" del browser
    window.onpopstate = function () {
        isNaviga = true;
        caricaStatoDaUrlPiatto();
    };

$("#searchPiatto").click(function () {

    var valoreInput = $("#searchBarPiatto").val().trim();
    if (valoreInput === "") {
        window.alert("Errore: Inserire un piatto!");
        $("#searchBarPiatto").val("");
        return;
    }

filtri_ = { "tipo_piatto": "", "persone": "", "iniziale": "" };
        
    searchForPiatto();
    });

    $("input[name='tp']").prop("checked", false);
    $("input[name='np']").prop("checked", false);

    // FILTRO TIPO PIATTO
    $("input[name='tp']").on("change", function () {
        if (this.checked) {
            filtri_["tipo_piatto"] = $(this).val();
            searchForPiatto();
        }
    });

    // FILTRO NUMERO PERSONE
    $("input[name='np']").on("change", function () {
        if (this.checked) {
            filtri_["persone"] = $(this).val();
            searchForPiatto();
        }
    });

    // FILTRO INIZIALE
    $("#lettere").on("change", function () {
        var lettera = $(this).val();
        filtri_["iniziale"] = (lettera === "none") ? "" : lettera;
        searchForPiatto();
    });

});

function searchForPiatto() {
    aggiornaUrlStatoPiatto();

    var piatto = $("#searchBarPiatto").val() ? $("#searchBarPiatto").val().trim() : "";
    $("#searched").html(piatto.toUpperCase());
    
    var parole = piatto.trim().split(" ");
    piatto = "";
    for (i = 0; i < parole.length; i++) {   //modifica dovuta alla struttura del database
        if (i != parole.length - 1 && parole[i].length > 0) {
            piatto += parole[i].trim() + " ";
            
        }
        else {
            piatto += parole[i].trim();
        }
    }

    console.log("piatto: " + piatto);
    var data = {
        "action": "search-piatto",
        "piatto": piatto,
        "filtri": JSON.stringify(filtri_)
    }

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../php/queries.php",
        data: data,
        success: function (data) {
            stampaRisultati(data);

            $("#accordion").removeClass("d-none").fadeIn();
            $("#ricette").removeClass("d-none").hide().fadeIn();
        },
        error: function () {
            console.error("Errore AJAX:", error);
            alert("ERROR");
        }
    });
}

function stampaRisultati(data) {

    $("#ricette").html("");

    var ricette = {};
    ricette["nome"] = JSON.parse(data["nome"]);
    ricette["tipo_piatto"] = JSON.parse(data["tipo_piatto"]);
    ricette["ing_principale"] = JSON.parse(data["ing_principale"]);
    ricette["persone"] = JSON.parse(data["persone"]);
    ricette["note"] = JSON.parse(data["note"]);
    ricette["ingredienti"] = JSON.parse(data["ingredienti"]);
    ricette["preparazione"] = JSON.parse(data["preparazione"]);
    
    if (data["autore_username"]) {
        ricette["autore_username"] = JSON.parse(data["autore_username"]);
    } else {
        ricette["autore_username"] = [];
    }

    var count = ricette.nome ? ricette.nome.length : 0;

    for (var i = 0; i < count; i++) {
        var autore = (ricette.autore_username && ricette.autore_username[i]) ? ricette.autore_username[i] : "HTMeaL";
        var ingredientiFormatted = ricette.ingredienti[i].split('+').join('<br>');

        var cardHtml = `
            <div class="card card-ricetta mb-3">
                <div class="card-header d-flex justify-content-between align-items-center" id="heading${i}">
                    <h6 class="mb-0 w-100 d-flex justify-content-between align-items-center">
                        <button class="btn collapsed btn-filtri filtri_text text-left" 
                                data-toggle="collapse" 
                                data-target="#sotto${i}" 
                                aria-expanded="false" 
                                aria-controls="sotto${i}">
                            ${ricette.nome[i]}
                        </button>
                        <span class="badge badge-secondary p-2 ml-2" style="font-size: 0.8em; font-weight: normal;">
                            di <strong>${autore}</strong>
                        </span>
                    </h6>
                </div>
                <div id="sotto${i}" class="collapse" aria-labelledby="heading${i}" data-parent="#ricette">
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
    if (count === 0) {
        $("#ricette").append('<div id="ricerca_fallita" class="alert alert-warning text-center">Ci dispiace ma la tua ricerca non ha prodotto alcun risultato!</div>');
    }

    $("#num_risultati").html(count + " risultati");

}

$("input[name='tp']").on("change", function () {
    if (this.checked) {
        // Aggiorna l'oggetto globale filtri_ 
        filtri_["tipo_piatto"] = $(this).val();
        searchForPiatto();
    }
});

function aggiornaUrlStatoPiatto() {
    if(!isNaviga) {
        var params = new URLSearchParams();

        var testoCercato = $("#searchBarPiatto").val().trim();

        if (testoCercato !== "") {
            params.set("q", testoCercato);
        }

        if (filtri_["tipo_piatto"]) params.set("tp", filtri_["tipo_piatto"]);
        if (filtri_["persone"]) params.set("np", filtri_["persone"]);
        if (filtri_["iniziale"]) params.set("ini", filtri_["iniziale"]);

        var newUrl = window.location.pathname + (params.toString() ? "?" + params.toString() : "");


        window.history.pushState({ path: newUrl }, '', newUrl);
    }
    isNaviga = false;
}

function caricaStatoDaUrlPiatto() {
    var params = new URLSearchParams(window.location.search);

    var qParam = params.get("q");
    var tp = params.get("tp");
    var np = params.get("np");
    var ini = params.get("ini");


    if (qParam) {
        var testo = decodeURIComponent(qParam.replace(/\+/g, ' '));
        $("#searchBarPiatto").val(testo);
    } else {
        $("#searchBarPiatto").val(""); 
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

    if (qParam || tp || np || ini) {
        searchForPiatto();
    } else {
        $("#ricette").empty().addClass("d-none");
        $("#accordion").addClass("d-none");

        isNaviga = false;
    }
}

/*###########################################################################################################################*/