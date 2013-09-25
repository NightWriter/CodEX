<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php');

class Modules extends codexController
{
    private $module_path = './codex/application/modules/';
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('directory');
        $this->load->helper('file');
    }
    //
    function install($alias='')
    {
        $alias = trim($this->security->xss_clean($this->uri->segment(3)));
        
        $data = array();
        
        if(file_exists($this->module_path . $alias))
        {
            $this->db->where('alias',$alias);
            $row = $this->db->get('modules',1);
            if($row->num_rows == 0)
            {
                try
                {
                    // sql
                    $files = get_filenames($this->module_path . $alias . '/sql/');
                    foreach($files as $sql)
                        $this->db->query(file_get_contents($this->module_path . $alias . '/sql/'.$sql));
                    // definitions
                    $files = get_filenames($this->module_path . $alias . '/yml/');
                    foreach($files as $yml)
                    {
                        copy($this->module_path . $alias . '/yml/'.$yml,
                                './codex/application/definitions/'.$yml);
                    }
                    // create module
                    if(!file_exists('./application/modules/'.$alias))
                        mkdir('./application/modules/'.$alias,0777);
                    
                    $dir_list = directory_map($this->module_path . $alias . '/module',TRUE);
                    foreach($dir_list as $dir)
                    {
                        if(!file_exists('./application/modules/'.$alias.'/'.$dir))
                            mkdir('./application/modules/'.$alias.'/'.$dir,0777);
                            
                        $files = get_filenames($this->module_path . $alias . '/module/'.$dir);
                        foreach($files as $file)
                        {
                            copy($this->module_path . $alias . '/module/'.$dir.'/'.$file,
                                    './application/modules/'.$alias.'/'.$dir.'/'.$file);
                        }
                    }
                    
                    $this->db->set('alias',$alias);
                    $this->db->set('date_install',time());
                    $this->db->insert('modules');
                } catch(Exception $e){
                    $data['messages'] = $e->getMessage();
                }
            }
        }
        $this->index($data);
    }
    //
    function index($data = array())
    {
        $data['view'] = 'modules';
        $data['title'] = 'Modules';
        
        $module_list = array();
        $module_install_list = array();
        
        $rows = $this->db->get('modules');
        if($rows->num_rows > 0)
        {
            foreach($rows->result() as $row)
            {
                $module_install_list[$row->alias] = $row->date_install;
            }
        }
        
        $dir_list = directory_map($this->module_path,TRUE);
        
        if($dir_list)
        {
            foreach($dir_list as $module)
            {
                if(preg_match('/[^a-zA-Z0-9_]+/',$module)) continue;
                
                $files = get_filenames($this->module_path . $module);
                if($files)
                {
                    foreach($files as $file)
                    {
                        if($file == 'info.yml')
                        {
                            $module_info = $this->spyc->YAMLLOAD($this->module_path . $module .'/'. $file);
                            
                            if(!empty($module_info))
                            {
                                $obj_module = new stdClass();
                                $obj_module->alias = $module;
                                $obj_module->title = $module_info['title'];
                                $obj_module->description = $module_info['description'];
                                $obj_module->access = '';
                                
                                if(!empty($module_info['access']))
                                    $obj_module->access = implode('<br>',$module_info['access']);
                                
                                $obj_module->status = (!empty($module_install_list[$module]));
                                
                                $module_list[] = $obj_module;
                            }
                        }
                    }
                }
            }
        }
        
        $data['module_install_list'] = $module_install_list;
        $data['rows'] = $module_list;
        $this->_view($data);
    }
}