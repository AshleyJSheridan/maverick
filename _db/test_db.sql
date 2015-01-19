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
/*Table structure for table `test` */

DROP TABLE IF EXISTS `test`;

CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_key` varchar(30) NOT NULL,
  `field_value` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `test` */

insert  into `test`(`id`,`field_key`,`field_value`) values (1,'test','sdfsdfsd');
insert  into `test`(`id`,`field_key`,`field_value`) values (2,'another_test','sdfsdf');
insert  into `test`(`id`,`field_key`,`field_value`) values (3,'third','sdfhj');
insert  into `test`(`id`,`field_key`,`field_value`) values (4,'insert bulk test','ash');
insert  into `test`(`id`,`field_key`,`field_value`) values (5,'insert bulk test2','tyutyu');
insert  into `test`(`id`,`field_key`,`field_value`) values (6,'insert bulk test','ash');
insert  into `test`(`id`,`field_key`,`field_value`) values (7,'insert bulk test2','utyu');
insert  into `test`(`id`,`field_key`,`field_value`) values (8,'insert bulk test','ash');
insert  into `test`(`id`,`field_key`,`field_value`) values (9,'insert bulk test2','uiouio');

/*Table structure for table `test2` */

DROP TABLE IF EXISTS `test2`;

CREATE TABLE `test2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `other_value` varchar(50) NOT NULL,
  `display` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `test2` */

insert  into `test2`(`id`,`test_id`,`other_value`,`display`) values (1,1,'foo','yes');
insert  into `test2`(`id`,`test_id`,`other_value`,`display`) values (2,1,'bar','yes');
insert  into `test2`(`id`,`test_id`,`other_value`,`display`) values (3,2,'fizz','yes');
insert  into `test2`(`id`,`test_id`,`other_value`,`display`) values (4,2,'buzz','no');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
