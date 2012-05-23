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


$id = $scriptProperties['id'];
$target = $scriptProperties['target'];

//$modx->log(modX::LOG_LEVEL_ERROR, 'trying to link resource '. $id .' to resource '. $target);

//if(isset($_POST['babel-link'])) {
    if($linkedResources[$contextKey] == $target) {
        /* target resource is equal to current resource -> nothing to do */
        //throw new Exception();
        return $modx->error->failure('Uh ? Linking this resource to itself ? Stop crack pal!');
    }

    /** @var modResource $targetResource */
    $targetResource = $modx->getObject('modResource', intval($target));
    if(!$targetResource) {
        /* error: resource id is not valid */
        $errorParameter = array('resource' => htmlentities($_POST['babel-link-target']));
        //throw new Exception('error.invalid_resource_id');
        return $modx->error->failure('Invalid targeted resource id');
    }

    if($targetResource->get('context_key') != $contextKey) {
        /* error: resource id of another context has been provided */
        $errorParameter = array(
            'resource' => $targetResource->get('id'),
            'context' => $contextKey);
        //throw new Exception('error.resource_from_other_context');
        return $modx->error->failure('Bad context key given (target does not belong to the given ctx)');
    }

    $targetLinkedResources = $babel->getLinkedResources($targetResource->get('id'));
    if(count($targetLinkedResources) > 1) {
        /* error: target resource is already linked with other resources */
        $errorParameter = array('resource' => $targetResource->get('id'));
        //throw new Exception('error.resource_already_linked');
        return $modx->error->failure('Resource already linked');
    }

    /* add or change a translation link */
    if(isset($linkedResources[$contextKey])) {
        /* existing link has been changed:
* -> reset Babel TV of old resource */
        $babel->initBabelTvById($linkedResources[$contextKey]);
    }

    $linkedResources[$contextKey] = $targetResource->get('id');
    $babel->updateBabelTv($linkedResources, $linkedResources);

    /* copy values of synchronized TVs to target resource */
//    if(isset($_POST['babel-link-copy-tvs']) && intval($_POST['babel-link-copy-tvs']) == 1) {
//        $babel->sychronizeTvs($resource->get('id'));
//    }
//}

return $modx->error->success('Resources should now be linked');
