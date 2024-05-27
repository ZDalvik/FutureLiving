<html>
    <head>
        <title>
            FutureLiving
        </title>
        <link href="css/FutureLiving.css" rel="stylesheet">
        <script>

            function sendGetRequest(){
    
                var url = 'http://192.168.0.107:80/15/open'
    
                var xht = new XMLHttpRequest();
    
                xht.open('GET', url, false);
    
                xht.onreadystatechange = function(){
                    if(xht.readyState == 4 && xht.status ==200){
                        alert('risposta server ' + xht.responseText);
                    }
                };
                alert("hai 15 secondi per entrare");
                xht.send();
            }
        </script>
    </head>


    <body>

        <div class="background-overlay">
            <!-- contenitore per aggiungere un'immagine di sfondo -->
            <div class="content">
                <!-- content è la classe che gestisce gli elementi all'interno del contenitore -->
                <h1 class="title">
                    FutureLiving
                </h1>
                <h3 class="subtitle">
                    La tecnologia al servizio della vita
                </h3>

                <ul class="list">
                    <li class="item"><a href="#home" class="link">Home</a></li>
                    <li class="item"><a href="#meteo" class="link">Meteo</a></li>
                    <li class="item"><a href="#illuminazione" class="link">Illuminazione</a></li>
                    <li class="item"><a href="#rifiuti" class="link">Rifiuti</a></li>
                </ul>

                <!-- SEZIONE HOME -->

                <div id="home" class="tab-content">
                    
                    <img src="rsc/img2.png">
                    
                    <br/><br/>

                    <button onclick="sendGetRequest()">CANCELLO AUTOMATICO</button>
                </div>

                <!-- SEZIONE METEO -->

                <div id="meteo" class="tab-content">

                    <br/><br/>

                    <div class="text-container">

                    <img src="rsc/meteoicon.png" class="img-icon">

                    <h2 class="overtext">Meteo</h2>
                    <p class="text">
                        La temperatura attuale è di: "<?php getLatestTemp() ?>"
                    <br/><br/>
                        Temperatura massima negli scorsi 7 giorni è: "<?php getTempMax() ?>"
                    <br/><br/>
                        Temperatura minima negli ultimi 7 giorni è: "<?php getTempMin() ?>"
                    <br/><br/>
                        L'umidità è al: "<?php GetHumidity() ?>"
                    </p>

                    </div>

                </div>

                <!-- SEZIONE ILLUMINAZIONE -->

                <div id="illuminazione" class="tab-content">
                    
                    <br/><br/>

                    <div class="text-container">

                        <img src="rsc/bulbicon.png" class="img-icon">

                    <h2 class="overtext">Illuminazione</h2>
                    <p class="text">
                        <table border="1" bgcolor="#FFFFFF" align="center">
                            <tr><th>Lampione</th><th>Stato</th></tr>
                            <?php getLights() ?>
                        </table>
                        <?php getSunrise() ?>
                        <?php getSunset() ?>
                    </p>

                    </div>

                </div>

                <!-- SEZIONE RIFIUTI -->
                
                <div id="rifiuti" class="tab-content">
                    
                    <br/><br/>

                    <div class="text-container">

                        <img src="rsc/rabbishicon.png" class="img-icon">

                    <h2 class="overtext">Rifiuti</h2>
                    <p class="text">
                        <table border="1" bgcolor="#FFFFFF" align="center">
                            <tr><th>Cassonetto</th><th>Riempimento</th></tr>
                            <?php getRubbish() ?>
                        </table>
                    </p>

                    </div>

                </div>
                
                <br/><br/>
                
            </div>
        </div>
    </body>
</html>

