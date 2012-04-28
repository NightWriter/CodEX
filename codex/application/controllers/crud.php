<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php'); 
class CRUD extends codexController{
    function CRUD(){
         
        codexcontroller::codexcontroller();

        if(array_key_exists('t',$_GET)){
            $table = $_GET['t'];

            if(!($this->db->table_exists($table) && !in_array($table,$this->config->item('codex_exclude_tables')))){
                show_error('Table generation for '.$table.' not allowed.');
            }
        }
        else
            show_error('Table name missing.');

        if(file_exists($this->codexadmin->getDefinitionFileName($table))){
            $this->load->library('spyc');
            
            $config = $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName($table));
            
            if(!isset($config['form_setup']))
              $config['form_setup'] = $this->codexforms->getSetupForTable($table);

            $config['controller_name'] = 'CRUD';
            $config['db_table'] = $table;
        }
        else
            $config = array(
                          'db_table'=>$table,
                          'form_setup'=>$this->codexforms->getSetupForTable($table),
                          'controller_name'=>'CRUD',
                      );

        if(!isset($config['page_header'])) 
            $config['page_header'] = humanize($table);

        // Setup the special links 
        
        $prefix = '?';
        if($this->config->item("enable_query_strings"))
            $prefix = '';
            
        $config['controller_link'] = $prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'index')));
        
        $config['ordering_link'] = $prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'ajax_ordering')));
        $config['pagination_link'] = site_url($prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'ajax_pagination'))));
        
        $config['edit_link'] = $prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'manage','a'=>'edit','id'=>'{num}')));
        $config['add_link'] = $prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'add')));
        $config['import_link'] = $prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'import')));
        
        if(isset($config['user_link']))
            $config['user_link'] = $config['user_link'];

        $config['add_action'] = $prefix.$this->implodeAssoc('=','&amp;',array_merge($_GET,array('m'=>'execute_add')));
        $config['edit_action'] = $prefix.$this->implodeAssoc('=','&amp;',array('c'=>'crud','m'=>'execute_edit','t'=>$table));
        $config['delete_action'] = $prefix.$this->implodeAssoc('=','&amp;',array('c'=>'crud','m'=>'manage','a'=>'delete_selected','t'=>$table));
        $config['search_action'] = $prefix.$this->implodeAssoc('=','&amp;',array('c'=>'crud','m'=>'search','a'=>'delete_selected','t'=>$table));
        $config['template_chooser_action'] = $prefix.$this->implodeAssoc('=','&amp;',array('c'=>'crud','m'=>'changeTemplate','a'=>'delete_selected','t'=>$table));
        $config['view_mode_chooser_action'] = $prefix.$this->implodeAssoc('=','&amp;',array('c'=>'crud','m'=>'changeViewMode','a'=>'delete_selected','t'=>$table));


        $this->setConfig($config);
    }
    //
    //
    function import()
    {
        parent::import();
    }
    //
    function ajax_pagination()
    {
        $per_page = intval($this->input->post('per_page'));
        $page     = intval($this->input->post('page'));
        
        $this->ajaxPagination($per_page,$page);
    }
    //
    function ajax_ordering()
    {
        $this->ajaxOrdering();
    }
    //
    function setupPagination($max_rows){
        
        $config = array();
        $this->load->library('codexpagination');

        $config['total_rows'] = $max_rows;
        $config['per_page'] = $this->on_one_page;
        $config['num_links'] = 2;
        $config['full_tag_open'] = '<div class="pagination-links">';
        $config['full_tag_close'] = '</div>';
        $config['first_link'] = $this->lang->line('codexadmin_first');
        $config['last_link'] = $this->lang->line('codexadmin_last');

        $config['query_string'] = true;
        $config['base_url'] = site_url('?c='.strtolower($this->controller_name).'&m=index&t='.$this->table);

        $this->codexpagination->initialize($config);
        return $config;
    }

    function show(){
        $this->index();
    }
    function implodeAssoc($glue_inner,$glue_outer,$array){
        $count = count($array);
        $output = '';
        $i = 0;

        foreach($array as $k=>$v){
            $output .= $k.$glue_inner.$v;

            if($i != $count - 1)
                $output .= $glue_outer;
            $i++;
        }
        return $output;
    }
}
?>
