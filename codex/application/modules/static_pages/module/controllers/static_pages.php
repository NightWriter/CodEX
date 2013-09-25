<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
class Static_pages extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('static_pages_model', 'static_pages');
        
    }
    /**
    * открываем статические страницы
    * 
    * @param string $ind
    */
    function static_page($ind='')
    {
        // очищаем недопустимые символы
        $ind = str_replace('.html','',$this->security->xss_clean($ind));
        
        $row = $this->static_pages->get_static_page($ind);
        
        if(empty($row))
        {
            $this->show_404();
            return FALSE;
        }
        $this->header_title = $row->title; // выставляем заголовок странице
        if(!empty($row->meta_title))
            $this->header_title = $row->meta_title; // выставляем заголовок странице
        
        $data = array();
        
        $data['meta_desc'] = $row->meta_description; //
        $data['meta_keywords'] = $row->meta_keywords; //
        
        $data['user'] = $this->user;
        
        //$row->url = site_url($row->ind).'.html';
        $data['article'] = $row;
        
        $this->content = $this->load->view('static_page',$data,TRUE);
        
        
        
        $this->_view($data);
    }
}