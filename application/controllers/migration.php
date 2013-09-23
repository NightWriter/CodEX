<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration extends MY_Controller 
{
    private $result_id = null;
    function __construct()
    {
        parent::__construct();
    }
    /**
    * создаём набор данных для создания таблиц
    * 
    */
    function fix_change()
    {
        $tables = $this->db->list_tables();

        foreach ($tables as $table)
        {
            $fields = $this->fields_data($table);
            echo $table, "\n";
           
            foreach ($fields as $field)
            {
                print_r($field);
            }
           break;
        }
    }
    /**
    * полный набор данных
    * 
    */
    function fields_data($table='')
    {
        $this->result_id = mysql_query("SELECT * FROM {$table}");
        $retval = array();
        $fields = array();
        while ($field = mysql_fetch_field($this->result_id))
        {
            $fields[] = $field;
        }

        return $fields;
    }
}