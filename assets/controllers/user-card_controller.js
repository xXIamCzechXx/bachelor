import { Controller } from '@hotwired/stimulus';

/**
 * @type {import('@hotwired/stimulus').Controller}
 * This class is showing a modal with a list of user
 */
export default class extends Controller {
    count = 0;
    static targets = ['container', 'modal', 'overlay'];

    /**
     * @param {Event} event
     * This method can be lately removed
     */
    connect() {
        console.log('Connected controller user-card_controller.js');
    }

    /**
     * @param {Event} event
     * Calls ajax method to generate modal of user
     */
    showModal() {
        let containerTarget = this.containerTarget;
        let modalTarget = this.modalTarget;
        $.ajax({
            url: '/player-data-ajaxize/'+event.currentTarget.dataset.userId,
            method: 'POST'
        }).then(function(response) {
            $(modalTarget).css('display', 'block');
            $(modalTarget).css('opacity', 1);
            containerTarget.innerHTML = response;
        }).done(function () {
            setTimeout(function(){
                $(".cv-spinner").fadeOut(300);
            },350);
        });
    }

    /**
     * @ngdoc method
     * Hide modal of specific user
     */
    hide() {
        $(this.modalTarget).css('display', 'none');
        $(this.modalTarget).css('opacity', 0);
        $(this.overlayTarget).css('display', 'none');
    }

    /**
     * @ngdoc method
     * Shows modal of specific user
     */
    show() {
        $(this.modalTarget).css('display', 'block');
        $(this.modalTarget).css('opacity', 1);
    }
}
