<?php
abstract class BabelProcessor extends modProcessor {
    /** @var Babel $babel */
    public $babel;
    /** @var modresource $resource */
    public $resource;

    public $contextKeys = array();
    public $currentContextKey;
    public $linkedResources = array();
    public $actions = array();

    public $list = array();

    public function initialize() {
        $this->babel =& $this->modx->babel;
        if (!$this->babel || !($this->babel instanceof Babel)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'babel not instantiated');

            $this->babel = $this->modx->getService('babel', 'Babel', $this->modx->getOption('babel.core_path', null, $this->modx->getOption('core_path') . 'components/babel/') . 'model/babel/');
            if (!($this->babel instanceof Babel)) return false;
        }

        /** @var $resource modResource */
        $this->resource = $this->modx->getObject('modResource', $this->getProperty('id'));
        if (!$this->resource) return $this->failure('No resource found');

        $this->contextKeys = $this->babel->getGroupContextKeys($this->resource->get('context_key'));
        $this->currentContextKey = $this->resource->get('context_key');

        $this->linkedResources = $this->babel->getLinkedResources($this->resource->get('id'));
        if(empty($this->linkedResources)) {
            /* always be sure that the Babel TV is set */
            $this->babel->initBabelTv($this->resource);
        }

        /* grab manager actions IDs */
        $this->actions = $this->modx->request->getAllActionIDs();

        return true;
    }

    public function process() {
        $this->modx->log(modX::LOG_LEVEL_ERROR, 'in babel processors');
        return 'test';
    }
}
