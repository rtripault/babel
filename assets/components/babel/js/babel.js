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
                        console.log(r);
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
        var notTranslatedSub = [];
        var notTranslated = [{
            text: 'Not translated'
            ,menu: notTranslatedSub
            ,handler: function() { return false ; }
        }];

        //Ext.each(cfg.translations, function(translation) {
        Ext.each(cfg, function(translation) {
            //console.log(translation);

            var subItems = [];
            var submenu = {
                items: subItems
            };
            if (translation.showLayer) {
                if (translation.showTranslateButton) {
                    subItems.push({
                        text: this.text_create
                    })
                }
                if (translation.showSecondRow) {
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
                if (translation.showUnlinkButton) {
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

            // The menu item
            var item = {
                text: translation.contextKey
                ,disabled: (translation.className == "selected") ? true : false
                ,menu: (subItems.length >= 1) ? submenu : ''
                ,scope: this
                ,iconCls: translation.cultureKey + '-lang'
                ,ctCls: 'babel-icon'
                ,handler: function () {
                    location.href = '?a=' + MODx.request.a + '&id=' + this.resourceId;
                }
            };

            if (this.resourceId != false) {
                // We already have a translation linked, add to the main menu
                menu.push(item);
            } else {
                // No translation existing yet, add to the "not translated" sub menu
                item.handler = function() { return false ; };
                notTranslatedSub.push(item);
            }
        });

        if (notTranslatedSub.length >= 1) {
            // We got several not translated languages, add the "not translated" sub menu
            menu.push('-');
            menu.push(notTranslated);
        }

        // The whole menu
        this.setMenu(menu);
    }

//    ,buildTranslations: function(cfg) {
//        console.log(cfg);
//        this.setMenu(cfg);
//    }

    ,link: function() {
        if (!this.linkWindow) {
            this.linkWindow = MODx.load({
                xtype: 'babel-window-translation-link'
                ,blankValues: true
                ,listeners: {
                    success: function(r) {
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

        this.linkWindow.show();
    }

    ,unlink: function(data) {
//        console.log('trigger!');
//        console.log(data);

        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/translation/unlink'
                ,id: MODx.request.id
                ,context_key: data.contextKey
            }
            ,listeners: {
                success: {
                    fn: function() {
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
    });
    Babel.Window.LinkTranslation.superclass.constructor.call(this, config);
};
Ext.extend(Babel.Window.LinkTranslation, MODx.Window);
Ext.reg('babel-window-translation-link', Babel.Window.LinkTranslation);