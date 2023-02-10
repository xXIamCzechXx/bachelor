import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    count = 0;
    static targets = ['count'];

    connect() {
        /*
        this.count = 0;

        $(this.element).on('click', () => {
            this.count++;
            this.element.innerHTML = this.count;
        })
        */
    }

    increment() {
        this.count++;
        this.countTarget.innerText = this.count;
    }

    decrement() {
        this.count--;
        this.countTarget.innerText = this.count;
    }
}