<?php

    //funzione per prendere la temperatura massima dal db
    function getTempMax(){
        //connession db
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password,$dbname);
        //utilizzo date per avere la data di oggi per il parametro della query WHERE
        $oggi = date("Y-m-d");
        //definizione query per selezionare la temperatura massima di oggi dalla tabella Termometro del DB FutureLiving
        //considerando solo le rilevazioni con data del giorno stesso
        $query = "SELECT MAX(`Temperatura (C°)`) FROM `Termometro` WHERE SUBSTRING(`Data/ora`,1,10)='$oggi'";
        $result = mysqli_query($conn,$query);
        //fetch dei risultati sotto forma di array indicizzato o associativo
        $max = mysqli_fetch_array($result);

        //stampo a schermo il risultato
        if($max[0]!=null){
            echo $max[0]."%";
        }else{
            echo "Dati non disponibili";
        }
        
        mysqli_close($conn);
    }

    //funzione che fa la stessa cosa della precedente ma con la temperatura minima
    function getTempMin(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password,$dbname);
        $oggi = date("Y-m-d");
        $query = "SELECT MIN(`Temperatura (C°)`) FROM `Termometro` WHERE SUBSTRING(`Data/ora`,1,10)='$oggi'";
        $result=mysqli_query($conn,$query);
        $min = mysqli_fetch_array($result);
        if($min[0]!=null){
            echo $min[0]." C°";
        }else{
            echo "Dati non disponibili";
        }

        mysqli_close($conn);
    }

    //funzione che ritorna la temperatura rilevata più recentemente
    function getLatestTemp(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password,$dbname);
        //query mySQL per selezionare l'ultimo elemento aggiunto alla tabella
        $query = "SELECT `Temperatura (C°)` FROM `Termometro` ORDER BY `Data/ora` DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        $current = mysqli_fetch_array($result);
        //controllo avvenuta selezione
        if($current!=null){
            //stampo temperatura
            echo $current[0]." C°";
        }else{
            echo "Dati non disponibili";
        }
        mysqli_close($conn);
    }
    //funzione che ritorna l'ultima rilevazione dell'umidità
    function GetHumidity(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password,$dbname);
        //query per selezionare l'ultima umidità rilevata
        $query = "SELECT `Umidità (%)` FROM `Termometro` ORDER BY `Data/ora` DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        $current = mysqli_fetch_array($result);
        if($current!=null){
            echo $current[0]."%";
        }else{
            echo "error";
        }
        
        mysqli_close($conn);
    }

    //funzione che ritorna lo stato dei lampioni del parco
    function getLights(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password,$dbname);

        //query mySQL che seleziona tutte le righe della tabella Luce del DB FutureLiving
        $query = "SELECT * FROM `Luce`";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                //se lo status nella riga è =1 allora scrive on altrimenti off
                //questo perchè il campo status è booleano
                if($row['status']==1){
                    $stato="on";
                }else{
                    $stato="off";
                }
                //stampa una riga di una tabella HTML
                echo "<tr><td>".$row['ID lamp']."</td><td>".$stato."</td></tr>";
            }
        } else {
            echo "no data";
        }
    }

    //funzione che ritorna sottoforma di riga di tabella lo stato del riempimento dei cassonetti
    function getRubbish(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password, $dbname);

        //query che seleziona tutte le righe della tabella spazzatura nel DB FutureLiving
        $query = "SELECT * FROM `spazzatura`";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0){
            //stampa la riga sottoforma di riga di tabella HTML
            while($row = mysqli_fetch_assoc($result)){
                echo "<tr><td>".$row['ID']."</td><td>".$row['% riempimento']."%</td></tr>";
            }
        } else {
            echo "no data";
        }
    }

    //funzione che ritorna con l'ausilio della funzione date_sunrise() l'orario approssimato dell'alba
    function getSunrise(){
        // Parete,Italy:
        // Latitude: 40.96 North, Longitude: 14.16 East
        // Zenith ~= 90, offset: +2 GMT

        //funzionamento di date_sunrise precisato su report
        echo("</br>Orario alba: ");
        echo(date_sunrise(time(),SUNFUNCS_RET_STRING,40.96,14.16,90,2));
        echo "</br>";
    }
    //funzione che ritorna con l'ausilio della funzione date_sunset() l'orario approssimato del tramonto
    function getSunset(){
        // Parete,Italy:
        // Latitude: 40.96 North, Longitude: 14.16 East
        // Zenith ~= 90, offset: +2 GMT

        //funzionamento di date_sunset precisato su report
        echo("</br>Orario tramonto: ");
        echo(date_sunset(time(),SUNFUNCS_RET_STRING,40.96,14.16,90,2));
        echo "</br>";
    }
?>

