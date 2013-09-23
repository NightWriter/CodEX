<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include("codexcontroller.php");

class Access_level extends codexController 
{ 
    function Access_level () 
    { 
        codexController::codexController();

        if(intval($this->codexsession->userdata('user_level')) == 3)
            $this->create_menu_list();
        
        $config = array(
                    'db_table' => 'access_level', 
                    'page_header' => 'Access levels', 
                    'form_setup' => $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName('access_level')),
                    'controller_name' => 'Access_level',
                    'primary_key' => 'id', 
                    );
        $this->setConfig($config);
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
    function create_menu_list()
    {
        $auto_generated_links = array();
        $user_level   = $this->codexsession->userdata('user_level');
        if($this->config->item('codex_auto_generate_crud'))
        {
            // по БД
            if($this->config->item('codex_auto_generate_menu') == 'db'){
                $tables = $this->db->list_tables();
                foreach($tables as $table){
                    if(!in_array($table,$this->config->item('codex_exclude_tables')))
                    {
                        $auto_generated_links[$table]['link'] = '?c=crud&amp;m=index&amp;t='.$table;
                        $auto_generated_links[$table]['access_link'] = 't='.$table;
                    }
                }
            }
            // по yml файлам
            if($this->config->item('codex_auto_generate_menu') == 'files'){
                
                $path = 'codex/application/'.$this->config->item('codex_definitions_dir');
                $tables = array();
                
                $dh = opendir($path);
                while(gettype( $file = readdir($dh)) != @boolean){
                    
                    if($file == '..' || $file == '.')
                        continue;
                    
                    if( is_file($path . $file) && $file != '')
                        if(strstr($file,'.yml'))
                            $tables[] = str_replace('.yml','',$file);
                }   
                @closedir($dh);
                
                foreach($tables as $table){
                    if(!in_array($table,$this->config->item('codex_exclude_tables'))){
                        
                        $table_config = $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName($table));
                        
                        $access_level = 0;
                        
                        if(isset($table_config['access_level']))
                            $access_level = intval($table_config['access_level']);
                        
                        if(!$this->codexmodel->check_access('?c=crud&amp;m=index&amp;t='.$table))
                            if($user_level != 3 && ($user_level < $access_level || $access_level == 0))
                                continue;
                        
                        $table_name = $table;
                        $groups = 'main';
                        
                        if(isset($table_config['page_header']))
                            $table_name = $table_config['page_header'];
                        if(isset($table_config['groups']))
                            $groups = $table_config['groups'];
                        
                        if(!empty($table_config['db_table']))
                            $table = $table_config['db_table'];
                        $auto_generated_links[$table_name] = array('key'=>$table_name, 'groups'=>$groups,'link'=>'?c=crud&amp;m=index&amp;t='.$table,'access_link'=>'t='.$table);
                    }
                }
            }
            $_access_level = $this->config->item('codex_navigation_access');
            foreach($this->config->item('codex_navigation') as $name=>$key){
                
                
                if(isset($_access_level[$name]))
                    $access_level = $_access_level[$name];
                else
                    $access_level = 0;

                if(!$this->codexmodel->check_access($key['link']))
                    if($user_level != 3 && ($user_level < $access_level || $access_level == 0))
                        continue;
                
                $auto_generated_links[$name] = array('key'=>$name, 'groups'=>$key['groups'], 'link'=>$key['link'], 'access_link'=>$key['link']);
            }
            ksort($auto_generated_links);
            
            $_auto_generated_links = array();
            foreach($auto_generated_links as $k=>$v){
                foreach($auto_generated_links as $_k=>$_v){
                    if($v['groups'] == $_v['groups']){
                            $_auto_generated_links[$v['groups']][$_v['key']] = $_v;
                    }
                }
            }
            $auto_generated_links = $_auto_generated_links;
        }
        
        $menu = array();
        foreach($auto_generated_links as $key=>$val)
        {
            if(is_array($val))
            {
                foreach($val as $_key=>$_val)
                {
                     $menu[$_val['key']]['link'] = $_val['link'];
                     $menu[$_val['key']]['access_link'] = $_val['access_link'];
                }
            }else{
                $menu[$key]['link'] = $val['link'];
                $menu[$key]['access_link'] = $val['access_link'];
            }
        } 
        foreach($menu as $title=>$link)
        {
            $this->db->where('title',$title);
            $row = $this->db->get('access',1);
            if($row->num_rows == 1)
            {
                $this->db->set('link',$link['link']);
                $this->db->set('access_link',$link['access_link']);
                $this->db->where('id',$row->row()->id);
                $this->db->update('access');
            }else{
                $this->db->set('link',$link['link']);
                $this->db->set('access_link',$link['access_link']);
                $this->db->set('title',$title);
                $this->db->insert('access');
            }
        }
    }
}