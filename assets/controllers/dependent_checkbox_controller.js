import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ "source", "synced" ];

    connect() {
        this.sync();
    }

    sync() {
        if (this.sourceTarget.checked) {
            this.syncedTargets.forEach(element => { 
                element.disabled = false; 
            });
        } else {
            this.syncedTargets.forEach(element => { 
                element.disabled = true;
                element.checked = false;
            });
        }
    }
}
