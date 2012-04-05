<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/* This is the level at which controllers/methods are insecure */
$config['public_level'] = 0;
/* This is the level given to any controllers/methods that are not defined */
$config['default_level'] = 1;
/* Users with this access level are administrators, they can do w/e they want! */
$config['admin_level'] = 3;
?>
