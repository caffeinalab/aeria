import React, { PureComponent } from 'react'
import { addValidator } from '../utils/prevent-post-update'
import scrollTo from '../utils/scroll-to'

export default function withPreventPostUpdate(WrappedComponent) {
  return class extends PureComponent {
    constructor(props) {
      super(props)
      addValidator(this.validate)
    }

    scrollToElement(elementId) {
      const el = document.getElementById(elementId)
      el.focus()
      el.blur()
      scrollTo(el, 100, 300)
    }

    validate = () => {
      this.lastInvalidField = false

      if (this.hasErrors()) {
        this.scrollToElement(this.lastInvalidField)
      }

      return !!this.lastInvalidField
    }

    hasErrors() {
      return this.fields.some(field => {
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

    onChange = fields => {
      this.fields = fields
    }

    render() {
      return <WrappedComponent {...this.props} onChange={this.onChange} />
    }
  }
}
