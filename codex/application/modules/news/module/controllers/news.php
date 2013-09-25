<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
class News extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('news_model', 'news');
        
    }
    /**
    * посмотреть все новости в разделе
    * 
    */
    function index($category='',$page=0)
    {
        // очищаем недопустимые символы
        $category = str_replace('.html','',$this->security->xss_clean($category));
        
        $category = $this->news->get_category(array('alias' => $category));
        
        if(empty($category))
        {
            $this->show_404();
            return FALSE;
        }
        
        $data = array();
        $data['category'] = $category;
        
        $news = $this->news->get_news(array(
                                            'limit'=> $this->per_page
                                            ,'offset'=> $page
                                            ,'all_cnt'=> TRUE
                                            ,'category_id' => $category->id));
        $data['news'] = $news[0];
        // begin pagination
        /*$config = array();
        $config['base_url']     = site_url("news/category/{$category->alias}.html/{$def_sort}");
        $config['total_rows']   = $news[1];
        $config['uri_segment']  = 5;
        
        $data['pagination'] = $this->_get_pagination($config);*/
        // end pagination
        
        $this->content = $this->load->view('news/news_list',$data,TRUE);
        
        $this->_view($data);
    }
    /**
    * открываем статью, новость
    * 
    * @param string $cat_alias  Alias категории
    * @param string $alias Alias статьи
    */
    function show($cat_alias='',$alias='')
    {
        // очищаем недопустимые символы
        $cat_alias = str_replace('.html','',$this->security->xss_clean($cat_alias));
        $alias = str_replace('.html','',$this->security->xss_clean($alias));
        
        $category = $this->news->get_category(array('alias' => $cat_alias));
        
        $news = $this->news->get_one_news(array('alias' => $alias));
        
        if(empty($category) || empty($news))
        {
            $this->show_404();
            return FALSE;
        }
        $this->header_title = $news->title; // выставляем заголовок странице
        if(!empty($news->meta_title))
            $this->header_title = $news->meta_title; // выставляем заголовок странице
        $data['meta_desc'] = $news->meta_description; //
        $data['meta_keywords'] = $news->meta_keywords; //
        
        $data = array();
        $data['user'] = $this->user;
        $data['category'] = $category;
        $data['news'] = $news;
        
        $this->content = $this->load->view('news/view',$data,TRUE);
        
        $news->url = site_url("news/{$cat_alias}/{$news->alias}").'.html';
        $data['news'] = $news;
        
        $this->_view($data);
    }
}