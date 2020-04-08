
const form = document.getElementById('post') || document.getElementById('form-aeria-options')
const submitter = document.getElementById('publish')
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
  e.stopPropagation()
  // // preventing wp save alert
  if (window.jQuery) {
    jQuery(window).off('beforeunload')
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

function handleClick(e) {
  if (isValid) {
    return true
  }
  e.preventDefault()
  e.stopPropagation()

  validate()
    .then(hasErrors => {
      isValid = !hasErrors
      if (isValid) {
        submitter.click()
      }
    })

  return false
}

function addListeners() {
  if (form) {
    form.addEventListener('submit', handleSubmit)
  }
  if (submitter) {
    submitter.addEventListener('click', handleClick)
  }
}

export function addValidator(validator) {
  validators.push(validator)
  if (!preventingSave) {
    preventingSave = true
    addListeners()
  }
}
