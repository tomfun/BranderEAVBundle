define([
    'jquery',
    'underscore',
    'routing'
], function ($, _, Routing) {
    'use strict';

    return {
        load: function (name, req, onLoad) {
            $.ajax({
                url:     Routing.generate('brander_eav_filter_list'),
                success: function (data) {
                    requirejs(data, function (view) {
                        var resHash = {};
                        _.each(arguments, function (module, i) {
                            resHash[data[i]] = module;
                        });
                        onLoad(resHash);
                    });
                }
            });
        },
    };
});