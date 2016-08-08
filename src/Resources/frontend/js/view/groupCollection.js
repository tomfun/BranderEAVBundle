import Base from './baseCollection';
import _ from 'underscore';
import {render as template} from 'templates/brander-eav/Widgets/group.collection.twig';
import 'backbone.radio';

const BaseProto = Base.prototype;

export default Base.extend({
  template,

  childViewContainer: 'ul',
  childViewOptions:   {currentLocale: 'ru'},
  initialize(options) {
    BaseProto.initialize.call(this, _.extend(options, {channel: 'attribute-group'}));
  },
  changeLocale(newLocale) {
    this.childViewOptions.currentLocale = newLocale;
    this.render();
  },

  events: {
    'click .add-new-model'() {
      this.channel.trigger('select', new this.collection.model());
    },
  },
});
