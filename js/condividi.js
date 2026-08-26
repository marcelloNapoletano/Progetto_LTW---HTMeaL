function aggiungiRigaIngrediente(quantitaVal = '', ingredienteVal = '') {
    var rigaHtml = `
        <div class="row riga-ingrediente mb-2 align-items-center">
            <div class="col-md-4 col-12 mb-1 mb-md-0">
                <input type="text" class="form-control ing-quantita" placeholder="Quantità (es. 100g, Qb)" value="${quantitaVal}">
            </div>
            <div class="col-auto text-center font-weight-bold d-none d-md-block">
                ====
            </div>
            <div class="col-md-4 col-10">
                <input type="text" class="form-control ing-nome" placeholder="Ingrediente (es. Farina 00)" value="${ingredienteVal}">
            </div>
            <div class="col-auto pl-0">
                <button type="button" class="btn btn-danger btn-sm rimuovi-ingrediente" onclick="rimuoviRigaIngrediente(this)">
                    &times;
                </button>
            </div>
        </div>
    `;
    $("#lista_ingredienti_container").append(rigaHtml);
}

function rimuoviRigaIngrediente(btn) {
    if ($(".riga-ingrediente").length > 1) {
        $(btn).closest(".riga-ingrediente").remove();
    } else {
        alert("Devi inserire almeno un ingrediente!");
    }
}

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
}

function validaForm() {
    var nome = $("#nome").val().trim();
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

    var ing_principale = $("#ing_principale").val().trim();
    if (ing_principale == "") {
        alert("Errore: inserire l'ingrediente principale!");
        return false;
    }

    var persone = $("#persone").val().trim();
    if (persone == "" || isNaN(persone)) {
        alert("Errore: inserire un numero valido per le persone!");
        return false;
    }

    var note = $("#note").val().trim();

    var ingredientiArray = [];
    var erroriIngredienti = false;

    $(".riga-ingrediente").each(function () {
        var qta = $(this).find(".ing-quantita").val().trim();
        var ing = $(this).find(".ing-nome").val().trim();

        if (ing === "") {
            erroriIngredienti = true;
            return false; // Esci dal ciclo
        }

        ing = ing.charAt(0).toUpperCase() + ing.slice(1);

        if (qta === "") {
            qta = "Qb";
        }

        ingredientiArray.push(qta + " ==== " + ing);
    });

    if (erroriIngredienti || ingredientiArray.length === 0) {
        alert("Errore: inserisci il nome di tutti gli ingredienti aggiunti!");
        return false;
    }

    var ingredientiStringa = ingredientiArray.join("+");

    var preparazione = $("#preparazione").val().trim();
    if (preparazione == "") {
        alert("Errore: inserire la preparazione!");
        return false;
    }

    nome = nome.charAt(0).toUpperCase() + nome.slice(1);
    ing_principale = ing_principale.charAt(0).toUpperCase() + ing_principale.slice(1);
    if (note !== "") note = note.charAt(0).toUpperCase() + note.slice(1);
    preparazione = preparazione.charAt(0).toUpperCase() + preparazione.slice(1);

    $("#nome").val("");
    $("#tipo_piatto").val("none");
    $("#ing_principale").val("");
    $("#persone").val("");
    $("#note").val("");
    $("#preparazione").val("");
    $("#lista_ingredienti_container").empty();
    aggiungiRigaIngrediente();

    inviaForm(nome, tipo_piatto, ing_principale, persone, note, ingredientiStringa, preparazione);
}

$(document).ready(function () {
    $("#invio_result").hide();
    $("#back").hide();

    $("body").fadeIn(1000);

    aggiungiRigaIngrediente();

    $("#back").on("click", function () {
        $("#invio_result").hide();
        $("#back").hide();
        $("#form_ricetta").fadeIn(400);
    });
});