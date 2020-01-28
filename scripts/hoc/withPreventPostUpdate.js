import React, { PureComponent } from 'react'
import { Validator } from '@aeria/uikit'
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
      scrollTo(el.hidden ? el.parentNode : el, 100, 300)
    }

    validate = async() => {
      this.lastInvalidField = false
      const fieldsUpdate = await this.updateFields(this.state.fields)
      this.onChange({fields: fieldsUpdate})
      this.updateErrorState(fieldsUpdate)

      if (this.lastInvalidField) {
        this.scrollToElement(this.props.id + '-' + this.lastInvalidField)
      }

      return !!this.lastInvalidField
    }

    updateErrorState(fields) {
      return fields.some(field => {
        if (field.error) {
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

    async updateFields(fields) {
      const fieldsUpdate = await Promise.all(
        fields.map(async field => {
          const v = new Validator(field)
          field.error = await v.validate((field.value || field.defaultValue))

          if (field.children) {
            field.children = await this.updateFields(field.children)
          }
          return field
        })
      )

      return fieldsUpdate
    }

    onChange = ({fields}) => {
      this.setState({fields})
    }

    render() {
      return <WrappedComponent {...this.props} {...this.state} onChange={this.onChange} />
    }
  }
}
