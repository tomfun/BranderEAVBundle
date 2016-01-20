define([
    'backbone.marionette',
    'werkint-templating/templating',
    'backbone',

    'backbone.modelbinder'
], function (Marionette, templating, Backbone) {
    'use strict';

    return Marionette.ItemView.extend({
        "tagName":      'li',
        "templateName": '@BranderEAV/Widgets/option.item.twig',

        "initialize":    function (options) {
            this.currentLocale = options.currentLocale;
            this.template = templating.get(this.templateName);
            this.optionBinder = new Backbone.ModelBinder();
            this.on('processing', function () {
                this.state = 'edit';
                this.render();
            }, this);
            this.on('render', function () {
                if (this.state === 'edit') {
                    this.optionBinder.bind(this.model, this.el);
                }
            }, this);
        },
        "ui":            {
            "remove": ".remove",
            "edit": ".edit",
        },
        "triggers":      {
            "click @ui.remove": "remove",
        },
        "events":        {
            "click @ui.edit": function (e) {
                e.preventDefault();
                if (this.state === "edit") {
                    this.$('input').focus();
                    return;
                }
                this.state = "edit";
                this.render();
            }
        },
        "serializeData": function () {
            var data = Marionette.ItemView.prototype.serializeData.apply(this, arguments);
            data.state = this.state;
            data.lcl = this.model.get('translations').indexOfLocale(this.currentLocale);

            return data;
        },
        "changeLocale": function (locale) {
            this.currentLocale = locale;
            this.render();
        }
    });
});