import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    count = 0;
    static targets = ['container', 'modal', 'overlay'];

    connect() {
        console.log('Connected controller user-card_controller.js');
    }

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

    hide() {
        $(this.modalTarget).css('display', 'none');
        $(this.modalTarget).css('opacity', 0);
        $(this.overlayTarget).css('display', 'none');
    }

    show() {
        $(this.modalTarget).css('display', 'block');
        $(this.modalTarget).css('opacity', 1);
    }
}
