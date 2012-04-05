-- 
-- Table structure for table `ci_sessions`
-- 

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `session_start` int(10) unsigned NOT NULL default '0',
  `session_last_activity` int(10) unsigned NOT NULL default '0',
  `session_ip_address` varchar(16) NOT NULL default '0',
  `session_user_agent` varchar(50) NOT NULL,
  `session_data` text NOT NULL,
  PRIMARY KEY  (`session_id`)
);

-- 
-- Table structure for table `example`
-- 

CREATE TABLE IF NOT EXISTS `example` (
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

-- 
-- Table structure for table `example_related_example`
-- 

CREATE TABLE IF NOT EXISTS `example_related_example` (
  `id` int(11) NOT NULL auto_increment,
  `example_id` int(11) NOT NULL,
  `related_example_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);

-- 
-- Table structure for table `related_example`
-- 

CREATE TABLE IF NOT EXISTS `related_example` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `example_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);

-- 
-- Table structure for table `users`
-- 

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `access_level` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);

-- 
-- Table structure for table `user_records`
-- 

CREATE TABLE IF NOT EXISTS `user_records` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `permissions` varchar(30) NOT NULL,
  `table_name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
);

INSERT INTO `users` VALUES(NULL,'username','".sha1(\'password\')."','3');
INSERT INTO `user_records` VALUES ( NULL ,  '1',  '1',  'owner',  'users');
