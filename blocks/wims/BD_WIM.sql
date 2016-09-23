
CREATE TABLE IF NOT EXISTS `mdl_assignment_sheetwims` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_curso` bigint(11) unsigned NOT NULL,
  `id_assignment` bigint(11) unsigned NOT NULL,
  `id_sheet` bigint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='assignment_sheetwims table retrofitted from MySQL' AUTO_INCREMENT=31 ;


CREATE TABLE IF NOT EXISTS `mdl_config_wim` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_curso` bigint(11) unsigned NOT NULL,
  `ref_classe_wims` bigint(11) unsigned NOT NULL,
  `senha_professor` varchar(16) NOT NULL DEFAULT '',
  `senha_classe` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='config_wim table retrofitted from MySQL' AUTO_INCREMENT=5 ;


CREATE TABLE IF NOT EXISTS `mdl_users_wims` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(11) unsigned NOT NULL,
  `login_wims` varchar(100) NOT NULL DEFAULT '',
  `senha_wims` varchar(32) NOT NULL DEFAULT '',
  `id_curso` bigint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='users_wims table retrofitted from MySQL' AUTO_INCREMENT=35 ;
