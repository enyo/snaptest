<?php

// begin addon information
$config['name']         = 'Snap Mock DAO Utility';
$config['version']      = '1.0.0';
$config['author']       = 'Jakob Heuser <rjheuser@gaiaonline.com>';
$config['description']  = <<<DESC
Creates mock objects of Gaia Online's DAO. Uses YAML for result sets, and
creates RS Static Query objects with proper data sets.  If any additional
methods are defined in the DAO object, those are also copied over.
DESC;

// begin custom configuration
$config['base_path'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'snapdao'. DIRECTORY_SEPARATOR;
$config['yaml_path'] = $config['base_path'] . 'yaml' . DIRECTORY_SEPARATOR;

// include the base Gaia Common File
// include_once ??

// include the snapdao file
include_once 'snapdao/dao.php';

// give the snapdao class the config
SnapDaoFactory::setConfig($config);