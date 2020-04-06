import React, { PureComponent } from 'react'
import klona from 'klona'
import { Validator } from '@aeria/uikit'
import { isFieldEnabled } from '@aeria/core'
import { addValidator } from '../utils/prevent-post-update'
import scrollTo from '../utils/scroll-to'

export default function withPreventPostUpdate(WrappedComponent) {
  return class extends PureComponent {
    constructor(props) {
      super(props)
      this.state = { fields: props.fields }
      addValidator(this.validate)
    }

    scrollToElement(elementId) {
      const el = document.getElementById(elementId + '-focus') || document.getElementById(elementId)
      if (!el) {
        console.log(`[AERIA] Element with error ( id -> ${elementId}) not found!`)
        return
      }
      el.focus()
      el.blur()
      scrollTo(el.type === 'hidden' ? el.parentNode : el, 100, 300)
    }

    validate = async() => {
      this.lastInvalidField = false
      this.lastInvalidFieldIndex = false
      const fieldsUpdate = await this.updateFields(klona(this.state.fields))
      this.onChange({fields: fieldsUpdate})
      this.updateErrorState(fieldsUpdate)

      if (this.lastInvalidField) {
        this.scrollToElement(this.props.id + '-' + this.lastInvalidField)
      }

      return !!this.lastInvalidField
    }

    async updateFields(fields) {
      const fieldsUpdate = await Promise.all(
        fields
          .filter(field => isFieldEnabled(field, fields))
          .map(async field => {
            const v = new Validator(field)
            field.error = await v.validate((field.value || field.defaultValue))

            if (field.children) {
              field.children = await this.updateFields(field.children)
            }

            if (field.fields) {
              field.fields = await this.updateFields(field.fields)
            }
            return field
          })
      )

      return fieldsUpdate
    }

    updateErrorState(fields, parentIndex, includeFieldIndex = false) {
      return fields.some((field, index) => {
        if (field.error) {
          this.lastInvalidField = field.id

          if (includeFieldIndex) {
            this.lastInvalidField = `${index}-${this.lastInvalidField}`
          }
          return true
        }

        if (field.children && this.updateErrorState(field.children, index, true)) {
          this.lastInvalidField = `${field.id}-${this.lastInvalidField}`
          return true
        }

        if (field.fields && this.updateErrorState(field.fields, index, !!field.type)) {
          if (field.type) {
            this.lastInvalidField = `${field.id}-${this.lastInvalidField}`
          } else {
            this.lastInvalidField = `${index}-${this.lastInvalidField}`
          }
          return true
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
