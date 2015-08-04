define([
    'jquery',
    'underscore',
    './set',
    'backbone',
    'brander-eav/eav',
    'brander-eav/view/attributeCollection',
    'brander-eav/view/attributeItem',

    'backbone.modelbinder',
    'jquery-ui'
], function ($, _, SetView) {
    'use strict';

    var BaseProto = SetView.prototype;

    return SetView.extend({
        templateName: {
            template: '@BranderEAV/Widgets/group.one.twig'
        },

        initialize: function (options) {
            BaseProto.initialize.call(this, {channel: 'attribute-group'});
            this.currentLocale = options.currentLocale;

        },

        serializeData: function () {
            return {
                model:            this.model,
                currentLocale:    this.currentLocale,
                lcl:              this.model
                                      ? this.model.get('translations').indexOfLocale(this.currentLocale) : undefined,
            };
        },

        changeLocale: function (newLocale) {
            if (!newLocale) {
                console.warn('locale is not defined');
                return;
            }
            this.currentLocale = newLocale;
            this.render();
        }
    });
});