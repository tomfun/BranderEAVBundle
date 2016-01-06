define([
    'backbone',
    'werkint-templating/templating',
    'underscore'
], function (Backbone, Templating, _) {
    'use strict';

    var BaseProto = Backbone.View.prototype;

    return Backbone.View.extend({
        templateName: undefined, //'@BranderEAV/Widgets/one.model.twig',{'template': '@BranderEAV/Widgets/one.model.twig'}
        template:     undefined,

        initialize: function (options) {
            BaseProto.initialize.apply(this, arguments);
            this.template = Templating.get((options && options.templateName)
                || (_.isObject(this.templateName) ? this.templateName.template : this.templateName));
            if (_.isObject(this.templateName)) {
                _.each(this.templateName, function (templateName, name) {
                    if (name !== 'template') {
                        this[name] = Templating.get(templateName);
                    }
                }, this);
            }
        },

        serializeData: function () {
            return this.model;
        },

        renderBefore: function () {
        },

        render: function () {
            _.partial(this.trigger, 'render:before').apply(this, arguments);
            this.renderBefore.apply(this, arguments);
            this.$el.html(this.template(this.serializeData()));
            this.renderAfter.apply(this, arguments);
            _.partial(this.trigger, 'render').apply(this, arguments);
            return this;
        },

        renderAfter: function () {
        },

    });
});