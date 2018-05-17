-- Adminer 4.6.2 MySQL dump

CREATE TABLE `rbac_user_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `rbac_user_action` (`id`, `name`, `description`) VALUES
(1,	'main/login', 'authentication page'),
(2,	'main/index', 'main route'),
(3,	'products/index', 'View products'),
(4,	'products/create', 'Create new product'),
(5,	'products/update', 'Update a product'),
(6,	'products/delete', 'Delete a product'),
(7,	'category/add',	'Create new categories.'),
(8,	'category/update', 'Update categories.'),
(9,	'something/else', 'Other permission');

CREATE TABLE `rbac_user_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `rbac_user_role` (`id`, `name`, `description`, `action`) VALUES
(1,	'Administrator', 'Administrator has access to everything', '*'),
(2,	'?', 'Guest/Not logged in user\r\n', '1,2,3'),
(3,	'Other Role', '', '1,2,3,4,5,6');

-- 2018-05-14 09:04:50
