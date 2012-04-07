<?php
$show_results = false;

$errors = array();
//    
if(!file_exists('./codex/application/definitions/'))
    $errors['perms'] = 'Не существует директории ./codex/application/definitions';
if(!is_writable('./codex/application/definitions/'))
    $errors['perms'] = 'Нет прав на запись в папке ./codex/application/definitions';
if(!file_exists('./application/modules/'))
    $data['errors'][] = 'Не существует директории ./application/modules';
if(!is_writable('./application/modules/'))
    $data['errors'][] = 'Нет прав на запись в папке ./application/modules';
//    
if(isset($_POST['codex_installer_submit'])){

    //Setup the variables
    $db_host = $_POST["db_host"];
    $db_username = $_POST["db_username"];
    $db_password = $_POST["db_password"];
    $db_database = $_POST["db_database"];
    $first_username = $_POST["first_username"];
    $first_password = $_POST["first_password"];

    $file_contents=<<<EOD
<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|    ['hostname'] The hostname of your database server.
|    ['username'] The username used to connect to the database
|    ['password'] The password used to connect to the database
|    ['database'] The name of the database you want to connect to
|    ['dbdriver'] The database type. ie: mysql.  Currently supported:
                 mysql, mysqli, postgre, odbc, mssql
|    ['dbprefix'] You can add an optional prefix, which will be added
|                 to the table name when using the  Active Record class
|    ['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|    ['db_debug'] TRUE/FALSE - Whether database warnings should be displayed.
|    ['active_r'] TRUE/FALSE - Whether to load the active record class
|    ['cache_on'] TRUE/FALSE - Enables/disables query caching
|    ['cachedir'] The path to the folder where cache files should be stored
|
| The \$active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
*/

\$active_group = "default";

\$db['default']['hostname'] = "$db_host";
\$db['default']['username'] = "$db_username";
\$db['default']['password'] = "$db_password";
\$db['default']['database'] = "$db_database";
\$db['default']['dbdriver'] = "mysql";
\$db['default']['dbprefix'] = "";
\$db['default']['active_r'] = TRUE;
\$db['default']['pconnect'] = TRUE;
\$db['default']['db_debug'] = TRUE;
\$db['default']['cache_on'] = FALSE;
\$db['default']['cachedir'] = "";
\$db['default']['char_set'] = "utf8";
\$db['default']['dbcollat'] = "utf8_unicode_ci";


?>
EOD;
    $warnings = array();
    
    $success = array();
    $file_path = './codex/application/config/database.php';
    if(!file_exists($file_path))
        $errors['insufficient_permissions'] =  "$file_path Doesn't exist.";
    if(!is_writable($file_path))
        $errors['insufficient_permissions'] =  "Insufficient permissions on $file_path. Please fix the permissions.";
    else{
            
        if(empty($first_username))
        {
            $errors['first_username']  = "Username are not optional. Please specify them.";
        }
        if(empty($first_password)){
            $errors['first_password']  = "Password are not optional. Please specify them.";
        }
        if(empty($db_host)){
            $errors['db_host']  = "Hostname are not optional. Please specify them.";
        }
        if(empty($db_database)){
            $errors['db_database']  = "Database are not optional. Please specify them.";
        }
        if(empty($errors)){
            
            $fp = fopen($file_path,'w+');
            if(!$fp){
                $errors[] =  "There was a problem opening the database.php file.";
            }else{
                fwrite($fp,$file_contents,strlen($file_contents));
                fclose($fp);
                $success['updating_file'] =  "Successfully updated the database.php file.<br>";
            }
            
            $db = @mysql_connect($db_host,$db_username,$db_password);
            if(!$db)
                $errors[] = "Problem connecting to the database. Make sure you database host, username, pass, and database name are correct.";
            else{
                if(!mysql_select_db($db_database)){
                    $sql = "CREATE DATABASE $db_database";
                    $result = mysql_query($sql);
                    if($result){
                        $success[] = "Successfully created the new database.";
                        mysql_select_db($db_database);
                    }
                    else
                        $errors[] = "Problem creating the new database. Do you have enough privleges to do so?";
                }
///
            $sql = 'CREATE TABLE  `dictionaries` (
                     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                     `alias_table` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                     `desc` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                    PRIMARY KEY (  `id` )
                    ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

            $result = mysql_query($sql);
            
            if(!$result)
               $warnings['dictionaries'] = "Problem creating the dictionaries table: ".mysql_error();
            
            $affected_rows = mysql_affected_rows();
            if($affected_rows == 1)
                $success['dictionaries'] =  "Successfully created the table (dictionaries).";
                
            $sql = 'CREATE TABLE IF NOT EXISTS `access` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              `link` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

            $result = mysql_query($sql);
            
            if(!$result)
               $warnings['access'] = "Problem creating the access table: ".mysql_error();

            $affected_rows = mysql_affected_rows();
            if($affected_rows == 1)
                $success['access'] =  "Successfully created the table (access).";
            //
            $sql = 'CREATE TABLE IF NOT EXISTS `access_level` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

            $result = mysql_query($sql);
            
            if(!$result)
               $warnings['access_level'] = "Problem creating the access_level table: ".mysql_error();

            $affected_rows = mysql_affected_rows();
            if($affected_rows == 1)
                $success['access_level'] =  "Successfully created the table (access_level).";


            $sql = 'CREATE TABLE IF NOT EXISTS `admin_data_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `date` datetime NOT NULL,
              `user_id` int(11) NOT NULL,
              `table` varchar(100) NOT NULL,
              `id_records` int(11) NOT NULL,
              `before` text NOT NULL,
              `after` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

            $result = mysql_query($sql);
            
            if(!$result)
               $warnings['admin_data_logs'] = "Problem creating the admin_data_logs table: ".mysql_error();

            $affected_rows = mysql_affected_rows();
            if($affected_rows == 1)
                $success['admin_data_logs'] =  "Successfully created the table (admin_data_logs).";

            $sql = 'CREATE TABLE IF NOT EXISTS `access_access_level` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `access_id` int(11) NOT NULL,
              `access_level_id` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

            $result = mysql_query($sql);
            
            if(!$result)
               $warnings['access_access_level'] = "Problem creating the access_access_level table: ".mysql_error();

            $affected_rows = mysql_affected_rows();
            if($affected_rows == 1)
                $success['access_access_level'] =  "Successfully created the table (access_access_level).";
///
                    $sql = "
                            CREATE TABLE `ci_sessions` (
                              `session_id` varchar(40) NOT NULL default '0',
                              `session_start` int(10) unsigned NOT NULL default '0',
                              `session_last_activity` int(10) unsigned NOT NULL default '0',
                              `session_ip_address` varchar(16) NOT NULL default '0',
                              `session_user_agent` varchar(50) NOT NULL,
                              `session_data` text NOT NULL,
                              PRIMARY KEY  (`session_id`)
                            );
                                                    
                    ";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['ci_sessions'] = "Problem creating the ci_sessions table: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['ci_sessions'] =  "Successfully created the table to handle sessions (ci_sessions).";

                    $sql = "
                            CREATE TABLE `example` (
                              `my_id` int(11) NOT NULL auto_increment,
                              `checkbox_test` set('yes','no') NOT NULL,
                              `date_test` date NOT NULL,
                              `dbdropdown_test` varchar(100) NOT NULL,
                              `dropdown_test` varchar(100) NOT NULL,
                              `hidden_test` varchar(100) NOT NULL,
                              `password_test` varchar(100) NOT NULL,
                              `radiogroup_test` varchar(100) NOT NULL,
                              `sessiondata_test` varchar(100) NOT NULL,
                              `textarea_test` text NOT NULL,
                              `textbox_test` varchar(100) NOT NULL,
                              `time_test` int(11) NOT NULL,
                              `file_test` varchar(100) NOT NULL,
                              `image_test` varchar(100) NOT NULL,
                              PRIMARY KEY  (`my_id`)
                            );
                             
                        ";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['example'] = "Problem creating the example table: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['example'] =  "Successfully created the example table (example).";


                    $sql = "CREATE TABLE `related_example` (
                              `id` int(11) NOT NULL auto_increment,
                              `name` varchar(200) NOT NULL,
                              `description` text NOT NULL,
                              `example_id` int(11) NOT NULL,
                              PRIMARY KEY  (`id`)
                            );
                        ";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['related_example'] = "Problem creating the related_example table: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['related_example'] =  "Successfully created the related_example table (related_example).";

                    $sql = "CREATE TABLE `users` (
                              `id` int(11) NOT NULL auto_increment,
                              `username` varchar(40) NOT NULL,
                              `password` varchar(40) NOT NULL,
                              `access_level` int(11) NOT NULL,
                              PRIMARY KEY  (`id`)
                            );";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['users'] = "Problem creating the users table: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['users'] = "Successfully created the table to handle users (users).";

                    $sql = "INSERT INTO users VALUES(NULL,'$first_username','".sha1($first_password)."','3')";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['new_user'] = "Problem adding the new user to the database: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['new_user'] =  "Successfully created the new account. <br>";

                    $sql = "CREATE TABLE `user_records` (
                              `id` int(11) NOT NULL auto_increment,
                              `user_id` int(11) NOT NULL,
                              `record_id` int(11) NOT NULL,
                              `permissions` varchar(30) NOT NULL,
                              `table_name` varchar(250) NOT NULL,
                              PRIMARY KEY  (`id`)
                            );
                    ";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['users_records'] = "Problem creating the user records table: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['users_records'] = "Successfully created the table to handle user permissions (users_records).";

                    $sql = "INSERT INTO  `user_records` VALUES ( NULL ,  '1',  '1',  'owner',  'users');";
                    $result = mysql_query($sql);
                    if(!$result)
                       $warnings['new_user_record'] = "Problem adding the new permissions for the new user: ".mysql_error();

                    $affected_rows = mysql_affected_rows();
                    if($affected_rows == 1)
                        $success['new_user_record'] =  "Successfully created the permissions for the new account. <br>";
                mysql_close();
            }
        }
    } 
    $show_results = true;
}

