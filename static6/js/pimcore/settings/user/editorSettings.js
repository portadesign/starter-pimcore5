/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */


pimcore.registerNS("pimcore.settings.user.editorSettings");
pimcore.settings.user.editorSettings = Class.create({

    initialize:function (userPanel, contentLanguages) {
        this.userPanel = userPanel;
        if (contentLanguages) {
            contentLanguages = contentLanguages.split(',');
        }
        this.contentLanguages = contentLanguages;
    },

    getPanel:function () {

        var items = [];


        var nrOfLanguages = this.contentLanguages.length;

        var data = [];
        for (var i = 0; i < nrOfLanguages; i++) {
            var language = this.contentLanguages[i];
            data.push([language, ts(pimcore.available_languages[language])]);
        }

        this.store = new Ext.data.ArrayStore({
                fields: ["key", "value"],
                data: data
            }
        );


        this.valueGrid = Ext.create('Ext.grid.Panel', {
            tbar: [{
                xtype: "tbtext",
                text: t("language_order")
            }],
            style: "margin-top: 10px",
            store: this.store,
            columnLines: true,
            width: 500,
            columns: [
                {header: t("language"), sortable: true, dataIndex: 'value', editor: new Ext.form.TextField({}),
                    width: 200},
                {header: t("abbreviation"), sortable: true, dataIndex: 'key', editor: new Ext.form.TextField({}),
                    width: 200},
                {
                    xtype:'actioncolumn',
                    width:40,
                    items:[
                        {
                            tooltip:t('up'),
                            icon:"/pimcore/static6/img/icon/arrow_up.png",
                            handler:function (grid, rowIndex) {
                                if (rowIndex > 0) {
                                    var rec = grid.getStore().getAt(rowIndex);
                                    grid.getStore().removeAt(rowIndex);
                                    grid.getStore().insert(--rowIndex, [rec]);
                                    var sm = this.valueGrid.getSelectionModel();
                                }
                            }.bind(this)
                        }
                    ]
                },
                {
                    xtype:'actioncolumn',
                    width:40,
                    items:[
                        {
                            tooltip:t('down'),
                            icon:"/pimcore/static6/img/icon/arrow_down.png",
                            handler:function (grid, rowIndex) {
                                if (rowIndex < (grid.getStore().getCount() - 1)) {
                                    var rec = grid.getStore().getAt(rowIndex);
                                    grid.getStore().removeAt(rowIndex);
                                    grid.getStore().insert(++rowIndex, [rec]);
                                }
                            }.bind(this)
                        }
                    ]
                }
            ],
            autoHeight: true
        });


        items.push(this.valueGrid);

        this.container = new Ext.form.FieldSet({
            title:t("editor_settings"),
            collapsible: true,
            items: items
        });

        return this.container;
    },

    getContentLanguages: function () {

        var settings = {};
        var languages = [];

        this.store.commitChanges();
        this.store.each(function (rec) {
            languages.push(rec.get("key"));
        });

        return languages;
    }

});