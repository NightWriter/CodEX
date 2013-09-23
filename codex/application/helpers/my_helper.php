<?php

/*
* описание - http://www.s-shot.ru/
* 
* параметр
* $url какого сайта  делать скрин
* Дополнительные параметры:
* $dimentions - разрешение - указывается в пикселях, например (1024x768), для полноразмерного скриншота указываем только ширину (1024)
* $qual - размер - указывается ширина масштабированной картинки
* $ext формат - может принимать два значения (JPEG|PNG), по умолчанию "JPEG"
* 
* 
* example screen("http://dosites.ru", "1024x768", "1200", "jpeg");
* http://mini.s-shot.ru/1024x768/400/jpeg/?http://www.site.ru<br>
* http://mini.s-shot.ru/1024/400/png/?http://www.site.ru
*/
function save_screen($url, $dimentions, $qual, $ext='JPEG',$save_url='')
{
    $toapi="http://mini.s-shot.ru/".$dimentions."/".$qual."/".$ext."/?".$url;
    $scim=@file_get_contents($toapi);
    if(empty($scim)){
        return false;
    }
    if(empty($save_url)){
        $save_url='./upload/temp';    
    }
    if(!file_exists($save_url)){
        mkdir($save_url,0777);
    }
    mt_srand();
    $filename = md5(uniqid(mt_rand())).'.'.$ext;
    if(file_put_contents($save_url.'/'.$filename, $scim)){
        if(!file_exists($save_url.'/thumb')){
            mkdir($save_url.'/thumb',0777);
        }
        $CI=&get_instance();
        $CI->_createThumb($save_url.'/'.$filename, $save_url.'/thumb/'.$filename, 200, 200);
        return $filename;
    }
    return false;
}

function whois($hostname){
    require_once('simple_html_dom_helper.php');
    $page=file_get_contents('http://www.whois.com/whois/'.$hostname);
    if(!empty($page)){
        $dom=str_get_html($page);
        $div=$dom -> find('#registryData',0);
        if(!empty($div)){
            return $div -> innertext;     
        }
        return '';   
    } 
    return '';   
}

//
function now_mysql($only_date=false){
   $pattern='Y-m-d';
   if(!$only_date)
      $pattern.=' H:i:s';
   return date($pattern);
}
