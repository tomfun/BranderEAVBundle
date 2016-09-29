import SetView from './set';
import {render as template} from 'templates/brander-eav/Widgets/group.one.twig';
import 'backbone';
import 'brander-eav/view/attributeCollection';
import 'brander-eav/view/attributeItem';
import 'backbone-chaining'; // for special model binder's names
import 'backbone.modelbinder';
import 'jquery-ui';


const BaseProto = SetView.prototype;

export default SetView.extend({
  template,

  initialize(options) {
    BaseProto.initialize.call(this, {channel: 'attribute-group'});
    this.currentLocale = options.currentLocale;
  },

  serializeData() {
    return {
      model:         this.model,
      currentLocale: this.currentLocale,
      lcl:           this.model
                       ? this.model.get('translations').indexOfLocale(this.currentLocale) : null,
    };
  },

  changeLocale(newLocale) {
    if (!newLocale) {
      throw new Error('newLocale is empty');
    }
    this.currentLocale = newLocale;
    this.render();
  },
});
