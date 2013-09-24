<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://www.codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['post_controller_constructor'][] = array(
                                'class'    => 'AuthHook',
                                'function' => 'setPreDefinedAccessLevels',
                                'filename' => 'loginhook.php',
                                'filepath' => 'hooks',
                          );

$hook['post_controller_constructor'][] = array(
                                'class'    => 'AuthHook',
                                'function' => 'auth',
                                'filename' => 'loginhook.php',
                                'filepath' => 'hooks'
                          );
$hook['post_controller_constructor'][] = array(
             'function' => '_hook_kh_ci_event',
             'filename' => 'plugins.php',
             'filepath' => 'hooks/khaos',
             'params'   => array('onPostControllerConstructor')
    );  
$hook['post_controller_constructor'][] = array(
             'function' => '_hook_kh_init_plugins',
             'filename' => 'plugins.php',
             'filepath' => 'hooks/khaos'
    );

$hook['pre_controller'] = array(
             'function' => '_hook_kh_ci_event',
             'filename' => 'plugins.php',
             'filepath' => 'hooks/khaos',
             'params'   => array('onPreController')
    );    

$hook['post_controller'] = array(
             'function' => '_hook_kh_ci_event',
             'filename' => 'plugins.php',
             'filepath' => 'hooks/khaos',
             'params'   => array('onPostController')
    );  

$hook['post_system'] = array(
             'function' => '_hook_kh_ci_event',
             'filename' => 'plugins.php',
             'filepath' => 'hooks/khaos',
             'params'   => array('onPostSystem')
    );  

?>
