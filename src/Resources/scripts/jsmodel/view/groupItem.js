define([
    'underscore',
    './setItem',
    'templating'
], function (_, SetItemView) {
    'use strict';

    return SetItemView.extend({
        "templateName": '@BranderEAV/Widgets/group.item.twig',
    });
});