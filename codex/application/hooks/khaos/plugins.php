<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

// Event library also holds other utility classes needed to setup the event handling
require_once APPPATH.'libraries/Event.php';

/**
 * Load Khaos Plugins
 *
 * Called via the 'pre_system' hook this function
 * autoloads all of the plugins specified in the khaos.php
 * config file.
 */
function _hook_kh_init_plugins()
{
    $dispatcher =& KH_Dispatcher::getInstance();

    /*
     * Config object doesnt exist at this point so directly
     * grab the list of the plugins from the config file.
     */
    
    if (!file_exists($file = APPPATH.'config/khaos.php'))
        log_message('Khaos :: Event Handler - Error Locating Config File \''.$file.'\'.');
    else 
        include $file;

    /*
     * Iterate over and include the plugins to be 
     * automatically loaded.
     */    
        
    if (isset($config['plugins']) && is_array($config['plugins']))
    {
        foreach ($config['plugins'] as $plugin)
        {
            if (!file_exists($file = APPPATH.'khaos/plugins/'.$plugin.'.php'))
                log_message('Khaos :: Event Handler - Error Locating Plugin \''.$file.'\'.');
            else 
            {
                include_once $file; 
                if (!class_exists($class = 'KH_Plugin_'.$plugin))
                    log_message('Khaos :: Event Handler - Error Locating Class \''.$class.'\'.');
                else 
                    $instance = new $class($dispatcher);  
            }  
        }
    }
    
    /*
     * Trigger the onPreSystem event here to save adding
     * another hook.
     */
    
    $dispatcher->Trigger('onPreSystem');    
}

/**
 * Trigger CI Event
 *
 * Triggers events for each of the applicable
 * CI hooks.
 * 
 * @param array $args
 */
function _hook_kh_ci_event($args)
{
    static $dispatcher;
    
    if (!is_object($dispatcher))
        $dispatcher =& KH_Dispatcher::getInstance();
        
    $dispatcher->Trigger($args[0]);
}

?>
