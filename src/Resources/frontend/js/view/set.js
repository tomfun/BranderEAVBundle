import $ from 'jquery';
import Base from './base';
import Backbone from 'backbone';
import ViewAttributeCollection from 'brander-eav/view/attributeCollection';
import ViewAttributeItem from 'brander-eav/view/attributeItem';
import validate from 'brander-eav/view/validation';
import {render as template} from 'templates/brander-eav/Widgets/set.one.twig';
import {render as templateCollection} from 'templates/brander-eav/Widgets/set.attribute.collection.twig';
import 'backbone.modelbinder';
import 'jquery-ui';

const BaseProto = Base.prototype;

export default Base.extend({
  templateCollection,
  template,

  initialize(options) {
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
    'click .save'(e) {
      e.preventDefault();
      const model = this.model;
      this.channel.trigger('saving', model);
      model.save(undefined, {
        success: function () {
          this.channel.trigger('saved', model);
          this.$('.save').css('border-color', '');
          this.render();
        }.bind(this),
        error:   function (m, xhr) {
          this.channel.trigger('save-error', model);
          this.$('.save').css('border-color', 'red');
          validate(this.$('.error-container'), xhr);
        }.bind(this),
      });
    },
    'click .hide-form'(e) {
      e.preventDefault();
      this.hide = true;
      this.showHide();
    },
    'click .show-form'(e) {
      e.preventDefault();
      this.hide = false;
      this.showHide();
    },
  },

  onDestroy() {
    this.model = false;
    this.render();
  },

  showHide() {
    this.$('.hide-form').toggle(!this.hide);
    this.$('.show-form').toggle(this.hide);
    if (this.hide) {
      this.$('.ibox-content').slideUp();
    } else {
      this.$('.ibox-content').slideDown();
    }
  },

  dropAttribute(event, ui) {
    let model      = $(ui.draggable).data('model'),
      collection = this.model.get('attributes'),
      dragZone   = $(event.target);
    if (!(model instanceof collection.model)) {
      dragZone.html('не атрибут');
    } else {
      if (collection.indexOf(model) !== -1) {
        dragZone.html('уже внутри');
      } else {
        collection.add(model);
        dragZone.html('перетащить сюда атрибут');
      }
    }
  },

  buildCollectionView(model) {
    const remove = function () {
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
      'channel':      undefined,
      'softRemove':   true,
      'template':     this.templateCollection,
      'collection':   model.get('attributes'),
      'childView':    ViewAttributeItem,
    });
  },

  prepareCollection(model) {
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

  renderCollection() {
    if (this.collectionView && this.collectionView.collection === this.model.get('attributes')) {
      this.collectionView.setElement(this.$('.model-attributes'));
      this.collectionView.render();
    }
  },

  renderBefore() {
    this.modelBinder.unbind();
  },

  serializeData() {
    return {model: this.model};
  },

  renderDroppable() {
    this.$('.for-draggable').droppable({
      hoverClass: 'ui-state-active',
      drop:       this.dropAttribute.bind(this),
    });
  },

  renderAfter() {
    if (this.model) {
      this.renderCollection();
      this.$el.show();
      this.modelBinder.bind(this.model, this.el);
      this.renderDroppable();
    } else {
      this.$el.hide();
    }
  },
});
