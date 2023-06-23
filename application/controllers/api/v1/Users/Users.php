<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Users extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		$this->load->model('ion_auth_model');
		$this->load->model('common_model');
		$this->load->model('Smtp_mail_model');
    }
	
	function users_post(){
		$user_id 	= $this->post('user_id');
		$email_id  	= $this->post('email_id');
		$mobile_no 	= $this->post('mobile_no');
		
		$chk_userid = $this->common_model->getsingle('users',array('id'=>$user_id));
		$chk_emailid = $this->common_model->getsingle('users',array('email'=>$email_id));
		$chk_mobile = $this->common_model->getsingle('users',array('phone'=>$mobile_no));
		
		if($user_id!='' && !$chk_userid)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User ID not found' , 'data'=>'' );
		}
		elseif($email_id!='' && !$chk_emailid)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Email ID not found' , 'data'=>'' );
		}
		elseif($mobile_no!='' && !$chk_mobile)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Mobile no not found' , 'data'=>'' );
		}
		else
		{
			
			$users = $this->common_model->getusers($user_id,$email_id,$mobile_no);
			
			$params=array();
			foreach($users as $user)
			{
				if($user->img_url==''){
					$image = "";
				}else{
					$image = SITEURL.$user->img_url;
				}
					$final_data['id'] = $user->id;
					$final_data['username'] = $user->username;
					$final_data['email'] = $user->email;
					$final_data['first_name'] = $user->first_name;
					$final_data['last_name'] = $user->last_name;
					$final_data['phone'] = $user->phone;
					$final_data['profile_image'] = $image;
					$params[]=$final_data;					
			}
			
			if($user_id || $email_id || $mobile_no ){
			$req = array(
					'user_id' 	=> $user_id,
					'email_id' 	=> $email_id,
					'mobile_no' 	=> $mobile_no,
				);
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.', 'request'=> $req,  'count'=>count($users),'data'=>$params);	
			}else{
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.', 'count'=>count($users),'data'=>$params);
			}
			
		}
		
				
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function notifications_post(){
		$user_id 	= $this->post('user_id');
		
		$chk_userid = $this->common_model->getsingle('users',array('id'=>$user_id));
		
		if($user_id!='' && !$chk_userid)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User ID not found' , 'data'=>'' );
		}
		else
		{
			$usersNoti = $this->common_model->getAllwhere('pf_notifications',array('receiver_id'=>$user_id));
			if($usersNoti)
			{
				$params=array();
				
				foreach($usersNoti as $val)
				{
					$message = $this->common_model->getsingle('pf_notifications_messages',array('id'=> $val->message_id));
					$proposal = $this->common_model->getsingle('pf_proposals',array('proposal_id'=> $val->proposal_id));
					$senderid = $this->common_model->getsingle('users',array('id'=>$val->sender_id));
					$final_data['notification_id'] = $val->notification_id;
					$final_data['order_id'] = $val->order_id;
					$final_data['proposal_id'] = $val->proposal_id;
					$final_data['proposal_name'] = $proposal?$proposal->proposal_title:"";
					$final_data['message_id'] = $val->message_id;
					$final_data['message'] = $message->message;
					$final_data['description'] = $val->description;
					$final_data['bell'] = $val->bell;
					$final_data['status'] = $val->status;
					$final_data['channel_id'] = $val->channel_id;
					$final_data['receiver_id'] = $val->receiver_id;
					$final_data['receiver_name'] = $chk_userid->username;
					$final_data['sender_id'] = $val->sender_id;
					$final_data['sender_name'] = $senderid->username;
					$final_data['sender_profile'] = $senderid->img_url?SITEURL.$senderid->img_url:'';
					$final_data['date'] = date('F d Y h:i:s', strtotime($val->date));
					
					$params[]=$final_data;					
				}
			
				$req = array(
					'user_id' 	=> $user_id,
				);
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','request'=> $req,'data'=>$params );
			
			}
			else
			{
				$response= array( 'status'=>'failed','code'=>'400','message'=>'No Notifications.' , 'data'=>'' );
			}
			
			
		}
		
				
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function addNotificiations_post(){
		$order_id 	= $this->post('order_id');
		$proposal_id 	= $this->post('proposal_id');
		$message_id 	= $this->post('message_id');
		$channel_id 	= $this->post('channel_id');
		
		$tdate = date('Y-m-d');
		$chk_messageid = $this->common_model->getsingle('pf_notifications_messages',array('id'=>$message_id));
		$chk_orderid = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		$chk_proposalid = $this->common_model->getsingle('pf_proposals',array('proposal_id'=>$proposal_id));
		
		if($order_id)
		{
			$rsvrId = $chk_orderid->buyer_id;
		}
		if($proposal_id)
		{
			$rsvrId = $chk_proposalid->proposal_seller_id;
		}
		
		$where = "message_id = ".$message_id." and receiver_id = ".$rsvrId." and date(date) ='".$tdate."'";
		$chk_already = $this->common_model->getAllwhere('pf_notifications',$where);
		
		if($order_id=='' && $proposal_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Please provide order_id or proposal_id' , 'data'=>'' );
		}
		elseif($channel_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Please provide channel id' , 'data'=>'' );
		}
		elseif($message_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Please provide message_id' , 'data'=>'' );
		}
		elseif(!$chk_messageid)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Message ID not found' , 'data'=>'' );
		}
		elseif(!$chk_orderid && $order_id!='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order ID not found' , 'data'=>'' );
		}
		elseif(!$chk_proposalid && $proposal_id!='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Proposal ID not found' , 'data'=>'' );
		}
		elseif($chk_already)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Notifcation already exist' , 'data'=>'' );
		}
		else
		{
			$admin_id="1";
			$notidata = array(
					'receiver_id' => $rsvrId,
					'sender_id' => $admin_id,
					'order_id' => $order_id?$order_id:'0',
					'proposal_id' => $proposal_id?$proposal_id:'0',
					'message_id' => $message_id,
					'description' => $chk_messageid->reason,
					'date' => date('Y-m-d h:i:s A'),
					'bell' => 'active',
					'status' => 'unread',
					'channel_id' => $channel_id
				);
				
				$addnotification =  $this->common_model->insertData('pf_notifications',$notidata);
				
				$req = array(
					'order_id' 	=> $order_id,
					'proposal_id' 	=> $proposal_id,
					'message_id' 	=> $message_id,
					'channel_id' 	=> $channel_id,
				);
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Notification added Successfuly.','request'=> $req,'data'=>$req);
			
			
			
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function updateNotifications_post(){
		$notification_id 	= $this->post('notification_id');
		$read_status 	= $this->post('read_status');
		$channel_id 	= $this->post('channel_id');
		
		$chk_notifications = $this->common_model->getsingle('pf_notifications',array('notification_id'=>$notification_id));
		
		if($notification_id=='' && !$chk_notifications)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Notification Id ' , 'data'=>'' );
		}
		elseif(($read_status=='') || ($read_status!='read' && $read_status!='unread') )
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid read status.' , 'data'=>'' );
		}
		elseif(($channel_id=='') || ($channel_id!=0 && $channel_id!=1))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid channel id.' , 'data'=>'' );
		}
		else
		{
			$notidata = array(
					'status' => $read_status,
					'channel_id' => $channel_id,
					'date' => date('Y-m-d h:i:s A')
				);
				
				$updatenotification =  $this->common_model->updateData('pf_notifications',$notidata,array('notification_id'=>$notification_id));
				
				$req = array(
					'notification_id' 	=> $notification_id,
					'read_status' 	=> $read_status,
					'channel_id' 	=> $channel_id,
				);
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Notification successfully updated.','request'=> $req,'data'=>$req );
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function deleteNotificiations_post(){
		$notification_id = $this->post('notification_id');
		$channel_id 	= $this->post('channel_id');
		
		$chk_notifications = $this->common_model->getsingle('pf_notifications',array('notification_id'=>$notification_id));
		
		if($notification_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Notification Id.' , 'data'=>'' );
		}
		elseif(!$chk_notifications)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Notification ID doed not exist.' , 'data'=>'' );
		}
		elseif(($channel_id=='') || ($channel_id!=0 && $channel_id!=1))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid channel id.' , 'data'=>'' );
		}
		else
		{
				
			$this->common_model->deleteData('pf_notifications',array('notification_id'=>$notification_id));
				
			$req = array(
				'notification_id' 	=> $notification_id,
				'channel_id' 	=> $channel_id,
			);
			
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Notification Deleted Successfully.','request'=> $req,'data'=>$req );
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function addTicket_post(){
		$user_id 	= $this->post('user_id');
		$subject 	= $this->post('subject');
		$message 	= $this->post('message');
		$order_id 	= $this->post('order_id');
		$enquiry_type 	= $this->post('enquiry_type');
		
		$date = date('Y-m-d H:i:s');
		$chk_user_id = $this->common_model->getsingle('users',array('id'=>$user_id));
		$chk_enquiry_types = $this->common_model->getsingle('pf_enquiry_types',array('enquiry_id'=>$enquiry_type));
		
		if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide user_id' , 'data'=>'' );
		}
		elseif($subject=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide subject' , 'data'=>'' );
		}
		elseif($enquiry_type=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'provide enquiry type' , 'data'=>'' );
		}
		elseif($message=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide Message' , 'data'=>'' );
		}
		elseif(!$chk_user_id)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User Id not found' , 'data'=>'' );
		}
		elseif(!$chk_enquiry_types)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Enquiry type not found, Provide appropriate enquiry type.' , 'data'=>'' );
		}
		else
		{
			
			$insertdata =array(
			   'sender_id'=> $user_id,
			   'enquiry_id'=>$enquiry_type,
			   'subject'=>$subject,
			   'message'=>$message,
			   'order_id'=>$order_id,
			   'created_date' =>$date,   
			   'status_id' =>25,    //pending
			);
				
				$addnticket =  $this->common_model->insertData('pf_support_tickets',$insertdata);
				
				$pf_configsetting = $this->db->get_where('pf_config_setting',array('id'=>'7'))->row();
				$pf_ccmail = $this->db->get_where('pf_config_setting',array('id'=>'22'))->row();
				$admin = $pf_configsetting->value;
				//$admin = "irisinformatics1@gmail.com";
				$admincc = $pf_ccmail->value;
				$baseurl = base_url();
				$email_subject = "Support Request";
				$support_contact =  $this->db->get_where('pf_config_setting',array('name' =>'support_contact'))->row();
				$support_email =  $this->db->get_where('pf_config_setting',array('name' =>'support_email'))->row();
		
				$logo = SITEURL."assets/images/email/logoimg.png";
				$userdata = $this->db->get_where('users',array('id'=> $userid))->row();
				$emailstemplete = $this->db->get_where('pf_email_templates',array('template_id' =>'44'))->row();
				$subject = $emailstemplete->email_subject;
				$logo = $baseurl.'assets/images/logo.jpg';
				$tnd = "<a href='".SITEURL."/frontend/terms_conditions' >terms and conditions</a>";
				$Username = $userdata->username;
				$searches = ['<?php echo $data["logo"]; ?>','<?php echo $data["subject"]; ?>','<?php echo $data["username"]; ?>','<?php echo $data["ticket_id"]; ?>','<?php echo $data["term_condition_link"]; ?>','<?php echo $data["support_email"]; ?>','<?php echo $data["support_contact"]; ?>'];
				$replaces = [$logo,$subject,$Username,$addnticket,$tnd,$support_email->value,$support_contact->value];
				$email_content_admin = str_replace($searches, $replaces, $emailstemplete->email_content);
				$mail = $this->Smtp_mail_model->PHPMailesend();
				$mail->AddAddress($admin);
				$mail->AddCC($admincc);
				$mail->Subject = $subject;
				$mail->Body = $email_content_admin;
				$mail->send();
				
			//add notification 
			$admin_id="1";
			$notidata = array(
					'receiver_id' => $admin_id,
					'sender_id' => $user_id,
					'order_id' => $order_id?$order_id:'0',
					'proposal_id' => '0',
					'message_id' => $addnticket,
					//'description' => $chk_messageid->reason,
					'description' => "add_ticket",
					'date' => date('Y-m-d h:i:s A'),
					'bell' => 'active',
					'status' => 'unread',
					'channel_id' => 1
					);
				
				$add =  $this->common_model->insertData('pf_notifications',$notidata);
				
				$req = array(
					'user_id' 	=> $user_id,
					'subject' 	=> $subject,
					'message' 	=> $message,
					'enquiry_type' 	=> $enquiry_type,
					'order_id' 	=> $order_id,
				);
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Ticket ID - ['.$addnticket.']  created successfully. Our support team will get back to you soon','request'=> $req,'data'=>null);
			
			
			
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function tickets_post(){
		$user_id 	= $this->post('user_id');
		
		$chk_userid = $this->common_model->getsingle('users',array('id'=>$user_id));
		$usersTicket = $this->common_model->getAllwhere('pf_support_tickets',array('sender_id'=>$user_id));
		
		
		if($user_id!='' && !$chk_userid)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User ID not found' , 'data'=>'' );
		}
		elseif(!$usersTicket)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'No tickets found' , 'data'=>'' );
		}
		else
		{
			
			$params=array();
			$reply=array();
			foreach($usersTicket as $val)
			{
				$usersTicketreply = $this->common_model->getAllwhere('pf_support_conversations',array('ticket_id'=>$val->ticket_id));
				$enquiry = $this->common_model->getsingle('pf_enquiry_types',array('enquiry_id'=> $val->enquiry_id));
				
				$final_data['user_id'] = $user_id;
				//$final_data['ticket_id'] = $val->ticket_id;
				$final_data['date'] =  date('F d Y h:i:s', strtotime($val->created_date));
				$final_data['subject'] = $val->subject;
				$final_data['message'] = $val->message;
				$final_data['order_no'] = $val->order_id;
				$final_data['enquiry_type'] = $enquiry->enquiry_title;
				$final_data['status'] = $val->status_id==25?"Pending":"Complete";
				$final_data['resolution'] = $val->resolution?$val->resolution:"";
				   
				   if($usersTicketreply){
				   foreach($usersTicketreply as $val2)
					{
						$user = $this->common_model->getsingle('users',array('id'=> $val2->sender_id));
						//$r['sender_id'] = $val2->sender_id;
						$r['sender_name'] = $user->username;
						$r['message'] = $val2->message;
						$r['date'] = $val2->date;
						$reply[]=$r;
					}
					
				   }
				   
				   $final_data['replies'] = $reply;
				
				
				
				$params[]=$final_data;					
			}
		
			$req = array(
				'user_id' 	=> $user_id,
			);
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','request'=> $req,'data'=>$params);
		
		}
		
				
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function postReply_post(){
		$ticket_id 	= $this->post('ticket_id');
		$sender_id 	= $this->post('sender_id');
		$message 	= $this->post('message');
		
		$date = date('Y-m-d H:i:s');
		$chk_user_id = $this->common_model->getsingle('users',array('id'=>$sender_id));
		$chk_support_tickets = $this->common_model->getsingle('pf_support_tickets',array('ticket_id'=>$ticket_id));
		
		if($sender_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide sender id' , 'data'=>'' );
		}
		elseif($ticket_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide ticket id' , 'data'=>'' );
		}
		elseif(!$chk_user_id)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Sender Id not found' , 'data'=>'' );
		}
		elseif(!$chk_support_tickets)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Ticket not found.' , 'data'=>'' );
		}
		else
		{
			
			$insertdata =array(
				'sender_id'=> $sender_id,
				'message'=>$message,
				'ticket_id'=>$ticket_id,
				'date' =>$date,
				'user_ip' =>$_SERVER['REMOTE_ADDR'],
				'channel_id' =>1,   			   
				'file_url' =>"",   			   
			);
			
			$addnticket =  $this->common_model->insertData('pf_support_conversations',$insertdata);
			$updateticket =  $this->common_model->updateData('pf_support_tickets',array('status_id'=>27),array('ticket_id'=>$ticket_id));	
				
				$req = array(
					'ticket_id' 	=> $ticket_id,
					'sender_id' 	=> $sender_id,
					'message' 	=> $message,
				);
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Reply posted successfully for Ticket ID -'.$ticket_id,'request'=> $req,'data'=>null);
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function closeTicket_post(){
		$ticket_id 	= $this->post('ticket_id');
		$user_id 	= $this->post('user_id');
		
		$date = date('Y-m-d H:i:s');
		$chk_user_id = $this->common_model->getsingle('users',array('id'=>$user_id));
		$chk_support_tickets = $this->common_model->getsingle('pf_support_tickets',array('ticket_id'=>$ticket_id));
		
		if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide user id' , 'data'=>'' );
		}
		elseif($ticket_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide ticket id' , 'data'=>'' );
		}
		elseif(!$chk_user_id)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User Id not found' , 'data'=>'' );
		}
		elseif(!$chk_support_tickets)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Ticket not found.' , 'data'=>'' );
		}
		else
		{
			
			$updatedata =array(
				'resolved_date'=>$date,
				'closed_by'=>$user_id,
				'created_date' =>$date,
				'user_ip' =>$_SERVER['REMOTE_ADDR'],
				'channel_id' =>1,
				'status_id'	=>	26
			);
			
			$updateticket =  $this->common_model->updateData('pf_support_tickets',$updatedata,array('ticket_id'=>$ticket_id));	
				
				$req = array(
					'ticket_id' 	=> $ticket_id,
					'user_id' 	=> $user_id,
				);
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Ticket Closed successfully.','request'=> $req,'data'=>null);
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}

}
	

