/*###########################################################################################################################*/

var ricetteProcessate = [];
//ESEGUE RICERCA PER RICETTE RANDOM PASSANDO PARAMETRI A PHP

function searchAll() {
    var data = {
        "action": "search-all",
    }
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../php/queries.php",
        data: data,
        success: function (data) {
            stampaRisultati(data);
        },
        error: function () {
            alert("ERROR");
        }
    });
}

/*###########################################################################################################################*/

//ELABORA RISULTATI QUERY PER RICETTE RANDOM

function stampaRisultati(data) {

    var ricette = [];
    ricette["nome"] = JSON.parse(data["nome"]);
    ricette["tipo_piatto"] = JSON.parse(data["tipo_piatto"]);
    ricette["ing_principale"] = JSON.parse(data["ing_principale"]);
    ricette["persone"] = JSON.parse(data["persone"]);
    ricette["note"] = JSON.parse(data["note"]);
    ricette["ingredienti"] = JSON.parse(data["ingredienti"]);
    ricette["preparazione"] = JSON.parse(data["preparazione"]);

    var nomi = ricette["nome"];
    var tipo_piatto = ricette["tipo_piatto"];
    var ing_principale = ricette["ing_principale"];
    var persone = ricette["persone"];
    var ingredienti = ricette["ingredienti"];
    var preparazione = ricette["preparazione"];
    var random;
    var ricetta;
    var tmp = "";

    ricetteProcessate = [];

    for (i = 1; i <= 6; i++) {
        ricetta = $("#r" + i);
        random = Math.floor(Math.random() * nomi.length);
        ricetta.find("#h" + i).html(nomi[random]);
        tmp_array = ingredienti[random].split('+');
        tmp_string = "Tipo Piatto: " + tipo_piatto[random] + "<br>" +
            "Ingrediente Principale: " + ing_principale[random] + "<br>" +
            "Persone: " + persone[random] + "<br><br>" +
            "Ingredienti: " + "<br>";
        for (k = 0; k < tmp_array.length; k++) {
            tmp_string = tmp_string + tmp_array[k] + "<br>";
        }
        tmp_string = tmp_string + "<br>" + "Preparazione: " + "<br>" + preparazione[random];
        
        ricetteProcessate.push({
            nome: nomi[random],
            dettagli: tmp_string
        });
    }
}

/*###########################################################################################################################*/

//READY

$(document).ready(function () {
    $(window).scrollTop();
    
    $("#close").hide();
    $("#ricetta-random-nome").hide();
    $("#ricetta-random").hide();

    searchAll();

    $("body").fadeIn(1000);

    $(".classic").on("click", function () {
        var index = $(this).data("index");

        if (ricetteProcessate[index]) {
            $("#cover").hide();
            $(".container").hide();

            $("#ricetta-random-nome").html(ricetteProcessate[index].nome);
            $("#ricetta-random").html(ricetteProcessate[index].dettagli);

            $(window).scrollTop();

            $("#close").fadeIn(500);
            $("#ricetta-random-nome").fadeIn(500);
            $("#ricetta-random").fadeIn(500);
        }
    });

    //CHIUDE RICETTA RANDOM E TORNA A HOME SENZA RICARICARE PAGINA

    $("#close").on("click", function () {
        $("#close").hide();
        $("#ricetta-random-nome").hide();
        $("#ricetta-random").hide();
        
        $("#cover").fadeIn(500);
        $(".container").fadeIn(500);
    });
});

/*###########################################################################################################################*/