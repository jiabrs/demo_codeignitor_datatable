<?php
class Note_model extends CI_Model {
	
	private $note_id = NULL;
	private $cntrct_id = NULL;
	private $body = '';
	private $file_ext = '';
	private $file_nm = '';
	private $lst_updt_tm = NULL;
	private $file_upload = NULL;
	
	public static $file_dir = "note_atts/";
	
	public function set_note_id($note_id)
	{
		if (intval($note_id) != 0)
			$this->note_id = intval($note_id);
	}
	
	public function get_note_id()
	{
		return $this->note_id;
	}
	
	public function set_cntrct_id($cntrct_id)
	{
		if (intval($cntrct_id) != 0)
			$this->cntrct_id = intval($cntrct_id);
	}
	
	public function get_cntrct_id()
	{
		return $this->cntrct_id;
	}
	
	public function set_body($body)
	{
		$this->body = substr($body, 0, 512);
	}
	
	public function get_body()
	{
		return $this->body;
	}
	
	public function esc_body()
	{
		return str_replace("'","''",$this->body);
	}
	
	public function set_file_upload($file_upload)
	{
		$this->file_upload = $file_upload;
		
		$file_parts = explode('.', $file_upload['name']);
		
		$this->set_file_ext(end($file_parts));
		$this->set_file_nm(substr($file_upload['name'], 0, strlen($this->file_ext)*-1));
	}
	
	public function set_file_ext($file_ext)
	{
		$this->file_ext = $file_ext;
	}
	
	public function get_file_ext()
	{
		return $this->file_ext;
	}
	
	public function set_file_nm($file_nm)
	{
		$this->file_nm = substr($file_nm,0,40);
	}
	
	public function get_file_nm()
	{
		return $this->file_nm;
	}
	
	public function esc_file_nm()
	{
		return str_replace("'","''",$this->file_nm);
	}
	
	public function get_lst_updt_tm()
	{
		return $this->lst_updt_tm;
	}
	
	public function set_lst_updt_tm($lst_updt_tm)
	{
		$this->lst_updt_tm = $lst_updt_tm;
	}
	
	public function dsp_lst_updt_tm($format = 'm/d/y g:i a')
	{
		$date_array = date_parse(substr($this->lst_updt_tm,0,10)." ".substr($this->lst_updt_tm, 11));
		
		return date($format, mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year']));
	}
	
	public function __construct($note_id = NULL)
	{
		parent::__construct();
		
		$this->set_note_id($note_id);
		$this->_load();
	}
	
	private function _load()
	{
		if ($this->note_id !== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".NOTE
				WHERE NOTE_ID = ".$this->note_id;
			
			if (FALSE !== ($results = $this->db2->simple_query($sql)->fetch_object()))
			{
				$this->set_cntrct_id($results[0]->CNTRCT_ID);
				$this->set_body($results[0]->BODY);
				$this->set_file_ext($results[0]->FILE_EXT);
				$this->set_file_nm($results[0]->FILE_NM);
				$this->set_lst_updt_tm($results[0]->LST_UPDT_TM);
			}
		}		
	}
	
	public function get_file_nm_ext()
	{
		return $this->file_nm.$this->file_ext;
	}
	
	public function get_file()
	{
		if ($this->note_id !== NULL)
		{
			return $this->note_id.".".$this->file_ext;
		}
		else
		{
			return "";
		}
	}
	
	public function get_file_path()
	{
		if ($this->file_nm != "")
		{
			return self::$file_dir.$this->note_id;
		}
		else
		{
			return "";
		}
	}
	
	/**
	 * Saves object to database
	 */
	public function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->_exists())
			{
				// create update sql statement
				$update = "UPDATE ".APP_SCHEMA.".NOTE
					SET BODY = ?,
						FILE_EXT = ?,
						FILE_NM = ?, 
						LST_UPDT_TM = CURRENT_TIMESTAMP
					WHERE NOTE_ID = ?
						AND CNTRCT_ID = ?";
				
				$update_parms = array(
					$this->get_body(),
					$this->get_file_ext(),
					$this->get_file_nm(),
					$this->note_id,
					$this->cntrct_id
				);
				
				$this->db2->query($update, $update_parms);
			}
			else
			{
				// create insert statement
				$insert = "SELECT * FROM FINAL TABLE ( 
					INSERT INTO ".APP_SCHEMA.".NOTE (NOTE_ID,CNTRCT_ID,BODY,FILE_EXT,FILE_NM,LST_UPDT_TM)
					VALUES ( NEXT VALUE FOR ".APP_SCHEMA.".SQ_NOTE_PK, ?, ?, ?, ?, CURRENT_TIMESTAMP)
				)";
				
