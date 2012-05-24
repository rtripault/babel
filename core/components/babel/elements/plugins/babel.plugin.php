<?php
/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * Babel Plugin to link and synchronize multilingual resources
 *
 * Based on ideas of Sylvain Aerni <enzyms@gmail.com>
 *
 * Events:
 * OnDocFormPrerender,OnDocFormSave,OnEmptyTrash,OnContextRemove,OnResourceDuplicate
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *
 * @var modX $modx
 * @var Babel $babel
 * @var array $scriptProperties
 *
 * @package babel
 */

$babel = $modx->getService('babel', 'Babel', $modx->getOption('babel.core_path', null, $modx->getOption('core_path') . 'components/babel/') . 'model/babel/', $scriptProperties);
if (!($babel instanceof Babel)) return;

/* be sure babel TV is loaded */
if(!$babel->babelTv) return;

switch ($modx->event->name) {
    case 'OnDocFormPrerender':
        /** @var modResource $resource */
        $resource =& $modx->event->params['resource'];
        if(!$resource) {
            /* a new resource is being to created
             * -> skip rendering the babel box */
            break;
        }
        $contextKeys = $babel->getGroupContextKeys($resource->get('context_key'));
        $currentContextKey = $resource->get('context_key');
        if(!in_array($currentContextKey, $contextKeys)) {
            // we are not editing a resource within a context defined in Babel
            break;
        }

        /* include CSS */
        $modx->regClientCSS($babel->config['cssUrl'].'babel.css?v=6');

        $modx->regClientStartupScript($babel->config['jsUrl'].'_babel.js?v=3');
        $modx->regClientStartupScript($babel->config['jsUrl'].'babel.js?v=3');
        $modx->regClientStartupScript('<script type="text/javascript">
            Ext.onReady(function() {
                Babel.config = '. $modx->toJSON($babel->config) .';

                var modAB = Ext.getCmp("modx-action-buttons");
                if (modAB) {
                    modAB.insert(0, new Babel.Translations());
                    // Keep the spacing between buttons
                    modAB.insert(1, "-");
                    modAB.doLayout();
                }
            });
        </script>', 1);
        break;

    case 'OnDocFormSave':
        $resource =& $modx->event->params['resource'];
        if(!$resource) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'No resource provided for OnDocFormSave event');
            break;
        }
        if($modx->event->params['mode'] == modSystemEvent::MODE_NEW) {
            /* no TV synchronization for new resources, just init Babel TV */
            $babel->initBabelTv($resource);
            break;
        }
        $babel->sychronizeTvs($resource->get('id'));
        break;

    case 'OnEmptyTrash':
        /* remove translation links to non-existing resources */
        $deletedResourceIds =& $modx->event->params['ids'];
        if(is_array($deletedResourceIds)) {
            foreach ($deletedResourceIds as $deletedResourceId) {
                $babel->removeLanguageLinksToResource($deletedResourceId);
            }
        }
        break;

    case 'OnContextRemove':
        /* remove translation links to non-existing contexts */
        $context =& $modx->event->params['context'];
        if($context) {
            $babel->removeLanguageLinksToContext($context->get('key'));
        }
        break;

    case 'OnResourceDuplicate':
        /* init Babel TV of duplicated resources */
        $resource =& $modx->event->params['newResource'];
        $babel->initBabelTv($resource);
        break;
}
return;