<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Ресайзит изображения
 */
class Resizer {
    
    /**
    *  Определит, является ли файл изображением
    *  @param string $src путь к файлу
    *  @returns boolean 
    */
    function is_image($src)
    {
        $is = @getimagesize($src);
        if(!$is)
            return false;
        return true;
    }
    
    /**
    * put your comment there...
    * 
    * @param string $src путь к исходному изображению
    * @param string $dst путь к получаемому изображению
    * @param int $width ширина получаемого изображения
    * @param int $height высота получаемого изображения
    * @param int $crop 0/1 нужен ли корп
    * @returns boolean 
    */
    function resize_image($src, $dst ,$width, $height, $crop = 1)
    {
        if(!list($w, $h) = getimagesize($src)) 
            throw new Exception("Unsupported picture type!");
        // определим тип по расширению файла
        $type = strtolower(substr(strrchr($src,"."),1)); 
        if($type == 'jpeg') 
            $type = 'jpg';
        switch($type){
            case 'bmp': $img = imagecreatefromwbmp($src); break;
            case 'gif': $img = imagecreatefromgif($src); break;
            case 'jpg': $img = imagecreatefromjpeg($src); break;
            case 'png': $img = imagecreatefrompng($src); break;
            default:
                throw new Exception('Unknown image type');
        }
        
          // resize
          if($crop){
            if($w < $width or $h < $height) 
                throw new Exception("Picture is too small!");
            $ratio = max($width/$w, $height/$h);
            $h = $height / $ratio;
            $x = ($w - $width / $ratio) / 2;
            $w = $width / $ratio;
          }
          else{
            if($w < $width and $h < $height)
                throw new Exception("Picture is too small!");
            $ratio = min($width/$w, $height/$h);
            $width = $w * $ratio;
            $height = $h * $ratio;
            $x = 0;
          }
          
          $new = imagecreatetruecolor($width, $height);

          // прозрачность 
          if($type == "gif" or $type == "png"){
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
          }

          imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

          switch($type){
            case 'bmp': imagewbmp($new, $dst); break;
            case 'gif': imagegif($new, $dst); break;
            case 'jpg': imagejpeg($new, $dst); break;
            case 'png': imagepng($new, $dst); break;
          }
          return true;
    }
    
}