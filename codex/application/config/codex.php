<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your Code Igniter root. Typically this will be your base URL, | WITH a trailing slash:
|
|	http://www.your-site.com/
|
 */
$config['base_url'] = "http://".$_SERVER['HTTP_HOST'];
$config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
/*
|--------------------------------------------------------------------------
| CodeExtinguisher Configuration Options
|--------------------------------------------------------------------------
|
| This is the parameter to be passed into site_url() to redirect the user
| when he/she logs in successfully
|
*/
$config['codex_site_title']         = "CodeExtinguisher";

$config['codex_site_title']         = "CodeExtinguisher";
$config['codex_items_per_page']     = "20";
$config['codex_trust_mode']         = "true";

$config['codex_asset_folder']       = $config['base_url'].'codex/assets/';
$config['codex_layout_dir']         = "view_modes/";
$config['codex_template']           = "alterego";
$config['codex_view_mode']           = "table";
$config['codex_default_view_mode']  = "table";
$config['codex_definitions_dir']    = "definitions/";

$config['codex_auto_generate_crud'] = true;
$config['codex_auto_generate_menu'] = 'files';//или db

$config['codex_exclude_tables']     = array(
                                        'ci_sessions',
                                        /*'example',*/
                                        'user_records'
                                      );

$config['codex_navigation']         = array(
                                        /*'Example'=>'example/',
                                        'User Registration'=>'userregistration/'*/
                                      );

?>
