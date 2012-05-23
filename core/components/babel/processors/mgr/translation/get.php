<?php
/**
 * @var modX $modx
 * @var Babel $babel
 * @var array $scriptProperties
 */
$babel =& $modx->babel;
if (!$babel || !($babel instanceof Babel)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'babel not instancianted');

    $babel = $modx->getService('babel', 'Babel', $modx->getOption('babel.core_path', null, $modx->getOption('core_path') . 'components/babel/') . 'model/babel/', $scriptProperties);
    if (!($babel instanceof Babel)) return;
}

/** @var $resource modResource */
$resource = $modx->getObject('modResource', $scriptProperties['id']);
if (!$resource) return $modx->error->failure('No resource found');

$contextKeys = $babel->getGroupContextKeys($resource->get('context_key'));
$currentContextKey = $resource->get('context_key');

$linkedResources = $babel->getLinkedResources($resource->get('id'));
if(empty($linkedResources)) {
    /* always be sure that the Babel TV is set */
    $babel->initBabelTv($resource);
}

/* grab manager actions IDs */
$actions = $modx->request->getAllActionIDs();


/* create babel-box with links to translations */
$linkedResources = $babel->getLinkedResources($resource->get('id'));
$outputLanguageItems = '';

// Active/already translated items
$list = array();
// Languages not yet translated
$notTranslated = array(
    'text' => 'No translations',
    'disabled' => false,
);

foreach($contextKeys as $contextKey) {
    /* for each (valid/existing) context of the context group a button will be displayed */
    /** @var modContext $context */
    $context = $modx->getObject('modContext', array('key' => $contextKey));
    if(!$context) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load context: '.$contextKey);
        continue;
        //return $modx->error->failure('Could not load context: '.$contextKey);
    }
    $context->prepare();
    $cultureKey = $context->getOption('cultureKey', $modx->getOption('cultureKey'));

    /* url to which the form will post it's data */
    //$formUrl = '?a='.$actions['resource/update'].'&amp;id='.$resource->get('id');

    $showLayer = true;
    if(isset($linkedResources[$contextKey])) {
        /* link to this context has been set */
        if($linkedResources[$contextKey] == $resource->get('id')) {
            /* don't show language layer for current resource */
            $showLayer = false;
        }

        $showTranslateButton = false;
        $showUnlinkButton = true;
        $showSecondRow = false;
        $resourceId = $linkedResources[$contextKey];
        //$resourceUrl = '?a='.$actions['resource/update'].'&amp;id='.$resourceId;
        if($resourceId == $resource->get('id')) {
            $className = 'selected';
        } else {
            $className = false;
        }

    } else {
        /* link to this context has not been set yet:
         * -> show button to create translation */
        $showTranslateButton = true;
        $showUnlinkButton = false;
        $showSecondRow = true;
        $resourceId = false;
        //$resourceUrl = '#';
        $className = false;
    }
    $placeholders = array(
        //'formUrl' => $formUrl,
        'contextKey' => $contextKey,
        'cultureKey' => $cultureKey,
        'resourceId' => $resourceId,
        //'resourceUrl' => $resourceUrl,
        'className' => $className,
        'showLayer' => $showLayer,
        'showTranslateButton' => $showTranslateButton,
        'showUnlinkButton' => $showUnlinkButton,
        'showSecondRow' => $showSecondRow,
        //
        'text_label' => $modx->lexicon('babel.language_'. $cultureKey, array('key' => $contextKey)),
        'text_unlink' => $modx->lexicon('babel.unlink_translation'),
        'text_link' => $modx->lexicon('babel.link_translation'),
        'text_create' => $modx->lexicon('babel.create_translation'),
    );

    $list[] = $placeholders;


    // Generate the ExtJS menu from here
/*    $menu = array(
        'text' => $contextKey,
        'disabled' => false,
        'scope' => 'this',
        'iconCls' => $cultureKey .'-lang',
        'ctCls' => 'babel-icon',
    );
    if ($resourceId) {
        $menu['handler'] = 'function() { location.href = "?a='. $actions['resource/update'] .'&id='. $resourceId .'" }';
    }

    // Submenu
    if ($className) {
        $menu['disabled'] = true;
    }
    if ($showLayer) {
        $submenu = array();
        // Unlink
        if ($showUnlinkButton) {
            $submenu[] = array(
                'text' => $modx->lexicon('babel.unlink_translation')
            );
        }
        // Create
        if ($showTranslateButton) {
            $submenu[] = array(
                'text' => $modx->lexicon('babel.create_translation'),
            );
        }
        // Link
        if ($showSecondRow) {
            $submenu[] = array(
                'text' => $modx->lexicon('babel.link_translation'),
            );
        }

        if (count($submenu) >= 1)
            $menu['menu'] = $submenu;
    }



    if (!$showTranslateButton) {
        // Already linked translation
        $list[] = $menu;
    } else {
        // Not created translation
        $notTranslated['menu'][] = $menu;
    }*/
}

// Adding languages without translations linked at the end of the menu
/*if (count($notTranslated['menu']) >= 1) {
    $list[] = '-';
    $list[] = $notTranslated;
}*/

return $modx->error->success('', $list);

//$response = array(
//    'success' => true,
//    'object' => $list,
//);
//
//$regex = '/"handler":"([\w\-\.]+)"/i';
//$replace = '"handler":$1';
//$response = preg_replace($regex, $replace, json_encode($response));
//
//return $response;
