<?php
function get_youtube_description($code){
    $url = 'http://gdata.youtube.com/feeds/api/videos/'.$code.'?v=2&alt=jsonc';
    $json = @json_decode(@file_get_contents($url));
    if(!empty($json) && !empty($json->data)){
        return $json->data->description ? $json->data->description:$json->data->title;    
    }
    return '';
}
function thumb_path_in_text($text='',$width=640,$height=360)
{
    if(preg_match_all('/<img(.*)src=["|\'](.*)["|\']([^>]*)>/mU',$text,$matches))
    {
        if(!empty($matches[2]))
        {
            foreach($matches[2] as $k=>$url)
            {
                $dir = str_replace(base_url(),'',dirname($url));
                $file = basename($url);
                $file_info = explode('_',$file);
                $file = array_pop($file_info);
                /*if(!empty($file_info[3]))
                    $file = $file_info[3];*/
                
                if(!empty($matches[3]) && !empty($matches[3][$k]))
                {
                    if(preg_match('/width=["|\']([0-9]+)["|\']/',$matches[3][$k],$match_width))
                    {
                        $width = $match_width[1];
                    }
                    
                    if(preg_match('/height=["|\']([0-9]+)["|\']/',$matches[3][$k],$match_height))
                    {
                        $height = $match_height[1];
                    }
                }
            
                $new_url = base_url() . thumb_path($dir,$file,$width,$height,TRUE);
                
                $text = str_replace($url,$new_url,$text);
            }
        }
    }
    
    return $text;
}
/**
*
* @author Vasilenko Denis (AlterEGO)
* очищаем все теги кроме <p><span><em><strong><img><iframe>
* и проверяем правильность URL на которые они ссылаются
* (для предотвращения XSS атаки)
* 
* @param string $text Text который нужно очистить
* @param array $valid_url
* @param string $valid_tag Перечисление списка допустимых тегов
* @return string
*/
function clear_tags_exept_iframe($text='',$valid_url=array(),$valid_tag='')
{
    $text = strip_tags($text,$valid_tag);
    
    if(preg_match_all('/<iframe(.*)src=["|\'](.*)["|\']([^>]*)><\/iframe>/mU',$text,$matches))
    {
        if(!empty($matches[2]))
        {
            foreach($matches[2] as $k=>$url)
            {
                $_url = parse_url($url);
                
                $not_valid_url = TRUE;
                
                if(!empty($_url['host']))
                {
                    foreach($valid_url as $v_url)
                    {
                        if(preg_match('/(.*)'.$v_url.'/',$_url['host']))
                            $not_valid_url = FALSE;
                    }
                }
                if(empty($_url['host']) || $not_valid_url)
                {
                    if(!empty($matches[0][$k]))
                        $text = str_replace($matches[0][$k],'',$text);
                    $text = str_replace($url,'',$text);
                }
            }
        }
    }
            
    return $text;
}

/**
* @author Vasilenko Denis (AlterEGO)
* 
* для всех блоков в тексте устанавливаем размеры
* ВАЖНО!
* Блок html должен иметь закрывающий тег например: <iframe (.*)></iframe>
* 
* @param string $text Исходный текст
* @param string $block htmk блок
* @param int $width Ширина
* @param int $height Высота
* @return string
*/

