<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Common_model extends CI_Model
{	
	function login($username,$password) {  
         $q = $this->db->query("SELECT * FROM users WHERE password_view='".$password."' AND ( email = '".$username."'  OR phone = '".$username."' )  ");    
		 $result = $q->row();		
		 return $result;  
	}

	function login_new($username) {  
         $q = $this->db->query("SELECT * FROM users WHERE email = '".$username."'  OR phone = '".$username."' ");    
		 $result = $q->row();		
		 return $result;  
	}	
	
	function getsingle($table, $where)
    {
        $q = $this->db->get_where($table, $where);
        return $q->row();
    }	
	
	function register($data){
        $this->db->insert('users', $data);
		$lastinsertid = $this->db->insert_id();	
		$Usergroup = array(		 
		'user_id' => $lastinsertid,	
		'group_id' => '5',	
		);		
		$this->db->insert('users_groups', $Usergroup);	
		$groups_ids=$this->db->get_where('users_groups',array('user_id'=>$lastinsertid))->row();	
		
		if($groups_ids->group_id=='1'){	
			$role='SuperAdmin';		
		}elseif($groups_ids->group_id=='11'){
			$role='Admin';		
		}elseif($groups_ids->group_id=='3'){
			$role='Accountant';		
		}elseif($groups_ids->group_id=='4'){	
			$role='Doctor';		
		}elseif($groups_ids->group_id=='5'){
			$role='Patient';	
		}elseif($groups_ids->group_id=='6'){	
			$role='Nurse';	
		}elseif($groups_ids->group_id=='7'){	
			$role='Pharmacist';	
		}elseif($groups_ids->group_id=='8'){		
			$role='Laboratorist';	
		}elseif($groups_ids->group_id=='10'){	
			$role='Receptionist';	
		}		

			$datalog = array(			  
				'user_id'=>$lastinsertid,		
				'ip_address'=>$_SERVER['REMOTE_ADDR'],	
				'activity'=>'otp requested',		
				'role'=>$role,		
				'activity_date_time'=>date('d-m-Y H:i:s')
			); 	
			$this->db->insert('pf_logs', $datalog);	
			return $lastinsertid;		
	}
	
	function getsinglenew($table, $where,$field)
    {
		$this->db->select($field);
        $q = $this->db->get_where($table, $where);
        return $q->row();
    }	
	/*<!--INSERT RECORD FROM SINGLE TABLE-->*/
    function insertData($table, $dataInsert)
    {
        $this->db->insert($table, $dataInsert);
        return $this->db->insert_id();
    }
	/*<!--UPDATE RECORD FROM SINGLE TABLE-->*/
    function updateData($table, $data, $where)
    {
        $this->db->update($table, $data, $where);
        return $this->db->affected_rows();
    }
	/*<!--DELETE RECORD FROM SINGLE TABLE-->*/
    function deleteData($table, $where)
    {
        //$this->db->delete('mytable', array('id' => $id));
        $this->db->delete($table, $where);
        return;
    }
	/*<!--GET ALL RECORD FROM SINGLE TABLE WITHOUT CONDITION-->*/
    function getAllrecord($table)
    {
        $this->db->select('*');
        $q = $this->db->get($table);
        $num_rows = $q->num_rows();
        if ($num_rows > 0) {
            foreach ($q->result() as $rows) {
                $data[] = $rows;
            }
            $q->free_result();
            return $data;
        }
    }
	/*---GET MULTIPLE RECORD---*/
    function getAllwhere($table, $where)
    {
        $this->db->select('*');
        $q = $this->db->get_where($table, $where);
        $num_rows = $q->num_rows();
        if ($num_rows > 0) {
            foreach ($q->result() as $rows) {
                $data[] = $rows;
            }
            $q->free_result();
            return $data;
        }
    }
	
	public function record_count($table,$where='') 
	{		
		$this->db->from($table);
		if($where!=''){
		$this->db->where($where);
		}
		return $this->db->count_all_results();		
	}
	
	
	public function getProposals($id='',$category='',$type='',$sort='asc',$featured='')
	{
		$sql = " WHERE pp.proposal_status=19 ";
		if($id!='')
		{
			$sql .= " and pp.proposal_id= ".$id;
		}
		if($category!='')
		{
			$sql .= " and pp.proposal_cat_id= ".$category;
		}
		if($type!='')
		{
			$sql .= " and pp.proposal_type= ".$type;
		}
		if($featured!='')
		{
			$sql .= " and pp.proposal_featured= ".$featured;
		}
		
		$sql .= " ORDER BY pp.proposal_id ".$sort;
		
		$sql ="SELECT pp.* FROM pf_proposals as pp ".$sql;
        $qq = $this->db->query($sql);
		//$this->db->last_query($qq); die;
		$result = $qq->result();
		return $result;
	}
	
	public function getorders($order_id='',$buyer_id='',$seller_id='',$order_date='')
	{
		if($order_id!='')
		{
			$this->db->where('order_id',$order_id);
		}
		if($buyer_id!='')
		{
			$this->db->where('buyer_id',$buyer_id);
		}
		if($seller_id!='')
		{
			$this->db->where('seller_id',$seller_id);
		}
		if($order_date!='')
		{
			$this->db->where('order_date',$order_date);
		}
		$query=$this->db->get('pf_orders');

		return $query->result();
	}
	
	
	public function getblogs($blog_id='',$featured='',$popular='',$category='')
	{
		$this->db->select('blogs.*,blog_categories.name as category_name');
		if($blog_id!='')
		{
			$this->db->where('blogs.blog_id',$blog_id);
		}
		if($featured!='')
		{
			$this->db->where('blogs.featured',$featured);
		}
		if($popular!='')
		{
			$this->db->where('blogs.isPopular',$popular);
		}
		if($category!='')
		{
			$this->db->where('blogs.cat_id',$category);
		}
		$this->db->join('blog_categories', 'blog_categories.id = blogs.cat_id');
		$query=$this->db->get('blogs');

		return $query->result();
	}
	
	public function getFavorites($user_id='')
	{
		$this->db->select('pf_proposals_favorites.*,pf_proposals.proposal_id,pf_proposals.proposal_title,pf_proposals.proposal_img1,pf_proposals.proposal_desc,pf_proposals.proposal_price');
	
		if($user_id!='')
		{
			$this->db->where('pf_proposals_favorites.user_id',$user_id);
		}
		
		$this->db->join('pf_proposals', 'pf_proposals_favorites.proposal_id = pf_proposals.proposal_id');
		$query=$this->db->get('pf_proposals_favorites');

		return $query->result();
	}
	
	public function proposalsdata($userid){
		$this->db->select('*');
		$this->db->from('pf_orders');
		$this->db->join('pf_proposals', 'pf_proposals.proposal_id = pf_orders.proposal_id');
		$this->db->order_by("pf_orders.order_id", "desc");
		$this->db->where('pf_proposals.proposal_type', '2');
		$this->db->where('pf_orders.proposal_type', '2');
		$this->db->where('pf_orders.pay_status', 'success');
		$this->db->where('pf_orders.buyer_id', $userid); 
		$query = $this->db->get();
		return $query->result();
	}
  
	public function challengestart($id,$data){
		$this->db->where('order_id', $id);
		$this->db->update('pf_orders', $data);
		return true;
	}
	
	public function getDatabylikes($table, $colname ='', $search_key='') 
	{		
		$this->db->select('*'); 
		$this->db->from($table);
		
		if($search_key!='')
		{
		    $this->db->like($colname,$search_key);
		}

		$query = $this->db->get();
		return $query->result(); 	
	}
	
	public function getusers($user_id='',$email_id='',$mobile_no=''){
		$this->db->select('*');
		$this->db->from('users');
		if($user_id!='')
		{
			$this->db->where('id', $user_id); 
		}
		if($email_id!='')
		{
			$this->db->where('email', $email_id); 
		}
		if($mobile_no!='')
		{
			$this->db->where('phone', $mobile_no); 
		}		
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getcateories($category_id=''){
		$this->db->select('*');
		$this->db->from('blog_categories');
		if($category_id!='')
		{
			$this->db->where('id', $category_id); 
		}		
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getsubcateories($category_id='',$subcategory_id=''){
		$this->db->select('blog_subcategories.*,blog_categories.name as cat_name');
		$this->db->from('blog_subcategories');
		$this->db->join('blog_categories', 'blog_categories.id = blog_subcategories.cat_id');
		if($category_id!='')
		{
			$this->db->where('cat_id', $category_id); 
		}
		if($subcategory_id!='')
		{
			$this->db->where('subcat_id ', $subcategory_id); 
		}			
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getAssociates($associate_id='',$associate_code='',$associate_type=''){
		$this->db->select('*');
		$this->db->from('associate');
		if($associate_id!='')
		{
			$this->db->where('associateid', $associate_id); 
		}
		if($associate_code!='')
		{
			$this->db->where('associate_code ', $associate_code); 
		}
		if($associate_type!='')
		{
			$this->db->like('associate_type',$associate_type); 
		}		
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getAllReviews($order_id='',$proposal_id='',$buyer_id='',$seller_id=''){
		$this->db->select('*');
		$this->db->from('pf_buyer_reviews');
		if($order_id!='')
		{
			$this->db->where('order_id', $order_id); 
		}
		if($proposal_id!='')
		{
			$this->db->where('proposal_id ', $proposal_id); 
		}
		if($buyer_id!='')
		{
			$this->db->where('review_buyer_id',$buyer_id); 
		}
		if($seller_id!='')
		{
			$this->db->where('review_seller_id',$seller_id); 
		}		
		$this->db->where('publish',1);
		$query = $this->db->get();
		return $query->result();
	}
	
	
	
}

 
?>