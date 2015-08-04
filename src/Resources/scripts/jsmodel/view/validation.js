define([
    'underscore'
], function (_) {
    'use strict';

    var validate = function (errors, xhr) {
        if (xhr.responseJSON) {
            _.each(xhr.responseJSON, function (v) {
                errors.append('<span class="error">' + v + '</span><br>');
            });
        } else {
            errors.append("error");
        }
    };

    return validate;
});