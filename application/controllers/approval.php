<?php

class Approval extends MY_Controller {
function __construct()
	{
		parent::__construct();
	}

   public function index(){
            
            
            
            
            $this->load->model('approval_model');
            
		$this->tab_title .= " >Approval User List";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('check_request/approval');
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('check_request/approval.js');		
			
		$this->_build();
            
            
            
        }
        function get_datatable()
	{
		if (is_ajax())
		{
			$this->load->model('approval_model');
			
			//$sort_cols = array(
			//	'U_FL_NM',
			//	'RE_FL_NM'
			//);
			
			$iDisplayStart = $this->input->post('iDisplayStart');
			$iDisplayLength = $this->input->post('iDisplayLength');
			$iColumns = $this->input->post('iColumns');
			$sSearch = $this->input->post('sSearch');
			$bRegex = $this->input->post('bRegex');
			$iSortingCols = $this->input->post('iSortingCols');
			$sEcho = intval($this->input->post('sEcho'));
			$iSortCol_0 = $this->input->post('iSortCol_0');
			$sSortDir_0 = strtoupper($this->input->post('sSortDir_0'));
			
			$aaData = array();
			
			$rec_cnt = 0;
                      
			foreach ($this->approval_model->get_datatable($sSearch) as $row)
			{
                          
				if ($rec_cnt >= $iDisplayStart && $rec_cnt <= ($iDisplayStart + $iDisplayLength - 1))
				{
					$aaData[] = array(
						'<button type="button" title="Edit" class="edit" value="'.site_url('approval/setup_approval/'.$row['appr_lst_id']).'"><span class="ui-icon ui-icon-wrench"></span></button>'.
						'<button type="button" title="Remove" class="remove modal" value="'.site_url('approval/remove/'.$row['appr_lst_id']).'"><span class="ui-icon ui-icon-trash"></span></button>',
			
                                       $row['usr_lst'],
                                       $row['loc'],
                                       $row['retrn_usr'],
                                           
                                          
                                            
					);
				}
				
				$rec_cnt++;
			}		

			echo json_encode(array(
				'iTotalRecords' => $this->approval_model->get_appr_lst_cnt(),
				'iTotalDisplayRecords' => $rec_cnt,
				'sEcho' => $sEcho,
				'aaData' => $aaData
			));
		}
	
        }
	
        public function setup_approval(){
                    
                $this->load->library('form_validation');
		$this->load->helper('form');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		
          
          
                $this->load->model('approval_model');
                
                	// did we validate form?
                
		if ($this->form_validation->run() == FALSE)
                    
                    		{
                   
			// get an element object.  If element_id was passed, we should
			// get back a real element
			$approval = $this->approval_model->get($this->uri->segment(3));
			
			//// if this is a new element, set the app
		
      
		
		$setup_approval_view['approval'] = $approval;
          
		$this->tab_title .= " >Setup Approval";
                
               $usrlist= $this->approval_model->get_usr_nm_lst();
        
                $setup_approval_view['usr_list']=$usrlist;
                
           
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('check_request/setup_approval',$setup_approval_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('check_request/setup_approval.js');		
			
		$this->_build();
                  
            
        }
        else 
            
            {
            
       $approval = $this->approval_model->get($this->input->post('appr_lst_id'));
       
       $approval->process_post();
     
            $approval->save();
            $setup_approval_view['approval'] = $approval;
                
		$this->tab_title .= " >Approval User List";
                
            $this->_add_view('sec_cont_open_view');

            	$this->_add_view('check_request/approval');
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('check_request/approval.js');		
			
		$this->_build();
        }
        
        }  
       
	
	
        
      function usr_appr_search()
	{
		if (is_ajax())
		{
			$this->load->model('approval_model');
				
			// get search criteria
			$user = $this->input->post('usr');
			
			// run search
			$users = $this->approval_model->appr_usr_search($user);
			
			$usr_list = array();
			
			// format for input select
			foreach ($users as $row)
			{
			  $usr_list[$row->USR_ID] = $row->FST_NM.' '.$row->LST_NM;
                       
			}
			
			echo json_encode($usr_list);
		}
                
           
                
	}  
        
            
    
        
        function remove()
	{
		$this->load->model('approval_model');
		
		// if not confirmed and uid provided in uri . . .
		if ($this->input->post('confirm') === FALSE && $this->uri->segment(3) !== FALSE)
		{	
			$remove_view['approval'] = $this->approval_model->get($this->uri->segment(3));
				
			$this->load->helper('form');

			// check for ajax request
			if (is_ajax())
			{
				// just send confirmation view
				$this->load->view('check_request/remove_view',$remove_view);
			}
			else // build view normally
			{
				$this->_add_view('sec_cont_open_view');
				$this->_add_view('check_request/remove_view', $remove_view);
				$this->_add_view('sec_cont_close_view');
					
				$this->_build();
			}
		}
		else
		{
			// get user's response
			$confirm = $this->input->post('confirm');
				
			$removed = FALSE;
			
			// user confirmed removal
			if ($confirm == "Continue")
			{
				// get user object
				$approval = $this->approval_model->get($this->input->post('appr_lst_id'));

				// remove user
				$approval->remove();
				
				 //log activity
				$this->audit_model->log_activity(
					'AL',
					$approval->get_appr_lst_id(), 
					'D',
					$this->session->userdata('usr_id')
				);
				
				$removed = TRUE;
			}
				
			if (is_ajax())
			{
				echo json_encode($removed);
			}
			else
			{
				// send back to users list
				redirect('approval');
                                
			}
		}
	}
	
	
       
        
        
        
}
?>
