<?php
/**
* аналог ucfirst только для UTF
* 
* @param string $str 
* @param string $encoding
* @param boolean $lower_str_end
* @retrun string
*/
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
        if(!function_exists('mb_strtoupper'))
            return ucfirst($str);
        
      $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
      $str_end = "";
      if ($lower_str_end) {
    $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
      }
      else {
    $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
      }
      $str = $first_letter . $str_end;
      return $str;
    }
}
/**
* добавляем префикс к полю в таблице
* 
* @param mixed $item
* @param mixed $key
* @param string $prefix
*/
function prepare_table(&$item, $key, $prefix)
{
    $item = $prefix.'.'.$item;
}

function get_sub_str_len($text='',$width_line=10,$max_line=1)
{
    $text = str_replace("\n",'',$text);
    $words = str_word_count($text,1, '\'"«»,.~!@#$%^&*()-=+-/;QAZWSXEDCRFVTGBYHNUJMIKOLPqazwsxedcrfvtgbyhnujmikolp0123456789АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя');
    
    $num_rows = 1;
    $chars = 0;
    $all_chars = 0;
    $all_cnt = sizeof($words)-1;
    foreach($words as $k => $word)
    {
        $chars += mb_strlen($word,'UTF-8')+1;
        $all_chars += mb_strlen($word,'UTF-8')+1;
        
        if($all_cnt == $k)
        {
            --$chars;
            --$all_chars;
        }
        if($chars > $width_line)
        {    
            $chars = mb_strlen($word,'UTF-8')+1;
            ++$num_rows;
        }
        if($num_rows > $max_line) break;
    }
    
    return $all_chars;
}
/**
* получаем сколько выёдет строк текста
* 
* @param string $text
* @param int $width_line кол-во символов в одно строке
* @return int
*/
function get_num_lines($text='',$width_line=10)
{
    $text = str_replace("\n",'',$text);
    $words = str_word_count($text,1, '\'"«»,.~!@#$%^&*()-=+-/;QAZWSXEDCRFVTGBYHNUJMIKOLPqazwsxedcrfvtgbyhnujmikolp0123456789АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя');
    
    $num_rows = 1;
    $chars = 0;
    $all_cnt = sizeof($words)-1;
    foreach($words as $k => $word)
    {
        $chars += mb_strlen($word,'UTF-8')+1;
        
        if($all_cnt == $k)
            --$chars;
        
        if($chars > $width_line)
        {
            $chars = mb_strlen($word,'UTF-8')+1;
            ++$num_rows;
        }
    }
    return $num_rows;
}

if ((!function_exists('mb_str_replace')) &&
    (function_exists('mb_substr')) && (function_exists('mb_strlen')) && (function_exists('mb_strpos'))) {
    function mb_str_replace($search, $replace, $subject) {
        if(is_array($subject)) {
            $ret = array();
            foreach($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }

        foreach((array) $search as $key => $s) {
            if($s == '') {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s, 0, 'UTF-8');
            while($pos !== false) {
                $subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject, $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
            }
        }
        return $subject;
    }
}
/**
* разбиваем строку с учётом тегов
* 
* @param string $string
* @param int $limit
*/
function html_word_wrap($string,$limit,$break='',$cut=false)
{
   //$string = strip_tags($string,'<a>');
   $string = str_replace('&nbsp;',' ',$string);
   if(preg_match_all('/<.*>(.*)([^>]*)/mU', $string, $res_arr))
   {
       foreach($res_arr[0] as $k => $link)
       {
          $string = str_replace($link,' ___'.$k.'___ ',$string);
       }
   }
   $string = wordwrap($string,$limit,$break,$cut) ;
   foreach($res_arr[0] as $k=>$link){
      $string = str_replace(' ___'.$k.'___ ',$link,$string);
   }
   return $string;
}
/**
* разбиваем строку по заданной длине
* 
* @param string $text Исходный текст
* @param int $len Интервал
* @param string $delimeter Разделитель
* @param string $additional_delimeter Дополнительный разделитель, если можно разбить строку после какогото символа
*/
function str_word_wrap($text='',$len=75,$delimeter=' ',$additional_delimeter='')
{
    $text = iconv('UTF-8','WINDOWS-1251',$text);
    
        if(!empty($additional_delimeter))
        {
            if(is_array($additional_delimeter))
            {
                foreach($additional_delimeter as $a_delimeter)
                    $text = str_replace($a_delimeter, $a_delimeter.$delimeter, $text);
            } else
            {
                $text = str_replace($additional_delimeter, $additional_delimeter.$delimeter, $text);
            }
        }
        $texts = explode($delimeter,$text); 
        
        foreach($texts as &$_text)
        {                           
            $_text = wordwrap($_text.' ',$len,$delimeter,TRUE);
        }
        unset($_text);
        
    return iconv('WINDOWS-1251','UTF-8',implode('',$texts));
}
/**
* формат целого числа
* 
* @param int $value
* @return string
*/
function integer_format($value=0)
{                                                                   
    return number_format($value, 0, '', ' ');
}
/**
* выризаем часть строки в кодировке UTF-8
* 
* @param string $str
* @param int $leng
* @return string
*/
function str_crop($str,$leng){
    if (function_exists('mb_strlen') && mb_strlen($str,"utf8")>$leng)
        return (mb_substr($str,0,$leng,"utf8").'...');
    return $str;
}
/**
* скланения
* 
* @param int $n
* @param string $one
* @param string $two
* @param string $other
* @return string
*/
function dec_string($n,$one,$two,$other)
{
  if($n%100>10 && $n%100<20) return $other;
  if($n%10==1) return $one;
  if($n%10>1 && $n%10<5) return $two;
  return $other;
}
/**
* скланения
* 
* @param int $n
* @param array $words
*/
function dec_string_arr($n,$words = array())
{
  if($n%100>10 && $n%100<20) return $words[2] ;
  if($n%10==1) return $words[0];
  if($n%10>1 && $n%10<5) return $words[1];
  return $words[2];
}