define([
    'jquery',
    'backbone',
    'werkint-templating/templating',
    './eav',
    './view/attributeCollection',
    './view/attributeItem',
    './view/attribute',

    './view/setCollection',
    './view/setItem',
    './view/set',
    './view/groupCollection',
    './view/groupItem',
    './view/group',
], function ($, Backbone, templating, EAV, ViewAttrCollection, ViewAttrItem, ViewAttribute,
             ViewSetCollection, ViewSetItem, ViewSet, ViewGroupCollection, ViewGroupItem, ViewGroup) {
    'use strict';

    var ManageView = Backbone.View.extend({
        templateName: '@BranderEAV/Manage/main.twig',
        initialize: function (options) {
            this.template = templating.get(this.templateName);
            var attrCollection = new EAV.AttributeCollection({manage: true}),
                groupCollection = new EAV.AttributeGroupCollection({manage: true}),
                setCollection   = new EAV.AttributeSetCollection({manage: true});
            attrCollection.fetch();
            groupCollection.fetch();
            setCollection.fetch();
            this.viewAttrCollection = new ViewAttrCollection({
                collection: attrCollection,
                childView:  ViewAttrItem,
            });
            this.viewAttribute = new ViewAttribute({
                currentLocale:  options.currentLocale,
                currentLocales: options.currentLocales,
            });

            this.viewSetCollection = new ViewSetCollection({
                collection: setCollection,
                childView:  ViewSetItem,
            });
            this.viewSet = new ViewSet();

            this.viewGroupCollection = new ViewGroupCollection({
                collection: groupCollection,
                childView:  ViewGroupItem,
            });
            this.viewGroup = new ViewGroup({
                currentLocale: options.currentLocale,
            });

            this.viewAttribute.on('locale:changing', function (locale) {
                this.viewGroup.changeLocale(locale);
            },this);
        },

        render: function () {
            this.$el.html(this.template());
            this.viewAttrCollection.setElement("#attributes");
            this.viewAttrCollection.render();
            this.viewAttribute.setElement("#attribute");
            this.viewAttribute.render({noEffect: true});
            this.viewSetCollection.setElement("#attribute-set-list");
            this.viewSetCollection.render();

            this.viewSet.setElement("#attribute-set");
            this.viewSet.render();
            this.viewGroupCollection.setElement("#attribute-group-list");
            this.viewGroupCollection.render();
            this.viewGroup.setElement("#attribute-group");
            this.viewGroup.render();
        }
    });

    return ManageView;
});