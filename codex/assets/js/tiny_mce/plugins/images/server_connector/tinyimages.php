<?php
class tinyimages {
    

    var $folder = '/upload/blog';
    
    function __construct() {
        define('DIR', $_SERVER['DOCUMENT_ROOT'].'/');
        define('BASE_PATH', "http://".$_SERVER['HTTP_HOST']);
        // ATTENTION!
        // You need to check the session here, because methods of this class can be danger to security!
        //if(!isset($_SESSION['user']['type'])) return false;
        $this->folder = $this->folder;
        $this->restrict = $this->folder;
    }
    
    private function leftPanel() {
        $ret = array();
        if ($handle = opendir ( DIR.$this->folder )) {
            while (false !== ($file = readdir ( $handle ))) {
                if (is_dir(DIR.$this->folder.'/'.$file) && $file != '.') {
                    $ret[] = array(
                        'path' => $file,
                        'name'    => $file
                    );
                }
            }
            closedir ($handle);
        }
        
        if(count($ret) > 0) {
            $return = '';
            foreach ($ret as $val) {
                if($val['path'] == '.thumbs') continue;
                if($val['path'] == '..') {
                    $act = '';
                    if($this->folder == $this->restrict) continue;
                    $path = substr($this->folder,0,strrpos($this->folder,'/'));
                    if($path == '') continue;
                }
                else { $path = $this->folder.'/'.$val['path']; $act = 'onclick="activateDir(this, \''.$path.'\'); return false;"'; }
                $return .= '<div class="folder"><a href="#" '.$act.' ondblclick="changeFolder(\''.$path.'\'); return false;">'.$val['name'].'</a></div>';
            }
            
            $name = '';
            
            return $return;
        }
    }
    
    private function addressBar() {
        $way = explode('/',str_replace($this->restrict, '', $this->folder));
        $way = array_filter($way);
        $ret = $link = '';
        foreach ($way as $val) {
            $link = $link.'/'.$val;
            $ret .= '<a href="#" onclick="changeFolder(\''.$this->restrict.$link.'\'); return false;">'.$val.'</a>';
        }
        return '<a href="#" onclick="changeFolder(\''.$this->restrict.'\'); return false;" class="first"><img src="images/folder.gif" width="16" height="16" /></a>'.$ret;
    }
    
    private function mainField() {
        $ret = array();
        if ($handle = opendir ( DIR.$this->folder )) {
            while (false !== ($file = readdir ( $handle ))) {
                if (is_file(DIR.$this->folder.'/'.$file)) {
                    
                    list($width, $height, $type, $attr) = getimagesize(DIR.$this->folder.'/'.$file);
                    
                    $size = number_format((filesize(DIR.$this->folder.'/'.$file)/1024),2,',',' ').' KB';
                    
                    $ret[] = array(
                        'src'    => BASE_PATH.'/'.$this->folder.'/.thumbs/100x100_'.$file,
                        'attr'    => $attr,
                        'path'    => BASE_PATH.'/'.$this->folder.'/'.$file,
                        'path_select'    => $this->folder.'/'.$file,
                        'name'    => $file,
                        'width'    => $width,
                        'height'=> $height,
                        'size'    => $size
                    );
                }
            }
            closedir ($handle);
        }
        
        
        if(count($ret) > 0) {
            $return = '';
            foreach ($ret as $val) {
                $width_for_insert = $val['width']>500?500:$val['width'];
                $return .= '<div class="item" ondblclick="addImage(this,\''.$val['path'].'\','.$width_for_insert.');" onclick="activateItem(this,\''.$val['path_select'].'\');"><img src="'.$val['src'].'" width="100" height="100" alt=" " /><div class="labels">'.$val['width'].'x'.$val['height'].'</div><div class="labels">'.$val['size'].'</div></div>';
            }
            
            $name = '';
            
            return $return.'<div style="clear:both;"></div>';
        }
    }
    
    function ajaxChangeDir($input) {
        
        $our_folder = $this->folder;
        
        if($input['uri'] != '') {
            $this->folder = $input['uri'];
            
            $realpath1 = realpath(DIR.$our_folder);
            $realpath2 = realpath(DIR.$input['uri']);
            
            $strlen1 = strlen($realpath1);
            $strlen2 = strlen($realpath2);
            if($strlen1 > $strlen2) { page404(); exit(); }
            for($i=0;$i<$strlen1;$i++) {
                if($realpath1[$i] != $realpath2[$i]) { page404(); exit(); }
            }
        }elseif(isset($_SESSION['tiny_folder'])) {
            if($_SESSION['tiny_folder'] != '') {
                $this->folder = $_SESSION['tiny_folder'];
            }
        }
        
        
        $_SESSION['tiny_folder'] = $this->folder;
        return array(
            'leftpanel'        => $this->leftPanel(),
            'addressbar'    => $this->addressBar(),
            'mainfield'        => $this->mainField(),
            'uri'            => $this->folder
        );
    }
    
    
    
