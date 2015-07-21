/*
SQLyog Community v11.28 (32 bit)
MySQL - 5.5.35 : Database - maverick
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `maverick_cms_form_elements` */

DROP TABLE IF EXISTS `maverick_cms_form_elements`;

CREATE TABLE `maverick_cms_form_elements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(10) unsigned NOT NULL,
  `element_name` varchar(50) NOT NULL,
  `type` enum('checkbox','color','datalist','date','email','file','hidden','number','password','radio','range','select','submit','tel','text','textarea','time','url') NOT NULL DEFAULT 'text',
  `display` enum('yes','no') NOT NULL DEFAULT 'yes',
  `label` varchar(200) NOT NULL,
  `placeholder` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  `display_order` tinyint(3) unsigned NOT NULL,
  `class` varchar(50) NOT NULL,
  `html_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_form_elements` */

insert  into `maverick_cms_form_elements`(`id`,`form_id`,`element_name`,`type`,`display`,`label`,`placeholder`,`value`,`display_order`,`class`,`html_id`) values (111,2,'name','text','yes','Name','John Smith','',1,'',''),(112,2,'email','email','yes','Email','name@email.com','',2,'',''),(113,2,'new element 3','text','yes','','','',3,'',''),(114,2,'new element 4','text','yes','','','',4,'',''),(135,13,'name','text','yes','Name','John Smith','',1,'',''),(136,13,'email','email','yes','Email','name@email.com','',2,'',''),(137,13,'new element 3','text','yes','','','',3,'',''),(138,13,'new element 4','text','yes','','','',4,'',''),(139,14,'name','text','yes','Name','John Smith','',1,'',''),(140,14,'email','email','yes','Email','name@email.com','',2,'',''),(141,14,'new element 3','text','yes','','','',3,'',''),(142,14,'new element 4','text','yes','','','',4,'',''),(147,16,'name','text','yes','Name','John Smith','',1,'',''),(148,16,'email','email','yes','Email','name@email.com','',2,'',''),(149,16,'new element 3','text','yes','','','',3,'','');

/*Table structure for table `maverick_cms_form_elements_extra` */

DROP TABLE IF EXISTS `maverick_cms_form_elements_extra`;

CREATE TABLE `maverick_cms_form_elements_extra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `special_type` varchar(50) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_form_elements_extra` */

insert  into `maverick_cms_form_elements_extra`(`id`,`element_id`,`special_type`,`value`) values (169,111,'regex','/^\\p{L}[\\p{L}0-9 \\-\\\']+$/'),(170,111,'between','2:100'),(171,112,'between','5:255'),(172,135,'regex','/^\\p{L}[\\p{L}0-9 \\-\\\']+$/'),(173,135,'between','2:100'),(174,136,'between','5:255'),(175,139,'regex','/^\\p{L}[\\p{L}0-9 \\-\\\']+$/'),(176,139,'between','2:100'),(177,140,'between','5:255'),(181,147,'regex','/^\\p{L}[\\p{L}0-9 \\-\\\']+$/'),(182,147,'between','2:100'),(183,148,'between','5:255');

/*Table structure for table `maverick_cms_forms` */

DROP TABLE IF EXISTS `maverick_cms_forms`;

CREATE TABLE `maverick_cms_forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `lang` varchar(5) NOT NULL DEFAULT 'en-gb',
  `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_forms` */

insert  into `maverick_cms_forms`(`id`,`name`,`active`,`lang`,`deleted`) values (1,'competition','yes','en-GB','no'),(2,'contact','yes','en-GB','no'),(13,'contact','yes','en-GB','yes'),(14,'contact (copy)','yes','en-GB','yes'),(15,'competition (copy)','yes','en-GB','yes'),(16,'contact (copy)','yes','en-GB','no');

/*Table structure for table `maverick_cms_languages` */

DROP TABLE IF EXISTS `maverick_cms_languages`;

