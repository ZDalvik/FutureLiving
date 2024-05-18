<!-- prova per salvataggio dati temperatura in un database (non abbiamo ancora il progetto di un database)
    il codice risulta del tutto funzionante, naturalmente non è per niente il codice finale ma sicuramente un punto di partenza -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>temperatura</title>
</head>
<body>
    <?php

    //setup della connesione al DB
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "TeMpO";
        $conn = mysqli_connect($servername, $username, $password,$dbname);
    //controllo errori di connessione
        if (!$conn) {
            die("Connessione fallita: " . mysqli_connect_error());
        }
        echo "Connessione riuscita <br>";
    //inizializzazione dati ricevuti via post dall'ESP32 (successivamente la parte decisionale avverra anche tramite api_key)
        if(isset($_POST['temp']) && isset($_POST['hum'])){

            $temp=$_POST['temp'];
            $umidita=$_POST['hum'];

        }
    //attuazione query di inserimento (mysqli_query cambia tipo di ritorno in base alla query, in questo caso è un boolean)
        $query= "INSERT INTO `meteo`(`Temperatura`, `Umidità`) VALUES ('$temp','$umidita')";
        $result=mysqli_query($conn,$query);
        if($result){
            echo "success";
        }else{
            echo "unsuccess";
        }
    ?>
</body>
</html>
