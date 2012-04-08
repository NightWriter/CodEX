<?php if (!defined('BASEPATH')) exit('No direct script access allowed');  
/*
 * setTitle
 * rawHeaderHTML
 */
class codexTemplates{ 
    var $CI;
    var $asset_folder;
    var $partials = array();
    var $content = array(
                    'doctype' => '',
                    'head' => '',
                    'title'=>'',
                    'body'=>''
                );
    var $loaded_objects = array('js' => array(),
                                'inlineJS' => array(),
                                'css' => array(),
                                'inlineCSS' => array());

    function codexTemplates () { 
        $this->CI = &get_instance();
        $this->asset_folder = $this->CI->config->item('codex_asset_folder');
    }
    function docType ($type){ 
        switch($type){
            case "transitional":
                $this->content['doctype'] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                                "http://www.w3.org/TR/html4/loose.dtd">'."\n";
            break;
            case "strict":
                $this->content['doctype'] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
                                "http://www.w3.org/TR/html4/strict.dtd">'."\n";
            break;
            case "html5":
                $this->content['doctype'] = '<!DOCTYPE html>'."\n";
            break;
            default:
            break;
        }
    }
    function clearHTML(){
    	$this->content['doctype'] = "";
    	$this->content['head']    = "";
    	$this->content['body']    = "";

    	$this->loaded_objects['inlineJS']=array();
    	$this->loaded_objects['inlineCSS']=array();
    	$this->loaded_objects['js']=array();
    	$this->loaded_objects['css']=array();
   	}

    function inlineJS ($identifier, $code){ 
        if(!in_array($identifier,$this->loaded_objects['inlineJS'])){
            $this->loaded_objects['inlineJS'][$identifier] = '<script type="text/javascript">'.$code.'</script>'."\n";
        }
    }
    function inlineCSS ($identifier, $code){ 
        if(!in_array($identifier,$this->loaded_objects['inlineCSS'])){
            $this->loaded_objects['inlineCSS'][$identifier] = '<script type="text/css">'.$code.'</script>'."\n";
        }
    }
    function js( $identifier, $file){ 
        if(!in_array($identifier,$this->loaded_objects['js'])){
            $this->loaded_objects['js'][$identifier] = '<script type="text/javascript" src="'.$file.'"></script>'."\n";
        }
    }
    function jsFromAssets ($identifier, $file){ 
        if(!in_array($identifier,$this->loaded_objects['js'])){
            $this->loaded_objects['js'][$identifier] = '<script type="text/javascript" src="'.$this->asset_folder.'js/'.$file.'"></script>'."\n";
        }
    }
    function cssFromAssets ($identifier, $file,$force=false){ 
        if($force OR !in_array($identifier,$this->loaded_objects['css']))
            $this->loaded_objects['css'][$identifier] = '<link rel="stylesheet" href="'.$this->asset_folder.'css/'.$file.'" type="text/css">'."\n";
    }
    function css ($identifier, $file,$force=false){ 
        if($force OR !in_array($identifier,$this->loaded_objects['css']))
            $this->loaded_objects['css'][$identifier] = '<link rel="stylesheet" href="'.$file.'" type="text/css">'."\n";
    }
    function setTitle ($new_val){ 
        $this->content['title'] = $new_val;
    }
    function rawHeaderHTML($html){
        $this->content['head'] .= $html;
    }
    function rawBodyHTML($html){
        $this->content['body'] .= $html;
    }
    function loadView ($file,$data=array()){ 
        $this->content['body'] .= $this->CI->load->view($file,$data,true);
    }
    function fetchView ($file,$data=array()){ 
        return $this->CI->load->view($file,$data,true);
    }
    function loadInlineView ($file,$data=array()){ 
        $this->CI->load->view($file,$data);
    }
    function _getHTML (){
        $head = '';


        foreach($this->loaded_objects['css'] as $val){
            $head .= $val;
        }

        foreach($this->loaded_objects['js'] as $val){
            $head .= $val;
        }

        foreach($this->loaded_objects['inlineCSS'] as $val){
            $head .= $val;
        }
        foreach($this->loaded_objects['inlineJS'] as $val){
            $head .= $val;
        }
        
        $this->content['head'] .= $head;
        
        $html = <<<EOT
{$this->content['doctype']}
<html>
    <head>
        <title>{$this->content['title']}</title>
        <link rel="shortcut icon" type="image/x-icon" src='{$this->asset_folder}favicon.ico' />
        {$this->content['head']}
    </head>
    <body>
        {$this->content['body']}
    </body>
</html>
EOT;
        return $html;
    }
    function fetchHTML (){ 
        return $this->_getHTML();
    }
    function printHTML(){
        echo $this->_getHTML();
    }
    function get($part='body'){
        if(array_key_exists($part,$this->partials))
            return $this->partials[$part];
        else
            return '';
    }
    function getPart($part='body'){
        if(array_key_exists($part,$this->content))
            return $this->content[$part];
        else
            return '';
    }
    function set($tag,$content){
        if(array_key_exists($tag,$this->partials))
            $this->partials[$tag] .= $content;
        else
            $this->partials[$tag] = $content;
    }
}
?>
