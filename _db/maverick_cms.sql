/*
SQLyog Community v11.27 (32 bit)
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_form_elements` */

insert  into `maverick_cms_form_elements`(`id`,`form_id`,`element_name`,`type`,`display`,`label`,`placeholder`,`value`,`display_order`,`class`,`html_id`) values (1,2,'name','text','yes','Name','John Smith','',1,'','');
insert  into `maverick_cms_form_elements`(`id`,`form_id`,`element_name`,`type`,`display`,`label`,`placeholder`,`value`,`display_order`,`class`,`html_id`) values (2,2,'email','email','yes','Email','name@email.com','',2,'','');

/*Table structure for table `maverick_cms_form_elements_extra` */

DROP TABLE IF EXISTS `maverick_cms_form_elements_extra`;

CREATE TABLE `maverick_cms_form_elements_extra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `special_type` varchar(50) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_form_elements_extra` */

insert  into `maverick_cms_form_elements_extra`(`id`,`element_id`,`special_type`,`value`) values (1,1,'regex','/^\\p{L}[\\p{L}0-9 \\-\\\']+$/');
insert  into `maverick_cms_form_elements_extra`(`id`,`element_id`,`special_type`,`value`) values (2,1,'between','2:100');
insert  into `maverick_cms_form_elements_extra`(`id`,`element_id`,`special_type`,`value`) values (3,2,'between','5:255');
insert  into `maverick_cms_form_elements_extra`(`id`,`element_id`,`special_type`,`value`) values (4,1,'required','true');
insert  into `maverick_cms_form_elements_extra`(`id`,`element_id`,`special_type`,`value`) values (5,2,'required','true');

/*Table structure for table `maverick_cms_forms` */

DROP TABLE IF EXISTS `maverick_cms_forms`;

CREATE TABLE `maverick_cms_forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `lang` varchar(5) NOT NULL DEFAULT 'en-gb',
  `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_forms` */

insert  into `maverick_cms_forms`(`id`,`name`,`active`,`lang`,`deleted`) values (1,'competition','yes','en-gb','no');
insert  into `maverick_cms_forms`(`id`,`name`,`active`,`lang`,`deleted`) values (2,'contact','yes','en-gb','no');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_logins` */

insert  into `maverick_cms_logins`(`id`,`username`,`ip`,`user_agent`,`login_at`,`successful`) values (1,'admin','127.0.0.1','Mozilla/5.0 (X11; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0','2015-05-13 11:39:02','yes');

/*Table structure for table `maverick_cms_logs` */

DROP TABLE IF EXISTS `maverick_cms_logs`;

CREATE TABLE `maverick_cms_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(11) unsigned NOT NULL,
  `type` enum('info','error') NOT NULL DEFAULT 'info',
  `category` varchar(100) NOT NULL,
  `sub_category` varchar(100) NOT NULL,
  `details` varchar(250) NOT NULL,
  `added_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_logs` */

/*Table structure for table `maverick_cms_permissions` */

DROP TABLE IF EXISTS `maverick_cms_permissions`;

CREATE TABLE `maverick_cms_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_permissions` */

insert  into `maverick_cms_permissions`(`id`,`name`,`description`) values (1,'form','Permissions to access the form admin area');
insert  into `maverick_cms_permissions`(`id`,`name`,`description`) values (2,'form edit','able to edit forms');
insert  into `maverick_cms_permissions`(`id`,`name`,`description`) values (3,'form delete','able to delete forms');

/*Table structure for table `maverick_cms_user_permissions` */

DROP TABLE IF EXISTS `maverick_cms_user_permissions`;

CREATE TABLE `maverick_cms_user_permissions` (
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `maverick_cms_user_permissions` */

insert  into `maverick_cms_user_permissions`(`user_id`,`permission_id`) values (1,1);
insert  into `maverick_cms_user_permissions`(`user_id`,`permission_id`) values (1,2);

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

insert  into `maverick_cms_users`(`id`,`username`,`password`,`email`,`forename`,`surname`,`admin`) values (1,'admin','e3274be5c857fb42ab72d786e281b4b8','ash@ashleysheridan.co.uk','Ashley','Sheridan','yes');
insert  into `maverick_cms_users`(`id`,`username`,`password`,`email`,`forename`,`surname`,`admin`) values (2,'ash','81cf14fabc01b7c88b186ee9e64fd5c5','ash@ashleysheridan.co.uk','Ashley','Sheridan','no');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
