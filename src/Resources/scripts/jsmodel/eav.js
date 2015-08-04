/**
 * Структура Brander\EAV для фронтенда.
 *
 * @author Vladimir Odesskij [odesskij1992@gmail.com]
 */
define([
    'lodash',

    './eav/attribute',
    './eav/attributeCollection',

    './eav/attributeGroup',
    './eav/attributeGroupCollection',
    './eav/attributeSet',
    './eav/attributeSetCollection',
    './eav/value',
    './eav/valueCollection',
], function (_,
             AttributeModel, AttributeCollection,
             AttributeGroupModel, AttributeGroupCollection, AttributeSetModel, AttributeSetCollection,
             ValueModel, ValueCollection) {
    'use strict';

    var EAV = {};

    _.extend(EAV, {
        "Attribute":                AttributeModel,
        "AttributeCollection":      AttributeCollection,
        "AttributeGroup":           AttributeGroupModel,
        "AttributeSet":             AttributeSetModel,
        "AttributeSetCollection":   AttributeSetCollection,
        "AttributeGroupCollection": AttributeGroupCollection,
        "Value":                    ValueModel,
        "ValueCollection":          ValueCollection
    });

    return EAV;
});