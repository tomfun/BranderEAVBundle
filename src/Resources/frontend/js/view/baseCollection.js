import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import _ from 'underscore';
import $ from 'jquery';


/* globals console*/
var BaseProto = Marionette.CompositeView.prototype;

export default Marionette.CompositeView.extend({
  templateName: undefined, // '@BranderEAV/Widgets/layout.twig',
  template:     undefined,
  // childViewContainer: '.collection',

  softRemove: false, // change remove behavior

  initialize(options) {
    BaseProto.initialize.apply(this, arguments);
    if (options && options.templateName) {
      this.templateName = options.templateName;
    }
    if (options.softRemove !== undefined) {
      this.softRemove = options.softRemove;
    }
    if (options && options.draggable !== undefined) {
      if (_.isObject(options.draggable)) {
        this.draggable = options.draggable;
      } else {
        if (!_.isFunction($.fn.draggable)) {
          console.error('jQuery draggable is not defined');
        }
        this.draggable = {
          delay:      400,
          distance:   3,
          revert:     true,
          cursor:     'copy',
          addClasses: false,
        };
      }

      this.on('childview:render', function (childView) {
        if (this.draggable) {
          childView.$(childView.ui.edit).data('model', childView.model).draggable(this.draggable);
        }
      }, this);
    }
    this.collection.on('sync', this.collectionSync, this);
    this.applyChildViewHandlers();
    if (options) {
      if (options && options.childViewContainer) {
        this.childViewContainer = options.childViewContainer;
      }
      if (options.channel) {
        if (!Backbone.Radio || !Backbone.Radio.channel) {
          throw 'Bacbone radio channel does not exist';
        }
        this.channel = Backbone.Radio.channel(options.channel);
        this.applyChannelHandlers();
      }
    }
  },

  collectionSync() {
    this.render();
  },

  applyChildViewHandlers() {
    this.on('childview:remove', function (childView, hash) {
      var model = hash.model;
      this.removeModel(model);
    }, this);
  },

  applyChannelHandlers() {
    this.on('childview:select', function (childView, hash) {
      this.channel.trigger('select', hash.model);
    }, this);
    this.channel.on('saving', this.savingHandler, this);
    this.channel.on('saved', this.savedHandler, this);
    this.channel.on('save-error', this.savingErrorHandler, this);
  },

  savingHandler(model) {
    if (model.isNew()) {
      this.collection.add(model);
    }
    var itemView = this.children.findByModel(model);
    if (itemView) {
      itemView.trigger('processing');
    }
  },

  savedHandler(model) {
    var itemView = this.children.findByModel(model);
    itemView.trigger('saved');
  },

  savingErrorHandler(model) {
    if (model.isNew()) {
      this.collection.remove(model);
    }
    var itemView = this.children.findByModel(model);
    if (itemView) {
      itemView.trigger('error');
    }
  },

  removeModelSoft(model) {
    var res;
    try {
      this.collection.remove(model);
    } catch (e) {
      this.collection.add(model);
      var itemView = this.children.findByModel(model);
      itemView.trigger('error', e);
      if (this.channel) {
        res = this.channel.trigger('error', model, e);
        if (window.$verbosity > 0) {
          console.log('error removing', model, res);
        }
      }
      return false;
    }
    if (this.channel) {
      res = this.channel.trigger('removed', model);
      if (window.$verbosity > 1) {
        console.log('removed', model, res);
      }
    }
    return true;
  },

  removeModelHard(model) {
    model.destroy({
      success: function () {
        if (this.channel) {
          var res = this.channel.trigger('removed', model);
          if (window.$verbosity > 1) {
            console.log('removed', model, res);
          }
        }
      }.bind(this),
      error:   function (e) {
        this.collection.add(model);
        var itemView = this.children.findByModel(model);
        itemView.trigger('error', e);
        if (this.channel) {
          var res = this.channel.trigger('error', model, e);
          if (window.$verbosity > 0) {
            console.log('error removing', model, res);
          }
        }
      }.bind(this),
    });
  },

  removeModel(model) {
    if (this.channel) {
      var res = this.channel.trigger('removing', model);
      if (window.$verbosity > 2) {
        console.log(res);
      }
    }
    var itemView = this.children.findByModel(model);
    itemView.trigger('processing');
    if (this.softRemove) {
      return this.removeModelSoft(model);
    } else {
      return this.removeModelHard(model);
    }
  },
});
