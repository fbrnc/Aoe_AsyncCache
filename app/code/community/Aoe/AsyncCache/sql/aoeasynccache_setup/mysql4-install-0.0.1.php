<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('aoeasynccache/asynccache')};
CREATE TABLE IF NOT EXISTS {$this->getTable('aoeasynccache/asynccache')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` tinytext NOT NULL,
  `mode` varchar(250) NOT NULL default '',
  `tags` varchar(250) NOT NULL default '',
  `trace` text NOT NULL default '',
  `status` varchar(250) NOT NULL default '',
  `processed` tinytext,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;	
");

$installer->endSetup();