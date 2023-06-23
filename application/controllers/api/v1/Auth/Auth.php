<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Auth extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		$this->load->model('ion_auth_model');
		$this->load->model('common_model');
		$this->load->model('Smtp_mail_model');
		$this->load->library(array('ion_auth', 'form_validation'));
		
    }
	
	
	
	function login_post(){
		$username 	= $this->post('username');
		$password 	= $this->post('password');
		if($username=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid username.' , 'data'=>'' );
		}
		else if($password=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid password.' , 'data'=>'' );
		}
		else 
		{
			$result = $this->common_model->login_new($username);
			if($result)
			{
				
				$req = array(
				'username' 	=> $username,
				'password' 	=> $password
				);
				
				$selectdata = $this->ion_auth->hash_password_db($result->id,$password);
				//echo "<pre>"; print_r($result); die;
				if($selectdata === TRUE)
				{
					$final_data['id'] = $result->id;
					$final_data['username'] = $result->username;
					$final_data['email'] = $result->email;
					$final_data['first_name'] = $result->first_name;
					$final_data['last_name'] = $result->last_name;
					$final_data['phone'] = $result->phone;
					$final_data['profile_image'] = SITEURL.$result->img_url;
					
					$response= array( 'status'=>'Ok','code'=>'200','message'=>'Login Successfully.', 'request'=> $req , 'data'=>$final_data );
				}else{
					$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid Login Credentials.' , 'data'=>'' );
				}
			}else{
				$response= array( 'status'=>'failed','code'=>'201','message'=>'Account is Not Registered.' , 'data'=>'' );
			}
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function register_post(){
		$name 		= $this->post('name');
		$email 		= $this->post('email');
		$mobile 	= $this->post('mobile');
		$password 	= $this->post('password');
		$terms 		= $this->post('terms');
		
		$chk_mobile = $this->common_model->getsingle('users',array('phone'=>$mobile));
		$chk_email  = $this->common_model->getsingle('users',array('email'=>$email));
		if($name=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid name.' , 'data'=>'' );
		}
		else if($email=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid email.' , 'data'=>'' );
		}
		else if($chk_email)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Email already exist.' , 'data'=>'' );
		}
		else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid email' , 'data'=>'' );
		}
		else if($mobile=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid mobile.' , 'data'=>'' );
		}
		else if(!preg_match('/^[0-9]{10}+$/', $mobile))
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid mobile.' , 'data'=>'' );
		}
		else if($chk_mobile)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Mobile already exist.' , 'data'=>'' );
		}
		else if($password=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid password.' , 'data'=>'' );
		}
		else if(!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) ||  !preg_match('/[^a-zA-Z0-9]/', $password) || strlen($password) < 8 )
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Password Should Consist of 1 Uppercase, 1 Lowercase, 1 Special Character 1 Number also , minium 8 characters.' , 'data'=>'' );
		}
		else if($terms=='' || $terms!=1)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid terms.' , 'data'=>'' );
		}
		else 
		{
			$newpassword	= $this->ion_auth_model->hash_password($password);	
			
			$ins_data = array(
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'username' 	=> $name,
				'phone' 	=> $mobile,
				'email' 	=> $email,
				'password' 	=> $newpassword,
				'password_view' 	=> $password,
				);
			$res = $this->common_model->register($ins_data);
			
			$req = array(
				'name' 	=> $name,
				'email' 	=> $email,
				'mobile' 	=> $mobile,
				'password' 	=> $password,
				'terms' 	=> $terms
			);
				
			$final_data['id'] = $res;
			$final_data['mobile'] = $mobile;
			$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Registered Sucessfully.', 'request'=> $req, 'data'=>$final_data);
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function generateOtp_post(){
		$mobile 	= $this->post('mobile');		
		$chk_mobile = $this->common_model->getsingle('users',array('phone'=>$mobile));
		if($mobile=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid mobile.' , 'data'=>'' );
		}
		else if(!$chk_mobile)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Mobile not exist.' , 'data'=>'' );
		}		
		else 
		{			
			$date = date('Y-m-d H:i:s A');
			$otp=rand(1000,9999);
	
			$otp_data = array(				
				'createdAt' => $date,
				'loginotp' => $otp,
				);
			$this->common_model->updateData('users',$otp_data,array('id'=>$chk_mobile->id));
			
			  $otp_log = array (
				'mobile_number' => $mobile,
				'otp' => $otp,
				'created_time' =>  $date,
			  );

			  $this->db->insert('otp_log', $otp_log);
			 $curl = curl_init();

			  curl_setopt_array($curl, array(
				CURLOPT_URL => "http://mobicomm.dove-sms.com//submitsms.jsp?user=PRIMEH&key=2881656c3cXX&mobile=$mobile&message=$otp%20is%20your%20secret%20one%20time%20password%20to%20be%20used%20on%20Proceed.Fit%20Team%20Proceed%20Fit&senderid=PRCFIT&accusage=1&entityid=1201160355958348305&tempid=1207161769274028633",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
				  'Cookie: JSESSIONID=81FB7ECCA7C336779F021F61180756F1'
				),


			  ));
			  $response = curl_exec($curl);
			  curl_close($curl); 
			  
			$msg = 'Please enter the OTP code that was sent to your phone '.substr($mobile,0, 1).'XXXXXX'.substr($mobile,7).' in the text field below to complete the verification process. The code will expire in 10 minutes.';
			
			$req = array(
				'mobile' 	=> $mobile
			);
			
			$response= array( 'status'=>'Ok','code'=>'200','message'=>$msg , 'request'=> $req, 'data'=>$req );
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function validateOtp_post(){
		$mobile 	= $this->post('mobile');
		$otp 	= $this->post('otp');		
		$chk_mobile = $this->common_model->getsingle('users',array('phone'=>$mobile));
		if($mobile=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid mobile.' , 'data'=>'' );
		}
		else if(!$chk_mobile)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Mobile not registered.' , 'data'=>'' );
		}
		else if($otp=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid otp.' , 'data'=>'' );
		}		
		else 
		{
			$loginotp	= $chk_mobile->loginotp;
			$createdAt	= $chk_mobile->createdAt;
			$convertedTime = date('Y-m-d H:i:s A', strtotime('+10 minutes', strtotime($createdAt)));
			
			$date = date('Y-m-d H:i:s A');
			
			if(strtotime($convertedTime) < strtotime($date))
			{
				$response= array( 'status'=>'failed','code'=>'201','message'=>'OTP expired, OTP valid for only 10 minutes.' , 'data'=>'' );
			}
			else if($loginotp!= $otp)
			{
				$response= array( 'status'=>'failed','code'=>'201','message'=>'OTP not match.' , 'data'=>'' );
			}
			else
			{
				$data = array (
				  'otp_status' => '1',
				  'active' => '1',
				   'created_on' => $date,
				);
				$this->common_model->updateData('users',$data,array('id'=>$chk_mobile->id));
				
				$final_data['id'] = $chk_mobile->id;
				$final_data['username'] = $chk_mobile->username;
				$final_data['email'] = $chk_mobile->email;
				$final_data['first_name'] = $chk_mobile->first_name;
				$final_data['last_name'] = $chk_mobile->last_name;
				$final_data['phone'] = $chk_mobile->phone;
				$final_data['profile_image'] = SITEURL.$chk_mobile->img_url;
				
								
				$req = array(
					'mobile' 	=> $mobile,
					'otp' 	=> $otp
				);
			
				$response= array( 'status'=>'success ','code'=>'200','message'=>'Logged in Successfully', 'request'=> $req , 'data'=>$final_data);
			}
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function changePassword_post(){
		$user_id 		= $this->post('user_id');
		$old_password 		= $this->post('old_password');
		$new_password 	= $this->post('new_password');
		$confirm_password 	= $this->post('confirm_password');

		$chk_user = $this->common_model->getsingle('users',array('id'=>$user_id));
		$selectdata = $this->ion_auth->hash_password_db($user_id,$old_password);
		
		
		if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid user id.' , 'data'=>'' );
		}
		else if($old_password=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid old password.' , 'data'=>'' );
		}
		else if($new_password == '')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid new password' , 'data'=>'' );
		}
		else if($confirm_password=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid confirm password.' , 'data'=>'' );
		}
		else if($new_password != $confirm_password)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'New Password and confirm password do not match' , 'data'=>'' );
		}
		else if($selectdata=='' || $selectdata!=1)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'old_password is wrong' , 'data'=>'' );
		}
		else if(!preg_match('/[a-z]/', $new_password) || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password) ||  !preg_match('/[^a-zA-Z0-9]/', $new_password) )
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Password does not meet criteria' , 'data'=>'' );
		}
		else 
		{
				$req = array(
					'user_id' 	=> $user_id,
					'old_password' 	=> $old_password,
					'new_password' 	=> $new_password,
					'confirm_password' 	=> $confirm_password,
				);
				//$newpassword =  $this->hash_password($new_password);
				$newpassword = $this->ion_auth_model->hash_password($new_password);
				$data = array(
					"password"=>$newpassword,
					"password_view"=>$new_password
				);
				
				$change =  $this->common_model->updateData('users',$data,array('id'=>$user_id));
				
					//$random =rand(10,10000000);
   
					$data1 = array(
						"user_id"=>$user_id,
						"token"  => ""
					);
					
					$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Password Changed Successfully.', 'request'=> $req, 'data'=>$data1 );
				
				
			
		}		
		 
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function verifyEmail_post(){
		$emailid 		= $this->post('email');
		
		$chk_email = $this->common_model->getsingle('users',array('email'=>$emailid));
		
		if($emailid=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid email.' , 'data'=>'' );
		}
		else if(!$chk_email)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Email ID not found.' , 'data'=>'' );
		}
		else 
		{
			if($chk_email->email_verified == "0"){
				
				$emailstemplete = $this->db->get_where('pf_email_templates',array('template_id' =>'35'))->row();
				$subject = $emailstemplete->email_subject;
				$randcode = $chk_email->remember_code;
                $logo = SITEURL.'assets/images/logo.jpg';
				$tnd = "<a href='".SITEURL."/frontend/terms_conditions' >terms and conditions</a>";
				$proposal_link = SITEURL."patient/confirm_number/".$randcode."/".base64_encode($chk_email->email);
				$Username = $chk_email->username;
				$searches = ['<?php echo $data["logo"]; ?>','<?php echo $data["subject"]; ?>','<?php echo $data["username"]; ?>','<?php echo $data["challenge_link"]; ?>','<?php echo $data["term_condition_link"]; ?>'];
				$replaces = [$logo,$subject,$Username,$proposal_link,$tnd];
				$email_content_admin = str_replace($searches, $replaces, $emailstemplete->email_content);
				$mail = $this->Smtp_mail_model->PHPMailesend();
                $mail->AddAddress($chk_email->email);
                $mail->Subject = $subject;
                $mail->Body = $email_content_admin;
                $mail->send();
				//$email = $chk_email->email;
				//$email = "irisinformatics1@gmail.com";
				
					/*$html ='<head><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title></title><style type="text/css">body, html{ font-family: "Verdana" !important; }</style></head>';
					$html .='<body bgcolor="#e1e5e8" style="margin-top:0 ;margin-bottom:0 ;margin-right:0 ;margin-left:0 ;padding-top:0px;padding-bottom:0px;padding-right:0px;padding-left:0px;background-color:#e1e5e8;">';
					$html .='<center style="width:100%;table-layout:fixed;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#e1e5e8;">';
					$html .='<div style="max-width:600px;margin-top:0;margin-bottom:0;margin-right:auto;margin-left:auto;">';
					$html .='<table align="center" cellpadding="0" style="border-spacing:0;color:#333333;margin:0 auto;width:100%;max-width:600px;">';
					$html .='<tbody><tr><td class="one-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;background-color:#ffffff;">';
					$html .='<table style="border-spacing:0;" width="100%"><tbody><tr>';
					$html .='<td align="center" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;height:100px;vertical-align:middle;" >';
					$html .='<img alt="App Logo" src="'.SITEURL.'assets/images/logo.jpg" style="border-width: 0px; width: auto; height: 80px;" ></td></tr>';
					$html .='<tr><td align="center" class="inner" style="padding-top:15px;padding-bottom:15px;padding-right:30px;padding-left:30px;" valign="middle"><span class="sg-image">';
					$html .='<img alt="verification_icon" class="banner" src="'.SITEURL.'assets/images/verify.png" style="border-width: 0px; margin-top: 30px; width: 130px; height: auto;"></span></td></tr>';
					$html .='<tr><td class="inner contents center" style="padding-top:15px;padding-right:30px;padding-left:30px;text-align:left;">';
					$html .='<p class="h1 center" style="margin:0;text-align:left;font-weight:500;font-size:25px;margin-bottom:20px;">Dear '.$Username.',</p>';
					$html .='<p class="h1 center" style="margin:2px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">Thank you for creating an account with us.</p>';
					$html .='<p class="h1 center" style="margin:4px 0px;line-height: 25px;font-weight: 400;color: black;text-align:left;font-size:14px;">To complete the account activation process, please click on the following link:</p>';
					$html .='<p style="margin:0;padding:16px 0 0;text-align: left;"><b style="background:#e7faf9;border-radius:8px;color:#000;display:inline-block;font-size:36px;font-weight:600;line-height:44px;letter-spacing:1px;padding:8px 32px">';
					$html .='<a href="'.SITEURL.'frontend/emailVerifitionApiStatus/'.$random_code.'/'.base64_encode($email).'" style="text-decoration:none">Account Verify</a></b></p>';
					$html .='<p class="h1 center" style="margin:20px 0px 2px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">In case you are unable to click on the above link,</p>';
					$html .='<p class="h1 center" style="margin:4px 0px;font-weight: 400;line-height: 25px;color: black;text-align:left;font-size:14px;">please copy and paste the link below into your browsers address bar to verify your email.</p>';
					$html .='<a href="'.SITEURL.'frontend/emailVerifitionApiStatus/'.$random_code.'/'.base64_encode($email).'">'.SITEURL.'frontend/emailVerifitionApiStatus/'.$random_code.'/'.base64_encode($email).'</a>';
					$html .='<p class="h1 center" style="margin:20px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">If you did not request an account with us, please disregard this email.</p>';
					$html .='<p class="h1 center" style="margin:20px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">Thank you for choosing our service, and we look forward to serving you.</p>';
					$html .='<p class="h1 center" style="margin:20px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">For more information, make sure to check out our youtube and instagram channel for updates.</p>';
					$html .='<p class="h1 center" style="margin:20px 0px 0px 0px;;font-weight: 400;color: black;text-align:left;font-size:14px;">YouTube  : <a href="youtube.com/@proceedfit">youtube.com/@proceedfit</a></p>';
					$html .='<p class="h1 center" style="margin:8px 0px 22px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">Instagram : <a href="instagram.com/proceedfit ">instagram.com/proceedfit </a></p>';
					$html .='<p class="h1 center" style="margin:20px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">By using our services, you agree with out terms and conditions.</p>';
					$html .='<a href="'.SITEURL.'frontend/terms_conditions">Terms & Conditions </a>';
					$html .='<p class="h1 center" style="margin:20px 0px 0px 0px;;font-weight: 400;color: black;text-align:left;font-size:14px;">Thanks & Regards,</p>';
					$html .='<p class="h1 center" style="margin:8px 0px 10px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;">Team  <a href="'.SITEURL.'Proceed.Fit</a></p>';
					$html .='<p class="h1 center" style="margin:0px 0px 22px 0px;font-weight: 400;color: black;text-align:left;font-size:14px;"><a href="tel:+91 80909 30909">+91 80909 30909</a></p>';
					$html .='</td></tr></tbody></table></td></tr><tr>';
					$html .='<tr><td><p style="line-height: 13px;padding:0 0 0 0;margin:0 0 0 0;">&nbsp;</p></td></tr>';
					$html .='<tr><td style="padding-top:0;padding-bottom:0;padding-right:30px;padding-left:30px;text-align:center;margin-right:auto;margin-left:auto;">';
					$html .='<center><p style="margin:0;text-align:center;margin-right:auto;margin-left:auto;padding-top:10px;padding-bottom:0px;font-size:15px;color:#a1a8ad;line-height:23px;">© Team Proceed.Fit</p></center></td></tr>';
					$html .='<tr><td><p style="line-height: 15px; padding: 0 0 0 0; margin: 0 0 0 0;">&nbsp;</p></td></tr></tbody></table></div></center></body>';
					$body = $html;
					$mail = $this->Smtp_mail_model->PHPMailesend();
					$mail->AddAddress($email);
					$mail->Subject = 'ProceedFit:'.$emailstemplete->email_subject;
					$mail->Body = $body;
					$mail->send();*/
				
				$data = array(
                  
                  'email' => $chk_email->email,
                  'token' => $chk_email->remember_code,
                );
				$req = array(
					'emailid' 	=> $emailid,
				);
				$response= array( 'status'=>'Ok','code'=>'200','message'=> 'An email has been sent to your email address to verify your email.', 'request'=> $req, 'data'=>$data);
				
				
			}else{
					$response= array( 'status'=>'failed','code'=>'201','message'=>'Email already verified.' , 'data'=>'' );
			}
					
			
		}		
		 
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function addAssocite_post(){
		$entity_name 		= $this->post('entity_name');
		$name 		= $this->post('name');
		$associate_code	= $this->post('associate_code');
		$email 	= $this->post('email');
		$mobile 		= $this->post('mobile');
		
		$chk_associate_code  = $this->common_model->getsingle('groups',array('id'=>$associate_code));
		
		if($entity_name=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid entity name.' , 'data'=>'' );
		}
		else if($name=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid name.' , 'data'=>'' );
		}
		else if($associate_code=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid associate code.' , 'data'=>'' );
		}
		else if($email=='' && !filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid email.' , 'data'=>'' );
		}
		else if($mobile=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid mobile.' , 'data'=>'' );
		}
		else if(!preg_match('/^[0-9]{10}+$/', $mobile))
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid mobile.' , 'data'=>'' );
		}
		else if(!$chk_associate_code)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Associate Code not Found. Please provide a valid code.' , 'data'=>'' );
		}
		else 
		{
			$ins_data = array(
				'user_ip' 		=> $_SERVER['REMOTE_ADDR'],
				'entity_name' 	=> $entity_name,
				'associate_code'=> $associate_code,
				'associate_type'=> $chk_associate_code->name,
				'person_name' 	=> $name,
				'email' 		=> $email,
				'mobile' 		=> $mobile,
				'entry_date' 	=> date('Y-m-d'),
				'entry_time' 	=> date('H:i:s A'),
				'channel_id' 	=> 1,
				);
			$res = $this->common_model->insertData('associate',$ins_data);
			
			$req = array(
				'entity_name' 	=> $entity_name,
				'name' 	=> $name,
				'associate_code' => $associate_code,
				'email' 	=> $email,
				'mobile' 	=> $mobile
			);
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Thank you,  your details are registered successfully. Our team will contact you soon.', 'request'=> $req, 'data'=>$req );
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function associate_post(){
		$associate_id 		= $this->post('associate_id');
		$associate_code 		= $this->post('associate_code');
		$associate_type	= $this->post('associate_type');
		
		$chkassociate_id = $this->common_model->getsingle('associate',array('associateid'=>$associate_id));
		$chkassociate_code = $this->common_model->getsingle('associate',array('associate_code'=>$associate_code));
		$chkassociate_type = $this->common_model->getDatabylikes('associate','associate_type',$associate_type);
		
		if($associate_id!='' && !$chkassociate_id)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Associate ID not found.' , 'data'=>'' );
		}
		else if($associate_code!='' && !$chkassociate_code)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Associate code not found.' , 'data'=>'' );
		}
		else if($associate_type!='' && !$chkassociate_type)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Associate type not found.' , 'data'=>'' );
		}
		else
		{
		$associates  = $this->common_model->getAssociates($associate_id,$associate_code,$associate_type);
			
			$finalarray = array();
			if($associates){
				foreach($associates as $associate){
					
					$p['associateid']  	= $associate->associateid;
					$p['associate_code']  	= $associate->associate_code;
					$p['associate_type']  	= $associate->associate_type;
					$p['entity_name']  	= $associate->entity_name;
					$p['person_name']  	= $associate->person_name;
					$p['email']  	= $associate->email;
					$p['mobile']  	= $associate->mobile;
					$p['entry_date']  	= $associate->entry_date;
					$p['entry_time']  	= $associate->entry_time;
					$p['channel_id']  	= $associate->channel_id;
					$p['user_ip']  	= $associate->user_ip;
					$finalarray[]=$p;
				}
			}else{
				$response= array( 'status'=>'failed','code'=>'201','message'=>'Data not found.' , 'data'=>'' );
			}
			
			$req = array(
				'associate_id' 	=> $associate_id,
				'associate_code' => $associate_code,
				'associate_type' => $associate_type,
			);
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched successfully.', 'request'=> $req, 'data'=>$finalarray );
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
    

}
	

