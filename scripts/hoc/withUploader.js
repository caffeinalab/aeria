import React, { PureComponent } from 'react'

export default function withPreventPostUpdate(WrappedComponent) {
  return class extends PureComponent {
    openUploader = (e, childState, callback) => {
      const { label, type, mimeTypes = [], index } = this.props
      const {value, children = []} = childState
      const multiple = type === 'gallery'
      const selectedImages = []

      if (multiple) {
        children.forEach(c => selectedImages.push(c.value))
      } else {
        value && selectedImages.push(value)
      }
      const mediaSettings = {
        title: `${label}`,
        multiple  // Set to true to allow multiple files to be selected
      }
      if (mimeTypes.length) {
        mediaSettings.library = { type: mimeTypes }
      }
      // Create a new media frame
      const frame = window.wp.media(mediaSettings)

      frame.on('open', () =>{
        const selection = frame.state().get('selection')
        selectedImages.forEach(id => {
          selection.add(wp.media.attachment(id))
        })
      })

      // When an image is selected in the media frame...
      frame.on('select', () => {
        const {models} = frame.state().get('selection')
        const attachments = models.map(element => {
          const attachment = element.toJSON()
          const data = {
            mimeType: attachment.mime,
            fileName: attachment.filename,
            showFilename: false,
            value: attachment.id,
            url: attachment.url,
          }
          if (attachment.mime.indexOf('image') < 0) {
            data.showFilename = true
            data.url = attachment.icon
            data.naturalSize = true
          }

          return data
        })

        if (multiple) {
          callback({
            value: attachments.length,
            children: attachments,
          }, index)
        } else {
          callback(attachments[0])
        }
        frame.close()
      })

      // Finally, open the modal on click
      frame.open()
    }

    render() {
      return (
        <WrappedComponent
          {...this.props}
          onEdit={this.openUploader}
          onButton={this.openUploader}
        />
      )
    }
  }
}
