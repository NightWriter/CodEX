<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class File extends codexForms
{
    var $CI,$config;
    
    function File($name,$params) { 
        codexForms::initiate($name,$params);
        $this->CI = &get_instance();
        $this->CI->load->library('upload');

        $this->config['upload_path']   = isset($this->params['upload_path'])? $this->params['upload_path'] : '';
        $this->config['allowed_types'] = isset($this->params['allowed_types'])? $this->params['allowed_types'] : 'jpg|jpeg|JPG|bmp|gif|png';
        $this->config['overwrite']     = isset($this->params['overwrite'])? $this->params['overwrite'] : false;
        $this->config['max_size']      = isset($this->params['max_size'])? $this->params['max_size'] : 0;
        $this->config['max_width']     = isset($this->params['max_width'])? $this->params['max_width'] : 0;
        $this->config['max_height']    = isset($this->params['max_height'])? $this->params['max_height'] : 0;
        $this->config['encrypt_name']  = isset($this->params['encrypt_name'])? $this->params['encrypt_name'] : false;
        $this->config['remove_spaces'] = isset($this->params['remove_spaces'])? $this->params['remove_spaces'] : true;
    }

    function prepForDb($value){
        $name = mysql_real_escape_string($_FILES[$this->name]['name']);
        //If there's a file and we want to overwrite w/e is there
        if(!empty($_POST[$this->name.'_dbValue']) AND isset($_POST['overwrite_'.$this->name])){
            $this->prepForDelete($_POST[$this->name.'_dbValue']);
        }
        //If a file is chosen for upload
        if(!empty($name)){
            $path = (!isset($this->config['upload_path']))? show_error("No path found in form setup") : $this->config['upload_path'];
            if(substr($path,-1) !== DIRECTORY_SEPARATOR) $path .= DIRECTORY_SEPARATOR;
            $file = $path.$_FILES[$this->name]['name'];
            return htmlspecialchars($this->doUpload());
        }
        else{ 
            return ($this->CI->input->post($this->name.'_dbValue'))? $this->CI->input->post($this->name.'_dbValue') : NULL;
        }
    }
    function prepForDelete($file){
        if(substr($this->config['upload_path'],-1) !== DIRECTORY_SEPARATOR) $this->config['upload_path'] .= DIRECTORY_SEPARATOR;
        $file = $this->config['upload_path'].$file;
        if(file_exists($file) AND !is_dir($file)){
            if(!unlink($file)){
                show_error("failed to remove $file");
                $this->CI->codexmessages->add('info','upload_remove_failure');
            }
            else
                $this->CI->codexmessages->add('info','upload_remove_success');
        }                     
    }
    /*
     * This is a helper function which does all the uploading. It doesn't take any parameters
     * but it returns an array containing the name and value of the stuff to add to the database.
     */
    function doUpload(){
        if(count($_FILES) > 0){
            if(array_key_exists($this->name,$_FILES) AND $_FILES[$this->name]['name'] != ""){
                $this->CI->upload->initialize($this->config);
            
                if (!$this->CI->upload->do_upload($this->name)){
                    show_error($this->CI->upload->display_errors());
                }	
                else{
                    $data = $this->CI->upload->data();
                    $this->CI->codexmessages->add('info',$this->CI->lang->line('codexforms_file').' '.$_FILES[$this->name]['name'].' '.$this->CI->lang->line('codexforms_uploaded_correctly'));
                    return $data['file_name'];
                }
            }
        }
    }

    function showPreview(){ return false; }

    function getHTML(){
        if(isset($this->attributes['class']))
            $this->attributes['class'] .= ' form-element-text';
        else
            $this->attributes['class'] = ' form-element-text';
        $allowed = $this->config['allowed_types'];
        $allowed = implode(', ',explode("|",$allowed));
        $html = $this->prefix;

        if($this->getMessage($this->name))
            $html .= '<div class="failure">'.$this->getMessage($this->name).'</div>';

        $html .= '
            <label for="'.$this->element_name.'">
                '.$this->label.'
            </label>
            <div class="form-element-text"><b>'. $this->CI->lang->line('codexforms_file_allowed_types') .'</b>: '.$allowed.'</div>
            <input type="file" value="'.$this->value.'" name="'.$this->name.'" '.$this->getAttributes($this->attributes).'><br>
            <div class="form-element-text"><input type="checkbox" class="file-checkbox" name="overwrite_'.$this->element_name.'"> '. $this->CI->lang->line('codexforms_file_overwrite') .'</div>';

        if($this->showPreview($this->value)) $html .='<div class="form-element-text">'.$this->getPreview($this->value).'</div>';

        if(!empty($this->value))
            $html .='
                <div class="form-element-text"><b>'.$this->CI->lang->line('codexforms_current_file').'</b> '.$this->value.'</div>
            ';
        $html .= '
            <input type="hidden" value="'.$this->value.'" name="'.$this->element_name.'_dbValue">
        ';

		$html .= $this->suffix;
		
		return $html;
	}
}
?>