function set_block_size($text='',$block='',$width=640,$height=360)
{
    if(is_array($block))
    {
        foreach($block as $_block)
        {
            $text = preg_replace('/<'.$_block.'(.*)width=["|\']([0-9]*)["|\']([^>]*)height=["|\']([0-9]*)["|\']([^>]*)>(.*)<\/'.$_block.'>/mUs',
                            '<'.$_block.'${1}width="'.$width.'"${3}height="'.$height.'"${5}>${6}</'.$_block.'>',
                            $text);
            
            $text = preg_replace('/<'.$_block.'(.*)height=["|\']([0-9]*)["|\']([^>]*)width=["|\']([0-9]*)["|\']([^>]*)>(.*)<\/'.$_block.'>/mUs',
                            '<'.$_block.'${1}width="'.$width.'"${3}height="'.$height.'"${5}>${6}</'.$_block.'>',
                            $text);
            
        }
        return $text;
    }
    $text = preg_replace('/<'.$block.'(.*)width=["|\']([0-9]*)["|\']([^>]*)height=["|\']([0-9]*)["|\']([^>]*)>(.*)<\/'.$block.'>/mUs',
                            '<'.$block.'${1}width="'.$width.'"${3}height="'.$height.'"${5}>${6}</'.$block.'>',
                            $text);
            
    $text = preg_replace('/<'.$block.'(.*)height=["|\']([0-9]*)["|\']([^>]*)width=["|\']([0-9]*)["|\']([^>]*)>(.*)<\/'.$block.'>/mUs',
                    '<'.$block.'${1}width="'.$width.'"${3}height="'.$height.'"${5}>${6}</'.$block.'>',
                    $text);
                    
    return $text;
}
/**
* @author Vasilenko Denis (AlterEGO)
* 
* для всех iframe в тексте добавляем див
* 
* @param string $text Исходный текст
* @param string $open Открывающий набор тегов
* @param string $close Закрывающий набор тегов
* @return string
*/
function add_wrapper_for_iframe($text='',$open='',$close='')
{           
    return preg_replace('/<iframe(.*)src=["|\'](.*)["|\']([^>]*)><\/iframe>/mU',
                        $open.'<iframe${1}src="${2}"${3}></iframe>'.$close,
                        $text);
}
/**
* @author Vasilenko Denis (AlterEGO)
* 
* для всех картинок в тексте добавляем див
* 
* @param string $text Исходный текст
* @param string $open Открывающий набор тегов
* @param string $close Закрывающий набор тегов
* @param string $deferred_call_img Если нужно загрузить картинки позже
* @return string
*/
function add_wrapper_for_img($text='',$open='',$close='',$deferred_call_img=FALSE)
{                
    $text = preg_replace('/<img(.*)class=["|\']([^"|\'])*["|\'].*/mU','<img$1',$text);
    
    if($deferred_call_img)
    {
        return preg_replace('/<img(.*)src=["|\'](.*)["|\']([^>]*)>/mU',
                        $open.'<img${1}src="#" class="deferred_call_js" data-deferred_call_src="${2}"${3}>'.$close,
                        $text);
    }
    return preg_replace('/<img(.*)src=["|\'](.*)["|\']([^>]*)>/mU',
                        $open.'<img${1}src="${2}"${3}>'.$close,
                        $text);
}

