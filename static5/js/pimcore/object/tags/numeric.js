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

pimcore.registerNS("pimcore.object.tags.numeric");
pimcore.object.tags.numeric = Class.create(pimcore.object.tags.abstract, {

    type:"numeric",

    initialize:function (data, fieldConfig) {

        this.defaultValue = null;
        if ((typeof data === "undefined" || data === null) && fieldConfig.defaultValue) {
            data = fieldConfig.defaultValue;
            this.defaultValue = data;
        }

        this.data = data;
        this.fieldConfig = fieldConfig;
    },

    getGridColumnEditor:function (field) {
        var editorConfig = {};

        if (field.config) {
            if (field.config.width) {
                if (intval(field.config.width) > 10) {
                    editorConfig.width = field.config.width;
                }
            }
        }

        if (field.layout.noteditable) {
            return null;
        }
        // NUMERIC
        if (field.type == "numeric") {
            editorConfig.decimalPrecision = 20;
            return new Ext.form.field.Spinner(editorConfig);
        }
    },

    getGridColumnFilter:function (field) {
        return {type:'numeric', dataIndex:field.key};
    },

    getLayoutEdit:function () {

        var input = {
            fieldLabel:this.fieldConfig.title,
            name:this.fieldConfig.name,
            componentCls:"object_field"
        };

        if (!isNaN(this.data)) {
            input.value = this.data;
        }

        if (this.fieldConfig.width) {
            input.width = this.fieldConfig.width;
        }

        if (this.fieldConfig["unsigned"]) {
            input.minValue = 0;
        }

        if (is_numeric(this.fieldConfig["minValue"])) {
            input.minValue = this.fieldConfig.minValue;
        }

        if (is_numeric(this.fieldConfig["maxValue"])) {
            input.maxValue = this.fieldConfig.maxValue;
        }

        if (this.fieldConfig["integer"]) {
            input.decimalPrecision = 0;
        } else if (this.fieldConfig["decimalPrecision"]) {
            input.decimalPrecision = this.fieldConfig["decimalPrecision"];
        } else {
            input.decimalPrecision = 20;
        }

        this.component = new Ext.form.field.Spinner(input);
        return this.component;
    },


    getLayoutShow:function () {

        var input = {
            fieldLabel:this.fieldConfig.title,
            name:this.fieldConfig.name,
            itemCls:"object_field"
        };

        if (!isNaN(this.data)) {
            input.value = this.data;
        }

        if (this.fieldConfig.width) {
            input.width = this.fieldConfig.width;
        }

        this.component = new Ext.form.TextField(input);
        this.component.disable();

        return this.component;
    },

    getValue:function () {
        if (this.isRendered()) {
            return this.component.getValue().toString();
        } else if (this.defaultValue) {
            return this.defaultValue;
        }
        return this.data;
    },

    getName:function () {
        return this.fieldConfig.name;
    },

    isInvalidMandatory:function () {

        if (!this.isRendered() && (!empty(this.getInitialData() || this.getInitialData() === 0) )) {
            return false;
        } else if (!this.isRendered()) {
            return true;
        }

        if (this.getValue()) {
            return false;
        }
        return true;
    },

    isDirty:function () {
        var dirty = false;

        if(this.defaultValue) {
            return true;
        }

        if (this.component && typeof this.component.isDirty == "function") {
            if (this.component.rendered) {
                dirty = this.component.isDirty();

                // once a field is dirty it should be always dirty (not an ExtJS behavior)
                if (this.component["__pimcore_dirty"]) {
                    dirty = true;
                }
                if (dirty) {
                    this.component["__pimcore_dirty"] = true;
                }

                return dirty;
            }
        }

        return false;
    }
});