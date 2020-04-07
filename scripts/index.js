import React from 'react'
import ReactDOM from 'react-dom'
import { Module, FieldsManager } from '@aeria/core'
import Metabox from './components/Metabox'
import config from './config/wp'

FieldsManager.use(config.uikit)

config.metaboxes.forEach((metaboxProps, i) => {
  ReactDOM.render((
    <Module {...config.module} key={i}>
      <Metabox {...metaboxProps} />
    </Module>
  ), document.getElementById('aeriaApp-' + metaboxProps.id))

  // ReactDOM.render((
  //   <Module>
  //     <GlobalStyles />
  //   </Module>
  // ), document.body.appendChild(document.createElement('div')))
})
document.addEventListener('DOMContentLoaded', _ => {
  const event = new CustomEvent('aeriaInit')
  window.dispatchEvent(event)
})
