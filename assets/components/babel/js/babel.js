Ext.ns('Babel');
/**
 *
 * @class Babel.Translations
 * @extends Ext.SplitButton
 * @param {Object} config An object of options.
 * @xtype babel-translations
 */
Babel.Translations = function(config) {
    config = Babel.config || {};
    //console.log(config);

    Ext.applyIf(config,{
        text: 'Translations'
        ,cls: 'x-btn-text bmenu'
        ,menu: this.buildTranslations(config)
//        ,menu: [{
//            text: 'Language 1'
//        }, {
//            text: 'Language 2'
//        }]
        //,handler: optionsHandler
    });
    Babel.Translations.superclass.constructor.call(this, config);
};

Ext.extend(Babel.Translations, Ext.SplitButton, {
    buildTranslations: function(cfg) {
        var menu = [];

        Ext.each(cfg.translations, function(translation) {
            console.log(translation);

            var subItems = [];
            var submenu = {
                items: subItems
            };
            if (translation.showLayer == "yes") {
                if (translation.showTranslateButton == "yes") {
                    subItems.push({
                        text:'Create translation'
                    })
                }
                if (translation.showSecondRow == "yes") {
                    subItems.push({
                        text:'Link translation'
                    })
                }
                if (translation.showUnlinkButton == "yes") {
                    subItems.push({
                        text:'Unlink translation'
                        ,scope: this
                        ,handler: function() {
                            //console.log(this);
                            MODx.Ajax.request({
                                url: Babel.config.connector_url
                                ,params: {
                                    action: 'mgr/test'
                                    //,id: this.config.team
                                }
                                ,listeners: {
                                    success: {
                                        fn: function(r) {
                                            console.log('succes');
                                            console.log(r);
                                        }
                                        ,scope: this
                                    }
                                }
                            });
                        }
                    })
                }
            }

            menu.push({
                text: translation.cultureKey
                //text: _('babel.language_fr')
                ,disabled: (translation.className == "selected") ? true : false
                ,menu: (subItems.length >= 1) ? submenu : ''
                ,scope: this
                ,handler: function() {
                    //console.log(this);
                    location.href = '?a='+ MODx.request.a +'&id='+ this.resourceId;
                }
            });
        });

        return menu;
    }

    ,unlink: function(translation) {
        console.log('trigger!');
    }
});
Ext.reg('babel-translations', Babel.Translations);