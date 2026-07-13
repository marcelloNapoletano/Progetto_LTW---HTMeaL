/*###########################################################################################################################*/

//CREA FORM INGREDIENTI

function creaIngredienti(){
    var ingrediente = document.createElement("input");
    ingrediente.setAttribute("type", "text");
    ingrediente.setAttribute("class", "form-control");
    ingrediente.setAttribute("placeholder", "Inserisci ingrediente...");
    
    var quantita = document.createElement("input");
    quantita.setAttribute("type", "text");
    quantita.setAttribute("class", "form-control");
    quantita.setAttribute("placeholder", "Inserisci quantità...");
    
    $("#ingredienti").append(ingrediente);
    $("#ingredienti").append(quantita);
};

/*###########################################################################################################################*/

//INVIA FORM 

function inviaForm(nome, tipo_piatto, ing_principale, persone, note, ingredienti, preparazione) {

    var data = {
        "nome": nome,
        "tipo_piatto": tipo_piatto,
        "ing_principale": ing_principale,
        "persone": persone,
        "note": note,
        "ingredienti": ingredienti,
        "preparazione": preparazione
    };

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../php/insert.php",
        data: data,
        success: function (data) {
            if (data == "success") {
                $("#form_ricetta").hide();

                $("#invio_result").html("Ricetta inviata con successo! Grazie per la collaborazione!");

                $("#invio_result").fadeIn(400);
                $("#back").fadeIn(400);
            }
        },
        error: function () {
            $("#form_ricetta").hide();

            $("#invio_result").html("Ops...qualcosa è andato storto! Riprova ad inviare la ricetta :)");

            $("#invio_result").fadeIn(400);
            $("#back").fadeIn(400);
        }
    });
};

/*###########################################################################################################################*/

//VALIDA FORM

function validaForm() {
    var nome = $("#nome").val();
    if (nome == "") {
        alert("Errore: inserire il nome!");
        return false;
    }
    if (!isNaN(nome)) {
        alert("Errore: il nome non può essere un numero!");
        $("#nome").val("");
        return false;
    }


    var tipo_piatto = $("#tipo_piatto").val();
    if (tipo_piatto == "none") {
        alert("Errore: inserire il tipo di piatto!");
        return false;
    }
    if (!isNaN(tipo_piatto)) {
        alert("Errore: il tipo del piatto non può essere un numero!");
        $("#tipo_piatto").val("");
        return false;
    }


    var ing_principale = $("#ing_principale").val();
    if (ing_principale == "") {
        alert("Errore: inserire l'ingrediente principale!");
        return false;
    }
    if (!isNaN(ing_principale)) {
        alert("Errore: l'ingrediente principale non può essere un numero!");
        $("#ing_principale").val("");
        return false;
    }


    var persone = $("#persone").val();
    if (persone == "") {
        alert("Errore: inserire il numero di persone!");
        return false;
    }
    if (isNaN(persone)) {
        alert("Errore: persone deve essere un numero!");
        $("#persone").val("");
        return false;
    }


    var note = $("#note").val();


    var ingredienti = $("#ingredienti").val();
    if (ingredienti == "") {
        alert("Errore: inserire gli ingredienti!");
        return false;
    }


    var preparazione = $("#preparazione").val();
    if (preparazione == "") {
        alert("Errore: inserire la preparazione!");
        return false;
    }
    if (!isNaN(preparazione)) {
        alert("Errore: la preparazionen non deve essere un numero!");
        $("#preparazione").val("");
        return false;
    }

    $("#nome").val("");
    $("#tipo_piatto").val("none");
    $("#ing_principale").val("");
    $("#persone").val("");
    $("#note").val("");
    $("#ingredienti").val("");
    $("#preparazione").val("");

    nome = nome.charAt(0).toUpperCase() + nome.slice(1, nome.length);
    ing_principale = ing_principale.charAt(0).toUpperCase() + ing_principale.slice(1, ing_principale.length);
    note = note.charAt(0).toUpperCase() + note.slice(1, note.length);
    //ingredienti = ingredienti.charAt(0).toUpperCase() + ingredienti.slice(1, ingredienti.length);
    preparazione = preparazione.charAt(0).toUpperCase() + preparazione.slice(1, preparazione.length);

    inviaForm(nome, tipo_piatto, ing_principale, persone, note, ingredienti, preparazione);
};


/*###########################################################################################################################*/

//READY

$(document).ready(function () {
    $(window).scrollTop();

    $("#invio_result").hide();
    $("#back").hide();

    $("body").fadeIn(1000);
    creaIngredienti();

    $("#back").on("click", function () {
        $("#invio_result").hide();
        $("#back").hide();

        $("#form_ricetta").fadeIn(400);
    });
});

/*###########################################################################################################################*/