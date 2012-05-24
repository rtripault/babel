<?php
/**
 * @var modX $modx
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$babelCorePath = $modx->getOption('babel.core_path', null, $modx->getOption('core_path') .'components/babel/');
require_once $babelCorePath.'model/babel/babel.class.php';

$modx->babel = new Babel($modx);

$modx->lexicon->load('babel:default');

// handle request
$path = $modx->getOption('processors_path', $modx->babel->config, $babelCorePath.'processors/');

$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));