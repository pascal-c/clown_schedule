import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.setRhythmTexts();
    }

    setRhythmTexts() {
        var rhythm = document.querySelector('input[name="recurring_date_form[rhythm]"]:checked').value;
        if (rhythm === 'monthly') {
            document.getElementById('rhythm_start_text').innerHTML = 'jeden';
            document.getElementById('rhythm_middle_text').innerHTML = '';
            document.getElementById('rhythm_end_text').innerHTML = 'im Monat';
        } else {
            document.getElementById('rhythm_start_text').innerHTML = 'jede';
            document.getElementById('rhythm_middle_text').innerHTML = 'Woche am';
            document.getElementById('rhythm_end_text').innerHTML = '';
        }
    }
}
