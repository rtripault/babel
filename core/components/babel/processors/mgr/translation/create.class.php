<?php
require_once (dirname(dirname(dirname(dirname(__FILE__)))) .'/model/babel/babelprocessor.class.php');
class createBabelTranslation extends BabelProcessor {
    public $contextKey;

    public function process() {
        $this->contextKey = $this->getProperty('context_key');

        return $this->create();
//        $response = $this->create();
//        if ($response) {
//            return $this->success('New translation should now be created', $response);
//        }
//
//        return $this->failure('Something went wrong, sorry');
    }

    public function create() {
        if($this->currentContextKey == $this->contextKey) {
            /* error: translation should be created in the same context */
            return $this->failure($this->modx->lexicon('error.translation_in_same_context'));
        }
        if(isset($linkedResources[$this->contextKey])) {
            /* error: there does already exist a translation */
            $errorParameter = array('context' => $this->contextKey);
            return $this->modx->error->failure($this->modx->lexicon('error.translation_already_exists', $errorParameter));
        }

        $newResource = $this->babel->duplicateResource($this->resource, $this->contextKey);
        if($newResource) {
            $this->linkedResources[$this->contextKey] = $newResource->get('id');
            $this->babel->updateBabelTv($this->linkedResources, $this->linkedResources);
        } else {
            /* error: translation could not be created */
            $errorParameter = array('context' => $this->contextKey);
            return $this->failure($this->modx->lexicon('error.could_not_create_translation', $errorParameter));
        }

        return $this->success('', $newResource);
        //return $newResource;
    }
}

return 'createBabelTranslation';
