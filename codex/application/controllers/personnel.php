<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

include("codexcontroller.php");

class Personnel extends codexController 
{ 
    function Personnel () 
    { 
 		codexController::codexController();
 
        $config = array(
                    'db_table' => 'personnel',
                    'controller_name' => 'personnel',
                    'groups' => 'AlterEGO',
                    'page_header' => 'Персонал',
                    'ordering_link' => site_url('personnel/ajax_ordering'),
                    'pagination_link' => site_url('personnel/ajax_pagination')
                    );
        $this->setConfig(array_merge($config,$this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName('personnel'))));
    } 
    // 
    function ajax_pagination()
    {
        $per_page = intval($this->input->post('per_page'));
        $page     = intval($this->input->post('page'));
        
        $this->ajaxPagination($per_page,$page);
    }
}