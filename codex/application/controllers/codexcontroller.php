<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class codexController extends CI_Controller {
    var $page_header = "";
    var $view_mode = "";
    var $controller_name = "";
    var $table = "";
    var $template = "";
    var $rules = "";
    var $order_by = "";
    var $order_type = "";
    var $on_one_page = "";
    var $extra_uri_segments = "";
    var $table_access_restriction = array();

    var $add_link         ;     
    var $import_link      ;     
    var $edit_link        ;     
    var $controller_link  ;    
     
    var $user_link        = '';
    var $ordering_link    = '';     
    var $pagination_link  = '';
    
    var $add_action       ;     
    var $edit_action      ;     
    var $delete_action    ;     
    var $copy_action    ;     
    var $search_action    ;     
    
    var $user_data = array();     
    var $responce  = array();
    //
    
    function codexController () {
        
        parent::__construct();
        
        $this->lang->load('codex');
        
        $this->load->model('codexmodel');

        $this->load->helper('preview');
        
        $this->codextemplates->docType('html5');
        $this->codextemplates->setTitle($this->config->item('codex_default_title'));
        $this->codextemplates->rawHeaderHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">'); 
    } 
    function _remap($method)
    {
        $this->$method();
    }
    function check_alias()
    {
        $id = trim($this->input->post('id'));
        $text = trim($this->input->post('text'));
        $attr = trim($this->input->post('attr'));
        $attr = explode(',',$attr);
        if(!is_numeric($id) || empty($attr[0]) || empty($attr[1]))
        {
            echo 0;
            exit;
        }
        //$this->db->select('id');
        $this->db->where($attr[1],$text);
        $row = $this->db->get($attr[0],1);
        if($row->num_rows == 1)
        {
            $row = $row->row();
            if($row->id == $id)
                echo 1;
            else
                echo 0;
            exit;
        }
        echo 1;
    }
    function getID($offset=3){
        if($id = $this->input->post($this->codexadmin->primary_key))
            return $id;
        else if(is_numeric($this->uri->segment(3)))
            return $this->uri->segment(3);
        else if(is_numeric($this->uri->segment(4)))
            return $this->uri->segment(4);
        else if(isset($_GET['id']))
            return $_GET['id'];
        else{
            show_error($this->lang->line('codex_id_not_found'));
        }
    }
    function setConfig($config){
         
         $this->table                    = (isset($config['db_table']))? $config['db_table'] : show_error($this->lang->line('codex_table_not_defined'));
         $this->form_setup               = (isset($config['form_setup']))? $config['form_setup'] : show_error($this->lang->line('codex_form_not_defined'));
         
         if(isset($config['form_setup_first'])){
            $this->form_setup = array_merge($config['form_setup_first'],$this->form_setup);
            $config['form_setup'] = $this->form_setup;
         }
         $this->controller_name          = (isset($config['controller_name']))? $config['controller_name'] : show_error($this->lang->line('codex_controller_not_defined'));
         
         $this->table_access_restriction = (isset($config['table_access_restriction']))? $config['table_access_restriction'] : array();

         $this->order_by                 = (isset($config['order_by']))? $config['order_by'] : "";
         $this->order_type               = (isset($config['order_type']))? $config['order_type'] : "";
         /*
         * пока используется только для поиска
         * во view_mode = filter_table
         */
         $this->user_data               = (isset($config['user_data']))? $config['user_data'] : "";
         
         $this->page_header              = (array_key_exists('page_header',$config))? $config['page_header'] : $config['controller_name'];
         $this->template                 = (isset($_COOKIE['codex_template']))? $_COOKIE['codex_template'] : $this->config->item("codex_template");
         $this->view_mode                 = (isset($_COOKIE['codex_view_mode']))? $_COOKIE['codex_view_mode'] : $this->config->item("codex_view_mode");
         
         $this->view_mode               = (isset($config["codex_view_mode"]))? $config["codex_view_mode"] : $this->view_mode;
         
         $this->rules                    = isset($config['rules']) ? $config['rules'] : array();

         $this->on_one_page              = isset($config['on_one_page']) ? $config['on_one_page'] : $this->config->item('codex_items_per_page');
         $this->first_item               = $this->getFirstItem();

         $this->add_link                 = (isset($config['add_link']))? $config['add_link'] : strtolower($this->controller_name).'/add';
         $this->import_link              = (isset($config['import_link']))? $config['import_link'] : strtolower($this->controller_name).'/import';
         $this->edit_link                = (isset($config['edit_link']))? $config['edit_link'] : strtolower($this->controller_name).'/manage/edit/{num}';
         $this->add_action               = (isset($config['add_action']))? $config['add_action'] : strtolower($this->controller_name).'/execute_add';
         $this->edit_action              = (isset($config['edit_action']))? $config['edit_action'] : strtolower($this->controller_name).'/execute_edit';
         $this->delete_action            = (isset($config['delete_action']))? $config['delete_action'] : strtolower($this->controller_name).'/manage';
         $this->search_action            = (isset($config['search_action']))? $config['search_action'] : strtolower($this->controller_name).'/search';
         
         if(!empty($config['no_search_form']))
            $this->search_action         = '';
            
         $this->theme_chooser_action     = (isset($config['template_chooser_action']))? $config['template_chooser_action'] : strtolower($this->controller_name).'/changeTemplate';
         $this->view_mode_chooser_action = (isset($config['view_mode_chooser_action']))? $config['view_mode_chooser_action'] : strtolower($this->controller_name).'/changeViewMode';
         $this->controller_link          = (isset($config['controller_link']))? $config['controller_link'] : strtolower($this->controller_name);

         $this->ordering_link            = (isset($config['ordering_link']))? $config['ordering_link'] : strtolower($this->ordering_link);
         $this->pagination_link          = (isset($config['pagination_link']))? $config['pagination_link'] : strtolower($this->pagination_link);
         $this->user_link                = (isset($config['user_link']))? $config['user_link'] : '';
         
         $this->codexcrumbs->add($this->lang->line('codex_crumbs_home'));
         $this->codexcrumbs->add($this->controller_link,$this->page_header);
         $this->codexcrumbs->setSelected($this->page_header);
         
         $this->codexadmin->_initialize($config);
         
         $access_level = 0;
         $user_level   = intval($this->codexsession->userdata('user_level'));
         if(!empty($config['access_level']))
            $access_level = intval($config['access_level']);
         if(!$this->codexmodel->check_access($this->uri->uri_string()))
         {
            if($user_level != 3 && ($user_level < $access_level || $access_level == 0))
            {
                if($red_link = $this->codexmodel->get_redirect_link())
                {
                    // если get-параметры - начало  get не нужно
                    if(strpos($red_link,'?') === 0)
                        $red_link = substr($red_link,1,strlen($red_link));
                    // декод спецсимволов в сущности
                    $red_link = html_entity_decode($red_link);
                    redirect($red_link);
                }
                else
                    redirect('login');
            }
         }
         
        //$this->load->helper('file');
        
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
        
        /*$this->codextemplates->css('template-css',$this->config->item('codex_asset_folder').$this->template.'/css/codex_'.$this->template.'.css');
        $this->codextemplates->jsFromAssets('js-framework','jquery.js');
        $this->codextemplates->jsFromAssets('js-livequery','jquery.livequery.min.js');*/
        
        /**
        * обрабатываем обновление полей в списке данных
        * не заходя на редактирование
        */
        if(!empty($_REQUEST['update_object']))
        {
            if($_REQUEST['update_object'] == 'change_checkbox')
            {
                $this->change_checkbox();
            }
            exit;
        }
    }
    //
    function ajaxPagination($per_page=10,$page=0)
    {
        $keywords = $this->input->post('query');
        $fields   = $this->input->post('field');
        
        $page = ($page <= 0) ? 0:$page;
        
        $this->codexcrumbs->add($this->lang->line('codex_crumbs_overview'));
        $this->codexadmin->state = 'index';

        ///
        if(!empty($keywords) && !empty($fields))
        {
            $keywords = explode(',',$keywords);
            $fields   = explode(',',$fields);
            
            for($i=0;$i<count($keywords);$i++){
                if(empty($keywords[$i]))
                    continue;
                if(!empty($this->codexadmin->form_setup[$fields[$i]])){
                    if(strtolower($this->codexadmin->form_setup[$fields[$i]]['class']) == 'dbdropdown'){
                        
                        if(empty($this->codexadmin->form_setup[$fields[$i]]['params']['primary_key']))
                            $this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'] = 'id';
                            
                        $this->db->select($this->codexadmin->form_setup[$fields[$i]]['params']['table'].'.'.$this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'].' as '.
                                            $this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'].'_'.$this->codexadmin->form_setup[$fields[$i]]);
                        
                        $this->db->join($this->codexadmin->form_setup[$fields[$i]]['params']['table'],
                                        $this->codexadmin->form_setup[$fields[$i]]['params']['table'].'.'.$this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'].'='.
                                        $this->table.'.'.$fields[$i]
                                        );
                        $this->db->like($this->codexadmin->form_setup[$fields[$i]]['params']['table'].'.'.$this->codexadmin->form_setup[$fields[$i]]['params']['field'],$keywords[$i]);
                        
                    }else{
                        $this->db->like($this->table.'.'.$fields[$i],$keywords[$i]);
                    }
                }else{
                    $this->db->like($this->table.'.'.$fields[$i],$keywords[$i]);
                }
            }
        }
        $this->setupQuery();
        //Retrieve all the records
        $per_page = empty($per_page) ? 10 : $per_page;
        $page = $per_page * $page;
        $query = $this->db->get($this->table,$per_page,$page);
        
        $this->loadAjaxOverview($query->result_array());
    }
    //
    function loadAjaxOverview($result){

        $result = $this->prepForDisplay($result); 
        
        $data['add_link'] = $this->add_link;
        $data['entries'] = $result;
        $data['messages'] = $this->codexmessages->get();
        
        //$this->codextemplates->loadView('templates/'.$this->template.'/codex_header',$data);
        $this->codextemplates->loadView($this->config->item('codex_layout_dir').'ajax_data',$data);
        //$this->codextemplates->loadView('templates/'.$this->template.'/codex_footer',$data);
        //$this->codextemplates->setTitle($this->page_header.$this->lang->line('codex_overview_title'));
        header('Content-Type:text/html; charset=utf-8');
        echo $this->codextemplates->getPart('body');
    }
    //
    function getTemplatePath(){
        return base_url().CODEXPATH.'views/templates/'.$this->template;
    }
    function index(){
        $this->codexcrumbs->add($this->lang->line('codex_crumbs_overview'));
        $this->codexadmin->state = 'index';

        $this->setupQuery();

        //Retrieve all the records
        $query = $this->db->get($this->table,$this->on_one_page);
        //if(!array_key_exists($this->codexadmin->primary_key,$this->form_setup))
            $primary_key = $this->codexadmin->primary_key;
        
        $this->db->select('COUNT(`'.$primary_key.'`) as cnt');
        if(count($this->table_access_restriction) > 0) 
            foreach($this->table_access_restriction as $field=>$value)
            {
                if(is_array($value))
                    $this->db->where_in($field,$value);
                else
                    $this->db->where($field,$value);
            }   
        $rows = $this->db->get($this->table);
        $total_rows = $rows->row()->cnt;
        
        $result = $query->result_array();
        
        $this->loadOverview($result,$total_rows);
    }

    function changeTemplate(){
        if(isset($_POST['template'])){
            $dir = APPPATH.'views/templates/'.$_POST['template'];
            if(is_dir($dir)){
                setcookie('codex_template',$this->input->post('template'),time()+3600*24*7*3,'/','',false);
                $this->template = $_POST['template'];
                $this->codextemplates->css('template-css',$this->config->item('codex_asset_folder').$this->template.'/css/codex_'.$this->template.'.css');
            }
        }
        $this->index();
    }

    function changeViewMode(){
        $dir = APPPATH.'views/view_modes/'.$_POST['view_mode'];
        if(is_dir($dir)){
            setcookie('codex_view_mode',$this->input->post('view_mode'),time()+3600*24*7*3,'/','',false);
            $this->template = $_POST['view_mode'];
        }

        $this->index();
    }

    function removePermission(){
        return 'failure';
    }

    function loadOverview($result,$total_rows){

        $result = $this->prepForDisplay($result); 
         
        $data['add_link'] = $this->add_link;
        $data['entries'] = $result;
        $data['total_rows'] = $total_rows;
        $data['messages'] = $this->codexmessages->get();

        $this->codextemplates->loadView('templates/'.$this->template.'/codex_header',$data);
        $this->codextemplates->loadView($this->config->item('codex_layout_dir').$this->view_mode,$data);
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_footer',$data);
        $this->codextemplates->setTitle($this->page_header.$this->lang->line('codex_overview_title'));
        $this->codextemplates->printHTML();
    }

    function search(){
        $this->codexadmin->state = 'search';
        $keywords = $this->input->post('query');
        $fields = $this->input->post('fields');

        if(count($keywords) != count($fields))
            show_error($this->lang->line('codex_search_keywords_vs_fields'));

        for($i=0;$i<count($keywords);$i++){
            if(!empty($this->codexadmin->form_setup[$fields[$i]])){
                if(strtolower($this->codexadmin->form_setup[$fields[$i]]['class']) == 'dbdropdown'){
                    
                    if(empty($this->codexadmin->form_setup[$fields[$i]]['params']['primary_key']))
                        $this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'] = 'id';
                        
                    $this->db->select($this->codexadmin->form_setup[$fields[$i]]['params']['table'].'.'.$this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'].' as '.
                                        $this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'].'_'.$this->codexadmin->form_setup[$fields[$i]]);
                    
                    $this->db->join($this->codexadmin->form_setup[$fields[$i]]['params']['table'],
                                    $this->codexadmin->form_setup[$fields[$i]]['params']['table'].'.'.$this->codexadmin->form_setup[$fields[$i]]['params']['primary_key'].'='.
                                    $this->table.'.'.$fields[$i]
                                    );
                    $this->db->like($this->codexadmin->form_setup[$fields[$i]]['params']['table'].'.'.$this->codexadmin->form_setup[$fields[$i]]['params']['field'],$keywords[$i]);
                    
                }else{
                    $this->db->like($this->table.'.'.$fields[$i],$keywords[$i]);
                }
            }else{
                $this->db->like($this->table.'.'.$fields[$i],$keywords[$i]);
            }
        }
        $this->codexcrumbs->add(strtolower($this->controller_link),$this->lang->line('codex_crumbs_overview'));
        $this->codexcrumbs->add($this->lang->line('codex_search'));

        $this->setupQuery();

        //Retrieve all the records
        $query = $this->db->get($this->table);
        $max_rows = $query->num_rows();

        $result = $query->result_array();
        //Split it up for pagination
        $this->loadOverview($result,$max_rows);
    }

    function getFirstItem(){
        if(is_numeric($this->uri->segment(3)))
            return (int)$this->uri->segment(3); 
        else{
            if(isset($_GET['p']) AND is_numeric($_GET['p']))
              return $_GET['p'];   
        }
    }

    function prepForDisplay($result){
        for($i=0;$i<count($result);$i++){
            //Update the active id
            $this->codexadmin->active_id = $result[$i][$this->codexadmin->primary_key];

            $db_data = $result[$i];
            $db_data = $this->event->trigger('prepForDisplay',array($db_data));
            $db_data = $db_data[0];

            if(!array_key_exists($this->codexadmin->primary_key,$this->form_setup) &&
                isset($result[$i][$this->codexadmin->primary_key])) 
                $db_data[$this->codexadmin->primary_key] = $result[$i][$this->codexadmin->primary_key];

            $result[$i] = $db_data;
        }

        return $result;
    }

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

        $config['base_url'] = site_url(strtolower($this->controller_name).'/index');

        $this->codexpagination->initialize($config);
        return $config;
    }

    function setupQuery(){
        /*
         * Setup the ordering
         */
        if(!$order_by = $this->input->post('order_by'))
            $order_by = ($this->order_by == "")? $this->codexadmin->primary_key : $this->order_by;

        if(!$order_type = $this->input->post('order_type'))
            $order_type = ($this->order_type == "")? 'desc' : $this->order_type;

        $this->db->order_by($this->table.'.'.$order_by,$order_type);

        /*
         * Get the list of database fields. We do this because some plugins 
         * don't require a field in their db
         */
        $field_names = $this->event->trigger('getDbFields');
        $field_names = $field_names[0];
        
        // if the primary_key isn't a field, we have to manually add it
        if(!array_key_exists($this->codexadmin->primary_key,$this->form_setup))
            $field_names[] = $this->codexadmin->primary_key;

        //$this->db->select(implode(',',$field_names));
        foreach($field_names as $row)
            $this->db->select($this->table.'.'.$row);
            
        //Apply any restrictions
        if(count($this->table_access_restriction) > 0) 
            foreach($this->table_access_restriction as $field=>$value)
            {
                if(is_array($value))
                    $this->db->where_in($this->table.'.'.$field,$value);
                else
                    $this->db->where($this->table.'.'.$field,$value);
            }
    }
    /**
    * обновляем значение Чекбокса
    * 
    */
    function change_checkbox()
    {
        $primary_key   = trim($this->input->post('primary_key'));
        $primary_value = intval($this->input->post('primary_value'));
        $table = trim($this->input->post('table'));
        $field = trim($this->input->post('field'));
        $value = intval($this->input->post('value'));
        
        $this->db->set($field,$value);
        $this->db->where($primary_key,$primary_value);
        
        $response = array('success' => FALSE, 'value' => $value);
        if($this->db->update($table))
        {
            $response['value'] = ($value ? 0 : 1);
            $response['src'] = base_url().'codex/images/status_'.($value ? 1 : 0).'.png';
            $response['success'] = TRUE;
        }
        
        echo json_encode($response);
    }
    //
    function change_select()
    {
        $primary_key   = trim($this->input->post('primary_key'));
        $primary_value = intval($this->input->post('primary_value'));
        $table = trim($this->input->post('table'));
        $field = trim($this->input->post('field'));
        $value = intval($this->input->post('value'));
        
        $this->db->set($field,$value);
        $this->db->where($primary_key,$primary_value);
        $this->db->update($table);
    }
    //
    function import()
    {
        $config = array();
        $config['upload_path'] = './codex/temp/';
        $config['allowed_types'] = 'csv|xls|xlsx';
        $config['max_size'] = '2048';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);
        
        if($this->upload->do_upload('import'))
        {
            
            $finfo = $this->upload->data();
            $file_name = $finfo['file_name']; 
            $file_ext = $finfo['file_ext']; 
            
            $insert_data = array();
            if(ltrim($file_ext,'.') == 'csv')
            {
                if (($handle = fopen('./codex/temp/'.$file_name, "r")) !== FALSE)
                {
                    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE)
                    {
                        $insert_data[] = $data;
                    }
                    fclose($handle);
                }
            }
            if(in_array(ltrim($file_ext,'.'),array('xls','xlsx')))
            {
                require_once 'codex/application/my_classes/classes/PHPExcel.php';
                require_once 'codex/application/my_classes/classes/PHPExcel/IOFactory.php';
                
                $objPHPExcel = PHPExcel_IOFactory::load('./codex/temp/'.$file_name);
                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
                {
                    $worksheetTitle     = $worksheet->getTitle();
                    $highestRow         = $worksheet->getHighestRow(); // например, 10
                    $highestColumn      = $worksheet->getHighestColumn(); // например, 'F'
                    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                    $nrColumns = ord($highestColumn) - 64;
                    
                    for ($row = 1; $row <= $highestRow; ++ $row)
                    {
                        $idata = array();
                        for ($col = 0; $col < $highestColumnIndex; ++ $col) 
                        {
                            $cell = $worksheet->getCellByColumnAndRow($col, $row);
                            $val = $cell->getValue();
                            $dataType = PHPExcel_Cell_DataType::dataTypeForValue($val);
                            $idata[] = $val;
                        }
                        $insert_data[] = $idata;
                    }
                }
            }
            //
            if(is_array($this->codexadmin->display_fields))
            {
                foreach($insert_data as $_k=>$_v)
                {
                    $j=0;
                    foreach($this->codexadmin->display_fields as $k=>$item)
                    {
                        if(!isset($_v[$j])) continue;
                        $this->db->set($k,$_v[$j]);
                        ++$j;
                    }
                    if($j)
                        $this->db->insert($this->table);
                }
            }
            if(file_exists('./codex/temp/'.$file_name) && is_file('./codex/temp/'.$file_name))
                unlink('./codex/temp/'.$file_name);
                
            $this->responce['success'] = true;
        }else{
            $this->responce['messages'] = strip_tags($this->upload->display_errors());
            $this->responce['success'] = false;
        }
        echo json_encode($this->responce);
        exit;
    }
    //
    function add($populate_form_with = array())
    {

        if(count($populate_form_with) > 0)
            $this->codexadmin->state = 'add_with_array';
        else
            $this->codexadmin->state = 'add';

        $this->codexcrumbs->add($this->uri->uri_string(),'Add');
        $this->codexcrumbs->setSelected('Add');

        // Set an identifier for CSRF protection
        $this->codexsession->set_flashdata('form_id',$this->codexsession->userdata('user_id'));

        if(count($populate_form_with) > 0)
            $this->codexforms->populateWithArrayInfo($populate_form_with);

        //Get the list of users for permissions
        $query                          = $this->db->get('users');
        $permissions_data['user_list']  = $query->result_array();

        $data['form_html']              = $this->codexforms->getHTML();
        $data['form_action']            = $this->add_action;
        $data['messages'] = $this->codexmessages->get();

        $data['permissions_form']       = $this->codextemplates->fetchView('templates/'.$this->template.'/codex_permissions_form',$permissions_data);

        $this->codextemplates->loadView('templates/'.$this->template.'/codex_header',$data);
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_form_view',$data);
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_footer',$data);
        $this->codextemplates->setTitle($this->lang->line('codex_add_title').$this->page_header);
        $this->codextemplates->printHTML();
    }

    function UserIsOwnerOfRecord(){
        $record_id = $this->codexadmin->active_id;
        $user_id = $this->codexsession->userdata('user_id');

        $this->db->select('permissions');
        $this->db->where('record_id',$record_id);
        $this->db->where('user_id',$user_id);
        $query = $this->db->get('user_records');
        $result = $query->result_array();

        foreach($result as $row){
            if($row['permissions'] == 'owner')
                return true;
        }

        return false;
    }

    function _edit($populate_form_with = array()){
        if(count($populate_form_with) > 0)
            $this->codexadmin->state = 'edit_with_array';
        else
            $this->codexadmin->state = 'edit';

        $id = $this->getID();
        $this->codexadmin->active_id = $id;
        $this->codexcrumbs->add($this->uri->uri_string(),'Edit');
        $this->codexcrumbs->setSelected('Edit');
        $this->codexsession->set_flashdata('form_id',$this->codexsession->userdata('user_id'));

        $ownsership_result = $this->event->trigger('checkRecordOwnership',array('edit',$this->table,$id));
        if($ownsership_result[0] === false){
            redirect(str_replace('&amp;','&',$this->controller_link));
        }
        
        $query = $this->db->get('users');
        $permissions_data['user_list'] = $query->result_array();

        $data = array();
        if(count($populate_form_with) > 0)
            $this->codexforms->populateWithArrayInfo($populate_form_with);
        else
            $this->codexforms->populateWithDbInfo($this->table,$id);

        if($this->UserIsOwnerOfRecord())
            $view_data['permissions_form']       = $this->load->view('templates/'.$this->template.'/codex_permissions_form',$permissions_data,true);
        else
            $view_data['permissions_form']       = '';

        $view_data['form_html'] = $this->codexforms->getHTML();
        $view_data['form_action'] = $this->edit_action;
        $view_data['messages'] = $this->codexmessages->get();

        $this->codextemplates->loadView('templates/'.$this->template.'/codex_header',$view_data);
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_form_view',$view_data);
        $this->codextemplates->setTitle($this->lang->line('codex_edit_title').$this->page_header);
        $this->codextemplates->printHTML();
    }
    function delete($id=""){
        $this->codexadmin->state = 'delete';
        if($id === "")
            if(!($this->getID()))
                show_error($this->lang->line('codex_invalid_id'));

        $this->_delete_selected_helper($id);
        redirect(str_replace('&amp;','&',$this->controller_link));
    }
    function execute_add(){
        if(count($this->rules) > 0){
            $this->setValidationErrors('add');
        }

        $db_data = array();
        $this->event->trigger('preInsertHook');
        
        // если мультиаплод файлов
        if(!empty($_REQUEST['multiupload']))
        {
            $db_data = $this->event->trigger('multiUploadFile',array($_POST));
        }
        else
        {
            $db_data = $this->event->trigger('prepForDb',array($_POST));
        }

        if(!empty($this->table_access_restriction))
            $db_data[0] = array_merge($db_data[0],$this->table_access_restriction);
            
        if(isset($db_data[0]['date_cr']))
        {
            $db_data[0]['date_cr'] = time();
        }

        if(count($db_data) > 0)
            try
            {
                if($this->codexmodel->_add($this->table,$db_data[0])){

                    $this->codexadmin->active_id        = $this->db->insert_id();
                    
                    //
                    $_POST[$this->codexadmin->primary_key]            = $this->codexadmin->active_id;
                    $_POST['table']         = $this->table;
                    $this->event->trigger('postInsertHook',array($_POST));

                    $this->codexmessages->add('success',$this->codexadmin->get_message('add_success'));
                    redirect(str_replace('&amp;','&',$this->controller_link));
                }
                else{
                    $this->codexmessages->add('success',$this->codexadmin->get_message('add_failure'));
                    redirect(str_replace('&amp;','&',$this->controller_link));
                }
            }
            catch (Exception $e)
            {
                $this->codexmessages->add('failure',$e->getMessage());
                redirect(str_replace('&amp;','&',$this->controller_link));
            }
    }
    function setValidationErrors($func){
            $this->codexvalidation->set_rules($this->rules);

            if($this->codexvalidation->run() == FALSE){
                $this->codexsession->keep_flashdata('form_id');

                $validation_errors = $this->codexmessages->get('form');
                $errors = array();
                
                if(is_array($validation_errors))
                {
                    foreach($validation_errors as $validation_error){
                        $parts = explode('|',$validation_error);
                        $errors[$parts[0]] = $parts[1];
                    }
                }
                $this->codexforms->setValidationErrors($errors);
                $this->$func($_POST);
                exit();
            }
    }
    function execute_edit(){
        $id = $this->getID();
        $this->codexadmin->active_id = $id;

        if(!(is_int($id) OR is_string($id))){
            show_error($this->lang->line('codex_invalid_id'));
        }

        if(count($this->rules) > 0){
            $this->setValidationErrors('_edit');
        }

        $this->event->trigger('preEditHook',array($this->table,$id));
        // если мультиаплод файлов
        if(!empty($_REQUEST['multiupload']))
        {
            $db_data = $this->event->trigger('multiUploadFile',array($_POST));
        }
        else
        {
            $db_data = $this->event->trigger('prepForDb',array($_POST));
        }

        if(isset($db_data[0]) && isset($db_data[0]['date_cr']))
            unset($db_data[0]['date_cr']);
        
        if(isset($db_data[0]) && isset($db_data[0]['date_md']))
            $db_data[0]['date_md'] = time();
        // обработаем возможные ошибки при редактировании.
        try
        {
            if($this->codexmodel->_edit($this->table,$id,$db_data[0]))
            {
                $this->event->trigger('postEditHook',array($db_data[0]));
            
                    
                $this->codexmessages->add('success',$this->codexadmin->get_message('edit_success'));
                redirect(str_replace('&amp;','&',$this->controller_link));
            }
            else{
                $this->codexmessages->add('failure',$this->codexadmin->get_message('edit_failure'));
                redirect(str_replace('&amp;','&',$this->controller_link));
            }
        }
        catch (Exception $e)
        {           
            $this->codexmessages->add('failure',$e->getMessage());
            redirect(str_replace('&amp;','&',$this->controller_link));    
        }
    }
    function _delete_selected_helper($id){
        $table = $this->table;
        $this->codexadmin->active_id = $id;

        if(!$id){
            show_error($this->lang->line('codex_invalid_id'));
        }

        $query = $this->db->get_where('user_records',array('user_id'=>$this->codexsession->userdata('user_id'),
                                                           'record_id'=>$id));
        $result = $query->result_array();
        if(!(count($result) > 0 AND strpos($result[0]['permissions'],'owner') !== false)){
            $ownsership_result = $this->event->trigger('checkRecordOwnership',array('delete',$table,$id));
            if($ownsership_result[0] === false){
                redirect(str_replace('&amp;','&',$this->controller_link));
            }
        }
        
        if($this->event->trigger('preDeleteHook',array($table,$id))){
            $this->event->trigger('prepForDelete',array($table,$id));
            
            try
            {
                $this->codexmodel->delete($table,$id);
            }
            catch (Exception $e)
            {
                $this->codexmessages->add('failure',$e->getMessage());
                redirect(str_replace('&amp;','&',$this->controller_link));    
            }
            
            
            $this->event->trigger('postDeleteHook',array($table,$id));

            if($this->db->affected_rows() != 1){
                return false;
            }
            else{
                return true;
            }
        }
        return false;
    }
    //
    function _copy_selected_helper($id){
        $table = $this->table;
        $this->codexadmin->active_id = $id;

        if(!$id){
            show_error($this->lang->line('codex_invalid_id'));
        }
        
        $query = $this->db->get_where('user_records',array('user_id'=>$this->codexsession->userdata('user_id'),
                                                           'record_id'=>$id));
        $result = $query->result_array();
        if(!(count($result) > 0 AND strpos($result[0]['permissions'],'owner') !== false)){
            $ownsership_result = $this->event->trigger('checkRecordOwnership',array('delete',$table,$id));
            if($ownsership_result[0] === false){
                redirect(str_replace('&amp;','&',$this->controller_link));
            }
        }
        
        $this->codexmodel->copy_row($table,$id);
        
        return false;
    }
    function delete_confirm($id=""){
        if(!($id = $this->getID()))
            show_error($this->lang->line('codex_invalid_id'));
        $data['id'] = $id;
        $data['form_action'] = strtolower($this->controller_name).'/delete'.$this->extra_uri_segments;
        $data['message'] = $this->codexadmin->get_message('delete_confirm');
        $data['link'] = $this->codexadmin->get_message('delete_confirm_link');
        
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_header',$data);
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_delete_confirm',$data);
        $this->codextemplates->printHTML();
    }
    function manage(){
        if($id = $this->input->post('edit'))
            return $this->_edit();

        else if($id = $this->input->post('delete'))
            return $this->delete_confirm($id);
        
        else if($id = $this->input->post('delete_selected')){
            $selected_rows = $this->input->post("selected_rows");

            if(is_array($selected_rows)){
                foreach($selected_rows as $id){
                    $this->_delete_selected_helper($id);
                }
            }

            redirect(str_replace('&amp;','&',$this->controller_link));
        }
        else if($id = $this->input->post('copy_selected')){
            $selected_rows = $this->input->post("selected_rows");
            
            
            if(is_array($selected_rows)){
                foreach($selected_rows as $id){
                    $this->_copy_selected_helper($id);
                }
            }

            redirect(str_replace('&amp;','&',$this->controller_link));
        }
        else
            if($this->uri->segment(3) === "edit")
                return $this->_edit();
            else if($this->uri->segment(3) === "delete")
                return $this->delete_confirm($this->getID());
            else if(isset($_GET['a']) AND $_GET['a'] == 'edit'){
                return $this->_edit();
            }
            else if(isset($_GET['a']) AND $_GET['a'] == 'delete'){
                return $this->delete_confirm($this->getID());
            }
            else
                redirect(str_replace('&amp;','&',$this->controller_link));
    }
    function pluginCallback (){ 
        $name = $this->uri->segment(3);
        $plugin = $this->input->post('plugin');
        $action = $this->input->post('action');
            
        $params = array();
        $params = explode(';',$this->input->post('params'));
        foreach($params as $k=>$param){
            $temp = explode(':',$param);
            $params[$temp[0]] = $temp[1];
            unset($params[$k]);
        }

        if(!class_exists($plugin))
            $this->codexforms->loadPlugin($plugin);

        $plugin_instance = new $plugin($name,$params);
        return $plugin_instance->$action();
    }
    //
    function _view($data=array())
    {
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
        $this->codextemplates->loadView('templates/'.$this->template.'/'.$data['view'],$data);
        $this->codextemplates->loadView('templates/'.$this->template.'/codex_footer');
        $this->codextemplates->setTitle($this->config->item('codex_site_title').' - '.$data['title']);
        $this->codextemplates->printHTML();
    }
    
    function ajax_file_upload($params = array())
    {
        $response = array();
        $_config = array_merge(array(
            'upload_path'=>'upload/temp',
            'sizeLimit'=>10485760,//2 * 1024 * 1024, // Максимально допустимый размер файла, в байтах  
            'allowed_extensions'=> array(),
            'saveFileName' => true,
        ),$params); 
        $_config['upload_path'] = rtrim('./'.ltrim($_config['upload_path'],'./'),'/').'/';
        //
        if(!file_exists('upload/temp')){
            mkdir('upload/temp',0777);    
        }
//        if(!file_exists($_config['upload_path'])){
//            mkdir($_config['upload_path'],0777);    
//        } 
        // библиотека в которой будет храниться код обработчика
        $this->load->library("qqfileuploader", array($_config['allowed_extensions'], $_config['sizeLimit'],$_config['saveFileName']));
        // результат работы 'success' => 'true' при успешной загрузке
        $result = $this->qqfileuploader->handleUpload($_config['upload_path'],true);
        if(!empty($result["error"])){
            $response['errors'][]=$result["error"];    
        }else{
            $respinse['results']=array();
            
            foreach($this->qqfileuploader->uploaded_files as $code)
            {
                   $is_image = getimagesize("upload/temp/{$code}")?true:false; 
                   $response['results'][]= array('name' => $code, 'is_image' => $is_image);
                   $response['success']=true;
            }
        }
        echo json_encode($response);
    }
}
