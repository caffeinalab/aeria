
const validators = []
let preventingSave = false

async function validate() {
  const results = await Promise.all(
    validators.map(async validation => {
      return await validation()
    })
  )
  return results.some(r => !r)
}

async function handleSubmit(e) {
  const isValid = await validate()
  if (!isValid) {
    e.preventDefault()
  }
}

function addListeners() {
  const formPost = document.getElementById('post')
  const saveButton = document.getElementById('publish')
  if (formPost) {
    formPost.addEventListener('submit', handleSubmit)
  }

  if (saveButton) {
    saveButton.addEventListener('click', handleSubmit)
  }
}

export function addValidator(validator) {
  validators.push(validator)
  if (!preventingSave) {
    preventingSave = true
    addListeners()
  }
}

