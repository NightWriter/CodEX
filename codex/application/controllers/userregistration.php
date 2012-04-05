<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include_once("codexcontroller.php");
/**
* User Registration controller
*/
class UserRegistration extends codexController
{	
	function UserRegistration()
	{
		codexController::codexController();
        $this->load->helper('inflector');                

        $rules['username'] = "trim|required";
        $rules['access_level'] = "trim|required";
        $config = array(
                    'db_table' => 'users',
                    'form_setup' => array(
                        'username' => array('class'=>'TextBox'),
                        'password' => array('class'=>'Password'),
                        'access_level' => array('class'=>'TextBox'),
                        ),
                    'rules'=>$rules,
                    'list_text'=>array('username'),
                    'page_header'=>$this->lang->line('codex_menu_userregistration'),
                    'controller_name' => 'userregistration',
                    );
        $this->setConfig($config);

	}
}
?>
