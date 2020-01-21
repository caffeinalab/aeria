import {
  Input,
  TextArea,
  Checkbox,
  Wysiwyg,
  Select,
  Picture,
  Gallery,
  Switch,
  DatePicker,
  DateRangePicker,
  // Maps
} from '@aeria/uikit'

import {
  Fieldset,
  Repeater,
  Sections
} from '@aeria/core'

export default {
  metaboxes: window.aeriaMetaboxes,
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
    // 'maps': Maps
  }
}