				$insert_parms = array(
					$this->cntrct_id,
					$this->get_body(),
					$this->get_file_ext(),
					$this->get_file_nm()				
				);
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->set_note_id($result[0]->NOTE_ID);
				$this->set_lst_updt_tm($result[0]->LST_UPDT_TM);
			}
						
			$this->_save_file_upload();
			
		} catch (Exception $e) {
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving Note", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}
	
	private function _save_file_upload()
	{
		if ($this->file_upload !== NULL)
		{
			move_uploaded_file($this->file_upload['tmp_name'], $this->get_file_path());
		}
	}
	
	/**
	 * Checks to see if record exists in db before inserting
	 * 
	 * @return boolean
	 */
	private function _exists()
	{
		if ($this->note_id !== NULL && $this->cntrct_id !== NULL)
		{
			$sql = "SELECT * 
				FROM ".APP_SCHEMA.".NOTE
				WHERE NOTE_ID = ".$this->note_id;
				
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
	
	/**
	 * Removes record from database
	 * 
	 * Enter description here ...
	 */
	public function remove()
	{
		if ($this->note_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".NOTE
				WHERE NOTE_ID = ".$this->note_id;
			
			$this->db2->simple_query($sql);
			
			$this->remove_file();
		}
	}
	
	/**
	 * Removes file from note
	 * 
	 * Enter description here ...
	 */
	public function remove_file()
	{
		if (file_exists($this->get_file_path()))
			unlink($this->get_file_path());
	}
	
	public static function get($note_id = NULL)
	{
		if ($note_id !== NULL)
		{
			return new Note_model($note_id);
		}		
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Returns loaded note objects by contract id
	 * 
	 * @param integer $cntrct_id
	 * @return Note_model
	 */
	public static function get_by_cntrct_id($cntrct_id = NULL)
	{
		if ($cntrct_id !== NULL)
		{
			$CI =& get_instance();
			
			$sql = "SELECT * 
				FROM ".APP_SCHEMA.".NOTE
				WHERE CNTRCT_ID = ".$cntrct_id."
				ORDER BY LST_UPDT_TM DESC";
			
			$notes = array();
			
			foreach ($CI->db2->simple_query($sql)->fetch_object() as $row)
			{
				$note = new Note_model();	

				$note->set_note_id($row->NOTE_ID);
				$note->set_cntrct_id($row->CNTRCT_ID);
				$note->set_body($row->BODY);
				$note->set_file_ext($row->FILE_EXT);
				$note->set_file_nm($row->FILE_NM);
				$note->set_lst_updt_tm($row->LST_UPDT_TM);
				
				$notes[] = $note;
			}
			
			return $notes;
		}
	}
	
	public function process_post()
	{
		$CI =& get_instance();
		
		if ($CI->input->post('body') !== FALSE)
			$this->set_body($CI->input->post('body'));
			
		if ($CI->input->post('cntrct_id') !== FALSE)
			$this->set_cntrct_id($CI->input->post('cntrct_id'));
			
		if (isset($_FILES['file']))
		{	
			$this->set_file_upload($_FILES['file']);
		} 		
	}
	
	public function has_file()
	{
		if (is_file($this->get_file_path()))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Shortens file display name.  
	 * 	ex: Really Long File Name.txt > Really Lon...ame.txt
	 * 
	 * @param integer $length
	 * @return string
	 */
	public function dsp_file_nm($length = 44)
	{
		// Minimum length is 15 characters
		if ($length < 15) $length = 15;
		
		// Get full file name
		$file = trim($this->get_file_nm_ext());
		
		// File name too big?
		if (strlen($file) > $length)
		{
			// Shorten name
			return substr($file,0,$length - 10)."...".substr($file, -7);
		}
		else // under limit so return name
		{
			return $file;
		}
	}
}