/**
*   Прописываем для всех изображений высоту
*   @param string $text Исходный текст
*   @return string
*/
function set_height_attribute($text, $real_width_to_show) {
    //if (preg_match_all('/<img(.*)src="(.*)"(.*)>/mU', $text, $matches))
    if (preg_match_all('/<img(.*)src=["|\'](.*)["|\']([^>]*)>/mU', $text, $matches)) {
        foreach($matches[2] as $key => $_url) {
            $_url = str_replace(base_url(), '', $_url);
            
            $_url = './'.ltrim($_url,'/');
            if (file_exists($_url)) {
                $image_info = getimagesize($_url);
                
                if($image_info[1] < 5)
                {
                    $new_height = $image_info[1];
                } else
                {
                    $k = $image_info[0] / $image_info[1]; //W/H
                    $new_height = round($real_width_to_show / $k);
                }
                $new_image = str_replace('<img','<img height="'.$new_height.'"',$matches[0][$key]);
                $text = str_replace($matches[0][$key], $new_image,$text);
            }
        }
        return $text;
    }
    return $text;    
}

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
* получаем html видео код
* 
* @param mixed $link
* @param mixed $type
*/
function get_video_html($link='',$type='',$width=640,$height=360)
{
    switch($type)
    {
        case 'coub':
            $code = get_coub_preview($link,TRUE);
            return '<iframe src="http://coub.com/embed/'.$code.'?muted=false&amp;autostart=false&originalSize=false" allowfullscreen="true" frameborder="0" width="'.$width.'" height="'.$height.'"></iframe>';
        case 'youtube':
            $code = get_youtube_preview($link,TRUE);
            return '<iframe  src="http://www.youtube.com/embed/'.$code.'?wmode=opaque" width="'.$width.'" height="'.$height.'" frameborder="0" data-real="'.$code.'" allowfullscreen></iframe>';
        case 'vimeo':
            $xml = simplexml_load_file('http://vimeo.com/api/oembed.xml?url='.$link);
            
            if (!empty($xml) && !empty($xml->html))
            {
                $html = (string) $xml->html;
                $html = str_replace('width="'.$xml->width.'"','width="'.$width.'"',$html);
                return str_replace('height="'.$xml->height.'"','height="'.$height.'"',$html);
            }
    }
}
/**
* получаем превью с coub
* 
* @param string $url
* @param boolean $return_code Если нужно вернуть код видео
* @return string Путь к картинке
*/
function get_coub_preview($url='',$return_code=FALSE)
{
    if(!empty($return_code))
        return trim(str_replace('http://coub.com/view/','',$url),'/');
    
    $page = file_get_contents($url);
    
    $html = str_get_html($page);
    $block = $html->find('meta[property=og:image]',0);
    if(!empty($block) && preg_match('/content=["|\']([^ ]*)["|\']/',$block,$match))
    {
        return $match[1];
    }
    return '';
}
/**
* получаем превью с vimeo
* 
* @param string $url
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
    
    if(!@file_get_contents('http://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg'))
        return 'http://img.youtube.com/vi/' . $videoId . '/0.jpg';

    return 'http://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
}
//
function thumb_path($path='',$img_main='',$w=150,$h='proportional',$not_crop=FALSE,$no_photo='no_photo.png',$quality=85)
{
    if(!is_numeric($w) && !is_numeric($h)){    // хоть один размер должен быть числовым
        $w = $h = 150;    
    }
    $uri = rtrim(ltrim($path,'./'),'/').'/';
    $path = './'.$uri;
    $uri = base_url() . $uri;
    
    $paths = explode('/',$path);
    $id = (int) $paths[sizeof($paths)-2];
    
    /*if(strstr($path,'/recipe/'))
    {
        //$paths = explode('/',$path);
        //$id = $paths[sizeof($paths)-2];
        
        $dir_prefix = 'recipe_'.ceil($id / 25000);
        if(!file_exists("./uploads/recipes/{$dir_prefix}"))
            mkdir("./uploads/recipes/{$dir_prefix}",0777); 
        
        if(!file_exists("./uploads/recipes/{$dir_prefix}/{$id}"))
            mkdir("./uploads/recipes/{$dir_prefix}/{$id}",0777); 
        
        $path = str_replace('/recipe/',"/{$dir_prefix}/",$path);
        $uri = str_replace('/recipe/',"/{$dir_prefix}/",$uri);
    }
    if(strstr($path,'/user/'))
    {
        //$paths = explode('/',$path);
        //$id = $paths[sizeof($paths)-2];
        
        $dir_prefix = 'users_'.ceil($id / 25000);
        if(!file_exists("./uploads/users/{$dir_prefix}"))
            mkdir("./uploads/users/{$dir_prefix}",0777); 
        if(!file_exists("./uploads/users/{$dir_prefix}/{$id}"))
            mkdir("./uploads/users/{$dir_prefix}/{$id}",0777); 
        
        $path = str_replace('/user/',"/{$dir_prefix}/",$path);
        $uri = str_replace('/user/',"/{$dir_prefix}/",$uri);
    }*/
    
    // нижние два условия временно
    if(strstr($path,'/recipe') && strpos($img_main,'r') === 0)
    {
        //$paths = explode('/',$path);
        //$id = $paths[sizeof($paths)-2];
        
        $id = preg_replace('/[^0-9]+/','',$img_main);
        
        $dir_prefix = floor($id / 1000);
        if(!$dir_prefix) $dir_prefix = 1;
        
        $_img_main = explode('.',$img_main);
        $ext = array_pop($_img_main);
        $img_main = implode('',$_img_main).'_large.'.$ext;
        
        return "http://www.koolinar.ru/all_image/recipes/{$dir_prefix}/{$id}/{$img_main}";
    }
    if(strstr($path,'/user') && strpos($img_main,'u') === 0)
    {
        //$paths = explode('/',$path);
        //$id = $paths[sizeof($paths)-2];
        
        $id = preg_replace('/[^0-9]+/','',$img_main);
        
        $dir_prefix = floor($id / 1000);
        if(!$dir_prefix) $dir_prefix = 1;
        
        $_img_main = explode('.',$img_main);
        $ext = array_pop($_img_main);
        $img_main = implode('',$_img_main).'_large.'.$ext;
        
        return "http://www.koolinar.ru/all_image/users/{$dir_prefix}/{$id}/{$img_main}";
    }
    /*
    if(strstr($path,'/posts/'))
    {
        //$paths = explode('/',$path);
        //$id = $paths[sizeof($paths)-2];
        
        $dir_prefix = 'post_'.ceil($id / 25000);
        if(!file_exists("./uploads/blogs/{$dir_prefix}"))
        {
            mkdir("./uploads/blogs/{$dir_prefix}",0777); 
        }
        $path = str_replace('/posts/',"/{$dir_prefix}/",$path);
        $uri = str_replace('/posts/',"/{$dir_prefix}/",$uri);
    }
    */
    if(empty($dir_prefix) && !empty($id) && !empty($paths[2]))
    {
        $dir_prefix = ceil($id / 25000);
        
        if(!file_exists("./uploads/{$paths[2]}/{$dir_prefix}"))
            mkdir("./uploads/{$paths[2]}/{$dir_prefix}",0777); 
        
        if(!file_exists("./uploads/{$paths[2]}/{$dir_prefix}/{$id}"))
            mkdir("./uploads/{$paths[2]}/{$dir_prefix}/{$id}",0777); 
        
        $path = "{$paths[0]}/{$paths[1]}/{$paths[2]}/{$dir_prefix}/{$id}";
        
        $uri = rtrim(ltrim($path,'./'),'/').'/';
        $path = './'.$uri;
        $uri = base_url() . $uri;
    }
    
    $thumb_name = $w.'x'.$h.'_'.(int)$not_crop.'_'.$quality.'_'.$img_main; 
    
    if(file_exists($path.$thumb_name) && is_file($path.$thumb_name))
    {
        return $uri.$thumb_name;
    }else{
        if($uri=='/' || !file_exists($path.$img_main) || !is_file(($path.$img_main))){
            if(file_exists('./images/'.$no_photo)){
                return thumb_path('images/',$no_photo,$w,$h,$not_crop);
            }
            return base_url() . 'images/'.$no_photo;
        }
    }
    
    /*if(strpos($img_main,'jpg') !== FALSE && strpos($img_main,'.jpg') === FALSE)
    {
        copy($path.$img_main,$path.$img_main.'.jpg');
        $img_main .= '.jpg';
        $thumb_name .= '.jpg';
        ///
        if(file_exists($path.$thumb_name) && is_file($path.$thumb_name))
        {
            return $uri.$thumb_name;
        }else{
            if($uri=='/' || !file_exists($path.$img_main) || !is_file(($path.$img_main))){
                if(file_exists('./images/'.$no_photo)){
                    return thumb_path('images/',$no_photo,$w,$h,$not_crop);
                }
                return 'images/'.$no_photo;
            }
        }
    }*/
    
    if(!file_exists($path.$img_main) || !is_file($path.$img_main))
        return base_url() . 'images/'.$no_photo;
        
    if(!filesize($path.$img_main))
        return base_url() . 'images/'.$no_photo;
        
    $orig_data = @getimagesize($path.$img_main);
    if(empty($orig_data))
        return base_url() . 'images/'.$no_photo;
        
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
    $ext = array_pop(explode('.',$path.$img_main));
    
    if(!empty($orig_data['mime']))
    {
        $_ext = explode('/',$orig_data['mime']);
        if(!empty($_ext[1]) && empty($ext))
        {
            copy($path.$img_main,$path.$img_main.$_ext[1]);
            $img_main = $img_main.$_ext[1];
            $thumb_name .= $_ext[1];
            ///
            if(file_exists($path.$thumb_name) && is_file($path.$thumb_name))
            {
                return $uri.$thumb_name;
            }else{
                if($uri=='/' || !file_exists($path.$img_main) || !is_file(($path.$img_main))){
                    if(file_exists('./images/'.$no_photo)){
                        return thumb_path('images/',$no_photo,$w,$h,$not_crop);
                    }
                    return base_url() . 'images/'.$no_photo;
                }
            }
        }
    }
    
    _createThumb($path.$img_main, $path.$thumb_name, $w, $h, $not_crop,$quality);
    if(file_exists($path.$thumb_name)  && is_file(($path.$thumb_name)))
        return $uri.$thumb_name;
    return $uri.$img_main;
}

