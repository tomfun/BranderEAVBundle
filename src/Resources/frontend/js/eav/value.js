import Backbone from 'backbone';
import BaseModel from 'brander-eav/basemodel';
import AttributeModel from './attribute';


const Model = BaseModel.extend({
  'defaults':  {
    'value': null,
    'discr': null,
  },
  'initialize'() {
    const attribute = this.get('attribute');
    if (attribute) {
      this.set('discr', attribute.get('discr'));

      // TODO: kostil
      if (attribute.get('discr') === 'boolean' && !this.get('value')) {
        this.set('value', false);
      }
    }
  },
  'relations': [
    {
      'type':         Backbone.HasOne,
      'key':          'attribute',
      'relatedModel': AttributeModel,
    },
  ],
});

export default Model;
