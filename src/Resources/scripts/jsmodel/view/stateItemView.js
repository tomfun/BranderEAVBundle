define([
    'underscore',
    'backbone.marionette',
    'templating'
], function (_, Marionette, templating) {
    'use strict';

    return Marionette.ItemView.extend({
//        "tagName":           'li',
        "templateName":        '@BranderEAV/Widgets/attribute.item.twig',
        "changeStateEvents":   [
            //'processing',
            //'error',
            //'saved',
        ],//or: {"eventName": "css-class-name"}
        "cssClassStatePrefix": 'item-',

        "initialize": function () {
            this.template = templating.get(this.templateName);
            _.each(this.changeStateEvents, function (value, index) {
                var eventName;
                if (_.isArray(this.changeStateEvents)) {
                    eventName = value;
                } else {
                    eventName = index;
                }
                this.on(eventName, this.getChangeStateHandler(eventName), this);
            }, this);
        },

        "getChangeStateHandler": function (eventName) {
            return function () {
                this.state = eventName;
                var cssClass;
                if (_.isArray(this.changeStateEvents)) {
                    cssClass = this.cssClassStatePrefix + eventName;
                } else {
                    cssClass = this.cssClassStatePrefix + this.changeStateEvents[eventName];
                }
                this.$el.addClass(cssClass);
            };
        },

        "ui": {
            "edit":   ".edit",
            "remove": ".remove",
        },

        "triggers": {
            "click @ui.edit":   "select",
            "click @ui.remove": "remove",
        },
    });
});