    function ajaxDelDir($input) {
        $our_folder = $this->folder;
        
        if($input['dir'] != '') {
            $this->folder = $input['dir'];
            
            $realpath1 = realpath(DIR.$our_folder);
            $realpath2 = realpath(DIR.$input['dir']);
            
            $strlen1 = strlen($realpath1);
            $strlen2 = strlen($realpath2);
            if($strlen1 > $strlen2) { page404(); exit(); }
            for($i=0;$i<$strlen1;$i++) {
                if($realpath1[$i] != $realpath2[$i]) { page404(); exit(); }
            }
        } else { page404(); exit(); }
        
        if ($handle = opendir ( DIR.$input['dir'] )) {
            while (false !== ($file = readdir ( $handle ))) {
                if (is_file(DIR.$input['dir'].'/'.$file)) {
                    
                    $thumb_info = pathinfo(DIR.$input['dir'].'/'.$file);
                    $thumb = $thumb_info['dirname'].'/.thumbs/100x100_'.$thumb_info['basename'];
                    unlink($thumb);
                    unlink(DIR.$input['dir'].'/'.$file);
                }
            }
            closedir ($handle);
        }
        
        if ($handle = opendir ( DIR.$input['dir'].'/.thumbs' )) {
            while (false !== ($file = readdir ( $handle ))) {
                if (is_file(DIR.$input['dir'].'/.thumbs'.'/'.$file)) {
                    
                    $thumb_info = pathinfo(DIR.$input['dir'].'/.thumbs'.'/'.$file);
                    $thumb = $thumb_info['dirname'].'/.thumbs/100x100_'.$thumb_info['basename'];
                    unlink($thumb);
                    unlink(DIR.$input['dir'].'/.thumbs'.'/'.$file);
                }
            }
            closedir ($handle);
        }
        rmdir(DIR.$input['dir'].'/.thumbs');
        
        if(!rmdir(DIR.$input['dir'])) {
            if($input['lng']=='ru') {
                return array('error'=>'Ошибка удаления папки, возможно она содержит не удаленные каталоги!');
            } else {
                return array('error'=>'Error delete a folder, perhaps it has not deleted directories!');
            }
        } else return array();
        
    }
    
    
    function ajaxDelFile($input) {
        
        $error = array();
        
        $input['src'] = array_filter($input['src']);
        
        $input['src'] = array_unique($input['src']);
        
        foreach ($input['src'] as $key=>$val) {
            if(!is_numeric($key)) continue;
            $our_folder = $this->folder;
            $pi = pathinfo($val);
            $input['uri'] = $pi['dirname'];
            
            if($input['uri'] != '') {
                $this->folder = $input['uri'];
                
                $realpath1 = realpath(DIR.$our_folder);
                $realpath2 = realpath(DIR.$input['uri']);
                
                $strlen1 = strlen($realpath1);
                $strlen2 = strlen($realpath2);
                if($strlen1 > $strlen2) { page404(); exit(); }
                for($i=0;$i<$strlen1;$i++) {
                    if($realpath1[$i] != $realpath2[$i]) { page404(); exit(); }
                }
            } else { page404(); exit(); }
            
            if(is_file(DIR.$input['src'][$key])) {
                $thumb_info = pathinfo(DIR.$input['src'][$key]);
                $thumb = $thumb_info['dirname'].'/.thumbs/100x100_'.$thumb_info['basename'];
                unlink($thumb);
                unlink(DIR.$input['src'][$key]);
            } else {
                if($input['lng']=='ru') { 
                    $error[] = 'Файл '.$val.' не найден!';
                } else {
                    $error[] = 'File '.$val.' not found!';
                }
            }
        
        }
        
        if(count($error) > 0) return array('error'=>implode(', ',$error));
        else return array();
    }
    
    
    function UploadFiles() {
        $our_folder = $this->folder;
        
        if($_GET['uri'] != '') {
            $this->folder = $_GET['uri'];
            
            $realpath1 = realpath(DIR.$our_folder);
            $realpath2 = realpath(DIR.$_GET['uri']);
            
            $strlen1 = strlen($realpath1);
            $strlen2 = strlen($realpath2);
            
            if($strlen1 > $strlen2) { page404(); exit(); }
            for($i=0;$i<$strlen1;$i++) {
                if($realpath1[$i] != $realpath2[$i]) { page404(); exit(); }
            }
        }
        
        $result = array();

        if (isset($_FILES['photoupload'])) {
            $file = $_FILES['photoupload']['tmp_name'];
            $error = false;
            $size = false;

            if (!is_uploaded_file($file) || ($_FILES['photoupload']['size'] > 2 * 1024 * 1024) ) {
                if($_GET['lng']=='ru') {
                    $error = 'Пожалуйста, загружайте файлы не более 2Мб!';
                } else {
                    $error = 'Please do not upload files over 2Mb!';
                }
            } else
            if (!$error && !($size = @getimagesize($file) ) ) {
                if($_GET['lng']=='ru') {
                    $error = 'Ошибка, не верный тип файла';
                } else {
                    $error = 'Error, unsupported type of file';
                }
            } else
            if (!$error && !in_array($size[2], array(1, 2, 3, 7, 8) ) ) {
                if($_GET['lng']=='ru') {
                    $error = 'Ошибка типа файла, рекомендуется загружать файлы JPEG';
                } else {
                    $error = 'Error type of file, recommend upload JPEG files';
                }
            } else
            if (!$error && ($size[0] < 5) || ($size[1] < 5)) {
                if($_GET['lng']=='ru') {
                    $error = 'Пожалуйста, загружайте картинки размером более 5px.';
                } else {
                    $error = 'Please upload pictures larger than 5px.';
                }
            }
            if ($error) {
                $result['result'] = 'failed';
                $result['error'] = $error;
            }
            else {
                $ext = substr($_FILES['photoupload']['name'],strrpos($_FILES['photoupload']['name'],'.')+1);
                $name = md5_file($_FILES['photoupload']['tmp_name']);
                $source = DIR.$this->folder.'/'.$name.'.'.$ext;
                
                if(!copy($_FILES['photoupload']['tmp_name'], $source)) {
                    $result['result'] = 'error';
                    if($_GET['lng']=='ru') {
                        $result['error'] = 'Ошибка при копировании файла!';
                    } else {
                        $result['error'] = 'Failed to copy a file!';
                    }
                } else {
                    
                    if(!file_exists(DIR.$this->folder.'/.thumbs')) mkdir(DIR.$this->folder.'/.thumbs');
                    $thumb = DIR.$this->folder.'/.thumbs/100x100_'.$name.'.'.$ext;
                    
                    //$image = new files('tinyimages');
                    $this->Resize($source,$thumb,100,100,'back-ffffff');
                    
                    $result['result'] = 'success';
                    if($_GET['lng']=='ru') {
                        $result['size'] = "Загружено изображение ({$size['mime']}) размером {$size[0]}px/{$size[1]}px.";
                    } else {
                        $result['size'] = "Uploaded image ({$size['mime']}) size {$size[0]}px/{$size[1]}px.";
                    }
                }
            }
        }
        else {
            $result['result'] = 'error';
            if($_GET['lng']=='ru') {
                $result['error'] = 'Нет файла или внутренняя ошибка!';
            } else {
                $result['error'] = 'No file or an internal error!';
            }
        }
        /*if (!headers_sent() ) {
            header('Content-type: application/json');
        }*/
        
        /*foreach ($result as $key=>$val) {
            $return[$key] = iconv("windows-1251", "utf-8", $val);
        }*/
        
        //header("Content-Type: text/plain; charset=UTF-8");
        //echo json_encode($return);
        $view = file_get_contents('../fancy_response.htm');
        $search = array('%errors%', '%message%', '%urlpart%');
        $urlpart = BASE_PATH."/codex/assets/js/tiny_mce/plugins/images";
        if ($result['result'] == 'success')
            $replace = array('Файл загружен успешно', $result['size'], $urlpart);
        else
            $replace = array('Произошла ошибка', $result['error'], $urlpart);
        $view = str_replace($search, $replace, $view);
        echo $view;
        exit();
    }
    
