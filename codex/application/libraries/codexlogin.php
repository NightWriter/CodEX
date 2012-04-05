<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class codexLogin {

    var $CI;
    var $access_levels;
    var $users_table;
	
    function codexLogin(){
        $this->CI = &get_instance();
        $this->access_levels = array();
		$this->CI->lang->load('codexlogin');
        $this->users_table = 'users';
    }
	
    /*
     * This method returns true if the user is logged in, false otherwise.
     * It does this by trying to find a record in the db which matches the
     * user_id and the session_id in the db
     */
    function isLoggedIn(){
        if(count($this->access_levels) == 0)
            $this->setPreDefinedAccessLevels();
        $success = false;

        $session_id = $this->CI->codexsession->userdata('session_id');
        $user_id = $this->CI->codexsession->userdata('user_id');

        $uri_segments = explode('/',$this->CI->uri->uri_string());
        if(count($uri_segments) > 1){
            $cur_controller = $uri_segments[1];
            if(!empty($uri_segments[2])) $cur_controller .= '/'.$uri_segments[2];
        }

        if(!isset($cur_controller)){
            @include(APPPATH.'config/routes'.EXT);
            $cur_controller = $route['default_controller'];
        }

        if(count($this->access_levels) == 0){
            $_access_levels = false;
        }
        else
            $_access_levels = true;


        $sql = "SELECT ".$this->users_table.".access_level 
                FROM   ci_sessions,".$this->users_table." 
                WHERE  ci_sessions.session_id='$session_id' AND ".$this->users_table.".id='$user_id'";

        //If no id is set
        if((!$user_id OR $user_id === 0 OR $user_id === '')){
            //if public level is the same as the default level
            if(!array_key_exists($cur_controller,$this->access_levels) AND $this->access_levels["public_level"] == $this->access_levels["default_level"]){
                $success = true;
            }
        }
        else{
            
            $query = $this->CI->db->query($sql);

                //If there are defined user roles, and they exist for this controller,
                //then we need to check that the user has proper privileges to access
                //that page.

            $result = $query->result_array();
            if($query->num_rows() > 0){
                if(array_key_exists($cur_controller,$this->access_levels)){
                    $access_level = $this->access_levels[$cur_controller];
                    if($result[0]['access_level'] >= $access_level)
                        $success = true;
                    else{
                        $this->CI->codexmessages->add('failure',$this->CI->lang->line('codexlogin_no_privilege'));
                        $success = false;
                    }
                }
                else{
                    if($result[0]['access_level'] >= $this->access_levels['default_level']){
                        $success = true;
                    }
                    else{
                        $this->CI->codexmessages->add('failure',$this->CI->lang->line('codexlogin_no_privilege'));
                    }
                }
            }
        }
        return $success;
    }

    function setPreDefinedAccessLevels(){
        $this->CI->config->load('access_levels',TRUE);
        $this->access_levels = $this->CI->config->config['access_levels'];
    }

    function setUserTable($name){
        $this->users_table = $name;
    }
	
    /*
     * This validates that the provided username and password are found in the db and sets
     * the username and id in a session.
     */
    function check($username,$password){
        $username=$this->CI->db->escape($username);
        $password=$this->CI->db->escape($password);
                
        $sql = "SELECT id,username,access_level FROM ".$this->users_table." WHERE username=$username AND password=$password";
        $query = $this->CI->db->query($sql);

        $result = $query->result_array();
        
        if($query->num_rows() > 0){
            if($referer = $this->CI->codexsession->userdata('page_referer'))
                $this->CI->codexsession->set_userdata('codex_login_redirect',$referer);
            else
                $this->CI->codexsession->set_userdata('codex_login_redirect',$this->CI->config->item('codex_login_redirect'));

            $this->CI->codexsession->set_userdata('user_id',$result[0]['id']);
            $this->CI->codexsession->set_userdata('user_level',$result[0]['access_level']);
            $this->CI->codexsession->set_userdata('user_name',$result[0]['username']);
            return true;
        }
        return false;
    }
}
?>
