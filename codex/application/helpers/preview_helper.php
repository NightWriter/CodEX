<?php

/**
* проверяем является ли вайл видео-файлом
* 
* @param string $video_file путь к файлу
* @return boolean
*/
function has_not_image($video_file='')
{
    if(empty($video_file) ||
        !file_exists($video_file) ||
        !is_file($video_file) ||
        !class_exists('ffmpeg_movie'))
        {
            return false;
        }
        
    $movie = new ffmpeg_movie($video_file);
    
    if(intval($movie->getDuration()))
        return true;
    return false;
}
/**
* получаем превью с vimeo
* 
* @param mixed $url
* @param boolean $return_code Если нужно вернуть код видео
* @return string Путь к картинке
*/
function get_vimeo_preview($url='',$return_code=FALSE)
{
    $videoId = NULL;
    // iframe
    if (preg_match("/http:\/\/[a-zA-Z0-9\-\_]+\.vimeo\.com\/video\/([0-9]*)([^<>\"'\s]*)/", $url, $matches))
        $videoId = $matches[1];
    // short link
    if (is_null($videoId) && preg_match("/http:\/\/([a-zA-Z0-9\-\_]+\.|)vimeo\.com\/([0-9]*)([^<>\"'\s]*)/", $url, $matches))
        $videoId = $matches[2];
    ////
    if(empty($videoId)) return false;
    
    $xml = simplexml_load_file('http://vimeo.com/api/v2/video/'.$videoId.'.xml');
    $preview = '';
    
    if ($xml)
        $preview = (string) $xml->video->thumbnail_large;
    
    if($return_code)
        return $videoId;
    
    return $preview;
}
/**
* получаем превью с ютуба
* 
* @param mixed $url
* @param boolean $return_code Если нужно вернуть код видео
* @return string Путь к картинке
*/
function get_youtube_preview($url='',$return_code=FALSE)
{
    $videoId = NULL;
    if (preg_match("/http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/(\?v\=|v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<>\"'\s]*)/", $url, $matches))
        $videoId = $matches[3];

    if (is_null($videoId) && preg_match("/http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<>\"'\s]*)/", $url, $matches))
        $videoId = $matches[3];
        
    if (is_null($videoId) && preg_match("/http:\/\/([a-zA-Z0-9\-\_]+\.|)youtu\.be\/([a-zA-Z0-9\-\_]{1,})/", $url, $matches))
        $videoId = $matches[2];
    
    if (is_null($videoId))
    {                                  
        $src = strstr($url,'embed');
        $src = str_replace('embed/','',$src);
        $videoId = '';
        //exit($videoId); 
        for($i=0;$i<strlen($src);$i++)
        {
            if(!preg_match("([a-zA-Z0-9_-]+)",$src[$i],$data))
                break;
            
            $videoId .= $src[$i];
        }
    }    
    if(empty($videoId)) return false;
    
    if($return_code)
        return $videoId;
    
    return 'http://img.youtube.com/vi/' . $videoId . '/0.jpg';
}
/**
* получаем продолжительность видео
*            
* @param string $video_file Путь к файлу
* @return int Количество секунд
*/
function getVideoDuration($video_file='')
{
    
    if(empty($video_file) ||
        !file_exists($video_file) ||
        !is_file($video_file) ||
        !class_exists('ffmpeg_movie'))
        {
            return false;
        }
        
    $movie = new ffmpeg_movie($video_file);
    
    $time = ceil($movie->getDuration());
    return sprintf("%02d:%02d:%02d", (int)($time / 3600), (int)(($time % 3600) / 60), $time % 60);
}
/**
* сохраняем кадр из видеофайла
* 
* @param string $video_file Путь к файлу
* @param string $save_to_file
*/
function createVideoPreview($video_file='',$save_to_file='',$frame=50)
{
    if(empty($video_file) ||
        !file_exists($video_file) ||
        !is_file($video_file) ||
        !is_file($video_file) ||
        !class_exists('ffmpeg_movie'))
        {
            return false;
        }
        
    $movie = new ffmpeg_movie($video_file);
    if(!$movie)
        return false;
    
    $image = $movie->getFrame($frame);
    if(empty($image)) return false;
    
    $show_img = $image->toGDImage(); 
     
    // сохраняем его
    //header("Content-type: image/jpeg");
    imagejpeg($show_img,$save_to_file);
    imagedestroy($show_img);
}
//
function thumb_path($path='',$img_main='',$w=150,$h=150,$not_crop=false,$no_photo='no_photo.jpg',$quality=100)
{
    if(!is_numeric($w) && !is_numeric($h)){    // хоть один размер должен быть числовым
        $w = $h = 150;    
    }
    $uri = rtrim(ltrim($path,'./'),'/').'/';
    $path = './'.$uri;
    $thumb_name = $w.'x'.$h.'_'.$img_main; 
    if(file_exists($path.$thumb_name) && is_file($path.$thumb_name)){
        return $uri.$thumb_name;
    }else{
        if($uri=='/' || !file_exists($path.$img_main) || !is_file(($path.$img_main))){
            if(file_exists('./images/'.$no_photo)){
                return thumb_path('images/',$no_photo,$w,$h,$not_crop);
            }
            return 'images/'.$no_photo;
        }
    }
    $CI = &get_instance();
    $orig_data = getimagesize($path.$img_main);
    if(!empty($orig_data[0]) && !empty($orig_data[1])){
                          // $w / $h
        $orig_k = $orig_data[0] / $orig_data[1];
        if($w == 'proportional'){
            $w = round($h*$orig_k);  
            $not_crop = true;      
        }elseif($h == 'proportional'){
            $h = $w/$orig_k;
            $not_crop = true;
        }    
    }
    
    $CI->_createThumb($path.$img_main, $path.$thumb_name, $w, $h, $not_crop,$quality);
    if(file_exists($path.$thumb_name)  && is_file(($path.$thumb_name)))
        return $uri.$thumb_name;
    return $uri.$img_main;
}

