<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/*
 *
 *  Usage (in YAML format:)
 *  
 *  image:
 *      class: Image
 *      params:
 *          upload_path:/full/path               required- (This option specifies the directory to put the files in)
 *          thumbnail_path:/full/path/to/thumbs  optional- (This option specifies the directory to put the thumbs in.
 *                                                             if not specified, thumbs will go into the same directory as the upload.)
 *          url_path:thumbs                      optional- (This is the path to the directory containing the files to be added after 
 *                                                             the domain. So if you save something to /home/john/sites/uploads and your
 *                                                             site's index is in the sites directory, then you would put 'uploads' for url_path
 *          make_thumbnail: true                 optional- (Tells the plugin that you want to create a thumbnail.
 *          height:                              optional- (The height of the thumbnail)
 *          width:                               optional- (The width of the thumbnail)
 *          download_function                    optional- (funcrtion witch acccept file name to download file)
 *
 */
include_once("file.php");
class Image extends File
{
    var $height;
    var $width;
    var $make_thumbnail;
    var $thumbnail_path;
    var $download_function;
    var $url_path;
    var $CI;
    var $max_count_subfolders;
    
    function Image($name,$params) { 
        $params['params']['allowed_types'] = "jpg|jpeg|JPG|JPEG|bmp|png|gif";
        File::File($name,$params);
        
        $this->CI = &get_instance();
        $this->CI->load->library('image_lib'); 
        $this->CI->load->library('upload');

        if(array_key_exists('make_thumbnail',$this->params) AND $this->params['make_thumbnail'] == true){
            $this->make_thumbnail = true;
            if(array_key_exists('height',$this->params)) $this->height =  $this->params['height'];
            if(array_key_exists('width',$this->params)) $this->width = $this->params['width'];

            $this->thumbnail_path = (array_key_exists('thumbnail_path',$this->params))? $this->params['thumbnail_path'] : $this->config['upload_path'];
            if(substr($this->thumbnail_path,-1) !== DIRECTORY_SEPARATOR) $this->thumbnail_path .= DIRECTORY_SEPARATOR;

        }

        $this->max_count_subfolders = (array_key_exists('max_count_subfolders',$this->params))? $this->params['max_count_subfolders'] : FALSE;
        $this->url_path = (array_key_exists('url_path',$this->params))? base_url().$this->params['url_path'] : $this->config['upload_path'];
        if(substr($this->url_path,-1) !== '/') $this->url_path .= '/';
        
    }

