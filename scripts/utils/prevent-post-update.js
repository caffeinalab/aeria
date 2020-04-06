
const form = document.getElementById('post') || document.getElementById('form-aeria-options')
const validators = []
let preventingSave = false
let isValid = false

async function validate() {
  const results = await Promise.all(
    validators.map(async validation => {
      return await validation()
    })
  )
  return results.some(r => r)
}

function handleSubmit(e) {
  if (isValid) {
    return true
  }

  e.preventDefault()

  // preventing wp save alert
  if (window.jQuery) {
    jQuery(window).off('beforeunload.edit-post')
  }

  validate()
    .then(hasErrors => {
      isValid = !hasErrors
      if (isValid) {
        form.submit()
      }
    })

  return false
}

function addListeners() {
  if (form) {
    form.addEventListener('submit', handleSubmit)
  }
}

export function addValidator(validator) {
  validators.push(validator)
  if (!preventingSave) {
    preventingSave = true
    addListeners()
  }
}
