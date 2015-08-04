define([
    './baseCollection',
    'brander-eav/eav/optionCollection',

    'backbone.radio',
], function (Base, Collection) {
    'use strict';

    var BaseProto = Base.prototype;

    return Base.extend({
        templateName:       '@BranderEAV/Widgets/option.collection.twig',
        options: {
            childViewOptions: {}
        },
        childViewContainer: 'ul',
        initialize:         function (options) {
            BaseProto.initialize.call(this, arguments);
            this.currentLocale = options.currentLocale;
            this.options.childViewOptions.currentLocale = this.currentLocale;
            this.on('childview:remove', function (childView, hash) {
                var model = hash.model;
                this.removeModel(model);
            }, this);
        },

        changeLocale: function (locale) {
            if (locale === this.currentLocale) {
                return;
            }
            this.currentLocale = locale;
            this.options.childViewOptions.currentLocale = this.currentLocale;
            this.children.each(function (v) {
                v.changeLocale(locale);
            });
        },

        events: {
            "click .add-option": function (e) {
                e.preventDefault();
                var model = new Collection.Model();
                this.collection.add(model);
                var itemView = this.children.findByModel(model);
                itemView.trigger('processing');
            }
        },

        removeModel: function (model) {
            this.collection.remove(model);
        }
    });
});
