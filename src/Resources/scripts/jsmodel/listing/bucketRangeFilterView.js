define([
    './abstractFilterView'
], function (BaseView) {
    'use strict';

    return BaseView.extend({
        templateName: '@BranderEAV/Filters/bucketRangeFilterView.twig'
    });
});