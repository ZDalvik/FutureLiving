<?php


 //libreria che permette un invio di notifiche pushover in modo semplice
include('Pushover.php');

//funzione per notificare su pushover
function notify($titolo, $messaggio, $dispositivo){

    $push = new Pushover();
    $push->setToken('aqy6bkr4c7aqucpec1743nsj38g2ce');
    $push->setUser('uwemowyqrd7ogguo8csr2fg4fo6hxu');

    $push->setTitle($titolo);
    $push->setMessage($messaggio);
    $push->setUrl('');
    $push->setUrlTitle('');

    $push->setDevice($dispositivo);
    $push->setPriority(0);
    $push->setTimestamp(time());
    $push->setDebug(true);
    $push->setSound('bike');

    $go = $push->send();

    $receipt = $push->getReceipt();

    echo '<pre>';
    print_r($go);
    //la receipt serve in caso di notifica di priorità 2 che va accettata dal ricevente
    print "Receipt: $receipt\n";
    echo '</pre>';
}
?>