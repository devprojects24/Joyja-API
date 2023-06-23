<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Wb extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        // Configure limits on our controller methods. Ensure
        // you have created the 'limits' table and enabled 'limits'
        // within application/config/rest.php
        $this->methods['user_get']['limit'] = 500; //500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; //100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; //50 requests per hour per user/key
		$this->load->model('common_model');
    }
	function users_role_get(){		 
		$dd = $this->common_model->getAllwhere('wp_5cadee33ab_options',array('option_name'=>'wp_5cadee33ab_user_roles'));
		$data= unserialize($dd[0]->option_value);
		$final= array();
		$finals= array();
		foreach ($data as $key=>$value)
		{
			if($key=='normal_user' OR $key =='street_team_member' OR $key =='influencer' OR $key=='wpamelia-customer' ){
			$final['role_id'] = $key;
			$final['role'] = $data[$key]['name'];
			$finals[] = $final;
			}
		}
		if($finals)
		{
			$response= array('status'=>'200','message'=>'Users role get Successfully', 'data'=>$finals);
			
		}else{
			$response= array( 'status'=>'201','message'=>'Users Roles Not Exists', 'data'=>'');
		}
        $this->response($response	, 200); // 200 being the HTTP response code
	}
	function register_post(){
		$username 	= $this->post('username');
		$first_name 	= $this->post('first_name');
		$last_name 	= $this->post('last_name');
		$email 		= $this->post('email');
		$password 	= $this->post('password');
		$cpassword 	= $this->post('cpassword');
		$role_id 	= $this->post('role_id');
		$exist_username = $this->common_model->getsingle('wp_5cadee33ab_users',array('user_login' => $username));
		$exist_email = $this->common_model->getsingle('wp_5cadee33ab_users',array('user_email' => $email));
		if($first_name=='')
		{
			$response= array( 'status'=>'201','message'=>'first_name input missing!' , 'data'=>'' );
		}
		if($last_name=='')
		{
			$response= array( 'status'=>'201','message'=>'last_name input missing!' , 'data'=>'' );
		}
		if($username=='')
		{
			$response= array( 'status'=>'201','message'=>'username input missing!' , 'data'=>'' );
		}
		else if($username!='' && $exist_username)
		{
			$response= array( 'status'=>'201','message'=>'username Already Exists!', 'data'=>'');
		}
		if($email=='')
		{
			$response= array( 'status'=>'201','message'=>'email input missing!' , 'data'=>'' );
		}
		else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$response= array( 'status'=>'201','message'=>'email Not Valid!', 'data'=>'');
		}
		else if($email!='' && $exist_email)
		{
			$response= array( 'status'=>'201','message'=>'email Already Exists!', 'data'=>'');
		}
		if($role_id=='')
		{
			$response= array( 'status'=>'201','message'=>'role_id input missing!' , 'data'=>'' );
		}
		if($cpassword!=$password)
		{
			$response= array( 'status'=>'201','message'=>'password and cpassword not Match!' , 'data'=>'' );
		}
		if($password=='')
		{
			$response= array( 'status'=>'201','message'=>'password input missing!' , 'data'=>'' );
		}
		if($cpassword=='')
		{
			$response= array( 'status'=>'201','message'=>'cpassword input missing!' , 'data'=>'' );
		}
		
		if($first_name!='' && $last_name!='' && $username!='' && !$exist_username && $email!='' && filter_var($email, FILTER_VALIDATE_EMAIL) && !$exist_email && $role_id!='' && $password!='' && $cpassword!='' && $cpassword==$password)
		{
			$ins_data = array(
						"user_login" 		=> $username,
						"user_pass" 		=> md5($password),
						"user_nicename" 	=> $username,
						"user_email" 		=> $email,
						"user_registered"	=> date('Y-m-d H:i:s'),
						"user_status" 		=> "0",
						"display_name" 		=> $username
					);
			$insert_id = $this->common_model->insertData('wp_5cadee33ab_users',$ins_data);
			
			//user Meta table data
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'nickname', 'meta_value' =>$username ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'first_name', 'meta_value'=>$first_name));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'last_name', 'meta_value'=>$last_name ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'description', 'meta_value'=>'' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'rich_editing', 'meta_value'=>'true' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'syntax_highlighting', 'meta_value'=>'true' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'comment_shortcuts', 'meta_value'=>'false' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'admin_color', 'meta_value'=>'fresh' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'use_ssl', 'meta_value'=>'0' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'show_admin_bar_front', 'meta_value'=>'true' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'locale', 'meta_value'=>'' ));
			//$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'wp_5cadee33ab_capabilities', 'meta_value'=>'' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'wp_5cadee33ab_user_level', 'meta_value'=>'0' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'pw_user_status', 'meta_value'=>'approved' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'user_login', 'meta_value'=>$username ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'user_email', 'meta_value'=>$email ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'hide_user_email', 'meta_value'=>'' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'user_role', 'meta_value'=>$role_id ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'no_captcha', 'meta_value'=>'yes' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'upme_approval_status', 'meta_value'=>'ACTIVE' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'upme_user_profile_status', 'meta_value'=>'ACTIVE' ));
			$this->common_model->insertData('wp_5cadee33ab_usermeta',array('user_id'=>$insert_id,'meta_key'=>'upme_activation_status', 'meta_value'=>'ACTIVE' ));			
			
			$response= array('status'=>'200','message'=> 'Registration successfully!', 'data'=>'' );
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	// Login 
	function login_post(){
		$password 			= $this->post('password');
		$email_username 	= $this->post('email_username');

		if($email_username=='')
		{
			$response= array( 'status'=>'201','message'=>'email_username input missing!' , 'data'=>'' );
		}
		if($password=='')
		{
			$response= array( 'status'=>'201','message'=>'password input missing!' , 'data'=>'' );
		}
		$site_url = base_url();
		$final_url = str_replace('/api','',$site_url);
		$url = $final_url."wp-admin/admin-ajax.php";
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "action=login_wordpress_for_api&username=".$email_username."&password=".$password,	 
		  
		));

		$res = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($password!='' && $email_username!='')
		{			
			if($res!="error")
			{
				$user_id = $res;
				$data = $this->common_model->getsingle('wp_5cadee33ab_users',array('ID' => $user_id));
				$first_name = $this->common_model->getsingle('wp_5cadee33ab_usermeta',array('user_id' => $user_id,'meta_key'=>'first_name'));
				$last_name = $this->common_model->getsingle('wp_5cadee33ab_usermeta',array('user_id' => $user_id,'meta_key'=>'last_name'));
				$user_role = $this->common_model->getsingle('wp_5cadee33ab_usermeta',array('user_id' => $user_id,'meta_key'=>'user_role'));
				
				$dd = $this->common_model->getAllwhere('wp_5cadee33ab_options',array('option_name'=>'wp_5cadee33ab_user_roles'));
				$datas= unserialize($dd[0]->option_value);
				
				$final_data = array();
				$final_data['id'] 			= $user_id;
				$final_data['username'] 	= $data->user_login;
				$final_data['email'] 		= $data->user_email;
				$final_data['first_name'] 	= $first_name->meta_value;
				$final_data['last_name'] 	= $last_name->meta_value;
				$final_data['role_id'] = "";
				$final_data['role'] = "";
				foreach ($datas as $key=>$value)
				{
					if($key==$user_role ){
					$final_data['role_id'] = $key;
					$final_data['role'] = $datas[$key]['name'];
					
					}
				}
				
				$response= array('data'=>$final_data );
				$data = $this->common_model->getsingle('wp_5cadee33ab_users',array('ID' => $res));				
				$response= array('status'=>'200','message'=> 'Login successfully', 'data'=>$final_data );
			}else{			
				$response= array('status'=>'201','message'=> 'Login Failed', 'data'=>'' );
			} 
		}
		
        $this->response($response	, 200); // 200 being the HTTP response code	
	} 
	
	// Forgot Password Link Using upme Plugin wordpress
	function forgot_password_post(){
        $email = $this->post('email');
		if($email=='')
		{
			$response= array( 'status'=>'201','message'=>'email input missing!' , 'data'=>'' );
		}
		else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$response= array( 'status'=>'201','message'=>'email Not Valid!', 'data'=>'');
		}
		$exist_email = $this->common_model->getsingle('wp_5cadee33ab_users',array('user_email' => $email));
		if($exist_email && $email!='' && filter_var($email, FILTER_VALIDATE_EMAIL))			
		{
			$site_url = base_url();
			$final_url = str_replace('/api','',$site_url);
			$url = $final_url."wp-admin/admin-ajax.php";
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "action=request_forgot_password&user_details=".$email,	 
			  
			));

			$data = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			
			if($data=='success'){	
				$response= array('status'=>'200','message'=> 'Password Changed Link Send successfully', 'data'=>'' );
			}else{
				$response= array('status'=>'201','message'=> 'Password Send Some Problem', 'data'=>'' );
			} 
			
		}else{			
			$response= array( 'status'=>'201','message'=>'Email Not Exist Over Database!', 'data'=>'');
		}																		
        $this->response($response	, 200); // 200 being the HTTP response code
		
	} 
	
	
	function change_password_post(){
		$old_password 			= $this->post('old_password');
		$email 					= $this->post('email');
		$new_password 			= $this->post('new_password');
		$confirm_new_password 	= $this->post('confirm_new_password');

		if($email=='')
		{
			$response= array( 'status'=>'201','message'=>'email input missing!' , 'data'=>'' );
		}
		if($old_password=='')
		{
			$response= array( 'status'=>'201','message'=>'old_password input missing!' , 'data'=>'' );
		}
		if($new_password=='')
		{
			$response= array( 'status'=>'201','message'=>'new_password input missing!' , 'data'=>'' );
		}
				
		$site_url = base_url();
		$final_url = str_replace('/api','',$site_url);
		$url = $final_url."wp-admin/admin-ajax.php";
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "action=login_wordpress_for_api&username=".$email."&password=".$old_password,	 
		  
		));

		$res = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($email!='' && $old_password!='' && $new_password!='')
		{			
			if($res!="error")
			{
				$this->common_model->updateData('wp_5cadee33ab_users',array('user_pass' => md5($new_password)),array('ID' => $res));				
				$data = $this->common_model->getsingle('wp_5cadee33ab_users',array('ID' => $res));				
				$response= array('status'=>'200','message'=> 'Password changed successfully', 'data'=>$data );
			}else{			
				$response= array('status'=>'201','message'=> 'Old Password not match!', 'data'=>'' );
			} 
		}
		
        $this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function locations_new_get(){		 
		$data = $this->common_model->getAllrecord('locations_for_api');
		
		if(count($data)>0)
		{
			$final = array();
			foreach($data as $d)
			{
				$final_data['location_id'] 	= $d->location_id;
				$final_data['location_title'] = $d->location_title;
				$final_data['tags'] 		= $d->tags;
				$final_data['content1'] 	= $d->content1;
				$final_data['content2'] 	= $d->content2;
				$final_data['content3'] 	= $d->content3;
				$final_data['content4'] 	= $d->content4;
				$final_data['content5'] 	= $d->content5;
				$images = $this->common_model->getAllwhere('locations_for_api_images',array('location_id' => $d->location_id));
				if($images!=NULL)
				{
					$site_url = base_url();
					$final_url = str_replace('/api','',$site_url);
			
					$final_images = array();
					foreach($images as $img)
					{
						$im['id'] 			= $img->id;
						$im['image'] 		= $final_url."wp-content/images/location_images/".$img->image;						
						$final_images[] = $im;
					}
				$final_data['images'] 	= $final_images;		
				}
				else{
				$final_data['images'] 	= "";	
				}
				
				$final[] 				= $final_data;
			}
			$response= array('status'=>'200', 'message'=>'Locations Get Successfully!', 'data'=>$final );
		}
		else
		{
			$response= array('status'=>'201', 'message'=>'No Record found!', 'data'=>'' );
		}
		
        $this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function location_details_page_post(){
		$location_id = $this->post('location_id');
		if($location_id=='')
		{
			$response= array( 'status'=>'201','message'=>'location_id input missing!' , 'data'=>'' );
		}		
		if($location_id!='')
		{
			$d = $this->common_model->getsingle('locations_for_api',array('location_id' => $location_id));
			if($d)
			{
				$final = array();			
				$final_data['location_id'] 	= $d->location_id;
				$final_data['tags'] 		= $d->tags;
				$final_data['location_title'] = $d->location_title;
				$final_data['content1'] 	= $d->content1;
				$final_data['content2'] 	= $d->content2;
				$final_data['content3'] 	= $d->content3;
				$final_data['content4'] 	= $d->content4;
				$final_data['content5'] 	= $d->content5;
				$images = $this->common_model->getAllwhere('locations_for_api_images',array('location_id' => $d->location_id));
				if($images!=NULL)
				{
					$site_url = base_url();
					$final_url = str_replace('/api','',$site_url);
			
					$final_images = array();
					foreach($images as $img)
					{
						$im['id'] 			= $img->id;
						$im['image'] 		= $final_url."wp-content/images/location_images/".$img->image;						
						$final_images[] = $im;
					}
				$final_data['images'] 	= $final_images;		
				}
				else{
				$final_data['images'] 	= "";	
				}
				$slots = $this->common_model->get_slots($location_id);				
				if($slots!=NULL)
				{					
					$final_slotes = array();
					$found = array();					
					foreach($slots as $s)
					{
						 if (in_array($s['slot_date'], $found)) {
							
						} else {
							$found[] 				= $s['slot_date'];
							$ss['slot_date'] 		= $s['slot_date'];
							
							$slots_data = $this->common_model->getAllwhere('calendars',array('location_id' => $location_id ,'slot_date' => $s['slot_date'],'is_booked' => '0'));
							if(count($slots_data)>0)
							{
								$ss['slot_times'] = $slots_data;
							}else{
								$ss['slot_times'] ="";
							}
							$final_slotes[] 		= $ss;
						}
						
					}
				$final_data['slots'] 	= $final_slotes;		
				}
				else{
				$final_data['slots'] 	= "";	
				}
				$final[] 				= $final_data;
				
				$response= array('status'=>'200', 'message'=>'Locations Details get Successfully!', 'data'=>$final );
			}
			else
			{
				$response= array('status'=>'201', 'message'=>'No Record found!', 'data'=>'' );
			}
		}
		
		
        $this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function slots_by_date_post(){
		$location_id = $this->post('location_id');
		$slot_date 	 = $this->post('slot_date');
		if($location_id=='')
		{
			$response= array( 'status'=>'201','message'=>'location_id input missing!' , 'data'=>'' );
		}
		if($slot_date=='')
		{
			$response= array( 'status'=>'201','message'=>'slot_date input missing!' , 'data'=>'' );
		}		
		if($location_id!="" && $slot_date!='' )
		{	
			$data = $this->common_model->getAllwhere('calendars',array('location_id' => $location_id ,'slot_date' => $slot_date));
			if(count($data)>0)
			{
				$response= array('status'=>'200','message'=>'get slot List successfully' ,'data'=>$data );
			}
			else
			{
				$response= array('status'=>'201','message'=>'no record found!' ,'data'=>'' );
			}
			
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function add_time_slot_post(){
		
		$location_id 	 = $this->post('location_id');
		$photographer_id = $this->post('photographer_id');
		$slot_date 		 = $this->post('slot_date');
		$from_time 		 = $this->post('from_time');
		$to_time 		 = $this->post('to_time');
		$from_date_time  =  date('Y-m-d H:i:s',strtotime($slot_date.' '.$from_time));
		$to_date_time    =  date('Y-m-d H:i:s',strtotime($slot_date.' '.$to_time));
		
		if($location_id=='')
		{
			$response= array( 'status'=>'201','message'=>'location_id input missing!' , 'data'=>'' );
		}		
		if($photographer_id=='')
		{
			$response= array( 'status'=>'201','message'=>'photographer_id input missing!' , 'data'=>'' );
		}		
		if($slot_date=='')
		{
			$response= array('status'=>'201', 'message'=>'slot_date input missing!' , 'data'=>'' );
		}		
		if($from_time=='')
		{
			$response= array( 'status'=>'201','message'=>'from_time input missing!' , 'data'=>'' );
		}
		if($to_time=='')
		{
			$response= array( 'status'=>'201','message'=>'to_time input missing!' , 'data'=>'' );
		}
		$exists_data = $this->common_model->get_time_slots($photographer_id,$from_date_time,$to_date_time);
		if($exists_data)
		{
			$response= array( 'status'=>'201', 'message'=>"Already Added Date ".date('Y-m-d',strtotime($exists_data[0]['from_date_time']))." Time slot ( ".$exists_data[0]['from_time']." To ".$exists_data[0]['to_time']." ) Please add another Time slot." , 'data'=>'' );
		}
		
		
		if($location_id!='' && $photographer_id!='' && $slot_date!='' && $from_time!='' && $to_time!='' && !$exists_data)
		{
			$begin = new DateTime($from_date_time);
		$end   = new DateTime($to_date_time);
		$interval = DateInterval::createFromDateString('30 min');
		$times    = new DatePeriod($begin, $interval, $end);

		foreach ($times as $time) {
			$from_time 		 = $time->format('h:i A');
			$to_time 		 = $time->add($interval)->format('h:i A');
			$from_date_time  =  date('Y-m-d H:i:s',strtotime($slot_date.' '.$from_time));
			$to_date_time    =  date('Y-m-d H:i:s',strtotime($slot_date.' '.$to_time));
			
			$ins_data = array(
						'location_id' 				=> $location_id,
						'photographer_id' 			=> $photographer_id,
						'slot_date' 				=> $slot_date,	
						'from_time' 				=> $from_time,
						'to_time'					=> $to_time,
						'from_date_time' 			=> $from_date_time,
						'to_date_time' 				=> $to_date_time,
						'entry_date'				=> date('Y-m-d'),
						'is_booked'					=>'0',
						'booker_name'				=>'',
						'booker_email'				=>'',
						'booker_no'					=>'',
						'amount'					=>''
					);
			$insert_id = $this->common_model->insertData('calendars',$ins_data);
			 
		}			
			
			$response= array('status'=>'200','message'=> 'Time slot Added successfully!', 'data'=>'' );
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function my_time_slot_list_post(){	
		$photographer_id = $this->post('photographer_id');
		if($photographer_id=='')
		{
			$response= array( 'status'=>'201','message'=>'photographer_id input missing!' , 'data'=>'' );
		}
		if($photographer_id!='')
		{
			$data = $this->common_model->getAllwhere('calendars',array('photographer_id' => $photographer_id));	
			if(count($data)>0)
			{
				$response= array('status'=>'200','message'=>'get time slot list successfully' ,'data'=>$data );
			}
			else
			{
				$response= array('status'=>'201','message'=>'no record found!' ,'data'=>'' );
			}
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function time_slot_list_get(){	
		$data = $this->common_model->getAllwhere('calendars',array('slot_date >=' => date('Y-m-d')));
		if($data)
		{	
			$data = $this->common_model->getAllwhere('calendars',array('photographer_id' => $photographer_id));	
			if(count($data)>0)
			{
				$response= array('status'=>'200','message'=>'get time slot List successfully' ,'data'=>$data );
			}
			else
			{
				$response= array('status'=>'201','message'=>'no record found!' ,'data'=>'' );
			}
			
		}
		else
		{
			$response= array( 'status'=>'201','message'=>'Time Slot Not Exists', 'data'=>'');
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function time_slot_list_by_location_post(){	
		$location_id = $this->post('location_id');
		$exist_data = $this->common_model->getsingle('calendars',array('location_id' => $location_id));
		if($location_id=='')
		{
			$response= array( 'status'=>'201','message'=>'location_id input missing!' , 'data'=>'Failed' );
		}
		if(!$exist_data)
		{
			$response= array( 'status'=>'201','message'=>'time slot OR location_id not exist' , 'data'=>'Failed' );
		}
		if($location_id!='' && $exist_data)
		{
			$data = $this->common_model->getAllwhere('calendars',array('location_id' => $location_id));				
			if(count($data)>0)
			{
				$response= array('status'=>'200','message'=>'get time slot List successfully' ,'data'=>$data );
			}
			else
			{
				$response= array('status'=>'201','message'=>'no record found!' ,'data'=>'' );
			}
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function delete_time_slot_post(){	
		$id = $this->post('id');
		$photographer_id = $this->post('photographer_id');
		$exist = $this->common_model->getsingle('calendars',array('photographer_id' => $photographer_id,'id'=>$id));
		$exist_data = $this->common_model->getsingle('calendars',array('photographer_id' => $photographer_id,'id'=>$id ,'is_booked'=>'1'));
		
		if($photographer_id=='')
		{
			$response= array( 'status'=>'201','message'=>'photographer_id input missing!' , 'data'=>'' );
		}
		if($id=='')
		{
			$response= array( 'status'=>'201','message'=>'id input missing!' , 'data'=>'' );
		}
		if(!$exist)
		{
			$response= array( 'status'=>'201','message'=>'time slot not Exist this id and photographer' , 'data'=>'' );
		}
		if($exist_data)
		{
			$response= array( 'status'=>'201','message'=>'time slot not deleted, is booked' , 'data'=>'' );
		}
		if($photographer_id!='' && !$exist_data && $exist)
		{
			$this->common_model->deleteData('calendars',array('photographer_id' => $photographer_id,'id'=>$id));
			$response= array('status'=>'200','message'=> 'time slot deleted successfully', 'data'=>'' );
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function my_booked_time_slot_list_post(){	
		$photographer_id = $this->post('photographer_id');
		if($photographer_id=='')
		{
			$response= array( 'status'=>'201','message'=>'photographer_id input missing!' , 'data'=>'' );
		}
		if($photographer_id!='')
		{
			$data = $this->common_model->getAllwhere('calendars',array('photographer_id' => $photographer_id,'is_booked'=>'1'));				
			if(count($data)>0)
			{
				$response= array('status'=>'200','message'=>'get my booked time slot List successfully' ,'data'=>$data );
			}
			else
			{
				$response= array('status'=>'201','message'=>'no record found!' ,'data'=>'' );
			}
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	
	function menus_get(){		 
		$site_url = base_url();
		$final_url = str_replace('/api','',$site_url);
		$url = $final_url."wp-admin/admin-ajax.php";
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "action=get_all_menus",	 
		  
		));
		$res = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
			
		if($res!="Not")
		{
			//$response= array('data'=>$res);
			echo $res;
			
		}else{
			$response= array('status'=>'201', 'message'=>'Location Not Exists', 'data'=>'Failed');
			$this->response($response	, 200); // 200 being the HTTP response code
		}
        
	}
	
	function get_profile_post(){	
		$user_id = $this->post('user_id');
		if($user_id=='')
		{
			$response= array( 'message'=>'user_id input missing!' , 'data'=>'Failed' );
		}
		if($user_id!='')
		{
			$data = $this->common_model->getsingle('wp_5cadee33ab_users',array('ID' => $user_id));
			$first_name = $this->common_model->getsingle('wp_5cadee33ab_usermeta',array('user_id' => $user_id,'meta_key'=>'first_name'));
			$last_name = $this->common_model->getsingle('wp_5cadee33ab_usermeta',array('user_id' => $user_id,'meta_key'=>'last_name'));
			$user_role = $this->common_model->getsingle('wp_5cadee33ab_usermeta',array('user_id' => $user_id,'meta_key'=>'user_role'));
			
			$dd = $this->common_model->getAllwhere('wp_5cadee33ab_options',array('option_name'=>'wp_5cadee33ab_user_roles'));
			$datas= unserialize($dd[0]->option_value);
			
			$final_data = array();
			$final_data['id'] 			= $user_id;
			$final_data['username'] 	= $data->user_login;
			$final_data['email'] 		= $data->user_email;
			$final_data['first_name'] 	= $first_name->meta_value;
			$final_data['last_name'] 	= $last_name->meta_value;
			$final_data['role_id'] = "";
			$final_data['role'] = "";
			foreach ($datas as $key=>$value)
			{
				if($key==$user_role ){
				$final_data['role_id'] = $key;
				$final_data['role'] = $datas[$key]['name'];
				
				}
			}
			
			$response= array('data'=>$final_data );
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	function check_payment_post(){
		$stripeToken 		= $this->post('stripeToken');
		if($stripeToken=='')
		{
			$response= array( 'status'=>'201','message'=>'stripeToken input missing!' , 'data'=>'' );
		}
		if($stripeToken!='')
		{
			$amt_final =  "1000";
			$parameters = array('path' =>'https://api.stripe.com/v1/charges','token'=>$stripeToken,'amount'=>$amt_final,'order_id'=>99999);
			//secret key
			//$username="sk_live_AkKoPL21n2vvSdfxcFN0iXVu"; 
			$username="sk_test_EjfkxEsyPfUcSTWQgCUuLcFs"; 
			
			$password= "";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$parameters['path']);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1024); //timeout after 30 seconds
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,"amount=".$parameters['amount']."&currency=eur&source=".$parameters['token']."&metadata[order_id]=".$parameters['order_id']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
			$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
			$result=curl_exec ($ch);
			curl_close ($ch);
			
			$result = json_decode($result);
			
			if(isset($result->paid))
			{
				$response= array( 'status'=>'200','message'=>'payment confirm!' , 'data'=>'10' );
			}
			else
			{
				$response= array( 'status'=>'201','message'=>'payment failed!' , 'data'=>'' );
			}
		}
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
	function booking_slot_post(){
		$id 		= $this->post('id');
		$name 	 	= $this->post('name');
		$mobile_no 	= $this->post('mobile_no');
		$email 	 	= $this->post('email');
		$address 	= $this->post('address');
		if($id=='')
		{
			$response= array( 'status'=>'201','message'=>'id input missing!' , 'data'=>'' );
		}
		if($name=='')
		{
			$response= array( 'status'=>'201','message'=>'name input missing!' , 'data'=>'' );
		}
		if($mobile_no=='')
		{
			$response= array( 'status'=>'201','message'=>'mobile_no input missing!' , 'data'=>'' );
		}
		if($email=='')
		{
			$response= array( 'status'=>'201','message'=>'email input missing!' , 'data'=>'' );
		}
		if($address=='')
		{
			$response= array( 'status'=>'201','message'=>'address input missing!' , 'data'=>'' );
		}
		if($id!="" && $name!='' && $mobile_no!='' && $email!='' && $address!='' )
		{	
			
			$ins_data = array(						
						'is_booked'		=>'1',
						'booker_name'	=> $name,
						'booker_email'	=> $email,
						'booker_no'		=> $mobile_no,
						'amount'		=> '10'
					);
			$this->common_model->updateData('calendars',$ins_data,array('id'=>$id));
			$response= array( 'status'=>'200','message'=>'Booking Confirmed!' , 'data'=>'' );
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code	
	}
    

}
	

