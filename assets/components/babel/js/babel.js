Ext.ns('Babel', 'Babel.Window');
/**
 *
 * @class Babel.Translations
 * @extends Ext.SplitButton
 * @param {Object} config An object of options.
 * @xtype babel-translations
 */
Babel.Translations = function(config) {
    config = config || {};
    //config = Babel.config || {};
//    config.translations = Babel.config.translations || {};
//    config.splitlabel = Babel.config.splitlabel || {};
//    console.log(config);

    Ext.applyIf(config,{
        text: 'Translations'
        //text: 'Loading translationg…'
        //text: config.splitlabel
        ,cls: 'x-btn-text bmenu'
        ,id: 'babel-toolbox'
        //,menu: this.buildTranslations(config)
        //,menu: []
        //,handler: this.showMenu()
        ,url: Babel.config.connector_url
        ,listeners: {
            setup: {
                fn: this.setup
                ,scope: this
            }
        }
    });
    Babel.Translations.superclass.constructor.call(this, config);
    this.addEvents({setup: true});
    this.fireEvent('setup', config);
};

Ext.extend(Babel.Translations, Ext.SplitButton, {

    setup: function() {
        console.log('in setup');

        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/translation/get'
                ,id: MODx.request.id
            }
            ,listeners: {
                success: {
                    fn: function(r) {
                        this.buildTranslations(r.object);
                    }
                    ,scope: this
                }
                ,failure: {
                    fn: function(r) {
                        console.log('failure dude!');
                    }
                    ,scope: this
                }
            }
        });
    }

    ,buildTranslations: function(cfg) {
        //console.log(this);
        var menu = [];

        //Ext.each(cfg.translations, function(translation) {
        Ext.each(cfg, function(translation) {
            //console.log(translation);

            var subItems = [];
            var submenu = {
                items: subItems
            };
            if (translation.showLayer == "yes") {
                if (translation.showTranslateButton == "yes") {
                    subItems.push({
                        text: this.text_create
                    })
                }
                if (translation.showSecondRow == "yes") {
                    subItems.push({
                        text: this.text_link
                        ,scope: this
                        ,handler: function() {
                            var toolbox = Ext.getCmp('babel-toolbox');
                            if (toolbox) {
                                toolbox.link();
                            }
                        }
                    })
                }
                if (translation.showUnlinkButton == "yes") {
                    subItems.push({
                        text: this.text_unlink
                        ,scope: this
                        ,handler: function() {
                            var split = Ext.getCmp('babel-toolbox');
                            if (split) {
                                split.unlink(this);
                            }
                        }
                    })
                }
            }

            menu.push({
                text: translation.text_label + '('+ translation.cultureKey +')'
                ,disabled: (translation.className == "selected") ? true : false
                ,menu: (subItems.length >= 1) ? submenu : ''
                ,scope: this
                ,handler: function() {
                    //console.log(this);
                    location.href = '?a='+ MODx.request.a +'&id='+ this.resourceId;
                }
            });
        });

        this.setMenu(menu);
        //this.set('text', 'Translations');

        var modAB = Ext.getCmp('modx-action-buttons');
        if (modAB) {
            modAB.doLayout();
        }
    }

    ,link: function(btn, e) {
        //console.log(btn);
        if (!this.linkWindow) {
            this.linkWindow = MODx.load({
                xtype: 'babel-window-translation-link'
                ,blankValues: true
//                ,record: this.menu.record
                ,listeners: {
                    success: function(r) {
                        console.log('succes with response: ');
                        console.log(r);
                        // Reload
                        location.href = location.href;
                    }
                    ,failure: function(r) {
                        console.log('error with response: ');
                        console.log(r);
                    }
                    ,scope: this
                }
            });
        }
        //this.linkWindow.setValues(this.menu.record);
        //this.linkWindow.show(e.target);
        this.linkWindow.show();
    }

    ,unlink: function(data) {
        console.log('trigger!');
        console.log(data);

        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/translation/unlink'
                ,id: MODx.request.id
                ,context_key: data.contextKey
            }
            ,listeners: {
                success: {
                    fn: function(r) {
                        console.log('good dude!');
                        console.log(r);
                        // Reload
                        location.href = location.href;
                    }
                    ,scope: this
                }
                ,failure: {
                    fn: function(r) {
                        console.log('failure dude!');
                        console.log(r);
                    }
                    ,scope: this
                }
            }
        });
    }

    ,setMenu: function(menu) {
        var hasMenu = (this.menu != null);
        this.menu = Ext.menu.MenuMgr.get(menu);
        if (this.rendered && !hasMenu)
        {
            this.el.child(this.menuClassTarget).addClass('x-btn-with-menu');
            this.menu.on("show", this.onMenuShow, this);
            this.menu.on("hide", this.onMenuHide, this);
        }
    }
});
Ext.reg('babel-translations', Babel.Translations);

/**
 * @class Babel.Window.LinkTranslation
 * @extends MODx.Window
 * @param config
 * @xtype babel-window-translation-link
 */
Babel.Window.LinkTranslation = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: 'Link translation'
        ,url: Babel.config.connector_url
        ,baseParams: {
            action: 'mgr/translation/link'
        }
        ,formDefaults: {
            anchor: '100%'
            ,allowNegative : false
            ,allowDecimals: false
            ,allowBlank: false
        }
        ,fields: [{
            xtype: 'numberfield'
            ,name: 'id'
            ,value: MODx.request.id
            ,hidden: true
        },{
            xtype: 'numberfield'
            ,fieldLabel: 'Target'
            ,name: 'target'
        },{
            xtype: 'textfield'
            ,fieldLabel: 'Context Key'
            ,name: 'context_key'
        }]
//        ,listeners: {
//
//        }
    });
    Babel.Window.LinkTranslation.superclass.constructor.call(this, config);
};
Ext.extend(Babel.Window.LinkTranslation, MODx.Window);
Ext.reg('babel-window-translation-link', Babel.Window.LinkTranslation);