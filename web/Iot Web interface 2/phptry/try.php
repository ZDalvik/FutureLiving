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

    echo "<h1>".$max[0]."</h1><br>";
    
    }
?>