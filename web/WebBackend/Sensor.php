<!-- smistamento dei dati nelle rispettive table del db -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>sensors</title>
</head>
<body>
    <?php
    //include file sul quale è stata definita la funzione notify() che invia una notifica pushover ai residenti
        include('Noti.php');

    //la funzione date() chiede come parametro la sintassi della data che si vuole utilizzare
    //e ritorna la data nel formato desiderato in questo caso è una data del tipo
    //                                  (2020/01/12-04:12:02)
        $day_time=date("Y/m/d-H:i:s");
    //setup della connesione al DB
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FutureLiving";
        $conn = mysqli_connect($servername, $username, $password,$dbname);
    //controllo errori di connessione
        if (!$conn) {
            die("Connessione fallita: " . mysqli_connect_error());
        }
        echo "Connessione riuscita <br>";
        //controlla se la chiave è stata inviata
        if(isset($_POST['key'])){
            //serie di if per il controllo del tipo di dato inviato
            if($_POST['key']=='temperatura'){
                if(isset($_POST['temp']) && isset($_POST['hum'])){
                    $temperatura=$_POST['temp'];
                    $umidità=$_POST['hum'];
                }
                //inserisce temperatura umidità e la data/ora in cui è avvenuta la rilevazione
                $query="INSERT INTO `Termometro`(`Temperatura (C°)`, `Umidità (%)`, `Data/ora`) VALUES ($temperatura, $umidità, '$day_time')";
                $result=mysqli_query($conn,$query);

                //se la temperatura va oltre una certa soglia o al di sotto di un altra manda
                //una notifica di allerta ai residenti dando consigli per evitare problemi
                //relativi alla temperatura
                if($temperatura > 35.00 && date("H") == "12"){
                    notify("temperatura alta","la temperatura registrata alle 12:00 è di ".$temperatura." C°, si consiglia di rimanere idratati e di non uscire nelle ore più calde della giornata","Residente");
                } else if($temperatura < 5.00 && date("H") == "12"){
                    notify("temperatura bassa","la temperatura registrata alle 12:00 è di ".$temperatura." C°, si consiglia di coprirsi adeguatamente quando si esce di casa","Residente");
                }

            }else if($_POST['key']=='luci'){
                if(isset($_POST['idlamp']) && isset($_POST['stato'])){
                    $lampione = $_POST['idlamp'];
                    $status = $_POST['stato'];
                }

                //aggiorna lo stato dei lampioni
                $query="UPDATE `Luce` SET `status`=$status WHERE `ID lamp`=$lampione";
                $result=mysqli_query($conn,$query);

            }else if($_POST['key']=='spazzatura'){
                if(isset($_POST['ids']) && isset($_POST['fill'])){
                    $identificatore=$_POST['ids'];
                    $riempimento=$_POST['fill'];
                }

                //aggiorna lo stato dei cassonetti (in questo caso solo uno per mancanza di sensori)
                $query="UPDATE `spazzatura` SET `% riempimento`=$riempimento WHERE `ID`=$identificatore";
                $result=mysqli_query($conn,$query);
            }
        }

    ?>
</body>
</html>
