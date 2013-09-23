<?php
$show_results = false;
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
    $errors = array();
    $success = array();
    $file_path = './codex/application/config/database.php';
    if(!file_exists($file_path))
        $errors['insufficient_permissions'] =  "$file_path Doesn't exist.";
    if(!is_writable($file_path))
        $errors['insufficient_permissions'] =  "Insufficient permissions on $file_path. Please fix the permissions.";
    else{
        $fp = fopen($file_path,'w+');
        if(!$fp){
            $errors[] =  "There was a problem opening the database.php file.";
        }
        else{
            fwrite($fp,$file_contents,strlen($file_contents));
            fclose($fp);
            $success['updating_file'] =  "Successfully updated the database.php file.<br>";

            if(empty($first_username) OR empty($first_password))
                $errors[]  = "Username and password are not optional. Please specify them.";
            else{
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
                $sql = 'CREATE TABLE IF NOT EXISTS `access` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) NOT NULL,
                  `access_link` varchar(255) NOT NULL,
                  `link` text NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

                $result = mysql_query($sql);
                
                if(!$result)
                   $warnings['ci_sessions'] = "Problem creating the access table: ".mysql_error();

                $affected_rows = mysql_affected_rows();
                if($affected_rows == 1)
                    $success['ci_sessions'] =  "Successfully created the table (access).";
                //
                $sql = 'CREATE TABLE IF NOT EXISTS `access_level` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

                $result = mysql_query($sql);
                
                if(!$result)
                   $warnings['ci_sessions'] = "Problem creating the access_level table: ".mysql_error();

                $affected_rows = mysql_affected_rows();
                if($affected_rows == 1)
                    $success['ci_sessions'] =  "Successfully created the table (access_level).";


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
                   $warnings['ci_sessions'] = "Problem creating the admin_data_logs table: ".mysql_error();

                $affected_rows = mysql_affected_rows();
                if($affected_rows == 1)
                    $success['ci_sessions'] =  "Successfully created the table (admin_data_logs).";

                $sql = 'CREATE TABLE IF NOT EXISTS `access_access_level` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `access_id` int(11) NOT NULL,
                  `access_level_id` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

                $result = mysql_query($sql);
                
                if(!$result)
                   $warnings['ci_sessions'] = "Problem creating the access_access_level table: ".mysql_error();

                $affected_rows = mysql_affected_rows();
                if($affected_rows == 1)
                    $success['ci_sessions'] =  "Successfully created the table (access_access_level).";
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


                        $sql = "
                                CREATE TABLE `related_example` (
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

                        $sql = "
                                CREATE TABLE `user_records` (
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

                        $sql = "
                                INSERT INTO  `user_records` VALUES ( NULL ,  '1',  '1',  'owner',  'users');
                            ";
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
    } 
    $show_results = true;
}

$base_url = "http://".$_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>CodeExtinguisher Installer</title>
    <meta name="author" content="Majd J Taby">
    <style type="text/css">
    body{
        font-family: "Lucida Grande", Lucida, Verdana, sans-serif;
    }
    input{
        display: block;
        margin: 10px 0 30px 0;
    }
    </style>
</head>
    <body>
        <div class="container">
            <?php
                if($show_results){ ?>
                    <div id="result-top"></div>
                    <div id="result-box">
                        <div id="result-box-bottom">
                            <h3>In total, there were <span class="err-highlight"><?=count($errors)?> errors</span>, <span class="war-highlight"><?=count($warnings)?> warnings</span> and <span class="suc-highlight"><?=count($success)?> success</span> messages. Here's the low-down:</h3>
                            <?php 
                                foreach($errors as $err){
                                    echo '<div class="result-bubble"><b><span class="err-highlight">Error</span></b>: '.$err.'</div>';
                                }
                                foreach($warnings as $err){
                                    echo '<div class="result-bubble"><b><span class="war-highlight">Warning</span></b>: '.$err.'</div>';
                                }
                                foreach($success as $suc){
                                    echo '<div class="result-bubble"><b><span class="suc-highlight">Success</span></b>: '.$suc.'</div>';
                                }

                                if(count($errors) == 0){
                                ?>
                                    <h3><a href="backend.php">Proceed now to your CodeExtinguisher Installation...</a></h3>
                                <?php
                                }
                            ?>
                        </div>
                    </div>
                <?php }
                else{ ?>
                    <form method="POST" action="">
                        <div id="top"></div>
                        <div id="first">
                            <div class="installer-box-contents">
                                <h2>Setup the database configuration:</h2>
                                    <label for="db_host">    <b>Hostname</b>:</label> <input class="textbox" type="text" value="localhost" name="db_host">
                                    <label for="db_username"><b>Username</b>:</label> <input class="textbox" type="text" value="" name="db_username">
                                    <label for="db_password"><b>Password</b>:</label> <input class="textbox" type="password" value="" name="db_password">
                                    <label for="db_database"><b>Database</b>: If the database you specify already exists, <br>nothing happens. However, if the database doesn't <br>exist, it will be created.<br></label> <input class="textbox" type="database" value="" name="db_database">
                            </div>
                        </div>
                        <div id="middle">
                            <div class="installer-box-contents">
                                <h2>Setup the database configuration:</h2>
                                Here you can specify the username and password of <br>the first account that you will need to access the <br>backend with.<br><br>
                                <label for="first_username"><b>Username</b>:</label> <input class="textbox" type="text" value="" name="first_username">
                                <label for="first_password"><b>Password</b>:</label> <input class="textbox" type="password" value="" name="first_password">
                            </div>
                        </div>
                        <div id="last">
                            <input type="submit" value="That's it, you're done. Now just press this button!" id="installer-submit" name="codex_installer_submit">
                        </div>
                    </form>
                <?php }
            ?>
                    </div>
    </body>
</html>