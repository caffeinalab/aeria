import {
  Input,
  TextArea,
  Checkbox,
  Wysiwyg,
  Select,
  Switch,
  DatePicker,
  DateRangePicker,
} from '@aeria/uikit'

import {
  Maps,
  Fieldset,
  Repeater,
  Sections
} from '@aeria/core'

import Picture from '../components/Picture'
import Gallery from '../components/Gallery'

export default {
  metaboxes: window.aeriaMetaboxes || [],
  module: {
    sectionTypes: window.aeriaSections || [],
    theme: window.aeriaTheme
  },
  uikit: {
    'text': Input,
    'number': Input,
    'url': Input,
    'email': Input,
    'hidden': Input,
    'textarea': TextArea,
    'checkbox': Checkbox,
    'wysiwyg': Wysiwyg,
    'select': Select,
    'picture': Picture,
    'gallery': Gallery,
    'repeater': Repeater,
    'sections': Sections,
    'switch': Switch,
    'fieldset': Fieldset,
    'date': DatePicker,
    'daterange': DateRangePicker,
    'maps': Maps
  }
}
