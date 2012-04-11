<?php
class MY_Controller extends CI_Controller
{
    var $content = '';
    function __construct()
    {
        parent::__construct();
    }
    //
    function _view($data=array())
    {
        $data['content'] = $this->content;
        $this->load->view('main',$data);
    }
}