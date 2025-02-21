<?php

date_default_timezone_set("America/Lima");
//require_once (dirname(dirname(dirname(__FILE__))).'/Classes/class.phpmailer.php');
include_once __DIR__ . '/PHPMailer/class.phpmailer.php';

class EmailEnvioUtil {

    //Variables para el envio SMTP
    private $host_s = "email-smtp.us-east-2.amazonaws.com";
    private $user_s = "AKIAQJZZ7NDRKUCYP3YB";
    private $pass_s = "BAIf+43OnXJC3l3rZJMlE6ZUvmTrooXjjsjpomLcJrPC";
    private $cert_s = "tls";
    private $port_s = 587;
    private $from = "notificaciones@itecsac.com";
    private $fromN = "ABC MULTISERVICIOS GENERALES S.A.C.";

    public function envio($to, $cc = NULL,$bcc, $subject, $body, $attachString = NULL, $attachFilename = NULL) {
          // $body='Para: '.$to.' <br>cc: '.$cc.' <br>bcc: '.$bcc.' <br>'.$body;
         // $to='erubio;';
//         $to='nleon;;';
       // $bcc='nleon;;';
        
        $bcc=null;
		
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = $this->host_s;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = $this->cert_s;
        $mail->Port = $this->port_s;

        $mail->Username = $this->user_s;
        $mail->Password = $this->pass_s;
        $mail->From = $this->from;
        $mail->FromName = $this->fromN;
        $prefix = "";

        if (is_string($to) && strlen($to) > 0) {
            $to = explode(";", $to);
        }
        if (!is_null($to) && is_array($to)) {
            $to = array_unique($to);
        } else {
            return array("status" => 0, "mensaje" => "No se especifico correctamente el to");
        }
        
        if(is_string($cc) && strlen($cc) > 0)
        {
            $cc = explode(";", $cc);
        }
        if(!is_null($cc) && is_array($cc))
        {
            $cc = array_unique($cc);
        }
//        else
//        {
//            return array("status"=>0,"mensaje"=>"No se especifico correctamente el CC");
//        }
        
        if(is_string($bcc) && strlen($bcc)>0)
        {
            $bcc = explode(";", $bcc);
        }
        if(!is_null($bcc) && is_array($bcc))
        {
            $bcc = array_unique($bcc);
        }
        //$mail->AddBCC('ccabanillas');
//        else
//        {
//            return array("status"=>0,"mensaje"=>"No se especifico correctamente el BCC");
//        }

        foreach ($to as $key => $value) {
            $mail->AddAddress($value);
        }
        if(is_array($cc)){
        foreach($cc  as $key=> $value)
        {
            $mail->AddCC($value);
        }
        }
        if(is_array($bcc)){
        foreach($bcc as $key=> $value)
        {
            $mail->AddBCC($value);
        }
        }
        
        if (!is_null($cc)) {
            $cc = array_unique($cc);
            foreach ($cc as $key => $value) {
                $mail->AddCC($cc[$key]);
            }
        }
        $mail->WordWrap = 90;
        if (!is_null($attachString))
            $mail->AddAttachment($attachString, $attachFilename);
        if (is_null($attachString) && !is_null($attachFilename)) {
            if (is_array($attachFilename)) {
                foreach ($attachFilename as $f) {
                    $mail->AddAttachment($f);
                }
            } else
                $mail->AddAttachment($attachFilename);
        }
        
//        $mail->AddAttachment($attachString, $attachFilename);

        $mail->IsHTML(true);

        $mail->Subject = $prefix . $subject;
        $mail->Body = $body;
        if (!$mail->Send()) {
            return array("status" => 0, "mensaje" => $mail->ErrorInfo);
        } else {
            return array("status" => 1);
        }
    }

}
