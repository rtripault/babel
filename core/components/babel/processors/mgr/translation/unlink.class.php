<?php
require_once (dirname(dirname(dirname(dirname(__FILE__)))) .'/model/babel/babelprocessor.class.php');
class unlinkBabelTranslation extends BabelProcessor {
    public $contextKey;

    public function process() {
        $this->contextKey = $this->getProperty('context_key');

        return $this->unlink();
    }

    public function unlink() {

        if(!isset($this->linkedResources[$this->contextKey])) {
            /* error: there is no link for this context */
            $errorParameter = array('context' => $this->contextKey);
            return $this->modx->error->failure($this->modx->lexicon('error.no_link_to_context', $errorParameter));
        }

        if($this->linkedResources[$this->contextKey] == $this->resource->get('id')) {
            /* error: (current) resource can not be unlinked from it's translations */
            $errorParameter = array('context' => $this->contextKey);
            return $this->modx->error->failure($this->modx->lexicon('error.unlink_of_selflink_not_possible', $errorParameter));
        }

        /** @var modResource $unlinkedResource */
        $unlinkedResource = $this->modx->getObject('modResource', intval($this->linkedResources[$this->contextKey]));
        if(!$unlinkedResource) {
            /* error: invalid resource id */
            $errorParameter = array('resource' => htmlentities($this->linkedResources[$this->contextKey]));
            return $this->modx->error->failure($this->modx->lexicon('error.invalid_resource_id', $errorParameter));
        }

        if($unlinkedResource->get('context_key') != $this->contextKey) {
            /* error: resource is of a another context */
            $errorParameter = array(
                'resource' => $unlinkedResource->get('id'),
                'context' => $this->contextKey);
            return $this->modx->error->failure($this->modx->lexicon('error.resource_from_other_context', $errorParameter));
        }
        /* unlink resource and reset its Babel TV */
        $this->babel->initBabelTv($unlinkedResource);
        unset($this->linkedResources[$this->contextKey]);
        $this->babel->updateBabelTv($this->linkedResources, $this->linkedResources);

        return $this->success();
    }
}

return 'unlinkBabelTranslation';