/*
    По пути к изображению определяет, горизонтальное или вертикальное оно.
*/
function is_image_vertical($image)
{
    if(file_exists($image) && is_file($image))
    {
        $size = getimagesize($image);
        // ширина меньше высоты - вертикальное.
        return $size[0] < $size[1];  
    }
    return false; // по умолчанию - горизонтальное
}

/**
* перемещает файл $file_name из папки $source_dir в папку $destination_dir. вместе с файлом перемещает все его превьюшки, образованные по принципу <ширина>x<высота>_<имя файла> и оригинал orig_<$filename> , если он существует
*/
function move_file_with_previews($source_dir,$destination_dir,$file_name){
    if(!file_exists($destination_dir)){
        @mkdir($destination_dir,0777);
    }
    $source_dir = rtrim('./'.ltrim($source_dir,'./'),'/').'/';
    $destination_dir = rtrim('./'.ltrim($destination_dir,'./'),'/').'/';
    if(!file_exists($source_dir.$file_name) || !file_exists($destination_dir)) return false;
    $names = scandir($source_dir);
    foreach($names as $name)
    {
        if($name == $file_name || preg_match('#(.*)'.$file_name.'$#',$name,$matches)){
            @rename($source_dir.$name,$destination_dir.$name);
        }
    } 
    return true;       
}

/**
* копирует файл $file_name из папки $source_dir в папку $destination_dir. вместе с файлом перемещает все его превьюшки, образованные по принципу <ширина>x<высота>_<имя файла> и оригинал orig_<$filename> , если он существует
* работает аналогично move_file_with_previews, но е сносит файлы из исходной директории
*/
function copy_file_with_previews($source_dir,$destination_dir,$file_name){
    if(!file_exists($destination_dir)){
        @mkdir($destination_dir,0777);
    }
    $source_dir = rtrim('./'.ltrim($source_dir,'./'),'/').'/';
    $destination_dir = rtrim('./'.ltrim($destination_dir,'./'),'/').'/';
    if(!file_exists($source_dir.$file_name) || !file_exists($destination_dir)) return false;
    $names = scandir($source_dir);
    foreach($names as $name)
    {
        if($name == $file_name || $name == 'orig_'.$file_name || preg_match('#(.*)'.$file_name.'$#',$name,$matches)){
            @copy($source_dir.$name,$destination_dir.$name);
        }
    } 
    return true;       
}

