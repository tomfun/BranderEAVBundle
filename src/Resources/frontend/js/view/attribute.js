import _ from 'underscore';
import Base from './base';
import Backbone from 'backbone';
import AttributeModel from 'brander-eav/eav/attribute';
import AttributeTypes from 'brander-eav/eav/attributeMapCollection';
import OptionCollectionView from 'brander-eav/view/optionCollection';
import OptionItemView from 'brander-eav/view/optionItem';
import validate from 'brander-eav/view/validation';
import $ from 'jquery';
import router from 'router';
import {render as template} from 'templates/brander-eav/Widgets/attribute.one.twig';
import {render as templateChanges} from 'templates/brander-eav/Widgets/attribute.changes.twig';
import 'backbone-chaining';
import 'backbone.modelbinder';
import 'jquery-ui';


var BaseProto = Base.prototype;

export default Base.extend({
  template,
  templateChanges,

  initialize(options) {
    BaseProto.initialize.apply(this, arguments);
    this.currentLocale = options.currentLocale;
    this.currentLocales = options.currentLocales;
    this.hide = true;
    if (!this.model) {
      this.model = new AttributeModel();
    }
    this.channel = Backbone.Radio.channel('attribute');
    this.modelBinder = new Backbone.ModelBinder();
    this.types = new AttributeTypes();
    this.types.on('sync', function () {
      this.render({noEffect: true});
    }, this);
    this.types.fetch();
    $.ajax({
      url:     router.generate('brander_eav_filter_list'),
      success: function (data) {
        this.filterModels = data;
        this.render({noEffect: true});
      }.bind(this),
    });
    this.channel.on('select', function (model) {
      this.unbindModelEvents();
      this.model = model;
      this.bindModelEvents();
      this.render();
    }, this);
    this.channel.on('removed', function (model) {
      if (this.model === model) {
        this.model = new AttributeModel();
        this.hide = true;
        this.render();
      }
    }, this);
    this.bindModelEvents();
  },

  optionView: {},

  renderOptionView(element) {
    var viewId     = this.model.cid,
      collection = this.model.get('options');
    if (!this.model.hasOptions()) {
      return this;
    }
    if (!this.optionView.hasOwnProperty(viewId)) {
      this.optionView[viewId] = new OptionCollectionView({
        childView:     OptionItemView,
        collection,
        currentLocale: this.currentLocale,
      });
      collection.on('update', this.onModelChange, this);
      collection.on('add', this.onModelChange, this);
    }
    this.optionView[viewId].setElement(element);
    this.optionView[viewId].render();
  },

  onModelChange() {
    var lastModel = this.model;
    this.model.check().done(function (data) {
      if (this.model === lastModel) {
        this.renderChanges(data);
      }
    }.bind(this));
  },

  bindModelEvents() {
    this.model.on('change', this.onModelChange, this);
  },

  unbindModelEvents() {
    this.model.off('change', this.onModelChange, this);
  },

  events: {
    'click .save'(e) {
      e.preventDefault();
      var model = this.model;
      this.channel.trigger('saving', model);
      model.save(undefined, {
        success: function () {
          this.channel.trigger('saved', model);
          this.render({noEffect: true});
        }.bind(this),
        error:   function (m, xhr) {
          this.channel.trigger('save-error', model);
          this.$('.save').css('border-color', 'red');
          validate(this.$('.attribute-changes'), xhr);
        }.bind(this),
      });
    },
    'click .new-model'(e) {
      e.preventDefault();
      this.model = new AttributeModel();
      if (this.hide) {
        this.hide = false;
        this.render();
      } else {
        this.render({noEffect: true});
      }
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
    'click .show-form-link'(e) {
      e.preventDefault();
      this.hide = !this.hide;
      this.showHide();
    },
    'click .language-item'(e) {
      e.preventDefault();
      var newLocale = this.$(e.currentTarget).data('language');
      if (!newLocale) {
        console.warn('locale don\'t found');
        return;
      }
      this.trigger('locale:changing', newLocale);
      this.currentLocale = newLocale;
      if (this.optionView[this.model.cid]) {
        this.optionView[this.model.cid].changeLocale(this.currentLocale);
      }
      this.trigger('locale:changed');
      this.render({noEffect: true});
    },
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

  renderChanges(data) {
    this.$('.attribute-changes .changes').html(this.templateChanges(data));
  },

  serializeData() {
    return {
      model:            this.model,
      hide:             this.hide,
      types:            this.types,
      filterModels:     this.filterModels,
      currentLocale:    this.currentLocale,
      lcl:              this.model.get('translations').indexOfLocale(this.currentLocale),
      currentLanguages: this.currentLocales,
    };
  },

  renderBefore() {
    this.$el.css('display', 'none');
  },

  renderAfter(options) {
    if (this.model) {
      if (options && options.noEffect) {
        this.$el.css('display', '');
      } else {
        this.$el.slideDown();
      }
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, 'name');
      _.each(bindings, function (v) {
        var element = this.$(v.selector),
          has     = element.closest('.icheckbox_square-green');
        if (element.is(':checkbox') && has && has.length) {
          v.converter = function (dir, val) {
            has.toggleClass('checked', !!val);
            return val;
          };
          // element.siblings('.iCheck-helper').eq(0).click(function() {
          //    debugger;
          //    this.model.set(i, !this.model.get(i));
          // }.bind(this)); <- check iCheck
        }
      }, this);
      this.modelBinder.bind(this.model, this.el, bindings);
      if (!this.hide) {
        if (options && options.noEffect) {
          this.$('.ibox-content').css('display', '');
        } else {
          this.$('.ibox-content').slideDown();
        }
      }

      this.renderOptionView(this.$('#model-options'));
    }
  },
});
