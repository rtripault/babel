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
    // Load the translations data
    setup: function() {
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
                        console.log(r);
                    }
                    ,scope: this
                }
            }
        });
    }

    // Build the split button menus & sub menus
    ,buildTranslations: function(cfg) {
        // The main menu
        var menu = [];
        // Each "not translated" language
        var notTranslatedSub = [];
        // The "not translated" menu
        var notTranslated = [{
            text: 'Not translated'
            ,menu: notTranslatedSub
            ,handler: function() { return false ; }
        }];

        Ext.each(cfg, function(translation) {
            // Sub menu for "not translated" languages
            var subItems = [];
            var submenu = {
                items: subItems
            };

            // We got some sub menu to display
            if (translation.showLayer) {
                // Create translation menu
                if (translation.showTranslateButton) {
                    subItems.push({
                        text: this.text_create
                        ,scope: this
                        ,handler: function() {
                            var toolbox = Ext.getCmp('babel-toolbox');
                            if (toolbox) {
                                toolbox.createTranslation(this);
                            }
                        }
                    })
                }
                // Manually link a translation menu
                if (translation.showSecondRow) {
                    subItems.push({
                        text: this.text_link
                        ,scope: this
                        ,handler: function() {
                            var toolbox = Ext.getCmp('babel-toolbox');
                            if (toolbox) {
                                toolbox.linkTranslation();
                            }
                        }
                    })
                }
                // Unlink translation menu
                if (translation.showUnlinkButton) {
                    subItems.push({
                        text: this.text_unlink
                        ,scope: this
                        ,handler: function() {
                            var toolbox = Ext.getCmp('babel-toolbox');
                            if (toolbox) {
                                toolbox.unlinkTranslation(this);
                            }
                        }
                    })
                }
            }

            // The language/translation menu item
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
            // We got several "not translated" languages, add the "not translated" sub menu
            menu.push('-');
            menu.push(notTranslated);
        }

        // The whole menu
        this.setMenu(menu);
    }

/*    // buildTranslations method used when the menu is generated from PHP
    ,buildTranslations: function(cfg) {
        console.log(cfg);
        this.setMenu(cfg);
    }*/

    ,linkTranslation: function() {
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

    ,unlinkTranslation: function(data) {
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

    ,createTranslation: function(data) {
        MODx.Ajax.request({
            url: this.url
            ,params: {
                action: 'mgr/translation/create'
                ,id: MODx.request.id
                ,context_key: data.contextKey
            }
            ,listeners: {
                success: {
                    fn: function(r) {
                        // Reload to the newly created resource
                        location.href = '?a='+ MODx.request.a +'&id='+ r.object.id;
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
        if (this.rendered && !hasMenu) {
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