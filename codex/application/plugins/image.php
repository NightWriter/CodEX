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
 *
 */
include_once("file.php");
class Image extends File
{
    var $height;
    var $width;
    var $make_thumbnail;
    var $thumbnail_path;
    var $url_path;
    var $CI;
    
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

    function prepForDb($value){
        if(isset($_FILES[$this->name])){
            $name = mysql_real_escape_string($_FILES[$this->name]['name']);
            //If there's a file and we want to overwrite w/e is there
            if(!empty($_POST[$this->element_name.'_dbValue']) AND isset($_POST['overwrite_'.$this->element_name])){
                $this->prepForDelete($_POST[$this->element_name.'_dbValue']);
            }
            //If a file is chosen for upload
            if(!empty($name)){
                $path = (!isset($this->config['upload_path']))? show_error("No path found in form setup") : $this->config['upload_path'];
                if(substr($path,-1) !== DIRECTORY_SEPARATOR) $path .= DIRECTORY_SEPARATOR;
                $source_file = $path.$name;

                if($uploaded_file_name = htmlspecialchars($this->doUpload())){
                    if($this->make_thumbnail){
                        $config['image_library'] = 'GD2';
                        $config['source_image']	= $source_file;
                        $config['new_image']	= $this->thumbnail_path.$uploaded_file_name;
                        $config['create_thumb'] = TRUE;
                        $config['maintain_ratio'] = TRUE;
                        $config['width']	 = $this->width;
                        $config['height']	= $this->height;
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
        return implode('.',$parts).'_thumb.'.$ext;
    }
    function prepForDelete($file){
        if(!empty($file)){
            if(substr($this->config['upload_path'],-1) !== DIRECTORY_SEPARATOR) $this->config['upload_path'] .= DIRECTORY_SEPARATOR;
            if(substr($this->thumbnail_path,-1) !== DIRECTORY_SEPARATOR) $this->thumbnail_path .= DIRECTORY_SEPARATOR;
            $file = $this->config['upload_path'].$file;
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
            $preview = '<img src="'.$file.'" style="max-width:100px; max-height:75px">';
        }
        else{
            $file = $this->url_path.$val;
            $preview = '<img src="'.$file.'" style="max-width:100px; max-height:75px">';
        }
        return $preview;
    }

    function showPreview($val){
        return !empty($val) AND isset($this->url_path);
    }
}
?>
