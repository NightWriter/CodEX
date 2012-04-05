<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php');

class Construct extends codexController
{
    function __construct()
    {
        parent::__construct();
    }
    //
    function index()
    {
        $this->template                 = (isset($_COOKIE['codex_template']))? $_COOKIE['codex_template'] : $this->config->item("codex_template");

        $this->codextemplates->clearHTML();
        $this->codextemplates->docType('html5');
        $this->codextemplates->rawHeaderHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">');
        
        $files = get_files('./codex/assets/'.$this->template.'/css/');
        if(!empty($files))
        {
            foreach($files as $k=>$file)
                $this->codextemplates->css('template-css-'.$k,$this->config->item('codex_asset_folder').$this->template.'/css/'.$file);
        }
        //
        $files = get_files('./codex/assets/'.$this->template.'/js/');
        if(!empty($files))
        {
            foreach($files as $k=>$file)
                $this->codextemplates->js('js-'.$k,$this->config->item('codex_asset_folder').$this->template.'/js/'.$file);
        }
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_header');
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_footer');
        $this->codextemplates->loadView('templates/alterego/build_component');
        $this->codextemplates->setTitle($this->config->item('codex_site_title').' - Build component');
        $this->codextemplates->printHTML();
    }
    //
    function build()
    {
        $fields     = '';
        $yml_fields = '';
        
        $title = $this->input->post('title');
        $alias = $this->input->post('alias');
        
        foreach($_POST['type_field'] as $k=>$v)
        {
            // пропускаем невидимые поля
            if($k == 0) continue;
            
            $type = '';
            if(in_array($v, array('textbox','aliasbox','checkbox','dropdown','password','image','file')))
                $type = 'varchar(255)';
            if($v == 'textarea')
                $type = 'text';
                
            $fields .= '`'.$_POST['name_field'][$k].'` '.$type.' NOT NULL, ';
            
            $yml_fields .= '    
    '.$_POST['name_field'][$k].":
        class: ".$v."
        label: '".$_POST['label_field'][$k]."'
        params:
            display_name: '".$_POST['label_field'][$k]."'";
        }
        
        $sql = 'CREATE TABLE `'.$alias.'` (
                                  `id` int(11) NOT NULL auto_increment,
                                  '.$fields.'
                                  PRIMARY KEY  (`id`));';
        mysql_query($sql);
        $yml = 'page_header: \''.$title.'\'
groups: \'Default\'
form_setup:'.$yml_fields;

        $fp = fopen('./codex/application/definitions/'.$alias.'.yml','w');
        fwrite($fp,$yml);
        fclose($fp);        
    }
}