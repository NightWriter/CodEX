<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include("codexcontroller.php");

class AjaxPortal extends codexController 
{ 
    function AjaxPortal(){
		codexController::codexController();

    }
    
    function index(){
        $this->codexsession->keep_flashdata('form_id');
        $action = $this->input->post('action');
        $params = $this->_formatParams($this->input->post('params'));
        $this->$action($params);
    }

    function _formatParams($raw){
        $raw = explode(';',$raw);
        $formatted = array();
        foreach($raw as $pair){
            $pair = explode('|',$pair);
            if(count($pair) == 2)
                $formatted[$pair[0]] =  $pair[1];
            else
                continue;
        }
        return $formatted;
    }

    function _removePermission($params){
        if($params['user'] == $this->codexsession->userdata('user_id')){
            $this->db->delete('user_records',array('record_id'=>$params['record_id'],
                                                   'user_id'=>$params['user']));
            echo 'success';
        }
        else{
            $ownsership_result = $this->event->trigger('checkRecordOwnership',array('edit',$params['table'],$params['record_id']));
            if($ownsership_result[0] === false){
                echo 'failure';
            }
            else{
                $this->db->delete('user_records',array('record_id'=>$params['record_id'],
                                                       'user_id'=>$params['user']));
                echo 'success';
            }
        }
    }
}
?>
