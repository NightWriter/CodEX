<?php
/**
* проверяем правильность даты в 
* формате YYYY-mm-dd
* 
* @param string $date
* @return boolean
*/
function check_date($date='')
{
    $date = preg_replace(array('/[^0-9]/', '/[\-]+/'),'-',$date);
    $_date_birth = explode('-',$date);
    $date_birth = '';
    if(!empty($_date_birth[0]) && !empty($_date_birth[1]) && !empty($_date_birth[2]))
        $date_birth = $_date_birth[0].'-'.$_date_birth[1].'-'.$_date_birth[2];
    
    $return = FALSE;
    if(!empty($date_birth))
    {
        $_date_birth = explode('-',$date_birth);
        
        $num = cal_days_in_month(CAL_GREGORIAN, $_date_birth[1], $_date_birth[0]);
        if($num >= $_date_birth[0])
            $return = TRUE;
    }
    return $return;
}
/**
* коректируем время относительно временной зоны
* 
* @param Date $format Формат даты для использования в date()
* @param int $time Unix time
*/
function time_zone($format='',$time=0)
{
    $timezone = 4 - round(date('Z')/3600,2);
    return date($format,($time+($timezone*3600)));
}
/**
* преобразуем строку в UNIX time
* предварительно заменив лишние разделители
* 
* @param string $date
* @return int
*/
function str_to_time($date='')
{
    $date = preg_replace(array('/[^0-9]/', '/[\-]+/'),'-',$date);
    
    return strtotime($date);
}

function calc_period($date_start, $date_finish, $get_string=false)
{
    
    $CI = &get_instance();
    
    if($date_start == $date_finish && $get_string){
        return $CI->lang->line('period_just_now');
    }
    
    $st = explode('-', date('d-m-Y-H-i-s', ($date_start)));
    $fin = explode('-', date('d-m-Y-H-i-s', ($date_finish)));

    if (($seconds = $fin[5] - $st[5]) < 0) {
            $fin[4]--;
            $seconds += 60;
    }

    if (($minutes = $fin[4] - $st[4]) < 0) {
            $fin[3]--;
            $minutes += 60;
    }

    if (($hours = $fin[3] - $st[3]) < 0) {
            $fin[0]--;
            $hours += 24;
    }

    if (($days = $fin[0] - $st[0]) < 0) {
            $fin[1]--;
            $days = date('t', mktime(1, 0, 0, (int)$st[1], (int)$st[0], (int)$st[2])) - (int)$st[0] + (int)$fin[0];
    }

    if (($months = $fin[1] - $st[1]) < 0) {
            $fin[2]--;
            $months += 12;
    }

    $years = $fin[2] - $st[2];
    
    $period = array($years, $months, $days, $hours, $minutes, $seconds);
    if($get_string)
    {
        $_period = '';
            
        if(!empty($period[0]))
            $_period .= $period[0].' г.';
        if(!empty($period[1]))
            $_period .= ' '.$period[1].' мес.';
        if(!empty($period[2]))
            $_period .= ' '.$period[2].' д.';
            
        if(empty($period[2]) && !empty($period[3]))
            $_period .= ' '.$period[3].' ч.';
        if(empty($period[2]) && empty($period[3]) && !empty($period[4]))
            $_period .= ' '.$period[4].' мин.';
        if(empty($period[4]) && empty($period[3]) && empty($period[2]) && empty($period[1])  && empty($period[0]) && !empty($period[5]))
            $_period .= ' '.$period[5].' сек.';
            
        return $_period;
    }
    return $period;
}

function time_ago($time = 0,$minute=false,$text='')
{
    if(!is_numeric($time)){
        $time = strtotime($time);
    }
    $_CI = &get_instance();
    $time_now = time();
    
    
    if($time<=$time_now){      // если дата уже прошла
        $p_arr = calc_period($time,$time_now);
        // добавляем недели
        $period_arr = array($p_arr[0],$p_arr[1],(int)($p_arr[2]/7),$p_arr[2],$p_arr[3],$p_arr[4],$p_arr[5]);
        
        $list = array('year','month','week','day','hour');
        if($minute)
            $list[] = 'minute';
        foreach($list as $index=>$key){
            if(!empty($period_arr[$index])){
                return $period_arr[$index].' '.dec_string_arr($period_arr[$index],$_CI->lang->line('arr_'.$key)).' '.$_CI->lang->line('lang_ago');
            }     
        }
        return (!empty($text)) ? $text : $_CI->lang->line('period_just_now');    
    }else{    // дата в будущем
        $p_arr = calc_period($time_now,$time);
        // добавляем недели
        $period_arr = array($p_arr[0],$p_arr[1],(int)($p_arr[2]/7),$p_arr[2],$p_arr[3],$p_arr[4],$p_arr[5]);
        foreach(array('year','month','week','day','hour') as $index=>$key){
            if(!empty($period_arr[$index])){
                return $_CI->lang->line('lang_in_time').' '.$period_arr[$index].' '.dec_string_arr($period_arr[$index],$_CI->lang->line('arr_'.$key));
            }     
        }
        return (!empty($text)) ? $text : $_CI->lang->line('lang_soon'); 
    }
    
}

function get_age($date)
{
  if ($date == "0000-00-00") return 0;

  $birthday = explode("-",$date);

  if(empty($birthday)) return 0;
  
  $birthday_date = mktime(0,0,0,$birthday[1],$birthday[2],$birthday[0]);

  $age = (time()-$birthday_date)/31536000;
  return floor($age);
}