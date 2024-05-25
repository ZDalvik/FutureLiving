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
                        La temperatura attuale è di: ""
                    <br/><br/>
                        Massima raggiunta negli scorsi 7 giorni è: "<?php getTempMax() ?>"
                    <br/><br/>
                        Minima raggiunta negli ultimi 7 giorni è: ""
                    <br/><br/>
                        L'umidità è al: ""
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
                        L'illuminazione attualmente è: ""
                    <br/><br/>
                        Ora prevista per il tramonto: ""
                    <br/><br/>
                        Ora prevista per l'alba: ""
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
                        LA MONEZZA
                    <br/><br/>
                        LA MONEZZA
                    <br/><br/>
                        DARK PAKY
                    </p>

                    </div>

                </div>
                
                <br/><br/>
                
            </div>
        </div>
    </body>
</html>

<?php
    function getTempMax(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "FutureLiving";
    $conn = mysqli_connect($servername, $username, $password,$dbname);

    $temps = array();
    $oggi = date("Y-m-d");
    $query = "SELECT MAX(`Temperatura (C°)`) FROM `Termometro` WHERE SUBSTRING(`Data/ora`,1,10)='$oggi'";
    $result = mysqli_query($conn,$query);
    $max = mysqli_fetch_array($result);
/*
    $query = "SELECT MIN(`Temperatura (C°)`) FROM `Termometro` WHERE SUBSTRING(`Data/ora`,1,10)='$oggi'";
    $result=mysqli_query($conn,$query);
    $min = mysqli_fetch_array($result);
    echo "<h1>".$min[0]."</h1><br>";
*/

    echo "".$max[0]."";
    
    }
?>