function _createThumb($source_image, $new_image, $new_width, $new_height, $not_crop=false,$quality=85)
{
     static $CI;
    
    if(empty($CI))
    {
     $CI = &get_instance();
     $CI->load->library('image_lib'); // загружаем библиотеку
    }
        
     $configs = array();
     $configs['image_library'] ='GD2'; // выбираем библиотеку
     $configs['source_image']    = $source_image;
     $configs['new_image']    = $new_image;
     $configs['maintain_ratio'] = false;
     $configs['quality'] = $quality;
     $configs['width'] = $new_width; // и задаем размеры
     $configs['height'] = $new_height;
     
     $size = getimagesize($configs['source_image']);
     if(empty($size))
        return false;
     
     $w = $size[0];
     $h = $size[1]; 
     
     // вертикальность изображения
     $is_vertical = $w<$h;
     $k = $w/$h;
     $k_need = $new_width/$new_height ;
     //if($w < $h)
     if($k < $k_need)
     {
         $configs['width'] = $new_width; 
         $configs['height'] =  $configs['width']/$k;
         
         
     }else{
         $configs['height'] = $new_height; 
         $configs['width'] =  $configs['height']*$k;
     }
     
     $CI->image_lib->clear();
     $CI->image_lib->initialize($configs);
     if ($CI->image_lib->resize() && empty($not_crop))
     {
         return _crop($new_image,$new_width,$new_height,$is_vertical);
     }
     return false;
     //exit;
     /*else
         return false;  */
}
    
    function _crop($new_image,$new_width,$new_height,$is_vertical,$x=null,$y=null)
    {
        static $CI;
    
        if(empty($CI))
        {
         $CI = &get_instance();
         $CI->load->library('image_lib'); // загружаем библиотеку
        }
         //
         $configs = array();
         $configs['image_library'] = 'GD2';
         $configs['source_image']    = $new_image;
         $configs['new_image']    = $new_image;
         list($width, $height) = getimagesize($configs['source_image']);
         $configs['quality'] = 85;
         $configs['maintain_ratio'] = false;
         $configs['width'] = $new_width; 
         $configs['height'] = $new_height;
             
         $configs['x_axis'] = 0;
         $configs['y_axis'] = 0;
         /*if($width == $new_width)
         {
             $configs['x_axis'] = 0;
             $configs['y_axis'] = round(($height - $new_height)/2);
         }*/
         // если вертикальное - попробуем вырезать центральную  часть
         if($is_vertical || $width == $new_width)
         {
            $configs['x_axis'] = 0;
            $configs['y_axis'] = round(($height - $new_height)/2);
         }
         if($height == $new_height)
         {
             $configs['x_axis'] = round(($width - $new_width)/2);
             $configs['y_axis'] = 0;
         }
         
         if(!is_null($x) && !is_null($y))
         {
             $configs['x_axis'] = $x;
             $configs['y_axis'] = $y;
         }
         
         $CI->image_lib->clear();
         $CI->image_lib->initialize($configs);

         if ( ! $CI->image_lib->crop()){
             show_error($CI->image_lib->display_errors());
         }
         else
             return true;
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

    /*
     * Extract data from URL
     * facebook 
     */
    function get_url($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $tmp = curl_exec($ch);
        curl_close($ch);
        return $tmp;
    }
    
    function pr($array)
    {
        echo '<pre>';
            print_r($array);
        echo '</pre>';
    }
    
    