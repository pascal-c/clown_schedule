import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    setUnavailable() {
        if (confirm("Wirklich alles auf Nein stellen?")) {
            var radios = document.querySelectorAll('input[value="no"][type="radio"]');
            radios.forEach((radio) =>  radio.checked = true);
        }
    }

    setAvailable() {
        if (confirm("Wirklich alles auf Ja stellen?")) {
            var radios = document.querySelectorAll('input[value="yes"][type="radio"]');
            radios.forEach((radio) =>  radio.checked = true);
        }
    }
}
