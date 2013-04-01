<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class www extends MY_Controller {
    var $per_page = 20;
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->library('pagination');
        $this->load->model('www_model','www');
    }
    //
    function get($id=0)
    {
        $data = array();
        $data['row'] = $this->www->get($id);
        $this->content = $this->load->view('item_view',$data,true);
        $this->_view();
    }
    //
    function index($page=0)
    {
        $data = array();
        $rows = $this->www->get_list($this->per_page,$page);
        
        $data['list'] = $rows[0];
        //
        $config = array();
        $config['base_url'] = site_url('www/index');
        $config['total_rows'] = ((!empty($rows[1]))?$rows[1]:0);
        $config['per_page'] = $this->per_page;
        $config['num_links'] = 1;
        $config['uri_segment'] = 3;
        $config['next_link'] = 'следующая  &rarr;';
        $config['prev_link'] = '&larr; предыдущая';
        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        //
        $this->content = $this->load->view('www',$data,true);
        $this->_view();
    }
}