<?php
/**
* Description:
* Для облегчения работы с ответам формата JSON
* 
* добавление параметров
* ->addError('описание ошибки');
* добавление параметров
* ->add('содержание','имя переменной'); // добавляем в массив data
* ->add('содержание','имя переменной',TRUE); // создаём новое поле
* 
* печатаем результат
* echo $response;
*
* проверяем есть ли у нас ошибки
* ->valid
* 
* можно указать куда перенаправить пользователя если он откроет страницу в браузере
* используется для того, чтобы он не увидел код json 
* ->set_redirect($url)
* 
* @author Vasilenko Denis (AlterEGO)
*/
class Response
{
    private $response;
    private $send_header = true;
    static  $instance = null;
    private $CI = null;
    private $redirect_if_not_ajax = TRUE;
    private $url_redirect = '/';
    
    function __construct($redirect_if_not_ajax=TRUE)
    {
        $this->redirect_if_not_ajax = $redirect_if_not_ajax;
        
        $this->response = new stdClass();
        $this->response->data = array();
        $this->response->errors = array();
        
        $this->CI = & get_instance();
    }
    /**
    * устанавливаем редирект, используется если пользователь откроет страницу
    * в браузере, чтобы он не увидел json код, его перенаправит на указаный адрес или 
    * по умолчанию на главную страницу
    * 
    * @param string $url
    */
    function set_redirect($url='')
    {
        $this->url_redirect = $url;
    }
    /**
    * получаем экземпляр объекта
    * необходим при испоьзовании HMVC
    * 
    */
    function getInstance($redirect_if_not_ajax=TRUE)
    {
        if(is_null(self::$instance))
            self::$instance = new Response($redirect_if_not_ajax);
            
        return self::$instance;
    }
    /**
    * отключаем посылку заголовков
    * необходимо для qqUploadedFile в IE
    * 
    */
    function disabledHeader()
    {
        $this->send_header = false;
    }
    /**
    * получаем добавленые данные
    * 
    * @param string $key
    * @param boolean $main
    * @return mixed $key
    */
    public function get($key='',$main=FALSE)
    {
        if($main)
        {
            if(empty($this->response->{$key})) return '';
            return $this->response->$key;
        }
        if(empty($this->response->data[$key])) return '';
        return $this->response->data[$key];
    }
    /**
    * очищаем ошибки
    * 
    */
    function clearErrors()
    {
        $this->response->errors = array();
    }
    /**
    * получаем список ошибок
    * 
    * @param boolean $return_string TRUE - если нужно вернуть строку
    * @return mixed
    */
    public function getErrors($return_string=FALSE)
    {
        if(empty($return_string))
            return $this->response->errors;
            
        $message = '';
        foreach($this->response->errors as $err)
            $message .= $err."\n";
        
        return $message;
    }
    /*
    * склеивает строку ошибок
    */
    private function _makeErrors()
    {
        $message = '';
        foreach($this->response->errors as $err)
            $message .= $err."\n";
        $this->response->errors = $message;
    }
    /*
    * склеивает строку добавленных данных
    */
    private function _makeMessage()
    {
        foreach($this->response->data as $k=>$err)
        {
            $text = '';
            foreach($this->response->data[$k] as $err)
                $text .= $err."\n";
            $this->response->data[$k] = $text;
        }
    }
    /*
    * проверяется есть ли ошибки, и если нет то success = true
    * @return Boolean
    */
    public function valid()
    {
        $this->response->success = FALSE;
        if(empty($this->response->errors))
            $this->response->success = TRUE;
        return $this->response->success;
    }
    /*
    * @param $val Mixed значение
    * @param $key string ключ для массива
    * @param $main Boolean определяет является ли основным полем объекта
    */
    public function addError($val,$key=null)
    {
        if($key)
            $this->response->errors[$key] = $val;
        else $this->response->errors[] = $val;
    }
    /*
    * @param $val Mixed значение
    * @param $key string ключ для массива
    * @param $main Boolean определяет является ли основным полем объекта
    */
    public function add($val,$key='messages',$main=FALSE)
    {
        if($main)
        {
            $this->response->{$key} = $val;
            return;
        }
        if(empty($key)) $this->response->data[] = $val;
        else $this->response->data[$key] = $val;
    }
    /*
    * echo объекта
    */
    public function __toString()
    {
        $this->valid();
        $this->_makeErrors();
        
        if($this->redirect_if_not_ajax && !$this->CI->input->is_ajax_request())
            redirect($this->url_redirect);
            
        if($this->send_header)
            header('Content-type: text/json');
        return json_encode($this->response);
    }
    /*
    * для формирования из массивов строки
    * например массив с сообщениями
    * __toString не работает в некоторых случаях
    */
    public function toString($key='')
    {                            
        $this->valid();
        $this->_makeErrors();
        
        if($this->redirect_if_not_ajax && !$this->CI->input->is_ajax_request())
            redirect($this->url_redirect);
            
        if($this->send_header)
            header('Content-type: text/json');
        if(!empty($key))
            exit(json_encode($this->response->{$key}));
        
        exit(json_encode($this->response));
    }
    /**
    * возвращаем массив
    * 
    */
    public function toArray() 
    {
        if($this->send_header)
            header('Content-type: text/json;');
        return json_encode($this->response);
    }
    // если вдруг нужно будет вызвать как ф.
    public function __invoke($type='')
    {
        switch(strtolower($type))
        {
            case 'array':
                return $this->toArray();
                break;
            default:
                return $this->__toString();
        }
    }
}