$base_url = "http://".$_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>CodEX Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="./codex/assets/alterego/css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <link href="./codex/assets/alterego/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

  </head>

  <body>
  <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <span class="brand">CodEX Installer</span>
          <div class="nav-collapse">
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">
  
        
            <?php
                if($show_results && empty($errors)){ ?>
                    <div class="container"><div id="result-top"></div>
                    <div id="result-box">
                        <div id="result-box-bottom">
                            <h2>In total, there were <span class="err-highlight"><?=count($errors)?> errors</span>, <span class="war-highlight"><?=count($warnings)?> warnings</span> and <span class="suc-highlight"><?=count($success)?> success</span> messages. Here's the low-down:</h2>
                            <?php 
                                foreach($warnings as $err){
                                    echo '<p><strong>Warning</strong>: '.$err.'</p>';
                                }
                                foreach($success as $suc){
                                    echo '<p><strong>Success</strong>: '.$suc.'</p>';
                                }

                                if(count($errors) == 0){
                                ?>
                                <div class="form-actions">
                                    <a href="backend.php" class="btn btn-primary">Proceed to adminpanel</a>
                                </div>
                                <?php
                                }
                            ?>
                        </div>
                    </div></div>
                <?php }else{ 
                    
                    if(!empty($errors))
                    {
                        foreach($errors as $k=>$err)
                        {
                            if(!in_array($k,array('db_host','db_database','first_username','first_password'),true))
                            echo '<p><strong>Error</strong>: '.$err.'</p>';
                        }
                    }
                    ?>
                
                <form method="post" class="form-horizontal">
                <fieldset>
                      <legend>Database settings</legend>
                    <div class="control-group <? if(!empty($errors['db_host'])): ?>error<? endif ?>">
                        <label class="control-label" for="hostname">Hostname *</label>
                        <div class="controls">
                            <input type="text" value="<?=((!empty($_POST['db_host']))?$_POST['db_host']:'localhost')?>" name="db_host" id="hostname" /> 
                            <? if(!empty($errors['db_host'])): ?>
                                <span class="help-inline"><?=$errors['first_password']?></span>
                            <? endif ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="username">Username</label>
                        <div class="controls">
                            <input type="text" name="db_username" value="<?=((!empty($_POST['db_username']))?$_POST['db_username']:'')?>" id="username" /> 
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="password">Password</label>
                        <div class="controls">
                            <input type="password" name="db_password" value="<?=((!empty($_POST['db_password']))?$_POST['db_password']:'')?>" id="password" /> 
                        </div>
                    </div>
                    <div class="control-group <? if(!empty($errors['db_database'])): ?>error<? endif ?>">
                        <label class="control-label" for="database">Database *</label>
                        <div class="controls">
                            <input type="text" name="db_database" value="<?=((!empty($_POST['db_database']))?$_POST['db_database']:'')?>" id="database" /> 
                            <span class="help-inline">
                            <? if(!empty($errors['db_database'])): ?>
                                Error in database name
                            <? else: ?>
                                If the database you specify already exists, nothing happens. However, if the database doesn't exist, it will be created
                            <? endif ?>
                            </span>
                            
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                      <legend>Administrator's account</legend>
                    <div class="control-group <? if(!empty($errors['first_username'])): ?>error<? endif ?>">  
                        <label class="control-label" for="useradmin">Username *</label>
                        <div class="controls">
                            <input type="text" name="first_username" value="<?=((!empty($_POST['first_username']))?$_POST['first_username']:'')?>" id="useradmin" /> 
                            <? if(!empty($errors['first_username'])): ?>
                                <span class="help-inline"><?=$errors['first_username']?></span>
                            <? endif ?>
                        </div>
                    </div>
                    <div class="control-group <? if(!empty($errors['first_password'])): ?>error<? endif ?>"> 
                        <label class="control-label" for="passadmin">Password *</label>
                        <div class="controls">    
                            <input type="password" name="first_password" value="<?=((!empty($_POST['first_password']))?$_POST['first_password']:'')?>" id="passadmin" />  
                            <? if(!empty($errors['first_password'])): ?>
                                <span class="help-inline"><?=$errors['first_password']?></span>
                            <? endif ?>
                        </div>
                    </div>
                </fieldset> 
                <div class="form-actions">
                    <button type="submit" name="codex_installer_submit" class="btn btn-primary">Save and continue</button>    
                </div>
                </form>
        <?php } ?>
        </div> <!-- /container -->
    </body>
</html>