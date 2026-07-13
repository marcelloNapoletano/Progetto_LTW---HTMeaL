<?php
    
   if (is_ajax()) {
        if (isset($_POST["action"]) && !empty($_POST["action"])) { //Checks if action value exists
            $action = $_POST["action"];
            switch($action) { //Switch case for value of action
                case "search-piatto":       ricercaPiatto(); break;
                case "search-ingredienti":  ricercaIngredienti(); break;
                case "search-all":          ricercaTutto(); break;
            }
        }
    }

    //Function to check if the request is an AJAX request
    function is_ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    function ricercaPiatto(){
        $piatto = $_POST["piatto"];
        $filtri = json_decode($_POST["filtri"], true);
        $filtri_query = "";
        $tipo_piatto = $filtri["tipo_piatto"];
        if ($tipo_piatto != "") {
            $filtri_query = $filtri_query . "and tipo_piatto = '$tipo_piatto' ";
        }
        $persone = $filtri["persone"];
        if ($persone != "") {
            if ($persone == "10") {
                $filtri_query = $filtri_query . "and persone >= $persone ";
            }
            else {
                $filtri_query = $filtri_query . "and persone = $persone ";
            }
        }
        $iniziale = $filtri["iniziale"];
        if ($iniziale != "none") {
            $filtri_query = $filtri_query . "and nome LIKE '$iniziale%' ";
        }
        $dbconn =   pg_connect("host = localhost port = 5432 dbname = Ricette user = postgres password = 0112") or die("Could not connect: " . pg_last_error());
        $query  =   "SELECT * FROM Ricette WHERE upper(nome) LIKE upper('%$piatto%') " . $filtri_query;            
        $result =   pg_query($query) or die("Query failed:" . pg_last_error());
        pg_close($dbconn);
        $return["nome"]             = [];
        $return["tipo_piatto"]      = []; 
        $return["ing_principale"]   = [];  
        $return["persone"]          = []; 
        $return["note"]             = []; 
        $return["ingredienti"]      = []; 
        $return["preparazione"]     = []; 
        while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            array_push($return["nome"],             $row["nome"]);
            array_push($return["tipo_piatto"],      $row["tipo_piatto"]); 
            array_push($return["ing_principale"],   $row["ing_principale"]);  
            array_push($return["persone"],          $row["persone"]); 
            array_push($return["note"],             $row["note"]); 
            array_push($return["ingredienti"],      $row["ingredienti"]); 
            array_push($return["preparazione"],     $row["preparazione"]); 
        }
        pg_free_result($result);
        $return["nome"]             = json_encode($return["nome"]);
        $return["tipo_piatto"]      = json_encode($return["tipo_piatto"]); 
        $return["ing_principale"]   = json_encode($return["ing_principale"]);  
        $return["persone"]          = json_encode($return["persone"]); 
        $return["note"]             = json_encode($return["note"]); 
        $return["ingredienti"]      = json_encode($return["ingredienti"]);
        $return["preparazione"]     = json_encode($return["preparazione"]);
        echo json_encode($return);
    }

    function ricercaIngredienti() {
        $lista_ingredienti = json_decode($_POST["ingredienti"]);
        $filtri = json_decode($_POST["filtri"], true);
        $query = "SELECT * FROM Ricette WHERE ";
        for($i = 0; $i < sizeof($lista_ingredienti); $i++) {
            if ($i == 0) {
                $tmp = $lista_ingredienti[$i];
                $query = $query . "upper(ingredienti) LIKE upper('%$tmp%') ";
            }
            else {
                $tmp = $lista_ingredienti[$i];
                $query = $query . "and upper(ingredienti) LIKE upper('%$tmp%') ";
            }
        }
        $filtri_query = "";
        $tipo_piatto = $filtri["tipo_piatto"];
        if ($tipo_piatto != "") {
            $filtri_query = $filtri_query . "and tipo_piatto = '$tipo_piatto' ";
        }
        $persone = $filtri["persone"];
        if ($persone != "") {
            if ($persone == "10+") {
                $filtri_query = $filtri_query . "and (";
                for ($i = 10; $i < 50; $i++) {
                    if ($i == 10) {
                        $filtri_query = $filtri_query . "persone = '$i' ";
                    }
                    else {
                        $filtri_query = $filtri_query . "or persone = '$i'";
                    }
                }
                $filtri_query = $filtri_query . ")";

            }
            else {
                $filtri_query = $filtri_query . "and persone = '$persone' ";
            }
        }
        $iniziale = $filtri["iniziale"];
        if ($iniziale != "") {
            $filtri_query = $filtri_query . "and nome LIKE '$iniziale%' ";
        }
        $query  = $query . $filtri_query;
        $dbconn =   pg_connect("host = localhost port = 5432 dbname = Ricette user = postgres password = 0112") or die("Could not connect: " . pg_last_error());      
        $result =   pg_query($query) or die("Query failed:" . pg_last_error());
        pg_close($dbconn);
        $return["nome"]             = [];
        $return["tipo_piatto"]      = []; 
        $return["ing_principale"]   = [];  
        $return["persone"]          = []; 
        $return["note"]             = []; 
        $return["ingredienti"]      = []; 
        $return["preparazione"]     = []; 
        while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            array_push($return["nome"],             $row["nome"]);
            array_push($return["tipo_piatto"],      $row["tipo_piatto"]); 
            array_push($return["ing_principale"],   $row["ing_principale"]);  
            array_push($return["persone"],          $row["persone"]); 
            array_push($return["note"],             $row["note"]); 
            array_push($return["ingredienti"],      $row["ingredienti"]); 
            array_push($return["preparazione"],     $row["preparazione"]); 
        }
        pg_free_result($result);
        $return["nome"]             = json_encode($return["nome"]);
        $return["tipo_piatto"]      = json_encode($return["tipo_piatto"]); 
        $return["ing_principale"]   = json_encode($return["ing_principale"]);  
        $return["persone"]          = json_encode($return["persone"]); 
        $return["note"]             = json_encode($return["note"]); 
        $return["ingredienti"]      = json_encode($return["ingredienti"]);
        $return["preparazione"]     = json_encode($return["preparazione"]);
        echo json_encode($return);
    }

    function ricercaTutto() {
        $dbconn =   pg_connect("host = localhost port = 5432 dbname = Ricette user = postgres password = 0112") or die("Could not connect: " . pg_last_error());
        $query  =   "SELECT * FROM Ricette";            
        $result =   pg_query($query) or die("Query failed:" . pg_last_error());
        pg_close($dbconn);
        $return["nome"]             = [];
        $return["tipo_piatto"]      = []; 
        $return["ing_principale"]   = [];  
        $return["persone"]          = []; 
        $return["note"]             = []; 
        $return["ingredienti"]      = []; 
        $return["preparazione"]     = []; 
        while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            array_push($return["nome"],             $row["nome"]);
            array_push($return["tipo_piatto"],      $row["tipo_piatto"]); 
            array_push($return["ing_principale"],   $row["ing_principale"]);  
            array_push($return["persone"],          $row["persone"]); 
            array_push($return["note"],             $row["note"]); 
            array_push($return["ingredienti"],      $row["ingredienti"]); 
            array_push($return["preparazione"],     $row["preparazione"]); 
        }
        pg_free_result($result);
        $return["nome"]             = json_encode($return["nome"]);
        $return["tipo_piatto"]      = json_encode($return["tipo_piatto"]); 
        $return["ing_principale"]   = json_encode($return["ing_principale"]);  
        $return["persone"]          = json_encode($return["persone"]); 
        $return["note"]             = json_encode($return["note"]); 
        $return["ingredienti"]      = json_encode($return["ingredienti"]);
        $return["preparazione"]     = json_encode($return["preparazione"]);
        echo json_encode($return);
    }
?>
