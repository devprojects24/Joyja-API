<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Proposals extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		$this->load->model('ion_auth_model');
		$this->load->model('common_model');
    }
	
	function proposals_post(){
		$id 		= $this->post('id');
		$category 	= $this->post('category');
		$type 		= $this->post('type');
		$sort 		= $this->post('sort');
		$featured 	= $this->post('featured');
		
		$proposals = $this->common_model->getProposals($id,$category,$type,$sort,$featured);
		
		$final_data = array();
		if($proposals)
		{
			foreach($proposals as $po)
			{
				$po->proposal_img1 = SITEURL.'uploads/proposals/'.$po->proposal_img1;
				$po->proposal_img2 = SITEURL.'uploads/proposals/'.$po->proposal_img2;
				$po->proposal_img3 = SITEURL.'uploads/proposals/'.$po->proposal_img3;
				$po->proposal_img4 = SITEURL.'uploads/proposals/'.$po->proposal_img4;
				
				$faq = $this->common_model->getAllwhere('pf_proposals_faqs',array('proposal_id'=>$po->proposal_id));				
				if($faq)
				{
					$po->FAQ = $faq;
				}else{
					$po->FAQ = array();
				}
				
				$params = $this->common_model->getAllwhere('pf_proposal_params',array('proposal_id'=>$po->proposal_id));				
				if($params)
				{
					$po->PARAMS = $params;
				}else{
					$po->PARAMS = array();
				}
				
				$final_data[] = $po;
			}
		}
		
		$req = array(
					'id' 	=> $id,
					'category' 	=> $category,
					'type' 	=> $type,
					'sort' 	=> $sort,
					'featured' 	=> $featured
				);
				
		$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.' ,'request'=> $req,'count'=>count($proposals) , 'data'=>$final_data);
		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function updateFavorites_post(){
		$proposal_id   	= $this->post('proposal_id');
		$user_id 	= $this->post('user_id');
		$action  	= $this->post('action');
		
		$chk_user = $this->common_model->getsingle('users',array('id'=>$user_id));
		$chk_proposals  = $this->common_model->getsingle('pf_proposals',array('proposal_id'=>$proposal_id));
		$chk_favorites  = $this->common_model->getsingle('pf_proposals_favorites',array('proposal_id'=>$proposal_id,'user_id'=>$user_id));
		if($proposal_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid proposal id.' , 'data'=>'' );
		}
		else if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid user id.' , 'data'=>'' );
		}
		else if($action=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid action.' , 'data'=>'' );
		}
		else if(!$chk_user)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User does not exist.' , 'data'=>'' );
		}
		else if(!$chk_proposals)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Proposal does not exist.' , 'data'=>'' );
		}
		else 
		{
			
			$ins_data = array(
				'proposal_id' 	=> $proposal_id,
				'user_id' 	=> $user_id,
				'channel_id' 	=> 1,
				'created_by' 	=> $chk_user->id,
				'created_date' 	=>date('Y-m-d h:i:s'),
				);
			

				$req = array(
					'proposal_id' 	=> $proposal_id,
					'user_id' 	=> $user_id,
					'action' 	=> $action,
				);
				
			if($action=='add'){
				if($chk_favorites){
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Favorites already exist.' , 'data'=>'');
				}else{
					$res = $this->common_model->insertData('pf_proposals_favorites',$ins_data);
					$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Favorites successfully.','request'=> $req, 'data'=>$req);
				}
			}
			if($action=='remove'){
				if(!$chk_favorites){
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Favorites does not exist.' , 'data'=>'' );
				}else{
					$res = $this->common_model->deleteData('pf_proposals_favorites',array('proposal_id'=>$proposal_id,'user_id'=>$user_id));
					$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Favorites removed successfully.','request'=> $req, 'data'=>null );
				}
			}
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function favorites_post(){
		$user_id 	= $this->post('user_id');
		
		$chk_user = $this->common_model->getsingle('users',array('id'=>$user_id));
		
		$chk_favorites  = $this->common_model->getFavorites($user_id);
		
		if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid user id.' , 'data'=>'' );
		}
		else if(!$chk_user)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User does not exist.' , 'data'=>'' );
		}
		else 
		{
			$req = array(
					'user_id' 	=> $user_id,
				);
			if(count($chk_favorites) == 0){
				$response= array( 'status'=>'Ok','code'=>'200','message'=> 'No proposals are added to your favorite list yet','request'=> $req, 'data'=>$req);
			}else{
				
				$finalarray=array();
			
					foreach($chk_favorites as $pf)
					{
						
						$p['proposal_id'] 		= $pf->proposal_id;
						$p['proposal_title']  	= $pf->proposal_title;
						$p['proposal_desc']  	= $pf->proposal_desc;
						$p['proposal_price'] 	= $pf->proposal_price;
						$p['proposal_img1']  	= SITEURL.'uploads/proposals/'.$pf->proposal_img1;
						$finalarray[]=$p;
					}
					
					
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','request'=> $req,'count'=>count($chk_favorites) , 'data'=>$finalarray);
			}
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function challengeDashboard_post(){
		$user_id 	= $this->post('user_id');
		
		$chk_user = $this->common_model->getsingle('users',array('id'=>$user_id));
		
		$chk_challenge = $this->common_model->proposalsdata($user_id);
		
		if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid user id.' , 'data'=>'' );
		}
		else if(!$chk_user)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User does not exist.' , 'data'=>'' );
		}
		else 
		{
			$arr = array();
			$arr['user_id'] 	= $user_id;
			$arr['user_name']  = $chk_user->username;
			$arr['count']  	= count($chk_challenge);
			
				$challenges=array();
			 
					foreach($chk_challenge as $val)
					{
						
						$date1 =  $val->challenge_start_date;
						$dat2e = date('d-m-Y');
						$datetime1 = date_create($date1);
						$datetime2 = date_create($dat2e);
						$interval = date_diff($datetime1, $datetime2);
						$daycoun =  $interval->format('%a');
						$enddate = date('d-m-Y', strtotime('+'.$val->order_duration.' day', strtotime($val->challenge_start_date)));
						/////
						$numrow = $this->db->get_where('pf_proposal_log',array('order_id'=>$val->order_id))->num_rows();
						$totaldays = $val->order_duration;
						$parsntege = ($numrow / $totaldays)*100;
						
						
                    if($daycoun == $val->order_duration){
						$status = "Expiry";
                    }else{
						if($val->challenge_start_date == ""){ 
						$status = "Start";
						}else{ 
						$status = "In Progress";
						} 
					} 
					
						$p['order_id'] 		= $val->order_id;
						$p['proposal_id']  	= $val->proposal_id;
						$p['proposal_title']  	= $val->proposal_title;
						$p['proposal_description'] 	= $val->proposal_price; 
						$p['image']  	= SITEURL.'uploads/proposals/'.$val->proposal_img1;
						$p['duration'] 	= $val->challenge_duration;
						$p['price'] 	= $val->price;
						$p['start_date'] 	= $date1?date('d F Y',strtotime($date1)):"";
						$p['end_date'] 	= $date1?date('d F Y',strtotime($enddate)):"";
						$p['status'] 	= $status;
						$p['perc'] 	= sprintf($parsntege == intval($parsntege) ? "%d" : "%.2f", $parsntege).'%';
						
						$challenges[]=$p;
					}
					
					
				$req = array(
					'user_id' 	=> $user_id,
				);
				
				if($challenges){
					$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','request'=> $req,'data'=>$arr,'challenges '=>$challenges );
				}else{
					$response= array( 'status'=>'failed','code'=>'400','message'=>'No challenges found.','request'=> $req,'data'=>$arr,'challenges '=>"" );
				}
				
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function startChallenge_post(){
		$order_id 	= $this->post('order_id');
		
		$chk_order = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid order id.' , 'data'=>'' );
		}
		else if(!$chk_order)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order ID not found' , 'data'=>'' );
		}
		else 
		{
			$req = array(
					'order_id' 	=> $order_id,
				);
			
			$enddates = date('d-m-Y', strtotime('+'.$chk_order->order_duration.' day', strtotime($chk_order->challenge_start_date)));
			
			$data = array ('challenge_start_date' => date('d-m-Y'),'challenge_end_date'=>$enddates);
			$updatechallenge = $this->common_model->challengestart($order_id,$data);
			
			if($updatechallenge){
				$chk_proposals = $this->common_model->getsingle('pf_proposals',array('proposal_id'=>$chk_order->proposal_id));
				$chk_user = $this->common_model->getsingle('users',array('id'=>$chk_proposals->proposal_seller_id));
				$date1 =  $chk_order->challenge_start_date;
				$dat2e = date('d-m-Y');
				$datetime1 = date_create($date1);
				$datetime2 = date_create($dat2e);
				$interval = date_diff($datetime1, $datetime2);
				$daycoun =  $interval->format('%a');
				$enddate = date('d-m-Y', strtotime('+'.$chk_order->order_duration.' day', strtotime($chk_order->challenge_start_date)));
							
				$p['order_id'] 		= $order_id;
				$p['user_id']  		= $chk_proposals->proposal_seller_id;
				$p['username']  	= $chk_user->username;
				$p['proposal_title'] = $chk_proposals->proposal_title; 
				$p['start_date'] 	= $date1?date('d F Y',strtotime($date1)):"";
				$p['end_date'] 		= $date1?date('d F Y',strtotime($enddate)):"";
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Challenge Started Successfully.','request'=> $req,'data'=>$p );
			}
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function challengeCalendar_post(){
		$order_id 	= $this->post('order_id');
		
		$chk_order = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid order id.' , 'data'=>'' );
		}
		else if(!$chk_order)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order ID does not exist' , 'data'=>'' );
		}
		else 
		{
			
				$chk_proposals = $this->common_model->getsingle('pf_proposals',array('proposal_id'=>$chk_order->proposal_id));
				$chk_user = $this->common_model->getsingle('users',array('id'=>$chk_proposals->proposal_seller_id));
				$date1 =  $chk_order->challenge_start_date;
				
				$enddate = date('d-m-Y', strtotime('+'.$chk_order->order_duration.' day', strtotime($chk_order->challenge_start_date)));
							
				$p['order_id'] 		= $order_id;
				$p['proposal_id']  	= $chk_order->proposal_id;
				$p['proposal_title']= $chk_proposals->proposal_title; 
				$p['start_date'] 	= $date1?date('d F Y',strtotime($date1)):"";
				$p['end_date'] 		= $date1?date('d F Y',strtotime($enddate)):"";
				$p['username']  	= $chk_user->username;
				
				//Calender
				$final_data = array();
				$begin = new DateTime( $chk_order->challenge_start_date );
				$enddate2 = date('Y-m-d', strtotime('+'.$chk_order->order_duration.' day', strtotime($chk_order->challenge_start_date)));
				$end = new DateTime( $enddate2 );
				
				$b = 1;
				
				for($i = $begin; $i <= $end; $i->modify('+1 day')){
					
						if(date('Y-m-d') == $i->format("Y-m-d")){
							$numrow = $this->db->get_where('pf_proposal_log',array('order_id'=>$order_id,'date'=>date('Y-m-d')))->num_rows();
							if($numrow > 0 ){
								$satatus = "Completed";
							}else{
								$satatus = "Pending";
							}
						}else{
							$cdate = strtotime(date('Y-m-d'));
							$calender =  strtotime($i->format("Y-m-d"));
							if($calender < $cdate ){
								$numrow = $this->db->get_where('pf_proposal_log',array('order_id'=>$order_id,'date'=> $i->format("Y-m-d")))->num_rows();
								if($numrow > 0){
									$satatus = "Completed";
								}else{
									$satatus = "Expired"; 
								}
							}else{
								$satatus = 'upcoming';
							}
						} 
				   
						$fd['day'] 		= $b;
						$fd['date'] 	= $i->format("Y-m-d");
						$fd['status'] 	= $satatus;
						
							$totalsumc = "0";     
							$numrow1 = $this->db->get_where('pf_proposal_log',array('order_id'=>$order_id,'date'=>$i->format("Y-m-d")))->num_rows();
							if($numrow1 > 0){
							$totalsum = $this->db->get_where('pf_proposal_score',array('order_id'=>$order_id,'created_date'=>$i->format("Y-m-d")))->result();
								foreach($totalsum as $totalsumdata){
									$totalsumc = $totalsumc + $totalsumdata->score;
								}
							}
							
						$fd['score'] 		= $totalsumc;   
						
						$final_data[] =$fd;
						   
						$b++;
				}
				$req = array(
					'order_id' 	=> $order_id,
				);
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Challenge calender fetched Successfully.','request'=> $req,'data'=>$p,'calendar'=>$final_data);
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function viewChallenge_post(){
		$order_id 	= $this->post('order_id');
		
		$chk_order = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid order id.' , 'data'=>'' );
		}
		else if(!$chk_order)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order ID does not exist' , 'data'=>'' );
		}
		else 
		{
			$chk_proposals = $this->common_model->getsingle('pf_proposals',array('proposal_id'=>$chk_order->proposal_id));
			$chk_user = $this->common_model->getsingle('users',array('id'=>$chk_proposals->proposal_seller_id));
			$date1 =  $chk_order->challenge_start_date;
			$enddate = date('d-m-Y', strtotime('+'.$chk_order->order_duration.' day', strtotime($chk_order->challenge_start_date)));
			
			$chk_params = $this->common_model->getAllwhere('pf_proposal_params',array('proposal_id'=>$chk_proposals->proposal_id));
			
			$p['order_id'] 		= $order_id;
			$p['proposal_id']  		= $chk_order->proposal_id;
			$p['proposal_title'] = $chk_proposals->proposal_title; 
			$p['start_date'] 	= $date1?date('d F Y',strtotime($date1)):"";
			$p['end_date'] 		= $date1?date('d F Y',strtotime($enddate)):"";
			$p['username']  	= $chk_user->username;
			$p['date']  	= $chk_order->order_date;
			$p['param_count']  	= count($chk_params);
			
			$params=array();
			foreach($chk_params as $val)
			{
				$pp['param_title'] 	= $val->param_title;
				$pp['param_value']  	= $val->param_value;
				$pp['param_units']  	= $val->param_units;
				$pp['input_type'] 	= $val->input_type; 
				
				$params[]=$pp;
			}
			
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','data'=>$p,'params'=>$params);
			
		}		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function addChallenge_post(){
		$order_id   	= $this->post('order_id');
		$date 	= $this->post('date');
		$description  	= $this->post('description');
		
		$chk_order = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		$chk_proposals = $this->common_model->getsingle('pf_proposals',array('proposal_id'=>$chk_order->proposal_id));
		$chk_param_count = $this->common_model->record_count('pf_proposal_params',array('proposal_id'=>$chk_order->proposal_id));
		$chk_proposal_score = $this->common_model->getsingle('pf_proposal_score',array('proposal_id'=>$chk_order->proposal_id));
		//echo $chk_param_count; die;
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid order id.' , 'data'=>'' );
		}
		else if($description=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid description.' , 'data'=>'' );
		}
		else if(!$chk_order)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order ID does not exist' , 'data'=>'' );
		}
		else if($chk_proposal_score)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Already exist' , 'data'=>'' );
		}
		else 
		{
			
			$req = array(
					'order_id' 	=> $order_id,
					'date' 	=> $date,
					'description' 	=> $description,
					'param_id1' 	=> $this->post('param_id1'),
					'value1' 	=> $this->post('value1'),
					'param_id2' 	=> $this->post('param_id2'),
					'value2' 	=> $this->post('value2'),
					'param_id3' 	=> $this->post('param_id3'),
					'value3' 	=> $this->post('value3'),
					'param_id4' 	=> $this->post('param_id4'),
					'value4' 	=> $this->post('value4'),
					'param_id5' 	=> $this->post('param_id5'),
					'value5' 	=> $this->post('value5'),
					'param_id6' 	=> $this->post('param_id6'),
					'value6' 	=> $this->post('value6'),
				);
			
			//for ($i = 1; $i <= 6; $i++) {
				if($chk_param_count == 6 && ($this->post('value1')=='' || $this->post('value2')=='' || $this->post('value3')=='' || $this->post('value4')=='' || $this->post('value5')=='' || $this->post('value6')==''))
				{
					$response = array( 'status'=>'failed','code'=>'400','message'=>'Please provide all parameters to update' , 'data'=>'' );
				
				}else{
					
				if($this->post('value1')!='' && $this->post('param_id1')!=''){
					$ins_data = array(
					'proposal_id' 	=> $chk_order->proposal_id,
					'order_id' 	=> $order_id,
					'param_id' 	=>  $this->post('param_id1'),
					'value' 	=>  $this->post('value1'),
					'description' 	=> '',
					'user_id' 	=> $chk_proposals->proposal_seller_id,
					'created_date' 	=>date('Y-m-d',strtotime($date)),
					);	
					$res = $this->common_model->insertData('pf_proposal_score',$ins_data);
				}
				if($this->post('value2')!='' && $this->post('param_id2')!=''){
					$ins_data1 = array(
					'proposal_id' 	=> $chk_order->proposal_id,
					'order_id' 	=> $order_id,
					'param_id' 	=>  $this->post('param_id2'),
					'value' 	=>  $this->post('value2'),
					'description' 	=> '',
					'user_id' 	=> $chk_proposals->proposal_seller_id,
					'created_date' 	=>date('Y-m-d',strtotime($date)),
					);	
					$res = $this->common_model->insertData('pf_proposal_score',$ins_data1);
				}
				if($this->post('value3')!='' && $this->post('param_id3')!=''){
					$ins_data2 = array(
					'proposal_id' 	=> $chk_order->proposal_id,
					'order_id' 	=> $order_id,
					'param_id' 	=>  $this->post('param_id3'),
					'value' 	=>  $this->post('value3'),
					'description' 	=> '',
					'user_id' 	=> $chk_proposals->proposal_seller_id,
					'created_date' 	=>date('Y-m-d',strtotime($date)),
					);	
					$res = $this->common_model->insertData('pf_proposal_score',$ins_data2);
				}
				if($this->post('value4')!='' && $this->post('param_id4')!=''){
					$ins_data3 = array(
					'proposal_id' 	=> $chk_order->proposal_id,
					'order_id' 	=> $order_id,
					'param_id' 	=>  $this->post('param_id4'),
					'value' 	=>  $this->post('value4'),
					'description' 	=> '',
					'user_id' 	=> $chk_proposals->proposal_seller_id,
					'created_date' 	=>date('Y-m-d',strtotime($date)),
					);	
					$res = $this->common_model->insertData('pf_proposal_score',$ins_data3);
				}
				if($this->post('value5')!='' && $this->post('param_id5')!=''){
					$ins_data4 = array(
					'proposal_id' 	=> $chk_order->proposal_id,
					'order_id' 	=> $order_id,
					'param_id' 	=>  $this->post('param_id5'),
					'value' 	=>  $this->post('value5'),
					'description' 	=> $description,
					'user_id' 	=> $chk_proposals->proposal_seller_id,
					'created_date' 	=>date('Y-m-d',strtotime($date)),
					);	
					$res = $this->common_model->insertData('pf_proposal_score',$ins_data4);
				}
				if($this->post('value6')!='' && $this->post('param_id6')!=''){
					$ins_data5 = array(
					'proposal_id' 	=> $chk_order->proposal_id,
					'order_id' 	=> $order_id,
					'param_id' 	=>  $this->post('param_id6'),
					'value' 	=>  $this->post('value6'),
					'description' 	=> '',
					'user_id' 	=> $chk_proposals->proposal_seller_id,
					'created_date' 	=>date('Y-m-d',strtotime($date)),
					);	
					$res = $this->common_model->insertData('pf_proposal_score',$ins_data5);
				}
					
				}
			//}
			
					$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Add challenge successfully.','request'=> $req, 'data'=>$req  );
				
			}
			
			
			
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function deleteScore_post(){
		$order_id  	 = $this->post('order_id');
		$scores_date  = $this->post('score_date');
		//$scores_date = date('Y-m-d',strtotime($score_date));
		
		$chkorder_id1 = $this->common_model->getsingle('pf_proposal_score',array('order_id'=>$order_id));
		$chkorder_id2 = $this->common_model->getsingle('pf_proposal_log',array('order_id'=>$order_id));
		$chkscore1 = $this->common_model->getsingle('pf_proposal_score',array('created_date'=>$scores_date));
		$chkscore2 = $this->common_model->getsingle('pf_proposal_log',array('date'=>$scores_date));
		
		$chkLogtbl2 = $this->common_model->getsingle('pf_proposal_log',array('order_id'=>$order_id,'date'=>$scores_date));
		$chkScortbl2 = $this->common_model->getsingle('pf_proposal_score',array('order_id'=>$order_id,'created_date'=>$scores_date));
		
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Order ID.' , 'data'=>'' );
		}
		else if($scores_date=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid score date.' , 'data'=>'' );
		}
		else if(!$chkorder_id1 || !$chkorder_id2)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order id does not exist.' , 'data'=>'' );
		}
		else if(!$chkscore1 || !$chkscore2)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Date does not exist.' , 'data'=>'' );
		}
		else if(!$chkLogtbl2 || !$chkScortbl2)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order id and date does not exist.' , 'data'=>'' );
		}
		else
		{
		
			$req = array(
				'order_id' 	=> $order_id,
				'score_date' => $scores_date,
			);
				$deletedata1 = $this->common_model->deleteData('pf_proposal_score',array('order_id'=>$order_id,'created_date'=>$scores_date));
				$deletedata2 = $this->common_model->deleteData('pf_proposal_log',array('order_id'=>$order_id,'date'=>$scores_date));
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data deleted successfully.','request'=> $req, 'data'=>$req);
		}	
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	
	function challengeReport_post(){
		$order_id  	= $this->post('order_id');
		
		$orderdata = $this->db->get_where('pf_orders',array('order_id'=> $order_id))->row(); 
		$pf_proposalsdata = $this->db->get_where('pf_proposals', array('proposal_id'=>$orderdata->proposal_id))->row();
		$proposalparams = $this->db->get_where('pf_proposal_params',array('proposal_id'=>$orderdata->proposal_id))->result();

		$pfproposalparamsfileid = $this->db->get_where('pf_proposal_params',array('proposal_id'=>$orderdata->proposal_id,'input_type' => 'file'))->row(); 
		$endimgscore = $this->db->order_by("score_id", "desc")->get_where('pf_proposal_score',array('proposal_id'=>$orderdata->proposal_id,'param_id'=>$pfproposalparamsfileid->param_id,'order_id'=>$order_id))->row();
		$startimgscore = $this->db->get_where('pf_proposal_score',array('proposal_id'=>$orderdata->proposal_id,'param_id'=>$pfproposalparamsfileid->param_id,'order_id'=>$order_id,'created_date' =>$orderdata->challenge_start_date))->row();

		$begin = new DateTime( $orderdata->challenge_start_date );
		$enddate = date('Y-m-d', strtotime('+'.($orderdata->order_duration - 1).' day', strtotime($orderdata->challenge_start_date)));
		$end = new DateTime( $enddate );

		if($order_id =='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid order id.' , 'data'=>'' );
		}
		else 
		{
			$req = array(
					'order_id ' 	=> $order_id,
			);
			
			$finalarray=array();
			$pp=array();
			$a = 1;
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
			{
				$numrow = $this->db->get_where('pf_proposal_log',array('order_id'=>$order_id,'date'=>$i->format("Y-m-d")))->num_rows();
				$totalsum = $this->db->get_where('pf_proposal_score',array('order_id'=>$order_id,'created_date'=>$i->format("Y-m-d")))->result();
				
				$p['day'] 		= "Day ".$a++;
				$p['Date'] 		= date('d-m-y',strtotime($i->format("Y-m-d")));
				
				if($numrow > 0)
				{
					foreach($proposalparams as $proposalparamsdata)
					{ 
						$singlevalue = $this->db->get_where('pf_proposal_score',array('order_id'=>$order_id,'created_date'=>$i->format("Y-m-d"),'param_id'=>$proposalparamsdata->param_id))->row();
						$cols = $proposalparamsdata->param_value .'_'. $proposalparamsdata->param_units .'_'. $proposalparamsdata->param_title;
						$cols = str_replace(",","_",$cols);
						$cols = str_replace(" ","_",$cols);
							
							$pp['value'] 		= $singlevalue->value;
							$pp['score'] 		= $singlevalue->score.'%';
							
						$colVal = $pp; 
						$p[$cols] 		= $colVal;						
					}
				}
				else
				{
					foreach($proposalparams as $proposalparamsdata)
					{ 
						
						$cols = $proposalparamsdata->param_value .'_'. $proposalparamsdata->param_units .'_'. $proposalparamsdata->param_title;
						$cols = str_replace(",","_",$cols);
						$cols = str_replace(" ","_",$cols);
						$pp['value'] 		= '';
						$pp['score'] 		= '';
						$p[$cols] 		= $pp;						
					}
				}
				
				if($numrow > 0)
				{
					$totalsumc = 0;
						foreach($totalsum as $totalsumdata)
						{
							$totalsumc = $totalsumc + $totalsumdata->score;
						}
					$p['daily_Score'] = $totalsumc.'%';
                }
				else
				{
					$p['daily_Score'] = '';
				}
				
				
				$finalarray[]=$p;
			}
			
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','request'=> $req, 'data'=>$finalarray);
		
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
	}
	
	function reviews_post(){
		
		$order_id  	= $this->post('order_id');
		$proposal_id  	= $this->post('proposal_id');
		$buyer_id  	= $this->post('buyer_id');
		$seller_id  	= $this->post('seller_id');
		
		$chk_review_order = $this->common_model->getsingle('pf_buyer_reviews',array('publish'=>1,'order_id'=>$order_id));
		$chk_review_proposal = $this->common_model->getsingle('pf_buyer_reviews',array('publish'=>1,'proposal_id'=>$proposal_id));
		$chk_review_buyer = $this->common_model->getsingle('pf_buyer_reviews',array('publish'=>1,'review_buyer_id'=>$buyer_id));
		$chk_review_seller = $this->common_model->getsingle('pf_buyer_reviews',array('publish'=>1,'review_seller_id'=>$seller_id));
		
		if($order_id!='' && !$chk_review_order)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order ID not found.' , 'data'=>'' );
		}
		else if($proposal_id!='' && !$chk_review_proposal)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Proposal ID not found' , 'data'=>'' );
		}
		else if($buyer_id!='' && !$chk_review_buyer)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Buyer ID not found' , 'data'=>'' );
		}
		else if($seller_id!='' && !$chk_review_seller)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Seller ID not found' , 'data'=>'' );
		}
		else 
		{
			$review = $this->common_model->getAllReviews($order_id,$proposal_id,$buyer_id,$seller_id);
			
			$final_arr=array();
			foreach($review as $val)
			{
				
				$pp['review_id'] 	= $val->review_id;
				$pp['proposal_id']  = $val->proposal_id;
				$pp['order_id'] 	= $val->order_id; 
				$pp['buyer_id'] 	= $val->review_buyer_id; 
				$pp['seller_id'] 	= $val->review_seller_id; 
				$pp['buyer_rating'] 	= $val->buyer_rating; 				
				$pp['buyer_review'] 	= $val->buyer_review; 				
				$pp['review_date'] 	= $val->review_date; 				
				$pp['channel_id'] 	= $val->channel_id; 				
				
				$final_arr[]=$pp;
			}
			
			$req['order_id'] 		= $order_id;
			$req['proposal_id']  	= $proposal_id;
			$req['buyer_id']  		= $buyer_id;
			$req['seller_id']  		= $seller_id;
			
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.','request'=> $req,'data'=>$final_arr);
			
		}		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function challengeCertificate_post(){
		$order_id  	 = $this->post('order_id');
		
		$chkorder = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Order ID.' , 'data'=>'' );
		}
		else if(!$chkorder)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Order_Id not found.' , 'data'=>'' );
		}
		else if($chkorder->order_status!=14)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Challenge not yet completed.' , 'data'=>'' );
		}
		else
		{
			$orderdata = $this->db->get_where('pf_orders', array('order_id' => $order_id))->row();
			$userdata = $this->db->get_where('users', array('id' => $orderdata->buyer_id))->row();
			$totalscrolog = $this->db->get_where('pf_proposal_log',array('order_id'=>$order_id))->num_rows();
			$parsntege = ($totalscrolog / $orderdata->order_duration)*100;
			$proposaldata = $this->db->get_where('pf_proposals', array('proposal_id'=>$orderdata->proposal_id))->row();
			$proprem = $this->db->get_where('pf_proposal_params', array('proposal_id' => $proposaldata->proposal_id, 'input_type' => 'file'))->row();
			$proposalimages = $this->db->get_where('pf_proposal_score', array('order_id'=> $order_id,'param_id' => $proprem->param_id))->result();
			
			if($proposaldata->eligibility_score <= $parsntege) {
				$ClaimYourPrize = "yes";
			}else{
				$ClaimYourPrize = "no";
			}
			
			foreach ($proposalimages as $proimages){
				$days = (intval(abs(strtotime($proimages->created_date) - strtotime($orderdata->challenge_start_date))/86400) + 1);
				if($days){
				$img["day".$days] = SITEURL."uploads/proposal_score/".$proimages->description;
				}else{
				$img["day"] = "";	
				}
			}
			
			$data = array(
				'proposal_id' 	=> $orderdata->proposal_id,
				'challenge_name' 	=> $proposaldata->proposal_title,
				'user_name' 	=> $userdata->username,
				'order_id' 	=> $order_id,
				'profile_image' 	=> SITEURL."uploads/".$userdata->img_url,
				'average_score' 	=> sprintf($parsntege == intval($parsntege) ? "%d" : "%.1f", $parsntege).'%',
				'duration' 	=> $orderdata->order_duration,
				'start_date' 	=> $orderdata->challenge_start_date,
				'end_date' 	=> date('d-m-Y',strtotime($orderdata->challenge_end_date)),
				'prizes' 	=> $proposaldata->prizes,
				'claim_prize' 	=> $ClaimYourPrize,
				'images' 	=> $img,
			);
			
			$req = array(
				'order_id' 	=> $order_id,
			);

			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched successfully.','request'=> $req, 'data'=>$data);
		}	
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
    

}
	

