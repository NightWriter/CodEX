<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php');

class Construct extends codexController
{
    function __construct()
    {
        parent::__construct();
    }
    // проверяем уникальность для компонента
    function check_alias()
    {
        $file_name = trim($this->input->post('name')).'.yml';
        $files = get_files('./codex/application/definitions/');
        
        if(!in_array($file_name,$files))
            echo 1;
        else
            echo 0;
    }
    // проверяем права на запись
    function _check_perms(&$data)
    {
        if(!file_exists('./codex/application/definitions/'))
            $data['errors'][] = 'Не существует директории ./codex/application/definitions';
        if(!is_writable('./codex/application/definitions/'))
            $data['errors'][] = 'Нет прав на запись в папке ./codex/application/definitions';
        //
        if(!file_exists('./application/modules/'))
            $data['errors'][] = 'Не существует директории ./application/modules';
        if(!is_writable('./application/modules/'))
            $data['errors'][] = 'Нет прав на запись в папке ./application/modules';
    }
    //
    function index($data = array())
    {
        $this->_check_perms($data);
        
        $this->template = (isset($_COOKIE['codex_template']))? $_COOKIE['codex_template'] : $this->config->item("codex_template");

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
        $this->codextemplates->js('jquery',$this->config->item('codex_asset_folder').'js/jquery.js');
        $files = get_files('./codex/assets/'.$this->template.'/js/');
        if(!empty($files))
        {
            foreach($files as $k=>$file)
                $this->codextemplates->js('js-'.$k,$this->config->item('codex_asset_folder').$this->template.'/js/'.$file);
        }
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_header');
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_footer');
        $this->codextemplates->loadView('templates/alterego/build_component',$data);
        $this->codextemplates->setTitle($this->config->item('codex_site_title').' - Build component');
        $this->codextemplates->printHTML();
    }
    //
    function build()
    {
        $data = array();
        
        $this->_check_perms($data);
        
        //if(!empty($data))
            //redirect('construct');
            
        $fields     = '';
        $yml_fields = '';
        
        $title = trim($this->input->post('title'));
        $alias = trim($this->input->post('alias'));
        //
        $file_name = $alias.'.yml';
        $files = get_files('./codex/application/definitions/');
        // проверяем есть ли уже такой файл,
        // чтобы соблюдать уникальность
        if(in_array($file_name,$files))
            $data['errors']['alias'] = 'Alias "'.$alias.'" exists';
        //
        if(empty($_POST['type_field']))
            $data['errors']['alias'] = 'Ошибка данных: не найден тип полей';
        if(empty($_POST['name_field']))
            $data['errors']['alias'] = 'Ошибка данных: не найдено имя полей';
        if(empty($_POST['label_field']))
            $data['errors']['alias'] = 'Ошибка данных: не найдено название полей';
        if(sizeof($_POST['type_field']) != sizeof($_POST['name_field']) || sizeof($_POST['name_field']) != sizeof($_POST['label_field']))
            $data['errors']['alias'] = 'Ошибка данных: не совпадает информация о полях';
        //
        if(empty($data['errors']))
        {
            foreach($_POST['type_field'] as $k=>$v)
            {
                // пропускаем невидимые поля
                if($k == 0) continue;
                
                $type = '';
                if(in_array($v, array('textbox','aliasbox','checkbox','dropdown','password','radio','image','file')))
                    $type = 'varchar(255)';
                
                if($v == 'date')
                    $type = 'DATE';
                if($v == 'time')
                    $type = 'DATETIME';
                    
                if($v == 'textarea')
                    $type = 'text';
                
                //dbdropdown
                //manytomany
                
                $fields .= '`'.$_POST['name_field'][$k].'` '.$type.' NOT NULL, ';
                
                $yml_fields .= '    
        '.$_POST['name_field'][$k].":
            class: ".$v."
            label: '".$_POST['label_field'][$k]."'
            params:
                display_name: '".$_POST['label_field'][$k]."'";
            }
/*        
создаём таблицу
*/
        $sql = 'CREATE TABLE `'.$alias.'` (
                                  `id` int(11) NOT NULL auto_increment,
                                  '.$fields.'
                                  PRIMARY KEY  (`id`));';
        mysql_query($sql);
        $yml = 'page_header: \''.$title.'\'
groups: \'Default\'
form_setup:'.$yml_fields;
/*        
создаём *.yml файл для автоматической генерации в админке
*/
        $fp = fopen('./codex/application/definitions/'.$alias.'.yml','w');
        fwrite($fp,$yml);
        fclose($fp);        
/*        
создаём модуль на фронтенде
*/

// редирект на созданые файл в админке
        }else{
            $this->index();            
        }
    }
}