import Backbone from 'backbone';
import _ from 'underscore';


var BaseProto = Backbone.View.prototype;

export default Backbone.View.extend({
  templateName: undefined, // '@BranderEAV/Widgets/one.model.twig',{'template': '@BranderEAV/Widgets/one.model.twig'}
  template:     undefined,

  initialize(options) {
    BaseProto.initialize.apply(this, arguments);
    if (this.templateName) {
      throw new Error('TODO templateName'); // todo
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
