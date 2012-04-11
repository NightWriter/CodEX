<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
    
class Permalink extends codexForms
{
	
	var $max_length, $max_num_length, $CI;
	
	function Permalink($name,$params) { 
        codexForms::initiate($name,$params);
        
        $this->CI = &get_instance();
        $this->CI->load->helpers(array('string', 'object'));
        
        $this->max_length = isset($this->params['max_length']) ? $this->params['max_length'] : 80;
        $this->max_num_length = isset($this->params['max_num_length']) ? $this->params['max_num_length'] : 3;
        
    }

	function getHTML()
	{
        $html = $this->prefix;
        $html .= $this->getMessage($this->name);
        $html .= '
            <label for="'.$this->name.'">
                '.$this->label.'
            </label>';
        $html .= '    <input class="text" type="text" value="'.$this->value.'" name="'.$this->name.'" '.$this->getAttributes($this->attributes).'>
        ';

		$html .= $this->suffix;
		
		return $html;
	}
    
	function prepForDb($value){
        // Stripslashes 
        if (get_magic_quotes_gpc()) { 
            $value = stripslashes($value); 
        } 
        // Quote if not integer 
        if (!is_numeric($value)) { 
            $value = mysql_real_escape_string($value); 
        }
        $value = webstyle_filename($value);
        $value = $this->findExistingValues($value);
        return $value;
    }
    
    function findExistingValues($str){
    	
    	$db2 = clone ($this->CI->db);
    	
    	$db2->_reset_select();
        
        if (strlen($str)<=$this->max_num_length || $this->max_num_length==0)
        	$like = '';
        elseif (strlen($str)>=$this->max_length-$this->max_num_length) 
        	$like = substr($str, 0, strlen($str)-$this->max_num_length);
        else
        	$like = $value;
        
        $db2->like($this->name, $like);
        if ($this->CI->codexadmin->active_id > 0)
        	$db2->where('id !=', $this->CI->codexadmin->active_id);
        $query = $db2->get($this->CI->table);
        
        
        if ($query->num_rows()) return $this->findUniqueString($str, $this->convertDBArray($query->result_array()));
        
    	return $str;
    	
    }
    
    function convertDBArray($table){
    	
    	$ret_val = array();
    	foreach ($table as $row) $ret_val[] = $row['permalink'];
    	return $ret_val;
    	
    }
    
    function findUniqueString($str, $table){
    	
    	$current_base = substr($str, 0, $this->max_length);
    	$checked = array();
    	// empty permalinks are not allowed
    	if ($current_base && !in_array($current_base, $table)) return $str;
    	
    	for ($i=1; $i<=$this->max_num_length; $i++){
    		
    		if (strlen($current_base)+$i > $this->max_length)
    			$current_base = substr($current_base, 0, strlen($current_base)-1);
    		
    		for ($j=pow(10, $i-1); $j<pow(10, $i); $j++){
    			if ($j==1) continue;	// I don't like to have permalinks to end at #1 but remove it if you don't care
    			
    			$str = $current_base.$j;
    			if (!in_array($str, $table)) {
    				return $str;
    			} else $checked[] = $str;
    		}
    		
    	}
    	show_error ($this->CI->lang->line('codexforms_permalink_generation_failed'));
    	
    }
    
}

?>