    function prepForDisplay($value){
        if($this->showPreview($value)){
            $preview = $this->getPreview($value);
        }
        if(!empty($preview))
            return $preview;
        else
            return '';
    }
    //
    function postInsertHook($data)
    {
        $last_upload_info = $this->CI->codexsession->userdata('last_upload_info');
        $this->CI->codexsession->set_userdata('last_upload_info',array());
        if(!empty($last_upload_info) && is_array($last_upload_info))
        {
            foreach($last_upload_info as $item)
            {
                $this->config['upload_path'] = $item['last_upload_orig_upload_path'];
                
                if($this->max_count_subfolders)
                {
                    $dir_prefix = $this->max_count_subfolders.'/'.ceil($this->CI->codexadmin->active_id / 25000);
                    
                    $this->config['upload_path'] = str_replace("/{$this->max_count_subfolders}/","/{$dir_prefix}/",$this->config['upload_path']);
                }
                $this->config['upload_path'] = str_replace('[id]',$this->CI->codexadmin->active_id,$this->config['upload_path']);
                $this->config['base_path'] = str_replace('[id]',$this->CI->codexadmin->active_id,$this->config['base_path']);
                if(!empty($this->params['thumbnail_path']))
                    $this->config['thumbnail_path'] = rtrim(str_replace('[id]',$this->CI->codexadmin->active_id,$this->params['thumbnail_path']),'/');
                
                $this->config['upload_path'] = str_replace('[parent_id]',$this->CI->codexsession->userdata('id'),$this->config['upload_path']);
                $this->config['base_path'] = str_replace('[parent_id]',$this->CI->codexsession->userdata('id'),$this->config['base_path']);
                
                if(!empty($this->config['base_path']) && !file_exists($this->config['base_path']))
                    mkdir($this->config['base_path'],0777);
                    
                $upload_path = explode('/',$this->config['upload_path']);
                $path = './';
                foreach($upload_path as $folder)
                {
                    $path .= $folder . '/';
                    
                    if(!empty($path) && !file_exists($path))
                        mkdir($path,0777);
                }
                //if(!empty($this->config['upload_path']) && !file_exists($this->config['upload_path']))
                //    mkdir($this->config['upload_path'],0777);
                
                $path = $item['last_upload_img_path'];
                $name = $item['last_upload_img_name'];
                rename($path.$name,rtrim($this->config['upload_path'],'/').'/'.$name);
                if(!empty($this->make_thumbnail)){
                    if(!file_exists($this->config['thumbnail_path'])){
                        mkdir($this->config['thumbnail_path'],0777);    
                    }
                    rename($item['last_upload_img_path'].$this->getThumbnailName($name),$this->config['thumbnail_path'].'/'.$name);    
                }
            }
            //$this->CI->codexsession->set_userdata('last_upload_info',array());
        }
        
    }
    //
    function prepForDb($value){
        
        //$this->CI->codexsession->set_userdata('last_upload_orig_upload_path',$this->config['upload_path']);
        $upload_orig_upload_path = $this->config['upload_path'];
        
        if($this->max_count_subfolders)
        {
            $dir_prefix = ceil($this->CI->codexadmin->active_id / 25000);
            
            $this->config['upload_path'] = str_replace("/{$this->max_count_subfolders}/","/{$this->max_count_subfolders}/{$dir_prefix}/",$this->config['upload_path']);
        }
                
        $this->config['upload_path'] = str_replace('[id]',$this->CI->codexadmin->active_id,$this->config['upload_path']);
        $this->config['base_path'] = str_replace('[id]',$this->CI->codexadmin->active_id,$this->config['base_path']);
        
        $this->thumbnail_path = str_replace('[id]',$this->CI->codexadmin->active_id,$this->thumbnail_path);

        /*if(!file_exists($this->config['upload_path']))
            mkdir($this->config['upload_path'],0777);
        */    
        
        $this->config['upload_path'] = str_replace('[parent_id]',$this->CI->codexsession->userdata('id'),$this->config['upload_path']);
        $this->config['base_path'] = str_replace('[parent_id]',$this->CI->codexsession->userdata('id'),$this->config['base_path']);
        
        if(!empty($this->config['base_path']) && !file_exists($this->config['base_path']))
            mkdir($this->config['base_path'],0777);
        
        $upload_path = explode('/',$this->config['upload_path']);
        $path = './';
        foreach($upload_path as $folder)
        {
            $path .= $folder . '/';
            
            if(!empty($path) && !file_exists($path))
                mkdir($path,0777);
        }
        //if(!file_exists($this->config['upload_path']))
        //    mkdir($this->config['upload_path'],0777);
            
        if(isset($_FILES[$this->name])){
            $name = mysql_real_escape_string($_FILES[$this->name]['name']);
            //If there's a file and we want to overwrite w/e is there
            if(!empty($_POST[$this->element_name.'_dbValue']) AND isset($_POST['overwrite_'.$this->element_name])){
                $this->prepForDelete($_POST[$this->element_name.'_dbValue']);
            }
            //If a file is chosen for upload
            if(!empty($name)){
                $path = (!isset($this->config['upload_path']))? show_error("No path found in form setup") : $this->config['upload_path'];
                //if(substr($path,-1) !== DIRECTORY_SEPARATOR) $path .= DIRECTORY_SEPARATOR;
                if(substr($path,-1) !== '/') $path .= '/';
                $source_file = $path.$name;
                
                if($uploaded_file_name = htmlspecialchars($this->doUpload())){
                    if(!empty($this->config['encrypt_name'])){
                        $source_file = $path.$uploaded_file_name;    
                    }
                    if($this->make_thumbnail){
                        if(!file_exists($this->thumbnail_path))
                             mkdir($this->thumbnail_path,0777);
                        if(!empty($dir_prefix) && !file_exists($this->thumbnail_path . $dir_prefix))
                        {
                             mkdir($this->thumbnail_path . $dir_prefix,0777);
                             $this->thumbnail_path .= $dir_prefix . '/';
                        }
                        $config['image_library'] = 'GD2';
                        $config['source_image']    = $source_file;
                        $config['new_image']    = $this->thumbnail_path . $uploaded_file_name;
                        $config['maintain_ratio'] = TRUE;
                        $config['width']     = $this->width;
                        $config['height']    = $this->height;
                        $this->CI->image_lib->initialize($config);
                        if ( ! $this->CI->image_lib->resize()){
                            show_error($this->CI->image_lib->display_errors());
                        }
                        else
                            $this->CI->codexmessages->add('success',$this->CI->lang->line('codexforms_image_thumbnail_created'),'',true);
                    }
                }
                else{
                    show_error($this->CI->lang->line('codexforms_image_uploading_failed'));
                }
                //$this->CI->codexsession->set_userdata('last_upload_img_path',$path);
                //$this->CI->codexsession->set_userdata('last_upload_img_name',$name);
                
                $last_upload_info = $this->CI->codexsession->userdata('last_upload_info');
                if(empty($last_upload_info)) $last_upload_info = array();
                
                $last_upload_info[] = array(
                                            'last_upload_img_path' => $path,
                                            'last_upload_img_name' => $uploaded_file_name,
                                            'last_upload_orig_upload_path' => $upload_orig_upload_path
                                            );
                $this->CI->codexsession->set_userdata('last_upload_info',$last_upload_info);
                return $uploaded_file_name;
            }
            else
                if(isset($_POST['overwrite_'.$this->element_name]))
                    return '';
                else{
                    return NULL;
                }
        }
    }
    // Takes the name of a file and returns the 
    // thumbnail file that corresponds to it.
    //
    // eg. Given file.jpg, it will return file_thumb.jpg
    function getThumbnailName($file){
        $parts = explode('.',$file);
        $ext = array_pop($parts);
        //return implode('.',$parts).'_thumb.'.$ext;
        return 'thumb_'.$file;
    }
    function prepForDelete($file){
        
        if(!empty($file)){
            if(substr($this->config['upload_path'],-1) !== DIRECTORY_SEPARATOR) $this->config['upload_path'] .= DIRECTORY_SEPARATOR;
            if(substr($this->thumbnail_path,-1) !== DIRECTORY_SEPARATOR) $this->thumbnail_path .= DIRECTORY_SEPARATOR;
            $file = $this->config['upload_path'].$file;
            
            if($this->max_count_subfolders)
            {
                $dir_prefix = $this->max_count_subfolders.'/'.ceil($this->CI->codexadmin->active_id / 25000);
                
                $file = str_replace("/{$this->max_count_subfolders}/","/{$dir_prefix}/",$file);
            }
            $file = str_replace('[id]',$this->CI->codexadmin->active_id,$file);
            $file = str_replace('[parent_id]',$this->CI->codexsession->userdata('id'),$file);
            
            if(file_exists($file) AND !is_dir($file)){
                if(!unlink($file)){
                    $this->CI->codexmessages->add('info',$this->CI->lang->line('codexadmin_upload_remove_failure'));
                }
                else
                    $this->CI->codexmessages->add('info',$this->CI->lang->line('codexadmin_upload_remove_success'));
            }                     
            if($this->make_thumbnail){
                $file = $this->getThumbnailName($file);

                if(file_exists($file) AND !is_dir($file)){
                    if(!unlink($file)){
                        $this->CI->codexmessages->add('info',$this->CI->lang->line('codexadmin_upload_remove_failure'));
                    }
                    else
                        $this->CI->codexmessages->add('info',$this->CI->lang->line('codexadmin_upload_remove_success'));
                }
            }
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
                    $this->CI->codexmessages->add('info',$this->CI->lang->line('codexforms_file').' '.$_FILES[$this->name]['name'].' '.$this->CI->lang->line('codexforms_uploaded_correctly'),'',true);
                    return $data['file_name'];
                }
            }
        }
    }

    function getPreview($val){
        $preview = '';
        if($this->make_thumbnail){
            $file = $this->getThumbnailName($val);
            $file = $this->url_path.$file;    
            //$preview = '<img src="'.$file.'" style="max-width:100px; max-height:75px">';
        }
        else{
            $file = $this->url_path.$val;
            //$preview = '<img src="'.$file.'" style="max-width:100px; max-height:75px">';
        }
        if($this->max_count_subfolders)
        {
            $dir_prefix = $this->max_count_subfolders.'/'.ceil($this->CI->codexadmin->active_id / 25000);
            
            $file = str_replace("/{$this->max_count_subfolders}/","/{$dir_prefix}/",$file);
        }
        $file = str_replace('[id]',$this->CI->codexadmin->active_id,$file);
        $file = str_replace('[parent_id]',$this->CI->codexsession->userdata('id'),$file);
        
        $preview = '<img src="'.$file.'" style="max-width:100px; max-height:75px">';
        if(!empty($this->params['download_function'])){
            $this->params['download_function']=trim($this->params['download_function'],'/');
            $this->params['download_function'] = str_replace('[id]',$this->CI->codexadmin->active_id,$this->params['download_function']);
            $preview='<a href="'.site_url($this->params['download_function'].'/'.$val).'">'.$preview.'</a>' ;   
        }
        return $preview;
    }

    function showPreview($val){
        return !empty($val) AND isset($this->url_path);
    }
}
?>
