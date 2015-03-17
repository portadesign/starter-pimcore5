/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @copyright  Copyright (c) 2009-2014 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

pimcore.registerNS("pimcore.object.classes.data.image");
pimcore.object.classes.data.image = Class.create(pimcore.object.classes.data.data, {

    type: "image",
    /**
     * define where this datatype is allowed
     */
    allowIn: {
        object: true,
        objectbrick: true,
        fieldcollection: true,
        localizedfield: true
    },

    initialize: function (treeNode, initData) {
        this.type = "image";

        this.initData(initData);

        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("image");
    },

    getIconClass: function () {
        return "pimcore_icon_image";
    },

    getLayout: function ($super) {

        $super();

        this.specificPanel.removeAll();
        this.specificPanel.add([
            {
                xtype: "numberfield",
                fieldLabel: t("width"),
                name: "width",
                value: this.datax.width
            },
            {
                xtype: "numberfield",
                fieldLabel: t("height"),
                name: "height",
                value: this.datax.height
            }, {
                fieldLabel: t("upload_path"),
                name: "uploadPath",
                cls: "input_drop_target",
                value: this.datax.uploadPath,
                disabled: this.isInCustomLayoutEditor(),
                width: 500,
                xtype: "textfield",
                listeners: {
                    "render": function (el) {
                        new Ext.dd.DropZone(el.getEl(), {
                            //reference: this,
                            ddGroup: "element",
                            getTargetFromEvent: function(e) {
                                return this.getEl();
                            }.bind(el),

                            onNodeOver : function(target, dd, e, data) {
                                return Ext.dd.DropZone.prototype.dropAllowed;
                            },

                            onNodeDrop : function (target, dd, e, data) {
                                try {
                                    var record = data.records[0];
                                    var data = record.data;
                                    if (data.elementType == "asset") {
                                        this.setValue(data.path);
                                        return true;
                                    }
                                }  catch (e) {
                                    console.log(e);
                                }

                                return false;
                            }.bind(el)
                        });
                    }
                }
            }
        ]);

        return this.layout;
    },

    applySpecialData: function(source) {
        if (source.datax) {
            if (!this.datax) {
                this.datax =  {};
            }
            Ext.apply(this.datax,
                {
                    width: source.datax.width,
                    height: source.datax.height,
                    uploadPath: source.datax.uploadPath
                });
        }
    }

});
