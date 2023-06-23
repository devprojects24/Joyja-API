<?php 

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Smtp_mail_model extends CI_model {

    function __construct() {
        parent::__construct();
      require APPPATH . 'third_party/smtp/PHPMailerAutoload.php';
        $this->load->database();
    }

  function PHPMailesend(){
    
    $smtpcode = $this->db->get_where('smtp_mail',array('id'=>'1'))->row();
    
    $mail = new PHPMailer(); 
    $mail->SMTPDebug = 0;
    $mail->IsSMTP(); 
    $mail->SMTPAuth = true; 
    $mail->Host = $smtpcode->hostname;
    $mail->Port = $smtpcode->port; 
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = $smtpcode->username;
    $mail->Password = $smtpcode->password;
    $mail->SetFrom($smtpcode->send_email);
    $mail->FromName = $smtpcode->project_name;
    $mail->SMTPOptions=array('ssl'=>array(
      'verify_peer'=>false,
      'verify_peer_name'=>false,
      'allow_self_signed'=>false
    ));
    return $mail;
  }
  
  
}

?>