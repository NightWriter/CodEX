<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
class DbCount extends RelationalContainer
{
    // @todo сделать так, чтобы это значение не сохранялось в базу. Пока сойдет и так.
    var $_params = array();
    var $table = ''; // таблица, число связанных записей с которой нужно получить
    var $field = ''; // поле для связи
    var $local_table = ''; // текущая таблица
    var $display_name = '';
    
    function DbCount($name, $params)
    {
        $CI = &get_instance();
        // инициализация стартовых параметров
        RelationalContainer::RelationalContainer($name,$params);
        $this->_params = $params;
        $this->table = $this->_params['params']['table'];
        $this->field = $this->_params['params']['field'];
        $this->display_name = $this->_params['params']['display_name'];
        $this->local_table = $CI->codexadmin->db_table;
        if(empty($this->table) || empty($this->field))
            exit('Ошибка подключения! Необходимо выбрать таблицу и поле для связки');
    }
    
    
    function getDisplayName(){
        return $this->display_name;
    }
    
    function getHTML()
    {
        $html = "";
        $html .= $this->prefix;
        $html .= '<label class="control-label" for="'.$this->element_name.'">
                '.$this->label.'
            </label><div class="controls">';
        $html .= '    <input class="input-xlarge type="text" value="'.($this->getCount()).'"/></div>';
        $html .= $this->suffix;
        return $html;
    }
    
    function prepForDisplay($value)
    {
        return $this->getCount();
    }
    
    function prepForDb($data,$field){
        return NULL;
    }
    
    /**
    * Получим количество соответствий в другой таблице
    * 
    */
    function getCount()
    {
        $CI = &get_instance();
        $CI->db->select('count(*) as counter');
        $CI->db->from($this->local_table);
        $CI->db->join($this->table,$this->table.".{$this->field} = {$this->local_table}.id");
        $CI->db->where($this->local_table.'.id',$CI->codexadmin->active_id);
        $counter = $CI->db->get()->row()->counter;
        return $counter;
    }
}