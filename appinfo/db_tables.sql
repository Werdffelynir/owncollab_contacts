CREATE TABLE `oc_collab_addressbook` (
  `id_book` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `uid` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `is_project` int(11) DEFAULT '0',
  `is_private` int(11) DEFAULT '1',
  PRIMARY KEY (`id_book`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `oc_collab_addresscontacts` (
  `id_contact` int(11) NOT NULL AUTO_INCREMENT,
  `fields` varchar(4096) DEFAULT NULL,
  `is_private` int(11) DEFAULT '1',
  PRIMARY KEY (`id_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `oc_collab_addressgroups` (
  `id_group` int(11) NOT NULL AUTO_INCREMENT,
  `id_book` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_private` int(11) DEFAULT '1',
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `oc_collab_address_rel_contacts` (
  `id_rel_contact` int(11) NOT NULL AUTO_INCREMENT,
  `id_group` int(11) DEFAULT NULL,
  `id_contact` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_rel_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `oc_collab_address_share` (
  `id_share` int(11) NOT NULL AUTO_INCREMENT,
  `id_book` int(11) DEFAULT NULL,
  `uid_owner` varchar(255) DEFAULT NULL,
  `uid_with` varchar(255) DEFAULT NULL,
  `is_private` int(11) DEFAULT '1',
  PRIMARY KEY (`id_share`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

