<!-- smistamento dei dati nelle rispettive table del db -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>sensors</title>
</head>
<body>
    <?php
        include('Noti.php');

        $day_time=date("Y/m/d-h:i:s");
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
        if(isset($_POST['key'])){
            if($_POST['key']=='temperatura'){
                if(isset($_POST['temp']) && isset($_POST['hum'])){
                    $temperatura=$_POST['temp'];
                    $umidità=$_POST['hum'];
                }
                $query="INSERT INTO `Termometro`(`Temperatura (C°)`, `Umidità (%)`, `Data/ora`) VALUES ($temperatura, $umidità, '$day_time')";
                $result=mysqli_query($conn,$query);

                if($temperatura > 35.00 && date("h") == "12"){
                    notify("temperatura alta","la temperatura registrata alle 12:00 è di ".$temperatura." C°, si consiglia di rimanere idratati e di non uscire nelle ore più calde della giornata");
                } else if($temperatura < 5.00 && date("h") == "12"){
                    notify("temperatura bassa","la temperatura registrata alle 12:00 è di ".$temperatura." C°, si consiglia di coprirsi adeguatamente quando si esce di casa");
                }

                if($result){
                    echo "success";
                }else{
                    echo "failure";
                }
            }else if($_POST['key']=='luci'){
                if(isset($_POST['idlamp']) && isset($_POST['stato'])){
                    $lampione = $_POST['idlamp'];
                    $status = $_POST['stato'];
                }

                $query="UPDATE `Luce` SET `status`=$status WHERE `n lampione`=$lampione";
                $result=mysqli_query($conn,$query);

            }else if($_POST['key']=='spazzatura'){
                if(isset($_POST['ids']) && isset($_POST['fill'])){
                    $identificatore=$_POST['ids'];
                    $riempimento=$_POST['fill'];
                }

                $query="UPDATE `spazzatura` SET `% riempimento`=$riempimento WHERE `ID`=$identificatore";
                $result=mysqli_query($conn,$query);
            }
        }

    ?>
</body>
</html>
