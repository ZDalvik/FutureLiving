<?php
/**
 * @author Chris Schalenborgh <chris.s@kryap.com>
 * @version 0.1
 */

include('Pushover.php');

if(isset($_POST['']) && isset($_POST['']) && isset($_POST[''])){




    $push = new Pushover();
    $push->setToken('aqy6bkr4c7aqucpec1743nsj38g2ce');
    $push->setUser('uwemowyqrd7ogguo8csr2fg4fo6hxu');

    $push->setTitle('');
    $push->setMessage('' .time());
    $push->setUrl('');
    $push->setUrlTitle('');

    $push->setDevice('Residente');
    $push->setPriority(2);
    $push->setRetry(60); //Used with Priority = 2; Pushover will resend the notification every 60 seconds until the user accepts.
    $push->setExpire(3600); //Used with Priority = 2; Pushover will resend the notification every 60 seconds for 3600 seconds. After that point, it stops sending notifications.
    $push->setCallback('http://chris.schalenborgh.be/');
    $push->setTimestamp(time());
    $push->setDebug(true);
    $push->setSound('bike');

    $go = $push->send();

    $receipt = $push->getReceipt();

    echo '<pre>';
    print_r($go);
    print "Receipt: $receipt\n";
    echo '</pre>';
}
?>