<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    public function save($path) 
    {    
        // Читаем из потока во временный файл,
        // проверяем размер файла, если все норм
        // записываем в файл $path и возвращаем true
        
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    
    
    function getName() {
        return $_GET['qqfile'];
    }
    
    
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}


/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class Qqfileuploader 
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    public $uploaded_files = array();

    function __construct(array $params = array())
    {     
        $allowedExtensions = $params[0];
        if(!empty($params[1]))
            $sizeLimit = $params[1];
        if(!empty($params[2]))
            $saveFileName = $params[2];
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        $this->saveFileName = isset($saveFileName)?$saveFileName:false;
        // проверка post_max_size и upload_max_filesize со значением $sizeLimit
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            echo "FALSE!";
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE)
    {                                
        if (!is_writable($uploadDirectory)){
            return array('error' => "Ошибка сервера. Каталог для загрузки не доступен для записи.");
        }
        
        if (!$this->file){
            return array('error' => 'Ошибка, файл не был загружен. ');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'Файл пуст');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'Файл слишком велик');
        }
        $pathinfo = pathinfo($this->file->getName());
        if($this->saveFileName)
            $filename = $pathinfo['filename'];  // сохранить имя файла, 
        else
            $filename = md5(uniqid());            // присвоить уникальное имя
        if(!empty($pathinfo['extension'])){
            $ext = $pathinfo['extension'];    
        }else{
            $ext='';
        }
        
        // проверка на тип файлов по расширению
        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'Файл имеет неверное расширение, оно должно быть: '. $these . '.');
        } 
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            
            /*list($width, $height, $type, $attr) = getimagesize($uploadDirectory . $filename . '.' . $ext);
            
            if($width > $this->allowedWidth || $height > $this->allowedHeight)
            {
                if(file_exists($uploadDirectory . $filename . '.' . $ext) && is_file($uploadDirectory . $filename . '.' . $ext))
                    unlink($uploadDirectory . $filename . '.' . $ext);
                    
                return array('error' => 'Файл имеет неверное разрешение, оно должно быть: '. $this->allowedWidth .'x'. $this->allowedHeight . '.');
            }*/
        
            $this->uploaded_files[] = $filename . '.' . $ext;
            return array('success' => true, 'file' => $filename . '.' . $ext);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}