import Backbone from 'backbone';
import _ from 'underscore';


var BaseProto = Backbone.View.prototype;

export default Backbone.View.extend({
  templateName: undefined, // '@BranderEAV/Widgets/one.model.twig',{'template': '@BranderEAV/Widgets/one.model.twig'}
  template:     undefined,

  initialize(options) {
    BaseProto.initialize.apply(this, arguments);
    this.template = Templating.get((options && options.templateName) // todo
      || (_.isObject(this.templateName) ? this.templateName.template : this.templateName));
    if (_.isObject(this.templateName)) {
      _.each(this.templateName, function (templateName, name) {
        if (name !== 'template') {
          this[name] = Templating.get(templateName);
        }
      }, this);
    }
  },

  serializeData() {
    return this.model;
  },

  renderBefore() {
  },

  render() {
    _.partial(this.trigger, 'render:before').apply(this, arguments);
    this.renderBefore.apply(this, arguments);
    this.$el.html(this.template(this.serializeData()));
    this.renderAfter.apply(this, arguments);
    _.partial(this.trigger, 'render').apply(this, arguments);
    return this;
  },

  renderAfter() {
  },

});
