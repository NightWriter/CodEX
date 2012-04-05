<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
class KH_Plugin_CodexEvents extends KH_Plugin{
    var $CI;
    function KH_Plugin_CodexEvents(&$subject){
        KH_Plugin::KH_Plugin($subject);
        $this->CI = &get_instance();
    }
    function preInsertHook($data=array()){
        $this->CI->codexforms->iterate('preInsertHook',$data);
    }
    function postInsertHook($data=array()){
        $this->addNewPermissions($data);
        $this->CI->db->insert('user_records',array('user_id'=>$data['user_id'],
                                                   'record_id'=>$data[$this->CI->codexadmin->primary_key],
                                                   'permissions'=>'owner',
                                                   'table_name'=>$data['table']
                                               ));
        $this->CI->codexforms->iterate('postInsertHook',$data);
    }
    function addNewPermissions($data){
        $permissions= '';
        if(isset($data['permissions_users']) AND isset($data['permissions_access'])){
            foreach($data['permissions_users'] as $key=>$users){
                $permissions .= implode(',',$data['permissions_access'][$key]);
                foreach($users as $user){
                    $this->CI->db->insert('user_records',array('user_id'=>$user,
                                                               'record_id'=>$data[$this->CI->codexadmin->primary_key],
                                                               'permissions'=>$permissions,
                                                               'table_name'=>$data['table']
                                                           ));
                }
            }
        }
        return $permissions;
    }
    function preEditHook($table,$id){
        $query = $this->CI->db->get_where($table,array($this->CI->codexadmin->primary_key=>$id));
        $this->CI->codexforms->iterate('preEditHook',$query->result_array());
    }
    function postEditHook($data){
        $_POST[$this->CI->codexadmin->primary_key]            = $this->CI->codexadmin->active_id;
        $_POST['table']         = $this->CI->codexadmin->db_table;
        $this->addNewPermissions($_POST);
        $this->CI->codexforms->iterate('postEditHook',$data);
    }
    /*
     * --------
     * Ownership logic:
     * --------
     * -    If no record is found, it's considered public
     * -    If a record is found, match it against the logged in user
     *          If the user has some sort of access to the record, make sure he has access to the specific action
     */
    function checkRecordOwnership($action,$table,$id){
        if($this->CI->codexsession->userdata('user_level') == $this->CI->config->config['access_levels']['admin_level']){
            return true;
        }

        $query = $this->CI->db->get_where('user_records',array(
                                                          'record_id'=>$id,
                                                          'table_name'=>$table
                           ));
        $result = $query->result_array();
        if(count($result) == 0){
            return true;
        }
        else{
            //If no special permissions are set, and trust mode is enabled, then it's fair game
            if(count($result) == 1 AND $result[0]['permissions'] == 'owner' AND $this->CI->config->item('codex_trust_mode') == 'true')
                return true;
            foreach($result as $record){
                if(array_key_exists('user_id',$record) && $record['user_id'] == $this->CI->codexsession->userdata('user_id')){
                    if(strpos($record['permissions'],'owner') !== FALSE){
                        return true;
                    }
                    if(strpos($record['permissions'],$action) !== FALSE){
                        return true;
                    }
                }
            }
            $this->CI->codexmessages->add('failure','Sorry, you do not have permission to '.$action.' this item.');
            return false;
        }
    }
    function preDeleteHook($table,$id){
        $query = $this->CI->db->get_where('user_records',array(
                                          
                                         ));
        $result = $query->result_array();
        if(count($result) > 0 AND strpos($result[0]['permissions'],'owner'))
            return true;
        else
            return false;
    }
    function postDeleteHook($table,$id){
        $this->CI->db->delete('user_records',array(
                                   'record_id'=>$id,
                                   'table_name'=>$table
                           ));
    }
    function getDbFields($data=array()){
        $fields =  $this->CI->codexforms->iterate('getFieldName',$data);
        foreach($fields as $k=>$v){
            if($v === NULL)
                unset($fields[$k]);
        }
        return array_keys($fields);
    }
    function prepForDisplay($data=array()){
        $db_data = $this->CI->codexforms->iterate('prepForDisplay',$data);

            //Unset all NULL values
            foreach($db_data as $name=>$value){
                if($value === NULL){
                    unset($db_data[$name]);
                }
            }
        return $db_data;
    }
    function prepForDelete($table,$id){
        $result = $this->CI->db->get_where($table,array($this->CI->codexadmin->primary_key=>$id));
        $result = $result->result_array();
        $purified_array = $this->CI->codexforms->iterate('prepForDelete',$result[0]);
        
        return $purified_array;
    }
    function prepForDb($data=array()){
        $result = $this->CI->codexforms->iterate('prepForDb',$data);
        foreach($result as $field=>$v)
            if($v === NULL){
                unset($result[$field]);
            }
            else if(is_array($v)){
                foreach($v as $key=>$val){
                    if($val !== NULL)
                        $result[$key] = $val;
                }
                array_merge($result,$v);
                unset($result[$field]);
            }
        return $result;
    }
}
?>
