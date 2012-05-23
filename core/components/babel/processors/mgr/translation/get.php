<?php
/**
 * @var modX $modx
 * @var Babel $babel
 * @var array $scriptProperties
 */
$babel = $modx->getService('babel','Babel',$modx->getOption('babel.core_path',null,$modx->getOption('core_path').'components/babel/').'model/babel/',$scriptProperties);
if (!($babel instanceof Babel)) return;

/** @var $resource modResource */
$resource = $modx->getObject('modResource', $scriptProperties['id']);
if (!$resource) return $modx->error->failure('No resource found');

$contextKeys = $babel->getGroupContextKeys($resource->get('context_key'));
$currentContextKey = $resource->get('context_key');
//if(!in_array($currentContextKey, $contextKeys)) {
//    // we are not editing a resource within a context defined in Babel
//    break;
//}

//    $linkedResources = $babel->getLinkedResources($resource->get('id'));
//    if(empty($linkedResources)) {
//        /* always be sure that the Babel TV is set */
//        $babel->initBabelTv($resource);
//    }

/* grab manager actions IDs */
$actions = $modx->request->getAllActionIDs();


/* create babel-box with links to translations */
$linkedResources = $babel->getLinkedResources($resource->get('id'));
$outputLanguageItems = '';

$list = array();
foreach($contextKeys as $contextKey) {
    /* for each (valid/existing) context of the context group a button will be displayed */
    /** @var modContext $context */
    $context = $modx->getObject('modContext', array('key' => $contextKey));
    if(!$context) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load context: '.$contextKey);
        //continue;
        return $modx->error->failure('Could not load context: '.$contextKey);
    }
    $context->prepare();
    $cultureKey = $context->getOption('cultureKey', $modx->getOption('cultureKey'));
    /* url to which the form will post it's data */
    $formUrl = '?a='.$actions['resource/update'].'&amp;id='.$resource->get('id');
    if(isset($linkedResources[$contextKey])) {
        /* link to this context has been set */
        if($linkedResources[$contextKey] == $resource->get('id')) {
            /* don't show language layer for current resource */
            $showLayer = '';
        } else {
            $showLayer = 'yes';
        }
        $showTranslateButton = '';
        $showUnlinkButton = 'yes';
        $showSecondRow = '';
        $resourceId = $linkedResources[$contextKey];
        $resourceUrl = '?a='.$actions['resource/update'].'&amp;id='.$resourceId;
        if($resourceId == $resource->get('id')) {
            $className = 'selected';
        } else {
            $className = '';
        }

    } else {
        /* link to this context has not been set yet:
         * -> show button to create translation */
        $showLayer = 'yes';
        $showTranslateButton = 'yes';
        $showUnlinkButton = '';
        $showSecondRow = 'yes';
        $resourceId = '';
        $resourceUrl = '#';
        $className = 'notset';
    }
    $placeholders = array(
        'formUrl' => $formUrl,
        'contextKey' => $contextKey,
        'cultureKey' => $cultureKey,
        'resourceId' => $resourceId,
        'resourceUrl' => $resourceUrl,
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
}

return $modx->error->success('', $list);
