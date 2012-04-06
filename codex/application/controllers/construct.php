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
        $name = $this->input->post('name');
        if(preg_match('/[^a-zA-Z0-9_]+/u',$name))
            exit('0');
        $file_name = strip_tags(trim($name));
        if(empty($file_name))
            exit('0');
            
        $file_name .= '.yml';
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
            $data['messages']['failure'][] = 'Не существует директории ./codex/application/definitions';
        if(!is_writable('./codex/application/definitions/'))
            $data['messages']['failure'][] = 'Нет прав на запись в папке ./codex/application/definitions';
        //
        if(!file_exists('./application/modules/'))
            $data['messages']['failure'][] = 'Не существует директории ./application/modules';
        if(!is_writable('./application/modules/'))
            $data['messages']['failure'][] = 'Нет прав на запись в папке ./application/modules';
    }
    //
    function index($data = array())
    {
        $this->_check_perms($data);
        
        $data['view'] = 'build_component';
        $data['title'] = 'Build component';
        
        $rows = $this->db->get('dictionaries');
        if($rows->num_rows > 0)
            $data['dictionaries'] = $rows->result();
        
        $this->_view($data);
    }
    //
    function build()
    {
        $data = array();
        
        $this->_check_perms($data);
        
        $fields     = '';
        $yml_fields = '';
        
        $title = mysql_escape_string(strip_tags(trim($this->input->post('title'))));
        $alias = mysql_escape_string(strip_tags(trim($this->input->post('alias'))));
        //
        $file_name = $alias.'.yml';
        $files = get_files('./codex/application/definitions/');
        // проверяем есть ли уже такой файл,
        // чтобы соблюдать уникальность
        if(empty($title))
            $data['messages']['failure'][] = 'Title is empty';
        //
        if(empty($alias))
            $data['messages']['failure'][] = 'Alias is empty';
        
        if(preg_match('/[^a-zA-Z0-9_]+/u',$alias))
            $data['messages']['failure'][] = 'Alias должен содержать только латинские буквы и цифры';
        
        if(in_array($file_name,$files))
            $data['messages']['failure'][] = 'Alias "'.$alias.'" exists';
        //
        if(empty($_POST['type_field']))
            $data['messages']['failure'][] = 'Ошибка данных: не найден тип полей';
        if(empty($_POST['name_field']))
            $data['messages']['failure'][] = 'Ошибка данных: не найдено имя полей';
        if(empty($_POST['label_field']))
            $data['messages']['failure'][] = 'Ошибка данных: не найдено название полей';
        if(sizeof($_POST['type_field']) != sizeof($_POST['name_field']) || sizeof($_POST['name_field']) != sizeof($_POST['label_field']))
            $data['messages']['failure'][] = 'Ошибка данных: не совпадает информация о полях';
        //
        if(empty($data['messages']))
        {
            foreach($_POST['type_field'] as $k=>$v)
            {
                $v = strip_tags(trim($v));
                // пропускаем невидимые поля или поля которые не выбрали
                if($k == 0 || $v == '-') continue;
                
                $type = '';
                if(in_array($v, array('textbox','aliasbox','checkbox','dropdown','password','radio','image','file')))
                    $type = 'varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci ';
                
                if($v == 'date')
                    $type = 'DATE';
                if($v == 'time')
                    $type = 'DATETIME';
                    
                if($v == 'textarea')
                    $type = 'text CHARACTER SET utf8 COLLATE utf8_unicode_ci';
                
                if($v == 'dbdropdown')
                    $type = 'int(11) UNSIGNED ';
                
                //dbdropdown
                //manytomany
                if($v != 'manytomany')
                    $fields .= '`'.mysql_escape_string(strip_tags(trim($_POST['name_field'][$k]))).'` '.$type.' NOT NULL, ';
                
                $yml_fields .= '    
        '.strip_tags(trim($_POST['name_field'][$k])).":
            class: ".$v."
            label: '".strip_tags(trim($_POST['label_field'][$k]))."'
            params:
                display_name: '".strip_tags(trim($_POST['label_field'][$k]))."'";
            }
/*        
создаём таблицу
*/
        $sql = 'CREATE TABLE `'.$alias.'` (
                                  `id` int(11) UNSIGNED  NOT NULL auto_increment,
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
        mkdir('./application/modules/'.$alias,0777);
        mkdir('./application/modules/'.$alias.'/controllers',0777);
        mkdir('./application/modules/'.$alias.'/models',0777);
        mkdir('./application/modules/'.$alias.'/views',0777);
        
        $fp = fopen('./application/modules/'.$alias.'/controllers/'.$alias.'.php','w');
        //
        fclose($fp);
        $fp = fopen('./application/modules/'.$alias.'/models/'.$alias.'_model.php','w');
        //
        fclose($fp);
        $fp = fopen('./application/modules/'.$alias.'/views/'.$alias.'.php','w');
        //
        fclose($fp);
        
        // редирект на созданые файл в админке
        redirect('c=crud&m=index&t='.$alias);
        }else{
            $this->index($data);
        }
    }
}
