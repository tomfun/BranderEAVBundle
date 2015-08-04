define([
    './stateItemView',
], function (StateItemView) {
    'use strict';

    return StateItemView.extend({
        "tagName":           'li',
        "templateName":      '@BranderEAV/Widgets/attribute.item.twig',
        "changeStateEvents": [
            'processing',
            'error',
            'saved',
        ],

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