<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Blogs extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		$this->load->model('ion_auth_model');
		$this->load->model('common_model');
    }
	
	function blogs_post(){
		$blog_id   	= $this->post('blog_id');
		$featured 	= $this->post('featured');
		$popular 	= $this->post('popular');
		$category = $this->post('category');
		$orders = $this->common_model->getblogs($blog_id,$featured,$popular,$category);
			
		$final_data = array();
		if($orders)
		{ 
			
			foreach($orders as $or)
			{
				$or->comments = array();
				$or->image = SITEURL.$or->image;				
				$images = $this->common_model->getAllwhere('blog_images',array('blog_id'=>$or->blog_id));				
				if($images)
				{
					$or->images = $images;
				}else{
					$or->images = array();
				}
				
				
				$comments = $this->common_model->getAllwhere('blogs_comments',array('blog_id'=>$or->blog_id));
				if($comments)
				{
					foreach($comments as $val){
						$or->comments[] = $val->comments;
					}
				}else{
					$or->comments[] = array();
				}
				
				$likes = $this->common_model->getAllwhere('blogs_likes',array('blog_id'=>$or->blog_id));
				$or->likes = count($likes);
				
				$final_data[] = $or;
			}
		}
		
		$req = array(
					'blog_id' 	=> $blog_id,
					'featured' 	=> $featured,
					'popular' 	=> $popular,
					'category' 	=> $category
				);
		$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.', 'request'=> $req ,'count'=>count($orders), 'data'=>$final_data );
	
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function updateComments_post(){
		$blog_id   	= $this->post('blog_id');
		$user_id 	= $this->post('user_id');
		$comment 	= $this->post('comment');
		 //echo $blog_id; die;
		$chk_user = $this->common_model->getsingle('users',array('id'=>$user_id));
		$chk_blog  = $this->common_model->getsingle('blogs',array('blog_id'=>$blog_id));
		if($blog_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid blog id.' , 'data'=>'' );
		}
		else if($user_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid user id.' , 'data'=>'' );
		}
		else if($comment=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid comment.' , 'data'=>'' );
		}
		else if(!$chk_user)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'User does not exist.' , 'data'=>'' );
		}
		else if(!$chk_blog)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Blog does not exist.' , 'data'=>'' );
		}
		else 
		{
			$req = array(
				'blog_id' 	=> $blog_id,
				'user_id' 	=> $user_id,
				'comments' 	=> $comment,
				);
			$ins_data = array(
				'user_ip' => $_SERVER['REMOTE_ADDR'],
				'blog_id' 	=> $blog_id,
				'user_id' 	=> $user_id,
				'comments' 	=> $comment,
				'channel_id' 	=> 1,
				'created_by' 	=> $chk_user->id,
				);
			$res = $this->common_model->insertData('blogs_comments',$ins_data);
			
			$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Comments updated successfully.', 'request'=> $req, 'data'=>$req);
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function updateLikes_post(){
		$blog_id   	= $this->post('blog_id');
		$user_id 	= $this->post('user_id');
		$action  	= $this->post('action');
		
		$chk_user = $this->common_model->getsingle('users',array('id'=>$user_id));
		$chk_blog  = $this->common_model->getsingle('blogs',array('blog_id'=>$blog_id));
		if($blog_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid blog id.' , 'data'=>'' );
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
		else if(!$chk_blog)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Blog does not exist.' , 'data'=>'' );
		}
		else 
		{
			
			$ins_data = array(
				'user_ip' => $_SERVER['REMOTE_ADDR'],
				'blog_id' 	=> $blog_id,
				'user_id' 	=> $user_id,
				'channel_id' 	=> 1,
				'created_by' 	=> $chk_user->id,
				'created_date' 	=>date('Y-m-d h:i:s'),
				);
			
				$req = array(
					'blog_id' 	=> $blog_id,
					'user_id' 	=> $user_id,
					'action' 	=> $action,
				);
				
			if($action=='add'){
				$res = $this->common_model->insertData('blogs_likes',$ins_data);
				$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Like updated successfully.', 'request'=> $req ,'data'=>$req  );
			}
			if($action=='remove'){
				$res = $this->common_model->deleteData('blogs_likes',array('blog_id'=>$blog_id,'user_id'=>$user_id));
				$response= array( 'status'=>'Ok','code'=>'200','message'=> 'Like removed successfully.', 'request'=> $req ,'data'=>$req);
			}
			
		}		
		
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function categories_post(){
		
		$category_id   	= $this->post('category_id');		
		$chkcategory = $this->common_model->getsingle('blog_categories',array('id'=>$category_id));
		
		if($category_id!='' && !$chkcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Category not found.' , 'data'=>'' );
		}
		else
		{
			$blog_categories = $this->common_model->getcateories($category_id);
			$final_data = array();
			if($blog_categories)
			{
				foreach($blog_categories as $or)
				{
					$or->image = SITEURL.$or->image;
					$final_data[] = $or;
				}
			}
		
			$req = array(
					'category_id' 	=> $category_id,
				);
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.' ,'request'=> $req,'count'=>count($blog_categories),  'data'=>$final_data );
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function subCategories_post(){
		
		$category_id   	= $this->post('category_id');
		$subcategory_id = $this->post('subcategory_id');
		
		$chkcategory = $this->common_model->getsingle('blog_categories',array('id'=>$category_id));
		$chksubcategory = $this->common_model->getsingle('blog_subcategories',array('subcat_id'=>$subcategory_id));
		
		if($category_id!='' && !$chkcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Category ID not found.' , 'data'=>'' );
		}
		else if($subcategory_id!='' && !$chksubcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Sub category_id not found.' , 'data'=>'' );
		}
		else
		{
			$blog_categories = $this->common_model->getsubcateories($category_id,$subcategory_id);
			$final_data = array();
			if($blog_categories)
			{
				foreach($blog_categories as $or)
				{
					$or->image = SITEURL.$or->image;
					$final_data[] = $or;
				}
			}
			$req = array(
					'category_id' 	=> $category_id,
					'subcategory_id' 	=> $subcategory_id,
				);
			$response= array( 'status'=>'Ok','code'=>'200','message'=>'Data Fetched Successfully.' ,'request'=> $req,'count'=>count($blog_categories), 'data'=>$final_data );
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function addBlog_post(){
		$category_id   	= $this->post('category_id');
		$subcategory_id = $this->post('subcategory_id');
		$title = $this->post('title');
		$creator = $this->post('creator');
		$content = $this->post('content');
		$featured  = $this->post('featured');
		$popular  = $this->post('popular');
		$tags   = $this->post('tags');
		$metatags = $this->post('metatags');
		$publish = $this->post('publish');
			
		$ext='';
		$ext1='';
		$supported_image = array('jpg','jpeg','png');
		if(isset($_FILES['image']))
		{
			$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
		}
		if(isset($_FILES['banner']))
		{
			$ext1 = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
		}
		
		$chkcategory = $this->common_model->getsingle('blog_categories',array('id'=>$category_id));
		$chksubcategory = $this->common_model->getsingle('blog_subcategories',array('subcat_id'=>$subcategory_id));
		$chktitle = $this->common_model->getsingle('blogs',array('title'=>$title));
		
		
		if($category_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid category id.' , 'data'=>'' );
		}
		else if($category_id!='' && !$chkcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Category not found.' , 'data'=>'' );
		}
		else if($subcategory_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid sub category id.' , 'data'=>'' );
		}
		else if($subcategory_id!='' && !$chksubcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Sub Category not found.' , 'data'=>'' );
		}
		else if($title=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid title.' , 'data'=>'' );
		}
		else if($chktitle)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Blog Already exists.' , 'data'=>'' );
		}
		else if($creator=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid creator.' , 'data'=>'' );
		}
		else if(!isset($_FILES['image']))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid image.' , 'data'=>'' );
		}
		else if(!in_array($ext, $supported_image))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Allowed image jpeg,jpg,png only.' , 'data'=>'' );
		}
		else if(!isset($_FILES['banner']))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid banner.' , 'data'=>'' );
		}
		else if(!in_array($ext1, $supported_image))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Allowed banner jpeg,jpg,png only.' , 'data'=>'' );
		}
		else if($content=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid content.' , 'data'=>'' );
		}
		else if($featured=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid featured.' , 'data'=>'' );
		}
		else if($featured!="yes" && $featured!="no")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid featured input pls provide yes or no value.' , 'data'=>'' );
		}
		else if($popular=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid popular.' , 'data'=>'' );
		}
		else if($popular!="yes" && $popular!="no")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid popular input pls provide yes or no value.' , 'data'=>'' );
		}
		else if($tags =="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid tags.' , 'data'=>'' );
		}
		else if($metatags =="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid metatags.' , 'data'=>'' );
		}
		else if($publish=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid publish.' , 'data'=>'' );
		}
		else if($publish!="yes" && $publish!="no")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid publish input pls provide yes or no value.' , 'data'=>'' );
		}
		else 
		{
			$error1=0;
			$image = $_FILES['image']['name'];
			$config['upload_path'] = 'uploads/';
			$config['allowed_types'] = 'jpeg|jpg|png';
			$this->load->library('upload', $config);
			if($_FILES['image']['name']!='')
			{	
				if ($this->upload->do_upload('image')) 
				{
					$uploadData = $this->upload->data();
					$image = "uploads/".$uploadData['file_name'];
				}
				else
				{
					//$error1 = $error1+1; 
				}
			}
			
			
			$error2=0;
			$banner = $_FILES['banner']['name'];
			$config['upload_path'] = './uploads/';
			$config['allowed_types'] = 'jpeg|jpg|png';
			$this->load->library('upload', $config);
			if($_FILES['banner']['name']!='')
			{	
				if ($this->upload->do_upload('banner')) 
				{
					$uploadData = $this->upload->data();
					$banner = "uploads/".$uploadData['file_name'];
				}
				else
				{
					//$error2 = $error2+1; 
				}
			}
			
			
			if($error1==0 && $error2==0)
			{
				if($featured=="yes")
				{
					$featured = 1;
				}else{
					$featured = 0;
				}
				if($popular=="yes")
				{
					$popular = 1;
				}else{
					$popular = 0;
				}
				if($publish=="yes")
				{
					$publish = 1;
				}else{
					$publish = 0;
				}
				
				$ins_data = array(
					'cat_id' 		=> $category_id,
					'subcat_id' 	=> $subcategory_id,
					'title' 		=> $title,
					'creator' 		=> $creator,
					'image' 		=> $image,
					'banner_image' 	=> $banner,
					'content' 		=> $content,
					'featured' 		=> $featured,
					'isPopular' 	=> $popular,
					'tags' 			=> $tags,
					'metatags' 		=> $metatags,
					'publish' 		=> $publish,
					'date_time'		=> date('Y-m-d H:i:s')
					);
				$this->common_model->insertData('blogs',$ins_data);	
				
			$req = array(
					'category_id' 	=> $category_id,
					'subcategory_id' 	=> $subcategory_id,
					'title' 	=> $title,
					'creator' 	=> $creator,
					'content' 	=> $content,
					'featured' 	=> $featured,
					'popular' 	=> $popular,
					'tags' 	=> $tags,
					'metatags' 	=> $metatags,
					'publish' 	=> $publish,
				);
				
				$response= array( 'status'=>'Ok','code'=>'200','message'=>'Blog Added Successfully.','request'=> $req, 'data'=>$req );	
			}
			else
			{
				if($error1>0)
				{
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Uploaded image not valid.' , 'data'=>'' );
				}else{
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Uploaded banner not valid.' , 'data'=>'' );
				}
				
			}
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}
	
	function updateBlog_post(){
		$blog_id   	= $this->post('blog_id');
		$category_id   	= $this->post('category_id');
		$subcategory_id = $this->post('subcategory_id');
		$title = $this->post('title');
		$creator = $this->post('creator');
		$content = $this->post('content');
		$featured  = $this->post('featured');
		$popular  = $this->post('popular');
		$tags   = $this->post('tags');
		$metatags = $this->post('metatags');
		$publish = $this->post('publish');
		
		$ext='';
		$ext1='';
		$supported_image = array('jpg','jpeg','png');
		if(isset($_FILES['image']))
		{
			$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
		}
		if(isset($_FILES['banner']))
		{
			$ext1 = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
		}
		
		$chkcategory = $this->common_model->getsingle('blog_categories',array('id'=>$category_id));
		$chksubcategory = $this->common_model->getsingle('blog_subcategories',array('subcat_id'=>$subcategory_id));
		$chktitle = $this->common_model->getsingle('blogs',array('title'=>$title,'blog_id !='=>$blog_id));
		$chkblog = $this->common_model->getsingle('blogs',array('blog_id'=>$blog_id));
		
		if($blog_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid blog id.' , 'data'=>'' );
		}
		else if($blog_id!='' && !$chkblog)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Blog does not exists.' , 'data'=>'' );
		}
		else if($category_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid category id.' , 'data'=>'' );
		}
		else if($category_id!='' && !$chkcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Category not found.' , 'data'=>'' );
		}
		else if($subcategory_id=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid sub category id.' , 'data'=>'' );
		}
		else if($subcategory_id!='' && !$chksubcategory)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Sub Category not found.' , 'data'=>'' );
		}
		else if($title=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid title.' , 'data'=>'' );
		}
		else if($chktitle)
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Blog Already exists.' , 'data'=>'' );
		}
		else if($creator=='')
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid creator.' , 'data'=>'' );
		}
		else if(isset($_FILES['image']) && !in_array($ext, $supported_image))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Allowed image jpeg,jpg,png only.' , 'data'=>'' );
		}
		else if(isset($_FILES['banner']) && !in_array($ext1, $supported_image))
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Allowed banner jpeg,jpg,png only.' , 'data'=>'' );
		}
		else if($content=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid content.' , 'data'=>'' );
		}
		else if($featured=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid featured.' , 'data'=>'' );
		}
		else if($featured!="yes" && $featured!="no")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid featured input pls provide yes or no value.' , 'data'=>'' );
		}
		else if($popular=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid popular.' , 'data'=>'' );
		}
		else if($popular!="yes" && $popular!="no")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid popular input pls provide yes or no value.' , 'data'=>'' );
		}
		else if($tags =="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid tags.' , 'data'=>'' );
		}
		else if($metatags =="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid metatags.' , 'data'=>'' );
		}
		else if($publish=="")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid publish.' , 'data'=>'' );
		}
		else if($publish!="yes" && $publish!="no")
		{
			$response= array( 'status'=>'failed','code'=>'400','message'=>'Invalid publish input pls provide yes or no value.' , 'data'=>'' );
		}
		else 
		{
			$error1=0;
			$image = $chkblog->image;
			$config['upload_path'] = 'uploads/';
			$config['allowed_types'] = 'jpeg|jpg|png';
			$this->load->library('upload', $config);
			if(isset($_FILES['image']) && $_FILES['image']['name']!='')
			{	
				$image = $_FILES['image']['name'];
				if ($this->upload->do_upload('image')) 
				{
					$uploadData = $this->upload->data();
					$image = "uploads/".$uploadData['file_name'];
				}
				else
				{
					//$error1 = $error1+1; 
				}
			}
			
			
			$error2=0;
			$banner = $chkblog->banner_image;
			$config['upload_path'] = './uploads/';
			$config['allowed_types'] = 'jpeg|jpg|png';
			$this->load->library('upload', $config);
			if(isset($_FILES['banner']) && $_FILES['banner']['name']!='')
			{
				$banner = $_FILES['banner']['name'];				
				if ($this->upload->do_upload('banner')) 
				{
					$uploadData = $this->upload->data();
					$banner = "uploads/".$uploadData['file_name'];
				}
				else
				{
					//$error2 = $error2+1; 
				}
			}
			
			
			if($error1==0 && $error2==0)
			{
				if($featured=="yes")
				{
					$featured = 1;
				}else{
					$featured = 0;
				}
				if($popular=="yes")
				{
					$popular = 1;
				}else{
					$popular = 0;
				}
				if($publish=="yes")
				{
					$publish = 1;
				}else{
					$publish = 0;
				}
				
				$updata = array(
					'cat_id' 		=> $category_id,
					'subcat_id' 	=> $subcategory_id,
					'title' 		=> $title,
					'creator' 		=> $creator,
					'image' 		=> $image,
					'banner_image' 	=> $banner,
					'content' 		=> $content,
					'featured' 		=> $featured,
					'isPopular' 	=> $popular,
					'tags' 			=> $tags,
					'metatags' 		=> $metatags,
					'publish' 		=> $publish
					);
				$up = $this->common_model->updateData('blogs',$updata,array('blog_id'=>$blog_id));	
				
				$req = array(
					'category_id' 	=> $category_id,
					'subcategory_id' 	=> $subcategory_id,
					'title' 	=> $title,
					'creator' 	=> $creator,
					'content' 	=> $content,
					'featured' 	=> $featured,
					'popular' 	=> $popular,
					'tags' 	=> $tags,
					'metatags' 	=> $metatags,
					'publish' 	=> $publish,
				);
				
				
				if($up==1)
				{
					$response= array( 'status'=>'Ok','code'=>'200','message'=>'Blog Update Successfully.','request'=> $req, 'data'=>$req );	
				}else{
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Please provide atleast one parameter to update blog.' , 'data'=>'' );
				}
				
			}
			else
			{
				if($error1>0)
				{
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Uploaded image not valid.' , 'data'=>'' );
				}else{
					$response= array( 'status'=>'failed','code'=>'400','message'=>'Uploaded banner not valid.' , 'data'=>'' );
				}
				
			}
			
		}
		$this->response($response	, 200); // 200 being the HTTP response code		
		
	}

    

}
	

