import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ "input", "hideme" ];

    connect() {
        this.toggleVisibility();
    }

    toggleVisibility() {
        if (this.inputTarget.checked) {
            this.hidemeTargets.forEach(element => { element.style.display = "block"; });
        } else {
            this.hidemeTargets.forEach(element => { element.style.display = "none"; });
        }
    }
}
