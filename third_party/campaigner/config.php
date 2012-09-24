<?php

/**
 * Campaigner NSM Add-on Updater information.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Campaigner
 * @version         4.5.1b1
 */

if ( ! defined('CAMPAIGNER_NAME'))
{
  define('CAMPAIGNER_NAME', 'Campaigner');
  define('CAMPAIGNER_VERSION', '4.5.1b1');
}

$config['name']     = CAMPAIGNER_NAME;
$config['version']  = CAMPAIGNER_VERSION;
$config['nsm_addon_updater']['versions_xml']
  = 'http://experienceinternet.co.uk/software/feeds/campaigner';

/* End of file      : config.php */
/* File location    : third_party/campaigner/config.php */
