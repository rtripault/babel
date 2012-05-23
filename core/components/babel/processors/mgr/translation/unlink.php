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

$linkedResources = $babel->getLinkedResources($resource->get('id'));
if(empty($linkedResources)) {
    /* always be sure that the Babel TV is set */
    $babel->initBabelTv($resource);
}

/* grab manager actions IDs */
$actions = $modx->request->getAllActionIDs();

$contextKey = $scriptProperties['context_key'];
/* check if context is valid */
$context = $modx->getObject('modContext', array('key' => $contextKey));
if(!$context) {
    $errorParameter = array('context' => $contextKey);
    //throw new Exception('error.invalid_context_key');
    return $modx->error->failure();
}


//$id = $scriptProperties['id'];
//$target = $scriptProperties['target'];

//$modx->log(modX::LOG_LEVEL_ERROR, 'trying to link resource '. $id .' to resource '. $target);

if(!isset($linkedResources[$contextKey])) {
    /* error: there is no link for this context */
    $errorParameter = array('context' => $contextKey);
    //throw new Exception('error.no_link_to_context');
    return $modx->error->failure('No link for this context');
}

if($linkedResources[$contextKey] == $resource->get('id')) {
    /* error: (current) resource can not be unlinked from it's translations */
    $errorParameter = array('context' => $contextKey);
    //throw new Exception('error.unlink_of_selflink_not_possible');
    return $modx->error->failure('Self unlink is not possible');
}

/** @var modResource $unlinkedResource */
$unlinkedResource = $modx->getObject('modResource', intval($linkedResources[$contextKey]));
if(!$unlinkedResource) {
    /* error: invalid resource id */
    $errorParameter = array('resource' => htmlentities($linkedResources[$contextKey]));
    //throw new Exception('error.invalid_resource_id');
    return $modx->error->failure('No link found for that context');
}

if($unlinkedResource->get('context_key') != $contextKey) {
    /* error: resource is of a another context */
//    $errorParameter = array(
//        'resource' => $targetResource->get('id'),
//        'context' => $contextKey);
//    throw new Exception('error.resource_from_other_context');
    return $modx->error->failure('Resource is from another context');
}
/* unlink resource and reset its Babel TV */
$babel->initBabelTv($unlinkedResource);
unset($linkedResources[$contextKey]);
$babel->updateBabelTv($linkedResources, $linkedResources);

$modx->log(modX::LOG_LEVEL_ERROR, 'should be unlinked');


return $modx->error->success('Resources should now be linked');