/**
* сносит файл $file_name из папки $source_dir. вместе с файлом сносит все его превьюшки, образованные по принципу <ширина>x<высота>_<имя файла>
* пропускает оригинал файла orig_<$file_name> , если он существует
*/
function delete_file_with_previews($source_dir,$file_name){
    $source_dir = rtrim('./'.ltrim($source_dir,'./'),'/').'/';
    if(!file_exists($source_dir.$file_name)) return false;
    $names = scandir($source_dir);
    
    foreach($names as $name)
    {
        if($name != 'orig_'.$file_name && ($name == $file_name || preg_match('#(.*)'.$file_name.'$#',$name,$matches))){
            @unlink($source_dir.$name);
        }
    } 
    return true;       
}

/**
* сносит для файла $file_name из папки $source_dir все его превьюшки, образованные по принципу <ширина>x<высота>_<имя файла>
* файл не удаляется, для удаления вместе с файлом используйте  delete_file_with_previews()
*/
function delete_file_previews($source_dir,$file_name){
    $source_dir = rtrim('./'.ltrim($source_dir,'./'),'/').'/';
    if(!file_exists($source_dir.$file_name)) return false;
    $names = scandir($source_dir);
    foreach($names as $name)
    {
        if($name != 'orig_'.$file_name && $name != $file_name && preg_match('#(.*)'.$file_name.'$#',$name,$matches)){
            @unlink($source_dir.$name);
        }
    } 
    return true;       
}

function get_ext_from_url($url){
    // опробовано на:
    //$url = 'http://sphotos-b.ak.fbcdn.net/hphotos-ak-prn1/32401_241323625998386_733773015_n.jpg';
    //$url ="http://video.ak.fbcdn.net/cfs-ak-prn1/v/717566/104/241345582662857_11492.mp4?oh=188db824ab5d5a4e0a0dc8873b44baf1&oe=50D442BB&__gda__=1356089025_0c3e4e8abc61215547f908dfe9845f62";
    if(strpos($url,'?')!==FALSE){
        $url = substr($url,0,strrpos($url,'?'));
    }
    $fileName = basename($url);
    $ext = substr($fileName, strrpos($fileName, "."));
    if(!empty($ext))
        return $ext;
    return '';
}

function get_image_resource_from_file($path = ''){
    if(!file_exists($path) || !is_file($path)) return false;
    // returns GD image resource of false
    $imageString = file_get_contents($path);
    if($imageString !== FALSE) {
        return imagecreatefromstring($imageString);
    }    
    return false;
}

function normal_path($path){
    return rtrim('./'.ltrim($path,'./'),'/').'/';    
}

// функция удаляет все файлы из указанной директории, $empty == false сносит и саму директорию
function deleteAll($directory, $empty = false) { 
    if(substr($directory,-1) == "/") { 
        $directory = substr($directory,0,-1); 
    } 

    if(!file_exists($directory) || !is_dir($directory)) { 
        return false; 
    } elseif(!is_readable($directory)) { 
        return false; 
    } else { 
        $directoryHandle = opendir($directory); 
        
        while ($contents = readdir($directoryHandle)) { 
            if($contents != '.' && $contents != '..') { 
                $path = $directory . "/" . $contents; 
                
                if(is_dir($path)) { 
                    deleteAll($path); 
                } else { 
                    unlink($path); 
                } 
            } 
        } 
        
        closedir($directoryHandle); 

        if($empty == false) { 
            if(!rmdir($directory)) { 
                return false; 
            } 
        } 
        
        return true; 
    } 
}