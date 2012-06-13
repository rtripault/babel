<?php
require_once (dirname(dirname(dirname(dirname(__FILE__)))) .'/model/babel/babelprocessor.class.php');
class getBabelTranslations extends BabelProcessor {

    public function process() {
        return $this->listTranslations();
    }

    public function listTranslations() {
        foreach($this->contextKeys as $contextKey) {
            /* for each (valid/existing) context of the context group a button will be displayed */
            /** @var modContext $context */
            $context = $this->modx->getObject('modContext', array('key' => $contextKey));
            if(!$context) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not load context: '.$contextKey);
                continue;
                //return $modx->error->failure('Could not load context: '.$contextKey);
            }
            $context->prepare();
            $cultureKey = $context->getOption('cultureKey', $this->modx->getOption('cultureKey'));


            $showLayer = true;
            if(isset($this->linkedResources[$contextKey])) {
                /* link to this context has been set */
                if($this->linkedResources[$contextKey] == $this->resource->get('id')) {
                    /* don't show language layer for current resource */
                    $showLayer = false;
                }

                $showTranslateButton = false;
                $showUnlinkButton = true;
                $showSecondRow = false;
                $resourceId = $this->linkedResources[$contextKey];
                //$resourceUrl = '?a='.$actions['resource/update'].'&amp;id='.$resourceId;
                if($resourceId == $this->resource->get('id')) {
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
                'text_label' => $this->modx->lexicon('babel.language_'. $cultureKey, array('key' => $contextKey)),
                'text_unlink' => $this->modx->lexicon('babel.unlink_translation'),
                'text_link' => $this->modx->lexicon('babel.link_translation'),
                'text_create' => $this->modx->lexicon('babel.create_translation'),
            );

            $this->list[] = $placeholders;
        }

        return $this->success('', $this->list);
    }

    // Builds the ExtJS menu from PHP. currently not used
    public function buildMenu($contextKey, $cultureKey, $resourceId, $className, $showLayer, $showUnlinkButton, $showTranslateButton, $showSecondRow) {
        $menu = array(
            'text' => $contextKey,
            'disabled' => false,
            'scope' => 'this',
            'iconCls' => $cultureKey .'-lang',
            'ctCls' => 'babel-icon',
        );
        if ($resourceId) {
            $menu['handler'] = 'function() { location.href = "?a='. $this->actions['resource/update'] .'&id='. $resourceId .'" }';
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
                    'text' => $this->modx->lexicon('babel.unlink_translation')
                );
            }
            // Create
            if ($showTranslateButton) {
                $submenu[] = array(
                    'text' => $this->modx->lexicon('babel.create_translation'),
                );
            }
            // Link
            if ($showSecondRow) {
                $submenu[] = array(
                    'text' => $this->modx->lexicon('babel.link_translation'),
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
        }
    }
}

return 'getBabelTranslations';
