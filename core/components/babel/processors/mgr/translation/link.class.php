<?php
require_once (dirname(dirname(dirname(dirname(__FILE__)))) .'/model/babel/babelprocessor.class.php');
class linkBabelTranslation extends BabelProcessor {
    public $contextKey;
    public $target;

    public function process() {
        $this->contextKey = $this->getProperty('context_key');
        $this->target = $this->getProperty('target');

        return $this->link();
//        $response = $this->link();
//        if ($response) {
//            $this->modx->log(modX::LOG_LEVEL_ERROR, 'got a good answer');
//            return $this->success('Translation should now be linked');
//        }
//
//        return $this->failure('Something went wrong, sorry');
    }

    public function link() {
        if($this->linkedResources[$this->contextKey] == $this->target) {
            /* target resource is equal to current resource -> nothing to do */
            //throw new Exception();
            return $this->modx->error->failure('Uh ? Linking this resource to itself ? Stop crack pal!');
        }

        /** @var modResource $targetResource */
        $targetResource = $this->modx->getObject('modResource', intval($this->target));
        if(!$targetResource) {
            /* error: resource id is not valid */
            $errorParameter = array('resource' => htmlentities($_POST['babel-link-target']));
            //throw new Exception('error.invalid_resource_id');
            return $this->modx->error->failure('Invalid targeted resource id');
        }

        if($targetResource->get('context_key') != $this->contextKey) {
            /* error: resource id of another context has been provided */
            $errorParameter = array(
                'resource' => $targetResource->get('id'),
                'context' => $this->contextKey);
            //throw new Exception('error.resource_from_other_context');
            return $this->modx->error->failure('Bad context key given (target does not belong to the given ctx)');
        }

        $targetLinkedResources = $this->babel->getLinkedResources($targetResource->get('id'));
        if(count($targetLinkedResources) > 1) {
            /* error: target resource is already linked with other resources */
            $errorParameter = array('resource' => $targetResource->get('id'));
            //throw new Exception('error.resource_already_linked');
            return $this->modx->error->failure('Resource already linked');
        }

        /* add or change a translation link */
        if(isset($this->linkedResources[$this->contextKey])) {
            /* existing link has been changed:
             * -> reset Babel TV of old resource */
            $this->babel->initBabelTvById($this->linkedResources[$this->contextKey]);
        }

        $this->linkedResources[$this->contextKey] = $targetResource->get('id');
        $this->babel->updateBabelTv($this->linkedResources, $this->linkedResources);

        /* copy values of synchronized TVs to target resource */
        if($this->getProperty('sync-tv') == 1) {
            $this->babel->sychronizeTvs($this->resource->get('id'));
        }

        return $this->success();
    }
}

return 'linkBabelTranslation';
