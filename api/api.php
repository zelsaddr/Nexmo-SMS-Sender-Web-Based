<?php
require "./function.php";
if(isset($_POST['nomor']) && isset($_POST['from']) && isset($_POST['message']) && isset($_POST['challenge'])){
    $mztr_sms = new mazterin_sms($_POST['nomor'], $_POST['from'], $_POST['message'], $_POST['challenge']);
    echo $mztr_sms->kirim();
}else{
    header("Location: ".mazterin_sms::base_url()."");
}
?>