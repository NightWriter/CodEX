CREATE TABLE IF NOT EXISTS `static_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ind` varchar(50) NOT NULL,
  `title` varchar(45) DEFAULT NULL COMMENT 'заголовок статической страницы',
  `text` text COMMENT 'контент статической страницы',
  `preview` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) DEFAULT NULL COMMENT 'мета-теги описания',
  `meta_keywords` varchar(255) DEFAULT NULL COMMENT 'мета-ключевые слова',
  `alias` varchar(45) DEFAULT NULL COMMENT 'алиас страницы для формирования ссылки на нее',
  `date_cr` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Статические страницы сайта' AUTO_INCREMENT=1;