    function ajaxMakeFolder($input) {
        
        if($input['lng']=='ru') {
            if(trim($input['name']) == '') return array('error' => 'Не задано имя');
        } else {
            if(trim($input['name']) == '') return array('error' => 'Not a name');
        }
        
        if($input['uri'] != '') {
            $this->folder = $input['uri'];
            
            $realpath1 = realpath(DIR.$our_folder);
            $realpath2 = realpath(DIR.$input['uri']);
            
            $strlen1 = strlen($realpath1);
            $strlen2 = strlen($realpath2);
            
            if($strlen1 > $strlen2) { page404(); exit(); }
            for($i=0;$i<$strlen1;$i++) {
                if($realpath1[$i] != $realpath2[$i]) { page404(); exit(); }
            }
        }
        
        if(mkdir(DIR.$this->folder.'/'.$input['name'])) { return array(); }
        else {
            if($input['lng']=='ru') {
                array('error' => 'Не удалось создать папку');
            } else {
                array('error' => 'Unable to create a folder');
            }
        }
    }
    
    
    function Resize($filename, $dest, $width, $height, $pictype = "") {
        $format = strtolower(substr(strrchr($filename,"."),1));
        switch($format)
        {
            case 'gif' :
                $type ="gif";
                $img = ImageCreateFromGif($filename);
                break;
            case 'png' :
                $type ="png";
                $img = ImageCreateFromPng($filename);
                break;
            case 'jpg' :
                $type ="jpg";
                $img = ImageCreateFromJpeg($filename);
                break;
            case 'jpeg' :
                $type ="jpg";
                $img = ImageCreateFromJpeg($filename);
                break;
            default :
                return false;
                break;
        }

        list($org_width, $org_height) = getimagesize($filename);
        $xoffset = 0;
        $yoffset = 0;
        if ($pictype == "thumb") // To minimize destortion
        {
            if ($org_width / $width > $org_height/ $height)
            {
                $xtmp = $org_width;
                $xratio = 1-((($org_width/$org_height)-($width/$height))/2);
                $org_width = $org_width * $xratio;
                $xoffset = ($xtmp - $org_width)/2;
            }
            elseif ($org_height/ $height > $org_width / $width)
            {
                $ytmp = $org_height;
                $yratio = 1-((($width/$height)-($org_width/$org_height))/2);
                $org_height = $org_height * $yratio;
                $yoffset = ($ytmp - $org_height)/2;
            }
            //Added this else part -------------
        } elseif(substr($pictype,0,4) == "back") {
            $xtmp = $org_width/$width;
            $new_width = $width;
            $new_height = $org_height/$xtmp;
            if ($new_height > $org_height && $new_width > $org_width) {
                $new_height = $org_height;
                $new_width = $org_width;
            } elseif ($new_height > $height){
                $ytmp = $org_height/$height;
                $new_height = $height;
                $new_width = $org_width/$ytmp;
            }
            $width_d = round($new_width)<$width?$width:round($new_width);
            $height_d = round($new_height)<$height?$height:round($new_height);
            
            $width = round($new_width);
            $height = round($new_height);
            
            $width_diff = $width_d - $width;
            $height_diff = $height_d - $height;
        } else {
            $xtmp = $org_width/$width;
            $new_width = $width;
            $new_height = $org_height/$xtmp;
            if ($new_height > $height){
                $ytmp = $org_height/$height;
                $new_height = $height;
                $new_width = $org_width/$ytmp;
            }
            $width = round($new_width);
            $height = round($new_height);
        }

        if(substr($pictype,0,4) == "back") {
            $img_n=imagecreatetruecolor ($width+$width_diff, $height+$height_diff);
            $r = hexdec(substr($pictype,5,2));
            $g = hexdec(substr($pictype,7,2));
            $b = hexdec(substr($pictype,9,2));
            $back = imagecolorallocate($img_n, $r, $g, $b);
            imagefill($img_n, 0, 0, $back);
            imagecopyresampled($img_n, $img, round($width_diff/2), round($height_diff/2), $xoffset, $yoffset, $width, $height, $org_width, $org_height);
        } else {
            $img_n=imagecreatetruecolor ($width, $height);
            imagecopyresampled($img_n, $img, 0, 0, $xoffset, $yoffset, $width, $height, $org_width, $org_height);
        }

        if($type=="gif")
        {
            imagegif($img_n, $dest);
        }
        elseif($type=="jpg")
        {
            imagejpeg($img_n, $dest, 100);
        }
        elseif($type=="png")
        {
            imagepng($img_n, $dest);
        }
        elseif($type=="bmp")
        {
            imagewbmp($img_n, $dest);
        }
        return true;
    }
}

?>