CREATE TABLE `maverick_cms_languages` (
  `culture_name` varchar(10) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `culture_code` varchar(6) NOT NULL,
  `iso_639x` varchar(3) NOT NULL,
  `in_use` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`culture_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_languages` */

insert  into `maverick_cms_languages`(`culture_name`,`display_name`,`culture_code`,`iso_639x`,`in_use`) values ('af-ZA','Afrikaans - South Africa','0x0436','AFK','no'),('ar-AE','Arabic - United Arab Emirates','0x3801','ARU','no'),('ar-BH','Arabic - Bahrain','0x3C01','ARH','no'),('ar-DZ','Arabic - Algeria','0x1401','ARG','no'),('ar-EG','Arabic - Egypt','0x0C01','ARE','no'),('ar-IQ','Arabic - Iraq','0x0801','ARI','no'),('ar-JO','Arabic - Jordan','0x2C01','ARJ','no'),('ar-KW','Arabic - Kuwait','0x3401','ARK','no'),('ar-LB','Arabic - Lebanon','0x3001','ARB','no'),('ar-LY','Arabic - Libya','0x1001','ARL','no'),('ar-MA','Arabic - Morocco','0x1801','ARM','no'),('ar-OM','Arabic - Oman','0x2001','ARO','no'),('ar-QA','Arabic - Qatar','0x4001','ARQ','no'),('ar-SA','Arabic - Saudi Arabia','0x0401','ARA','no'),('ar-SY','Arabic - Syria','0x2801','ARS','no'),('ar-TN','Arabic - Tunisia','0x1C01','ART','no'),('ar-YE','Arabic - Yemen','0x2401','ARY','no'),('be-BY','Belarusian - Belarus','0x0423','BEL','no'),('bg-BG','Bulgarian - Bulgaria','0x0402','BGR','no'),('ca-ES','Catalan - Catalan','0x0403','CAT','no'),('cs-CZ','Czech - Czech Republic','0x0405','CSY','no'),('Cy-az-AZ','Azeri (Cyrillic) - Azerbaijan','0x082C','','no'),('Cy-sr-SP','Serbian (Cyrillic) - Serbia','0x0C1A','','no'),('Cy-uz-UZ','Uzbek (Cyrillic) - Uzbekistan','0x0843','','no'),('da-DK','Danish - Denmark','0x0406','DAN','no'),('de-AT','German - Austria','0x0C07','DEA','no'),('de-CH','German - Switzerland','0x0807','DES','no'),('de-DE','German - Germany','0x0407','','no'),('de-LI','German - Liechtenstein','0x1407','DEC','no'),('de-LU','German - Luxembourg','0x1007','DEL','no'),('div-MV','Dhivehi - Maldives','0x0465','','no'),('el-GR','Greek - Greece','0x0408','ELL','no'),('en-AU','English - Australia','0x0C09','ENA','no'),('en-BZ','English - Belize','0x2809','ENL','no'),('en-CA','English - Canada','0x1009','ENC','no'),('en-CB','English - Caribbean','0x2409','','no'),('en-GB','English - United Kingdom','0x0809','ENG','yes'),('en-IE','English - Ireland','0x1809','ENI','no'),('en-JM','English - Jamaica','0x2009','ENJ','no'),('en-NZ','English - New Zealand','0x1409','ENZ','no'),('en-PH','English - Philippines','0x3409','','no'),('en-TT','English - Trinidad and Tobago','0x2C09','ENT','no'),('en-US','English - United States','0x0409','ENU','no'),('en-ZA','English - South Africa','0x1C09','ENS','no'),('en-ZW','English - Zimbabwe','0x3009','','no'),('es-AR','Spanish - Argentina','0x2C0A','ESS','no'),('es-BO','Spanish - Bolivia','0x400A','ESB','no'),('es-CL','Spanish - Chile','0x340A','ESL','no'),('es-CO','Spanish - Colombia','0x240A','ESO','no'),('es-CR','Spanish - Costa Rica','0x140A','ESC','no'),('es-DO','Spanish - Dominican Republic','0x1C0A','ESD','no'),('es-EC','Spanish - Ecuador','0x300A','ESF','no'),('es-ES','Spanish - Spain','0x0C0A','','no'),('es-GT','Spanish - Guatemala','0x100A','ESG','no'),('es-HN','Spanish - Honduras','0x480A','ESH','no'),('es-MX','Spanish - Mexico','0x080A','ESM','no'),('es-NI','Spanish - Nicaragua','0x4C0A','ESI','no'),('es-PA','Spanish - Panama','0x180A','ESA','no'),('es-PE','Spanish - Peru','0x280A','ESR','no'),('es-PR','Spanish - Puerto Rico','0x500A','ES','no'),('es-PY','Spanish - Paraguay','0x3C0A','ESZ','no'),('es-SV','Spanish - El Salvador','0x440A','ESE','no'),('es-UY','Spanish - Uruguay','0x380A','ESY','no'),('es-VE','Spanish - Venezuela','0x200A','ESV','no'),('et-EE','Estonian - Estonia','0x0425','ETI','no'),('eu-ES','Basque - Basque','0x042D','EUQ','no'),('fa-IR','Farsi - Iran','0x0429','FAR','no'),('fi-FI','Finnish - Finland','0x040B','FIN','no'),('fo-FO','Faroese - Faroe Islands','0x0438','FOS','no'),('fr-BE','French - Belgium','0x080C','FRB','no'),('fr-CA','French - Canada','0x0C0C','FRC','no'),('fr-CH','French - Switzerland','0x100C','FRS','no'),('fr-FR','French - France','0x040C','','no'),('fr-LU','French - Luxembourg','0x140C','FRL','no'),('fr-MC','French - Monaco','0x180C','','no'),('gl-ES','Galician - Galician','0x0456','','no'),('gu-IN','Gujarati - India','0x0447','','no'),('he-IL','Hebrew - Israel','0x040D','HEB','no'),('hi-IN','Hindi - India','0x0439','HIN','no'),('hr-HR','Croatian - Croatia','0x041A','HRV','no'),('hu-HU','Hungarian - Hungary','0x040E','HUN','no'),('hy-AM','Armenian - Armenia','0x042B','','no'),('id-ID','Indonesian - Indonesia','0x0421','','no'),('is-IS','Icelandic - Iceland','0x040F','ISL','no'),('it-CH','Italian - Switzerland','0x0810','ITS','no'),('it-IT','Italian - Italy','0x0410','','no'),('ja-JP','Japanese - Japan','0x0411','JPN','no'),('ka-GE','Georgian - Georgia','0x0437','','no'),('kk-KZ','Kazakh - Kazakhstan','0x043F','','no'),('kn-IN','Kannada - India','0x044B','','no'),('ko-KR','Korean - Korea','0x0412','KOR','no'),('kok-IN','Konkani - India','0x0457','','no'),('ky-KZ','Kyrgyz - Kazakhstan','0x0440','','no'),('Lt-az-AZ','Azeri (Latin) - Azerbaijan','0x042C','','no'),('lt-LT','Lithuanian - Lithuania','0x0427','LTH','no'),('Lt-sr-SP','Serbian (Latin) - Serbia','0x081A','','no'),('Lt-uz-UZ','Uzbek (Latin) - Uzbekistan','0x0443','','no'),('lv-LV','Latvian - Latvia','0x0426','LVI','no'),('mk-MK','Macedonian (FYROM)','0x042F','MKD','no'),('mn-MN','Mongolian - Mongolia','0x0450','','no'),('mr-IN','Marathi - India','0x044E','','no'),('ms-BN','Malay - Brunei','0x083E','','no'),('ms-MY','Malay - Malaysia','0x043E','','no'),('nb-NO','Norwegian (Bokmål) - Norway','0x0414','','no'),('nl-BE','Dutch - Belgium','0x0813','NLB','no'),('nl-NL','Dutch - The Netherlands','0x0413','','no'),('nn-NO','Norwegian (Nynorsk) - Norway','0x0814','','no'),('pa-IN','Punjabi - India','0x0446','','no'),('pl-PL','Polish - Poland','0x0415','PLK','no'),('pt-BR','Portuguese - Brazil','0x0416','PTB','no'),('pt-PT','Portuguese - Portugal','0x0816','','no'),('ro-RO','Romanian - Romania','0x0418','ROM','no'),('ru-RU','Russian - Russia','0x0419','RUS','no'),('sa-IN','Sanskrit - India','0x044F','','no'),('sk-SK','Slovak - Slovakia','0x041B','SKY','no'),('sl-SI','Slovenian - Slovenia','0x0424','SLV','no'),('sq-AL','Albanian - Albania','0x041C','SQI','no'),('sv-FI','Swedish - Finland','0x081D','SVF','no'),('sv-SE','Swedish - Sweden','0x041D','','no'),('sw-KE','Swahili - Kenya','0x0441','','no'),('syr-SY','Syriac - Syria','0x045A','','no'),('ta-IN','Tamil - India','0x0449','','no'),('te-IN','Telugu - India','0x044A','','no'),('th-TH','Thai - Thailand','0x041E','THA','no'),('tr-TR','Turkish - Turkey','0x041F','TRK','no'),('tt-RU','Tatar - Russia','0x0444','','no'),('uk-UA','Ukrainian - Ukraine','0x0422','UKR','no'),('ur-PK','Urdu - Pakistan','0x0420','URD','no'),('vi-VN','Vietnamese - Vietnam','0x042A','VIT','no'),('zh-CHS','Chinese (Simplified)','0x0004','','no'),('zh-CHT','Chinese (Traditional)','0x7C04','','no'),('zh-CN','Chinese - China','0x0804','CHS','no'),('zh-HK','Chinese - Hong Kong SAR','0x0C04','ZHH','no'),('zh-MO','Chinese - Macau SAR','0x1404','','no'),('zh-SG','Chinese - Singapore','0x1004','ZHI','no'),('zh-TW','Chinese - Taiwan','0x0404','CHT','no');

/*Table structure for table `maverick_cms_logins` */

DROP TABLE IF EXISTS `maverick_cms_logins`;

CREATE TABLE `maverick_cms_logins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(250) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `user_agent` varchar(250) NOT NULL,
  `login_at` datetime NOT NULL,
  `successful` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_logins` */

insert  into `maverick_cms_logins`(`id`,`username`,`ip`,`user_agent`,`login_at`,`successful`) values (1,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-05-13 11:39:02','yes'),(2,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-01 20:28:24','yes'),(3,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 20:25:13','yes'),(4,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 20:25:22','yes'),(5,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 20:31:39','yes'),(6,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 21:32:06','yes'),(7,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:14:37','yes'),(8,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:44:09','yes'),(9,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:44:22','yes'),(10,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:50:46','yes'),(11,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:50:50','yes'),(12,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:51:02','yes'),(13,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-22 23:58:28','yes'),(14,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-23 00:05:18','yes'),(15,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-24 20:37:43','yes'),(16,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-28 17:40:35','yes'),(17,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-06-28 20:50:42','yes'),(18,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-01 15:55:40','yes'),(19,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-05 15:08:14','yes'),(20,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-05 15:13:47','yes'),(21,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-06 23:45:16','yes'),(22,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-09 10:29:13','yes'),(23,'test','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-09 13:15:35',''),(24,'test','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-09 13:15:45',''),(25,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-09 13:16:22','yes'),(26,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-09 20:06:19','yes'),(27,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-12 09:32:54','yes'),(28,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-14 19:08:41','yes'),(29,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-14 19:26:39','yes'),(30,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-17 21:03:14','yes'),(31,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-07-20 19:44:36','yes');

/*Table structure for table `maverick_cms_logs` */

DROP TABLE IF EXISTS `maverick_cms_logs`;

CREATE TABLE `maverick_cms_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) unsigned NOT NULL,
  `type` enum('info','error') NOT NULL DEFAULT 'info',
  `category` varchar(100) NOT NULL,
  `sub_category` varchar(100) NOT NULL,
  `details` varchar(250) DEFAULT NULL,
  `added_at` datetime NOT NULL,
  `request_data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_logs` */

insert  into `maverick_cms_logs`(`id`,`user_id`,`type`,`category`,`sub_category`,`details`,`added_at`,`request_data`) values (5,1,'info','permissions','update from code','null','2015-07-09 13:59:33','[]'),(6,1,'info','permissions','update from code','null','2015-07-09 13:59:59','[]'),(7,1,'info','permissions','update manual','{\"id\":[\"1\",\"19\",\"3\",\"18\",\"2\",\"16\",\"15\",\"12\",\"26\",\"27\",\"28\",\"25\",\"39\",\"24\",\"\"],\"name\":[\"form\",\"form_copy\",\"form_delete\",\"form_delete_full\",\"form_edit\",\"form_new\",\"form_undelete\",\"user\",\"user_create\",\"user_delete\",\"user_edit\",\"user_list_permissions\",\"u','2015-07-09 14:00:16','{\"id\":[\"1\",\"19\",\"3\",\"18\",\"2\",\"16\",\"15\",\"12\",\"26\",\"27\",\"28\",\"25\",\"39\",\"24\",\"\"],\"name\":[\"form\",\"form_copy\",\"form_delete\",\"form_delete_full\",\"form_edit\",\"form_new\",\"form_undelete\",\"user\",\"user_create\",\"user_delete\",\"user_edit\",\"user_list_permissions\",\"user_new_permission\",\"user_update_permissions\",\"test\"],\"description\":[\"Permissions to access the form admin area\",\"able to duplicate forms\",\"able to mark a form as deleted in the db\",\"able to delete forms\",\"able to edit forms\",\"able to create new forms\",\"able to undelete forms marked in the db\",\"able to view the list of users\",\"able to create new users\",\"able to delete users\",\"able to edit users\",\"able to list all permissions within the cms\",\"able to edit permissions manually\",\"able to update the list of permissions from those used in code files\",\"test\"]}'),(8,1,'info','permissions','deletion','{\"permission_id\":\"208\"}','2015-07-09 14:00:57','[]'),(9,1,'info','permissions','update manual','{\"id\":[\"1\",\"19\",\"3\",\"18\",\"2\",\"16\",\"15\",\"12\",\"26\",\"27\",\"28\",\"25\",\"39\",\"24\"],\"name\":[\"form\",\"form_copy\",\"form_delete\",\"form_delete_full\",\"form_edit\",\"form_new\",\"form_undelete\",\"user\",\"user_create\",\"user_delete\",\"user_edit\",\"user_list_permissions\",\"user','2015-07-09 14:01:03','{\"id\":[\"1\",\"19\",\"3\",\"18\",\"2\",\"16\",\"15\",\"12\",\"26\",\"27\",\"28\",\"25\",\"39\",\"24\"],\"name\":[\"form\",\"form_copy\",\"form_delete\",\"form_delete_full\",\"form_edit\",\"form_new\",\"form_undelete\",\"user\",\"user_create\",\"user_delete\",\"user_edit\",\"user_list_permissions\",\"user_new_permission\",\"user_update_permissions\"],\"description\":[\"Permissions to access the form admin area\",\"able to duplicate forms\",\"able to mark a form as deleted in the db\",\"able to delete forms\",\"able to edit forms\",\"able to create new forms\",\"able to undelete forms marked in the db\",\"able to view the list of users\",\"able to create new users\",\"able to delete users\",\"able to edit users\",\"able to list all permissions within the cms\",\"able to edit permissions manually\",\"able to update the list of permissions from those used in code files\"]}'),(10,1,'info','forms','undeleted','{\"form_id\":\"13\"}','2015-07-09 14:01:27','[]'),(11,1,'info','forms','deleted (soft)','{\"form_id\":\"14\"}','2015-07-09 14:01:39','[]'),(12,1,'info','forms','deleted (soft)','{\"form_id\":\"13\"}','2015-07-09 14:01:43','[]'),(13,1,'info','forms','duplicated','{\"form_id\":\"2\"}','2015-07-09 14:01:45','[]'),(14,1,'info','users','updated','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:29:01','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(15,1,'info','users','updated','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:29:17','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(16,1,'info','users','updated','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:43:33','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(17,1,'info','users','updated','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:46:16','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(18,1,'info','users','updated','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:53:58','{\"username\":\"ash\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(19,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:54:23','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(20,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:55:25','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(21,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 20:55:36','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(22,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 21:03:05','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(23,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 21:03:20','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(24,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 21:09:49','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(25,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 21:10:18','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"password\",\"password_confirm\":\"password\",\"permissions\":[\"1\",\"3\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(26,1,'info','users','updated','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"19\",\"3\",\"18\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}','2015-07-09 21:10:25','{\"username\":\"ash2\",\"forename\":\"Ashley\",\"surname\":\"Sheridan\",\"email\":\"ash@ashleysheridan.co.uk\",\"password\":\"\",\"password_confirm\":\"\",\"permissions\":[\"1\",\"19\",\"3\",\"18\",\"2\",\"16\",\"15\"],\"submit\":\"save user\"}'),(27,1,'info','forms','updated','{\"form_name\":\"contact (copy)\",\"lang\":\"en-GB\",\"id\":[\"143\",\"144\",\"145\"],\"type\":[\"text\",\"email\",\"text\"],\"name\":[\"name\",\"email\",\"new element 3\"],\"label\":[\"Name\",\"Email\",\"\"],\"value\":[\"\",\"\",\"\"],\"display_order\":[\"1\",\"2\",\"3\"],\"class\":[\"\",\"\",\"\"],\"html_id\":[\"\"','2015-07-14 20:10:11','{\"form_name\":\"contact (copy)\",\"lang\":\"en-GB\",\"id\":[\"143\",\"144\",\"145\"],\"type\":[\"text\",\"email\",\"text\"],\"name\":[\"name\",\"email\",\"new element 3\"],\"label\":[\"Name\",\"Email\",\"\"],\"value\":[\"\",\"\",\"\"],\"display_order\":[\"1\",\"2\",\"3\"],\"class\":[\"\",\"\",\"\"],\"html_id\":[\"\",\"\",\"\"],\"placeholder\":[\"John Smith\",\"name@email.com\",\"\"],\"display\":[\"on\",\"on\",\"on\"],\"regex\":[\"\\/^\\\\p{L}[\\\\p{L}0-9 \\\\-\\\\\']+$\\/\",\"\",\"\"],\"min\":[\"2\",\"5\",\"\"],\"max\":[\"100\",\"255\",\"\"]}'),(28,1,'info','forms','undeleted','{\"form_id\":\"15\"}','2015-07-14 20:10:22','[]'),(29,1,'info','forms','deleted (soft)','{\"form_id\":\"15\"}','2015-07-14 20:10:27','[]'),(30,1,'info','permissions','update from code','null','2015-07-21 21:02:40','[]');

/*Table structure for table `maverick_cms_nav` */

DROP TABLE IF EXISTS `maverick_cms_nav`;

CREATE TABLE `maverick_cms_nav` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `view` varchar(50) NOT NULL DEFAULT 'nav',
  `display` enum('yes','no') NOT NULL DEFAULT 'yes',
  `lang` varchar(5) NOT NULL DEFAULT 'en-gb',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_nav` */

insert  into `maverick_cms_nav`(`id`,`name`,`view`,`display`,`lang`) values (1,'admin_nav','nav','yes','en-gb');

/*Table structure for table `maverick_cms_nav_elements` */

DROP TABLE IF EXISTS `maverick_cms_nav_elements`;

CREATE TABLE `maverick_cms_nav_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nav_id` smallint(6) unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `uri` varchar(250) CHARACTER SET latin1 NOT NULL,
  `page_id` int(11) unsigned NOT NULL,
  `order` tinyint(4) unsigned NOT NULL,
  `class` varchar(20) NOT NULL,
  `display` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_nav_elements` */

insert  into `maverick_cms_nav_elements`(`id`,`nav_id`,`name`,`uri`,`page_id`,`order`,`class`,`display`) values (7,1,'Privacy','http://www.unileverprivacypolicy.com/en_gb/policy.aspx',0,2,'','yes');

/*Table structure for table `maverick_cms_page_content` */

DROP TABLE IF EXISTS `maverick_cms_page_content`;

CREATE TABLE `maverick_cms_page_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL,
  `content_order` mediumint(9) unsigned NOT NULL,
  `content` text,
  `display` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`,`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_page_content` */

/*Table structure for table `maverick_cms_page_tags` */

DROP TABLE IF EXISTS `maverick_cms_page_tags`;

CREATE TABLE `maverick_cms_page_tags` (
  `page_id` int(11) unsigned NOT NULL,
  `tag_id` int(11) unsigned NOT NULL,
  `tag_group` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`page_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_page_tags` */

/*Table structure for table `maverick_cms_pages` */

DROP TABLE IF EXISTS `maverick_cms_pages`;

CREATE TABLE `maverick_cms_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_name` varchar(250) NOT NULL,
  `page_url` varchar(250) NOT NULL,
  `status` enum('draft','live','deleted') NOT NULL DEFAULT 'draft',
  `template_id` mediumint(9) unsigned NOT NULL,
  `added_at` datetime NOT NULL,
  `last_edit` datetime NOT NULL,
  `template` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_pages` */

insert  into `maverick_cms_pages`(`id`,`page_name`,`page_url`,`status`,`template_id`,`added_at`,`last_edit`,`template`) values (1,'home','/','draft',0,'2015-07-20 21:03:17','2015-07-20 21:03:17','no');

/*Table structure for table `maverick_cms_permissions` */

DROP TABLE IF EXISTS `maverick_cms_permissions`;

CREATE TABLE `maverick_cms_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_permissions` */

insert  into `maverick_cms_permissions`(`id`,`name`,`description`) values (1,'form','Permissions to access the form admin area'),(2,'form_edit','able to edit forms'),(3,'form_delete','able to mark a form as deleted in the db'),(12,'user','able to view the list of users'),(15,'form_undelete','able to undelete forms marked in the db'),(16,'form_new','able to create new forms'),(18,'form_delete_full','able to delete forms'),(19,'form_copy','able to duplicate forms'),(24,'user_update_permissions','able to update the list of permissions from those used in code files'),(25,'user_list_permissions','able to list all permissions within the cms'),(26,'user_create','able to create new users'),(27,'user_delete','able to delete users'),(28,'user_edit','able to edit users'),(39,'user_new_permission','able to edit permissions manually'),(47,'logs',''),(48,'tags','');

/*Table structure for table `maverick_cms_tag_groups` */

DROP TABLE IF EXISTS `maverick_cms_tag_groups`;

CREATE TABLE `maverick_cms_tag_groups` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_tag_groups` */

insert  into `maverick_cms_tag_groups`(`id`,`group_name`) values (1,'fruits'),(2,'uk');

/*Table structure for table `maverick_cms_tags` */

DROP TABLE IF EXISTS `maverick_cms_tags`;

CREATE TABLE `maverick_cms_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `group_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_tags` */

insert  into `maverick_cms_tags`(`id`,`tag`,`group_id`) values (1,'apple',1),(2,'banana',1),(3,'pear',1),(4,'orange',1),(5,'England',2),(6,'Ireland',2),(7,'Scotland',2),(8,'Wales',2),(9,'page',NULL),(10,'blog',NULL),(11,'portfolio',NULL);

/*Table structure for table `maverick_cms_user_permissions` */

DROP TABLE IF EXISTS `maverick_cms_user_permissions`;

CREATE TABLE `maverick_cms_user_permissions` (
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_user_permissions` */

insert  into `maverick_cms_user_permissions`(`user_id`,`permission_id`) values (2,1),(2,2),(2,3),(2,15),(2,16),(2,18),(2,19);

/*Table structure for table `maverick_cms_users` */

DROP TABLE IF EXISTS `maverick_cms_users`;

CREATE TABLE `maverick_cms_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `forename` varchar(100) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `admin` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_users` */

insert  into `maverick_cms_users`(`id`,`username`,`password`,`email`,`forename`,`surname`,`admin`) values (1,'admin','e3274be5c857fb42ab72d786e281b4b8','ash@ashleysheridan.co.uk','Ashley','Sheridan','yes'),(2,'ash2','d89b4b1c28556f8e23828101ecd28665','ash@ashleysheridan.co.uk','Ashley','Sheridan','no');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
