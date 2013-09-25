<?php
class MY_Controller extends CI_Controller
{
    protected $content = '';
    // для очистки кеша, добавляется к js и css файлам
    private $js_version = 110;
    
    protected $per_page = 18;
    //
    public $user = null;
    public $_user_ip_address = '';
    public $site_title   = 'Site name';
    public $header_title = '';
    public $_response    = null;
    // путь к папке для загрузки временых файлов
    public $path_temp = './uploads/temp/';
    
    // допустимые адреса, откуда разрешено получать видео контент
    protected $valid_video_url = array(
                                        'youtube.com',
                                        'youtu.be',
                                        'vimeo.com',
                                        'vk.com'
                                       );
    // допустимый список тегов
    protected $valid_tags_list = '<p><span><em><strong><img><iframe><a><br>';
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('response');
        $this->load->helper('preview');
        $this->load->helper('date_time');
        $this->load->helper('str');
        
        $this->_init();
    }
    /**
    * инициализируем данные для пользователя
    * а также определяем является ли платформа мобильной или нет
    * 
    */
    function _init()
    {
        $this->_user_ip_address = $this->input->ip_address();
        $response = new Response();
        
        $this->_response = $response->getInstance();
    }
    /**
    * страница 404
    * 
    */
    protected function show_404()
    {
        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
        
        //if($this->input->is_ajax_request())
        {
            if (substr(php_sapi_name(), 0, 3) == 'cgi')
            {
                header("Status: 404 Not Found", TRUE);
            }
            elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
            {
                header($server_protocol." 404 Not Found", TRUE, 404);
            }
            else
            {
                header("HTTP/1.1 404 Not Found", TRUE, 404);
            }    
        }
        
        $this->_view(array('hide_right_column' => TRUE,'main_content' => $this->load->view('404.php',array(),TRUE)));
    }
    //
    function _view($data=array())
    {
        $data['header_title']    = trim($this->site_title.' - '.$this->header_title,' - ');
        $data['content'] = $this->content;
        $this->load->view('main',$data);
    }
}