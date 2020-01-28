import React, { PureComponent } from 'react'
import { addValidator } from '../utils/prevent-post-update'
import scrollTo from '../utils/scroll-to'

export default function withPreventPostUpdate(WrappedComponent) {
  return class extends PureComponent {
    constructor(props) {
      super(props)
      this.state = { fields: props.fields}
      addValidator(this.validate)
    }

    scrollToElement(elementId) {
      const el = document.getElementById(elementId)
      if (!el) {
        console.log(`[AERIA] Element with error ( id -> ${elementId}) not found!`)
        return
      }
      el.focus()
      el.blur()
      scrollTo(el, 100, 300)
    }

    validate = () => {
      this.lastInvalidField = false
      if (this.hasErrors(this.state.fields)) {
        this.scrollToElement(this.props.id + '-' + this.lastInvalidField)
      }

      return !!this.lastInvalidField
    }

    hasErrors(fields) {
      return fields.some(field => {
        if (field.error || (field.required && !field.value)) {
          this.lastInvalidField = field.id
          return true
        }
        if (field.children) {
          if (this.hasErrors(field.children)) {
            this.lastInvalidField = field.id + '-' + this.lastInvalidField
            return true
          }
        }
        return false
      })
    }

    onChange = ({fields}) => {
      this.setState({fields})
    }

    render() {
      return <WrappedComponent {...this.props} {...this.state} onChange={this.onChange} />
    }
  }
}
