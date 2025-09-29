import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.dataset['action'] = 'submit->form#disableForm'
    }

    disableForm() {
        this.submitButtons().forEach(button => {
        button.disabled = true
        button.innerHTML = 'Bitte warten...'
     })
  }

    submitButtons() {
        return this.element.querySelectorAll("button")
    }
}
