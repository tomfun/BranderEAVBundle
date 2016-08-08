import Base from './baseCollection';
import _ from 'underscore';
import {render as template} from 'templates/brander-eav/Widgets/attribute.collection.twig';
import 'backbone.radio';
import 'jquery-ui';


const BaseProto = Base.prototype;

export default Base.extend({
  template,

  childViewContainer: 'ul',
  draggable:          true,
  childViewOptions:   {currentLocale: 'ru'},
  initialize(options) {
    BaseProto.initialize.call(this, _.extend({channel: 'attribute', draggable: true}, options));
  },
  changeLocale(newLocale) {
    this.childViewOptions.currentLocale = newLocale;
    this.render();
  },
});
