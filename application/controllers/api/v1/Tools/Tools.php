<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Tools extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		$this->load->model('ion_auth_model');
		$this->load->model('common_model');
    }
	
	function bmi_post(){
		 
		$weight   	= $this->post('weight');
		$height_feet = $this->post('height_feet');
		$height_inch = $this->post('height_inch');
		$age 		= $this->post('age');
		$gender 	= $this->post('gender');
		
		if($weight=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid weight, provide weight in kgs.' , 'data'=>'' );
		}
		else if($height_feet == '')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in feet.' , 'data'=>'' );
		}
		else if($height_inch == '')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in inches.' , 'data'=>'' );
		}
		else if($age=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Provide appropriate age in years' , 'data'=>'' );
		}
		else if($gender=='' || ($gender!='male' && $gender!='female'))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid gender, Gender should be male or female' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $weight))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid weight, provide weight in kgs.' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $height_feet) && !preg_match('/^([0-9]*)$/', $height_inch))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in feet and inches.' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $age))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Provide appropriate age in years.' , 'data'=>'' );
		}
		else 
		{
			$fheight = ($height_feet*12) + $height_inch;
			
			$hgt_mtr = $fheight *0.0254;
			
			$hgt_square = bcmul($hgt_mtr, $hgt_mtr,5);
			
			$bmi = $weight/$hgt_square;
			
			if($bmi <= 18.5) {
				$msg = "Underweight";
			}else if ($bmi > 18.5 AND $bmi<=24.9 ) {
				$msg = "Normal weight";
			}else if ($bmi > 25 AND $bmi<=29.9) {
				$msg = "Overweight";
			}else if ($bmi > 30 AND $bmi<=34.9) {
				$msg = "Obese";
			}else if ($bmi > 35 AND $bmi<=39.9) {
				$msg = "Obese";
			}else if ($bmi > 40) {
				$msg = "Obese";
			}
			
			$bmidata = array(
					'bmi' 	=> $bmi,
					'bmi_category' 	=> $msg,
				);
				
			$req = array(
					'weight' 	=> $weight,
					'height_feet' 	=> $height_feet,
					'height_inch' 	=> $height_inch,
					'age' 		=> $age,
					'gender' 	=> $gender,
				);
				
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched Successfully.','request'=> $req, 'data'=>$bmidata);	
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function bmr_post(){
		$weight   	= $this->post('weight');
		$height_feet = $this->post('height_feet');
		$height_inch = $this->post('height_inch');
		$age 		= $this->post('age');
		$gender 	= $this->post('gender');
		
		if($weight=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid weight, provide weight in kgs.' , 'data'=>'' );
		}
		else if($height_feet == '')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in feet.' , 'data'=>'' );
		}
		else if($height_inch == '')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in inches.' , 'data'=>'' );
		}
		else if($age=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Provide appropriate age in years' , 'data'=>'' );
		}
		else if($gender=='' || ($gender!='male' && $gender!='female'))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid gender, Gender should be male or female' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $weight))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid weight, provide weight in kgs.' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $height_feet) && !preg_match('/^([0-9]*)$/', $height_inch))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in feet and inches.' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $age))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Provide appropriate age in years.' , 'data'=>'' );
		}
		else 
		{
			$inch_height = ($height_feet*12) + $height_inch;
			$fheight = $inch_height *2.54;
			
			if($gender == "male"){
				$BMR = (10*$weight) + (6.25*$fheight) - (5*$age) + 5;

			}else{
				$BMR = (10*$weight) + (6.25*$fheight) - (5*$age) - 161;
			}
			
			
		
			if($gender == "male"){

				$BMR2 = (13.397*$weight) + (4.799*$fheight) - (5.677*$age) + 88.362;

			}else{
				
				$BMR2 = (9.247*$weight) + (3.098*$fheight) - (4.330*$age) + 447.593;
			}
			
			$bmidata = array(
					'bmr_method1' 	=> $BMR,
					'bmr_method2' 	=> $BMR2,
				);
				
			$req = array(
					'weight' 	=> $weight,
					'height_feet' => $height_feet,
					'height_inch' => $height_inch,
					'age' => $age,
					'gender' 	=> $gender,
				);
				
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched Successfully.','request'=> $req, 'data'=>$bmidata);	
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function calorie_post(){
		$weight   	= $this->post('weight');
		$height_feet = $this->post('height_feet');
		$height_inch = $this->post('height_inch');
		$age 		= $this->post('age');
		$gender 	= $this->post('gender');
		$activity 	= $this->post('activity');
		
		if($weight=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid weight, provide weight in kgs.' , 'data'=>'' );
		}
		else if($activity=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid activity.' , 'data'=>'' );
		}
		else if($height_feet == '')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in feet.' , 'data'=>'' );
		}
		else if($height_inch == '')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in inches.' , 'data'=>'' );
		}
		else if($age=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Provide appropriate age in years' , 'data'=>'' );
		}
		else if($gender=='' || ($gender!='male' && $gender!='female'))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid gender, Gender should be male or female' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $weight))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid weight, provide weight in kgs.' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $height_feet) && !preg_match('/^([0-9]*)$/', $height_inch))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Height . Provide height in feet and inches.' , 'data'=>'' );
		}
		else if(!preg_match('/^([0-9]*)$/', $age))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Provide appropriate age in years.' , 'data'=>'' );
		}
		else 
		{
			
			$inch_height = ($height_feet*12) + $height_inch;	
			$fheight = $inch_height *2.54;
			
			if($gender == "male"){
				$BMR = (10*$weight) + (6.25*$fheight) - (5*$age) + 5;

			}else{
				$BMR = (10*$weight) + (6.25*$fheight) - (5*$age) - 161;
			}
			
			
		
			if($gender == "male"){

				$BMR2 = (13.397*$weight) + (4.799*$fheight) - (5.677*$age) + 88.362;

			}else{
				
				$BMR2 = (9.247*$weight) + (3.098*$fheight) - (4.330*$age) + 447.593;
			}
			
			if($BMR){
				$act_res= $BMR * $activity;
				$moderately = $act_res - 250;
				$extremely = $act_res - 500;
				$bmrMethod1 = array(
					'to_current_weight' =>$act_res,
					'moderately' 	=> $moderately,
					'extremely' 	=> $extremely,
				);
			}
			if($BMR2){
				$act_res= $BMR2 * $activity;
				$moderately = $act_res - 250;
				$extremely = $act_res - 500;
				$bmrMethod2 = array(
					'to_current_weight' =>$act_res,
					'moderately' 	=> $moderately,
					'extremely' 	=> $extremely,
				);
			}
			
			
			
			$bmidata = array(
					'calorie_by_bmr_method1' 	=> $bmrMethod1,
					'calorie_by_bmr_method2' 	=> $bmrMethod2,
				);
				
			$req = array(
					'weight' 	=> $weight,
					'height_feet' => $height_feet,
					'height_inch' => $height_inch,
					'gender' 	=> $gender,
					'age' 	=> $age,
					'activity' 	=> $activity,
				);
				
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched Successfully.','request'=> $req, 'data'=>$bmidata);	
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function proglyco_post(){
		$items   	= $this->post('items');
		$terms = $this->post('terms');
		
		if($items=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide Items' , 'data'=>'' );
		}
		else if($terms=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide valid terms.' , 'data'=>'' );
		}
		else if($terms!='yes')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide valid terms.' , 'data'=>'' );
		}
		else 
		{
			
			$sym =  'Provide me only glycemic index and glycemic load figure for '.$items;
			if($sym == ""){
				$textfont="";
			}
			else
			{
				$dTemperature = 0.9;
				$iMaxTokens = 300;
				$top_p = 1;
				$frequency_penalty = 0.0;
				$presence_penalty = 0.0;
				$OPENAI_API_KEY = "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";
				$sModel = "text-davinci-003";
				//$sModel="BioMed-RoBERTa";
				//$sModel="Clinical-Bert";
				$prompt = $sym;
				$ch = curl_init();
				$headers  = [
				  'Accept: application/json',
				  'Content-Type: application/json',
				  'Authorization: Bearer ' . $OPENAI_API_KEY . ''
				];

				$postData = [
				  'model' => $sModel,
				  'prompt' => str_replace('"', '', $prompt),
				  'temperature' => $dTemperature,
				  'max_tokens' => $iMaxTokens,
				  'top_p' => $top_p,
				  'frequency_penalty' => $frequency_penalty,
				  'presence_penalty' => $presence_penalty,
				  'stop' => '[" Human:", " AI:"]',
				];

				curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

				$result = curl_exec($ch);

				$decoded_json = json_decode($result, true);
				  

				$textfont = $decoded_json['choices'][0]['text'];
				$datalog = array(
				  'ip_address'=>$_SERVER['REMOTE_ADDR'],
				  'activity'=>'sympton request',
				  'activity_date_time'=>date('d-m-Y H:i:s')
				);

				$this->common_model->insertData('pf_logs',$datalog);  
			}
			$req = array(
					'items' 	=> $items,
					'terms' => $terms,
				);
				
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched Successfully.','request'=> $req, 'data'=>$textfont);	
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function prosympto_post(){
		$age   	= $this->post('age');
		$gender = $this->post('gender');
		$sym = $this->post('symptoms');
		$from = $this->post('from');
		$param = $this->post('period');
		$terms = $this->post('terms');
		
		if($age=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Pls provide proper data' , 'data'=>'' );
		}
		if(!preg_match('/^([0-9]*)$/', $age))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid Age. Pls provide proper data' , 'data'=>'' );
		}
		else if($gender=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid gender. Pls provide proper data.' , 'data'=>'' );
		}
		else if($gender!='male' && $gender!='female')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid gender. Pls provide proper data.' , 'data'=>'' );
		}
		else if($sym=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide valid symptoms.' , 'data'=>'' );
		}
		else if($from=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid from. Pls provide proper data' , 'data'=>'' );
		}
		if(!preg_match('/^([0-9]*)$/', $from))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid from. Pls provide proper data' , 'data'=>'' );
		}
		else if($param=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid period. Pls provide proper data' , 'data'=>'' );
		}
		else if($param!='days' && $param!='weeks' && $param!='months')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid period. Pls provide proper data' , 'data'=>'' );
		}
		else if($terms!='yes')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Provide valid terms.' , 'data'=>'' );
		}
		else 
		{
			$sym="Following is the list of 3 potential health condition(s) that may be causing the symptoms mentioned by you. " . $sym." since ". $from." - ". $param. " For ". $age . " Years " .$gender;  
			//$data['texttitlefont'] = "<font color=blue>".$sym."</font>"; 
			//echo "<br>";
			$dTemperature = 0.9;
			$iMaxTokens = 200;
			$top_p = 1;
			$frequency_penalty = 0.0;
			$presence_penalty = 0.0;
			$OPENAI_API_KEY = "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";
			$sModel = "text-davinci-003";
			//$sModel="BioMed-RoBERTa";
			//$sModel="Clinical-Bert";
			$prompt = $sym;
			$ch = curl_init();
			$headers  = [
			  'Accept: application/json',
			  'Content-Type: application/json',
			  'Authorization: Bearer ' . $OPENAI_API_KEY . ''
			];

			$postData = [
			  'model' => $sModel,
			  'prompt' => str_replace('"', '', $prompt),
			  'temperature' => $dTemperature,
			  'max_tokens' => $iMaxTokens,
			  'top_p' => $top_p,
			  'frequency_penalty' => $frequency_penalty,
			  'presence_penalty' => $presence_penalty,
			  'stop' => '[" Human:", " AI:"]',
			];

			curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

			$result = curl_exec($ch);
			$decoded_json = json_decode($result, true);

			$textfont = $decoded_json['choices'][0]['text'];

			
			$datalog = array(
			  'ip_address'=>$_SERVER['REMOTE_ADDR'],
			  'activity'=>'sympton request',
			  'activity_date_time'=>date('d-m-Y H:i:s')
			);
		$this->common_model->insertData('pf_logs',$datalog);
					
					
			$req = array(
					'age' 	=> $age,
					'gender' 	=> $gender,
					'symptoms' 	=> $this->post('symptoms'),
					'from' 	=> $from,
					'period' 	=> $param,
					'terms' => $terms,
				);
				
				
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data fetched Successfully.','request'=> $req, 'data'=>$textfont);	
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
    

}
	

