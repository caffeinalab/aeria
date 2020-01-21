const formPost = document.getElementById('post')
const saveButton = document.getElementById('publish')
const validators = []

function validate() {
  return validators.some(validate => !validate())
}

function handleSubmit(e) {
  if (!validate()) {
    e.preventDefault()
  }
}

export function addValidator(validator) {
  validators.push(validator)
}

if (formPost) {
  formPost.addEventListener('submit', handleSubmit)
}

if (saveButton) {
  saveButton.addEventListener('click', handleSubmit)
}
