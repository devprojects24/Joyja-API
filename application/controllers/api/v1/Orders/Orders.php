<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Orders extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		$this->load->model('ion_auth_model');
		$this->load->model('common_model');
    }
	
	function orders_post(){
		$order_id 	= $this->post('order_id');
		$buyer_id 	= $this->post('buyer_id');
		$seller_id 	= $this->post('seller_id');
		$order_date = $this->post('order_date');
		
		if($order_id=='' && $buyer_id=='' && $seller_id=='' && $order_date=='' )
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'order_id OR buyer_id OR seller_id OR order_date required.' , 'data'=>'' );
		}
		else
		{
			$orders = $this->common_model->getorders($order_id,$buyer_id,$seller_id,$order_date);
			
			$final_data = array();
			if($orders)
			{
				foreach($orders as $or)
				{	
					$final_data[] = $or;
				}
				
			}
			
			$req = array(
					'order_id' 	=> $order_id,
					'buyer_id' 	=> $buyer_id,
					'seller_id' 	=> $seller_id,
					'order_date' 	=> $order_date
				);
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.' , 'request'=> $req, 'count'=>count($orders),'data'=>$final_data  );
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function updateReview_post(){
		$order_id 	= $this->post('order_id');
		$rating 	= $this->post('rating');
		$review 	= $this->post('review');
		
		$chkorder = $this->common_model->getsingle('pf_orders',array('order_id'=>$order_id));
		
		if($order_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Please provide order id.' , 'data'=>'' );
		}
		elseif($rating=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Please provide rating.' , 'data'=>'' );
		}
		elseif(!is_numeric($rating) )
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid Rating.' , 'data'=>'' );
		}
		elseif($rating < 1 || $rating > 5)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Invalid Rating.' , 'data'=>'' );
		}
		elseif($review=='')
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Please provide review details.' , 'data'=>'' );
		}
		elseif(!$chkorder)
		{
			$response= array( 'status'=>'failed','code'=>'201','message'=>'Order id not found.' , 'data'=>'' );
		}
		else
		{
			$datareq = array(
					'order_id' 	=> $order_id,
					'buyer_rating' 	=> $rating,
					'buyer_review' 	=> $review,
					'review_buyer_id' 	=> $chkorder->buyer_id,
					'review_seller_id' 	=> $chkorder->seller_id,
					'proposal_id' 	=> $chkorder->proposal_id,
					'channel_id'	=> '1',
					'review_date' 	=> date('Y-m-d H:i:s'),
				);
			$updateData = $this->common_model->insertData('pf_buyer_reviews',$datareq);
			
			$req = array(
					'order_id' 	=> $order_id,
					'rating' 	=> $rating,
					'review' 	=> $review,
				);
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Review Inserted Successfully.', 'request'=> $req ,'data'=>$req);
		}
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}

    

}
	

