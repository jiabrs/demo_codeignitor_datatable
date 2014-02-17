<?php
class MY_Exceptions extends CI_Exceptions {
	
	public function __construct()
	{
		parent::__construct();
		
		set_exception_handler(array('MY_Exceptions', 'exception_handler'));
	}
	
	public static function exception_handler(Exception $e)
	{
		$message = "<h3>Stack Trace:</h3><pre>".$e->getTraceAsString()."</pre>";
		$message .= "<h3>Message:</h3><pre>".$e->getMessage()."</pre>";
		
		log_message('error', $message);
		
		show_error($message, 500, 'Uncaught Exception');
	}
}