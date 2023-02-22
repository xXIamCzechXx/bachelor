import { Controller } from '@hotwired/stimulus';

/**
 * @type {import('@hotwired/stimulus').Controller}
 * This class is showing a modal with a list of user
 */
export default class extends Controller {
    connect() {
        this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
        console.log('awfawfeawfe');
    }
}

