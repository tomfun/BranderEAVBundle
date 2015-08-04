define([
    'jquery',
    'underscore',
    './base',
    'backbone',
    'brander-eav/eav',
    'brander-eav/view/attributeCollection',
    'brander-eav/view/attributeItem',
    'brander-eav/view/validation',

    'backbone.modelbinder',
    'jquery-ui'
], function ($, _, Base, Backbone, EAV, ViewAttributeCollection, ViewAttributeItem, validate) {
    'use strict';

    var BaseProto = Base.prototype;

    return Base.extend({
        templateCollection: '@BranderEAV/Widgets/set.attribute.collection.twig',
        templateName:       {
            template: '@BranderEAV/Widgets/set.one.twig'
        },

        initialize: function (options) {
            BaseProto.initialize.apply(this, arguments);
            this.modelBinder = new Backbone.ModelBinder();
            this.channel = Backbone.Radio.channel((options && options.channel) || 'attribute-set');
            if (options && options.templateCollection) {
                this.templateCollection = options.templateCollection;
            }
            this.channel.on('select', function (model) {
                if (this.model) {
                    this.model.off('destroy', this.onDestroy, this);
                }
                this.model = model;
                this.model.on('destroy', this.onDestroy, this);
                this.render();
                this.prepareCollection(model);
            }, this);
        },

        events: {
            "click .save": function (e) {
                e.preventDefault();
                var model = this.model;
                this.channel.trigger('saving', model);
                model.save(undefined, {
                    success: function () {
                        this.channel.trigger('saved', model);
                        this.$('.save').css('border-color', '');
                        this.render();
                    }.bind(this),
                    error: function (m, xhr) {
                        this.channel.trigger('save-error', model);
                        this.$('.save').css('border-color', 'red');
                        validate(this.$('.error-container'), xhr);
                    }.bind(this)
                });
            },
            "click .hide-form": function (e) {
                e.preventDefault();
                this.hide = true;
                this.showHide();
            },
            "click .show-form": function (e) {
                e.preventDefault();
                this.hide = false;
                this.showHide();
            },
        },

        onDestroy: function () {
            this.model = false;
            this.render();
        },

        showHide: function () {
            this.$('.hide-form').toggle(!this.hide);
            this.$('.show-form').toggle(this.hide);
            if (this.hide) {
                this.$('.ibox-content').slideUp();
            } else {
                this.$('.ibox-content').slideDown();
            }
        },

        dropAttribute: function (event, ui) {
            var model      = $(ui.draggable).data('model'),
                collection = this.model.get('attributes'),
                dragZone   = $(event.target);
            if (!(model instanceof collection.model)) {
                dragZone.html("не атрибут");
            } else {
                if (collection.indexOf(model) !== -1) {
                    dragZone.html("уже внутри");
                } else {
                    collection.add(model);
                    dragZone.html("перетащить сюда атрибут");
                }
            }
        },

        buildCollectionView: function (model) {
            var remove = function () {
                if (this.collectionView) {
                    this.collectionView.remove();
                    this.collectionView = false;
                }
            }.bind(this);
            if (this.model !== model) {
                return remove();
            } else {
                if (this.collectionView && this.model.get('attributes') === this.collectionView.collection) {
                    return;
                } else {
                    remove();
                }
            }
            this.collectionView = new ViewAttributeCollection({
                "channel":      undefined,
                "softRemove":   true,
                "templateName": this.templateCollection,
                "collection":   model.get('attributes'),
                "childView":    ViewAttributeItem,
            });
        },

        prepareCollection: function (model) {
            if (model.isNew() || model.get('attributes').length > 0) {
                this.buildCollectionView(model);
                this.renderCollection();
                return;
            }
            model.fetch().always(function () {
                this.buildCollectionView(model);
                this.renderCollection();
            }.bind(this));
        },

        renderCollection: function () {
            if (this.collectionView && this.collectionView.collection === this.model.get('attributes')) {
                this.collectionView.setElement(this.$('.model-attributes'));
                this.collectionView.render();
            }
        },

        renderBefore: function () {
            this.modelBinder.unbind();
        },

        serializeData: function () {
            return {model: this.model};
        },

        renderDroppable: function () {
            this.$('.for-draggable').droppable({
                hoverClass: "ui-state-active",
                drop:       this.dropAttribute.bind(this)
            });
        },

        renderAfter: function () {
            if (this.model) {
                this.renderCollection();
                this.$el.show();
                this.modelBinder.bind(this.model, this.el);
                this.renderDroppable();
            } else {
                this.$el.hide();
            }
        }
    });
});