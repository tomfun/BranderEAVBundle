import Base from './baseCollection';
import _ from 'underscore';
import {render as template} from 'templates/brander-eav/Widgets/set.collection.twig';
import 'backbone.radio';


const BaseProto = Base.prototype;

export default Base.extend({
  template,

  childViewContainer: 'ul',
  initialize(options) {
    BaseProto.initialize.call(this, _.extend(options, {channel: 'attribute-set'}));
  },

  events: {
    'click .add-new-model'() {
      this.channel.trigger('select', new this.collection.model());
    },
  },
});
