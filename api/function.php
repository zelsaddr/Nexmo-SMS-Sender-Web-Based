<?php
class mazterin_sms
{
    private $NEXMO_KEY    = ""; // NEXMO API KEY
    private $NEXMO_SECRET = ""; // NEXMO API SECRET
    private $nomor;
    private $from; 
    private $message;
    private $angka;
    public function __construct($nomor, $from, $message, $challenge)
    {
        $this->nomor    = $nomor;
        $this->from     = urlencode($from);
        $this->message  = urlencode($message);
        $this->angka = explode("|", $challenge);
    }
    public function challenge()
    {
        $first  = trim($this->angka[0]);
        $second = trim($this->angka[1]);
        $hasil  = trim($this->angka[2]);
        $result = $first + $second;
        if($hasil != $result){ 
            return $this->json_builder("400", "Are you robot? Wrong Answer!"); die(); 
        }
    }
    public function kirim()
    {
        if($this->challenge() == NULL){
            if($this->validate() == NULL){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "api_key=".$this->NEXMO_KEY."&api_secret=".$this->NEXMO_SECRET."&to=".$this->nomor."&from=".$this->from."&text=".$this->message);
                curl_setopt($ch, CURLOPT_POST, 1);
                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = json_decode(curl_exec($ch), true);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                if($result['messages'][0]['status'] == "0"){
                    $this->save("balance.txt", $result['messages'][0]['remaining-balance']);
                    return $this->json_builder("200", "SMS SENT. THANK YOU!");
                }else{
                    return $this->json_builder("500", "FAILED TO SEND SMS, Try Again!");
                }
                curl_close($ch);
            }else{
                return $this->validate();
            }
        }else{
            return $this->challenge();
        }
    }
    private function validate(){
        if(strlen($this->nomor) > 15 || strlen($this->nomor) <= 8){ return $this->json_builder("500", "Number Length should be under 16 and minimum 8 number"); die(); }
        if(strlen($this->from) > 11){ return $this->json_builder("500", "From Name Length should be under 12"); die(); }
        if(strlen($this->message) < 1){ return $this->json_builder("500", "Message form should be filled."); }
    }
    public static function base_url()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? $url = "https://".$_SERVER['SERVER_NAME'] : $url = "http://".$_SERVER['SERVER_NAME'];
    }
    private function save($name, $isi){ // filename, content
        $open = fopen($name, "w");
        fwrite($open, $isi);
        fclose($open);
    }
    private function json_builder($status, $msg, array $extend = NULL)
    {
        return json_encode(
            array(
                "status"    => $status,
                "msg"       => $msg,
                $extend != NULL ? $extend : ""
            )
        );
    }
}
?>