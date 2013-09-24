<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

 /**
 * Доработал кеширование с использованием времени жизни кеша и проверкой чексуммы таблицы
 * @author Vasilenko Denis (AlterEGO)
 */

// ------------------------------------------------------------------------

/**
 * Database Cache Class
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
 
class CI_DB_Cache {

	var $CI;
	var $db;	// allows passing of db object so that multiple database connections and returned db objects can be supported
    private $cache_live_time = 0;// время жизни кеша
	/**
	 * Constructor
	 *
	 * Grabs the CI super object instance so we can access it.
	 *
	 */
	function CI_DB_Cache(&$db)
	{
		// Assign the main CI object to $this->CI
		// and load the file helper since we use it a lot
		$this->CI =& get_instance();
		$this->db =& $db;
		$this->CI->load->helper('file');
	}   
    /**
    * удаляем старые файлы
    * 
    * @param string $dir Каталог
    * @param string $file_mask Часть имени файла
    */
    private function remove_old_files($source_dir='',$file_name='')
    {
        $source_dir = rtrim('./'.ltrim($source_dir,'./'),'/').'/';
        if(!file_exists($source_dir.$file_name)) return false;
        
        $names = scandir($source_dir);
        
        foreach($names as $name)
        {
            if($name == $file_name || preg_match('#(.*)'.$file_name.'$#',$name,$matches)){
                @unlink($source_dir.$name);
            }
        } 
        return true;
    }
    /**
    * получаем чек сумму по всем таблицам, которые участвуют в запросе
    * 
    * @return string;
    */
    private function get_check_sum()
    {
        $this->db->cache_off();
        
        $checksum = '';
        foreach($this->db->ar_from as $table)
        {
            $new_table = str_replace('  ',' ',$table);
            if(preg_match('/(.*) as (.*)/',$new_table,$matches))
            {
                if(!empty($matches[1]))
                    $new_table = $matches[1];
            }
            
            $row = $this->db->query("CHECKSUM TABLE {$new_table}");
            $checksum .= $row->row()->Checksum;
        }
        foreach($this->db->ar_join as $join)
        {
            if(preg_match('/JOIN ([^ ]*) ON/',$join,$matches))
            {
                $row = $this->db->query("CHECKSUM TABLE {$matches[1]}");
                $checksum .= $row->row()->Checksum;
            }
            
        }
        $this->db->cache_on($this->cache_live_time);
        return md5($checksum);
    }
	// --------------------------------------------------------------------

	/**
	 * Set Cache Directory Path
	 *
	 * @access	public
	 * @param	string	the path to the cache directory
	 * @return	bool
	 */
	function check_path($path = '')
	{
		if ($path == '')
		{
			if ($this->db->cachedir == '')
			{
				return $this->db->cache_off();
			}

			$path = $this->db->cachedir;
		}

		// Add a trailing slash to the path if needed
		$path = preg_replace("/(.+?)\/*$/", "\\1/",  $path);

		if ( ! is_dir($path) OR ! is_really_writable($path))
		{
			// If the path is wrong we'll turn off caching
			return $this->db->cache_off();
		}

		$this->db->cachedir = $path;
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve a cached query
	 *
	 * The URI being requested will become the name of the cache sub-folder.
	 * An MD5 hash of the SQL statement will become the cache file name
	 *
	 * @access	public
     * @param int $cache_live_time Время жизни кеша
	 * @return	string
	 */
	function read($sql,$cache_live_time=0)
	{
		if ( ! $this->check_path())
		{
			return $this->db->cache_off();
		}
        $this->cache_live_time = $cache_live_time;
        
        $check_sum = $this->get_check_sum($this->cache_live_time);
        
        $segment_one = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);

		$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);

		$filepath = $this->db->cachedir.$segment_one.'_'.$segment_two.'/'.$check_sum.'_'.md5($sql);

		if (FALSE === ($cachedata = read_file($filepath)))
		{
            $this->remove_old_files($this->db->cachedir.$segment_one.'_'.$segment_two,md5($sql));
			return FALSE;
		}
        // проверяем время жизни кеша
        if( !empty($this->cache_live_time) && (filemtime($filepath) + $this->cache_live_time) < time())
        {
            unlink($filepath);
            return false;
        }
		return unserialize($cachedata);
	}

	// --------------------------------------------------------------------

	/**
	 * Write a query to a cache file
	 *
	 * @access	public
	 * @return	bool
	 */
	function write($sql, $object)
	{
		if ( ! $this->check_path())
		{
			return $this->db->cache_off();
		}
        $check_sum = $this->get_check_sum();
        
		$segment_one = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);

		$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);

		$dir_path = $this->db->cachedir.$segment_one.'_'.$segment_two.'/';

		$filename = $check_sum.'_'.md5($sql);

		if ( ! @is_dir($dir_path))
		{
			if ( ! @mkdir($dir_path, DIR_WRITE_MODE))
			{
				return FALSE;
			}

			@chmod($dir_path, DIR_WRITE_MODE);
		}

		if (write_file($dir_path.$filename, serialize($object)) === FALSE)
		{
			return FALSE;
		}

		@chmod($dir_path.$filename, FILE_WRITE_MODE);
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete cache files within a particular directory
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete($segment_one = '', $segment_two = '')
	{
		if ($segment_one == '')
		{
			$segment_one  = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		}

		if ($segment_two == '')
		{
			$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
		}

		$dir_path = $this->db->cachedir.$segment_one.'_'.$segment_two.'/';

		delete_files($dir_path, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete all existing cache files
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete_all()
	{
		delete_files($this->db->cachedir, TRUE);
	}

}


/* End of file DB_cache.php */
/* Location: ./system/database/DB_cache.php */