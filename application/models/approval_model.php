<?php
class Approval_model extends CI_Model {
    
    	private $appr_lst_id = NULL;
	private $return_usr_id = NULL;
        private $usr_id_lst=array();
        private  $loaded = NULL;
	//private $app = NULL; // assigned application

        
        public function get_datatable($search = ''){
             
   
            
            	$search_where = '';
		$params=array();
		if ($search != '')
		{
			$search_where = " WHERE LOWER(U.FST_NM ||' '||U.LST_NM) LIKE ? OR LOWER(A.FST_NM ||' '|| A.LST_NM) LIKE ?";
			
			$params[] = strtolower('%'.$search.'%');
			$params[] = strtolower('%'.$search.'%');
		}
		
		
            
            
      
          $select= "select AU.APPR_LST_ID,U.FST_NM ||' '||U.LST_NM AS U_FL_NM,U.SLS_CTR_CD,
              AU.ORDER, A.FST_NM ||' '|| A.LST_NM AS RE_FL_NM 
              FROM " .APP_SCHEMA.".USR AS U
                 INNER JOIN " .APP_SCHEMA. ".APPR_LST_USR  AS AU ON U.USR_ID=AU.USR_ID
                 INNER JOIN (SELECT U.FST_NM,U.LST_NM, AL.RETRN_USR_ID,AL.APPR_LST_ID FROM " .APP_SCHEMA. ".USR AS U 
                     INNER JOIN " .APP_SCHEMA. ".APPR_LST AS AL ON U.USR_ID=AL.RETRN_USR_ID )AS A 
                 ON A.APPR_LST_ID=AU.APPR_LST_ID ".$search_where." ORDER BY AU.APPR_LST_ID,AU.ORDER";
      
           $query_result=$this->db2->query($select,$params)->fetch_object(); 
           //reorganize data
           $result=array();
           
             
         
          foreach ($query_result as $row){
           
             $result[$row->APPR_LST_ID]['usr_lst']='';                    
          $result[$row->APPR_LST_ID]['appr_lst_id']=$row->APPR_LST_ID;
         $result[$row->APPR_LST_ID]['retrn_usr']=$row->RE_FL_NM;
         
         if(($row->ORDER)==1){ 
             
         $select_loc="SELECT SLS_CTR_NM FROM DW.SLS_CTR AS DS WHERE DS.SLS_CTR_CD=?";
         
         $query_loc=$this->db2->query($select_loc,array($row->SLS_CTR_CD))->fetch_assoc();
          }
        
           $result[$row->APPR_LST_ID]['loc']=$query_loc[0]['SLS_CTR_NM'];
          }
          //get approval user name list
         foreach ($query_result as $row){
    
           $result[$row->APPR_LST_ID]['usr_lst'].= $row->U_FL_NM.',' ;  
           
        }   //get rid of the ','at the of the user list
            foreach ($result as $row){
    
       $result[$row['appr_lst_id']]['usr_lst']= substr($row['usr_lst'],0,-1);
           
        }    
        return $result; 
        
    
            
            
            
            
            
        
        
        }
      
        
        
        
  public function get_usr_nm_lst(){
        
     $select="SELECT FST_NM,LST_NM,USR_ID FROM " .APP_SCHEMA.".USR";
    $result=$this->db2->simple_query($select)->fetch_object();
    $arr=array();

    
    foreach($result as $value)
        
    {
    $arr[$value->USR_ID]=$value->FST_NM." ".$value->LST_NM;
        
    }
    
     return $arr;
        
        } 
        
        
        
              
  public function get_usr_nm($usr_id){
        
     $select="SELECT FST_NM,LST_NM,USR_ID FROM ".APP_SCHEMA.".USR WHERE USR_ID=?";
    $result=$this->db2->query($select,array($usr_id))->fetch_object();
    
   $full_nm=$result[0]->FST_NM.' '.$result[0]->LST_NM;
   
     return $full_nm;
        
        } 
        
        
      public function process_post()
        
	{ 
		$CI =& get_instance();
		
		if ($CI->input->post('usr_lst') !== FALSE)
			$this->set_usr_id_lst($CI->input->post('usr_lst'));
		
		if ($CI->input->post('return_usr') !== FALSE)
			$this->set_return_usr_id($CI->input->post('return_usr'));
		
	
	
                
	}  
        function &get($appr_lst_id  = NULL)
	{
		// create new object
		$approval = new Approval_model();

		// if appr_lst_id provided, load values from db
		if ($appr_lst_id!== NULL && $appr_lst_id  !== FALSE)
		{
			$approval->set_appr_lst_id($appr_lst_id);
			$approval->_load();
           
		}
		
		// return element_model object
               
		return $approval;
          
	}
        
      function _load()
	{
		if ($this->appr_lst_id!== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".APPR_LST
				WHERE APPR_LST_ID = ".$this->appr_lst_id;
				
			$results = $this->db2->simple_query($sql)->fetch_assoc();
                        
                                               
                        
			$sql_usr_lst = "SELECT *
				FROM ".APP_SCHEMA.".APPR_LST_USR
				WHERE APPR_LST_ID = ".$this->appr_lst_id;
				
			$results_usr_lst = $this->db2->simple_query($sql_usr_lst)->fetch_assoc();
				
                       // var_dump($results_usr_lst);
                        //exit();
                        
			if (count($results) > 0 && count($results_usr_lst) > 0)
			{
				// assign values from element table
				$this->appr_lst_id = $results[0]['APPR_LST_ID'];
                                $this->return_usr_id=$results[0]['RETRN_USR_ID'];
                                foreach ($results_usr_lst as $usr){
                                    
                                  // $this->usr_id_lst[$usr['ORDER']]=$usr['USR_ID']; 
                                    
                                 $this->usr_id_lst[]=$usr['USR_ID']; 
                                    
                                }
			
      
				$this->loaded = TRUE;
                           
                               
			}
		}
	}
	
