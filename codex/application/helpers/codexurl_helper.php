<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

if ( ! function_exists('get_files'))
{
    function get_files($source_dir, $include_path = FALSE, $_recursion = FALSE, $root = true)
    {
        static $_filedata = array();

        if ($fp = @opendir($source_dir))
        {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($_recursion === FALSE)
            {
                $_filedata = array();
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            }

            while (FALSE !== ($file = readdir($fp)))
            {
                if (!$root && @is_dir($source_dir.$file) && strncmp($file, '.', 1) !== 0)
                {
                    get_filenames($source_dir.$file.DIRECTORY_SEPARATOR, $include_path, TRUE);
                }
                elseif (@is_file($source_dir.$file) && strncmp($file, '.', 1) !== 0)
                {
                    $_filedata[] = ($include_path == TRUE) ? $source_dir.$file : $file;
                }
            }
            return $_filedata;
        }
        else
        {
            return FALSE;
        }
    }
}

function codexAnchor($uri = '', $title = '', $attributes = ''){
		$title = (string) $title;

        $formatted = site_url($uri);
	
        if(substr($uri,0,1) == '?'){
            $formatted = site_url();
            $site_url = $formatted.$uri;
        }
        else
            $site_url = ( ! preg_match('!^\w+://! i', $uri)) ? $formatted : $uri;
	
		if ($title == '')
		{
			$title = $site_url;
		}

		if ($attributes != '')
		{
			$attributes = _parse_attributes($attributes);
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
}

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