       function save()
	{   // echo $this->appr_lst_id;
           // exit();
		$this->db2->begin_transaction();
		
		try {
                    
                 
			if ($this->exists())
			{
                           
				// update element
				$update = "UPDATE ".APP_SCHEMA.".APPR_LST
					SET RETRN_USR_ID = ?	
				        WHERE APPR_LST_ID = ?";
				
				$update_parms = array(
                                        $this->return_usr_id,
					$this->appr_lst_id
					
					
				);
				
				$this->db2->query($update, $update_parms);
                                $delete="Delete From ".APP_SCHEMA.".APPR_LST_USR 				
				        WHERE APPR_LST_ID = ?";
				$this->db2->query($delete, array($this->appr_lst_id));
                                $update_usr="INSERT INTO ".APP_SCHEMA.".APPR_LST_USR (APPR_LST_ID,USR_ID,ORDER) VALUES(?,?,?)";
					
				                                    
                                
                                    $order=1;
                        foreach ($this->usr_id_lst as $user){
                               	
                         	$result_usr_lst=$this->db2->query($update_usr, array($this->appr_lst_id,$user,$order));
				$order++;		
				
			}
                             	$this->audit_model->log_activity(
					'AL',
					$this->get_appr_lst_id(), 
					'U',
					$this->session->userdata('usr_id')
				);   
                                
			}
			else
			{
                            
				// insert new element
			
                                
                                $insert_return="SELECT * FROM FINAL TABLE (INSERT INTO ".APP_SCHEMA.".APPR_LST (RETRN_USR_ID) VALUES(?))";
			
				
				
			$result_return=$this->db2->query($insert_return, array($this->return_usr_id))->fetch_object();
                    	$this->set_appr_lst_id($result_return[0]->APPR_LST_ID);
                   
                     
                        $insert_usr_lst="INSERT INTO ".APP_SCHEMA.".APPR_LST_USR (APPR_LST_ID,USR_ID,ORDER) VALUES(?,?,?)";
                         $order=1;
                        foreach ($this->usr_id_lst as $user){
                               	
                         	$result_usr_lst=$this->db2->query($insert_usr_lst, array($this->appr_lst_id,$user,$order));
				$order++;		
				
			}
	
				$this->audit_model->log_activity(
					'AL',
					$this->get_appr_lst_id(), 
					'C',
					$this->session->userdata('usr_id')
				);
                        
                        }
                }     
		 catch(Exception $e) 
                        {
                 
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving Element", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
      
        }
        
	function exists()
	{
          
		if ($this->appr_lst_id != NULL)
		{
			$sql = "SELECT A.* 
				FROM ".APP_SCHEMA.".APPR_LST AS A
				WHERE A.APPR_LST_ID = ".$this->appr_lst_id;
				
			if (count($this->db2->simple_query($sql)->fetch_assoc()) > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
        
     	function set_usr_id_lst($usr_id_lst)
	{
		$this->usr_id_lst=$usr_id_lst;
	}
        	
        
        function set_return_usr_id($return_usr_id)
	{
		$this->return_usr_id=$return_usr_id;
	}
     
         function    set_appr_lst_id($appr_lst_id)
	{
		$this->appr_lst_id=$appr_lst_id;
	}
        
        function get_usr_id_lst()
        {
            return $this->usr_id_lst;
        }
        
         function get_return_usr_id()
        {
            return $this->return_usr_id;
        }
        
         function get_appr_lst_id()
        {
            return $this->appr_lst_id;
        }
        
        function appr_usr_search($search ='')
	{
		
			
			$query="select * from " .APP_SCHEMA. ".usr where LOWER(CONCAT(FST_NM,LST_NM)) like ?";
			
			return $this->db2->query( $query, array("%".strtolower(str_replace(" ","",$search))."%"))->fetch_object();
	           
	}
        
        
        	function set_app($app)
	{
		$this->app = $app;
	}
        
        
        
        function remove()
	{
		if ($this->appr_lst_id !== NULL)
		{
			$this->db2->begin_transaction();
			
			try {
				
				$sql = "DELETE FROM ".APP_SCHEMA." .APPR_LST
					WHERE APPR_LST_ID =".$this->appr_lst_id;
                                            
                                
			        $sql_usr = "DELETE FROM ".APP_SCHEMA." .APPR_LST_USR
					WHERE APPR_LST_ID = ".$this->appr_lst_id;
                                
				$this->db2->simple_query($sql);
                                $this->db2->simple_query($sql_usr);
			} catch (Exception $e) {
				$this->db2->rollback();
			
				$error =& load_class('Exceptions', 'core');
				echo $error->show_error("Error removing Element", $e->getMessage(), 'error_general');
				exit;
			}
			
			$this->db2->commit();
		}
	}
        public function clear_appr_lst_id()
	{
		$this->appr_lst_id = NULL;
	}
        public function get_appr_lst_cnt()
	{
		
			$select = "SELECT COUNT(DISTINCT APPR_LST_ID) AS APPR_CNT
				FROM ".APP_SCHEMA.".APPR_LST
			";
			
			$result = $this->db2->simple_query($select)->fetch_assoc();
		
			return $result[0]['APPR_CNT'];
		
	}
}

?>
