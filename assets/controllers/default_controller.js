import { Controller } from '@hotwired/stimulus';

/**
 * @type {import('@hotwired/stimulus').Controller}
 * This class is showing a modal with a list of user
 */
export default class extends Controller {
    connect() {
        $('.navbar-toggler, .close-layer').on('click', function () {
            if ($(document).find('.nav-open')) {
                $('.main-panel').css('height', '100vh');
                $('.main-panel').css('overflow', 'hidden');
                console.log('Nav opened');
            } else {
                $('.main-panel').css('height', '100%');
                $('.main-panel').css('overflow', 'visible');
                console.log('Nav closed');
            }
        });

        //File input js
        $('.custom-file-input').on('change', function(event) {
            var inputFile = event.currentTarget;
            $(inputFile).parent()
                .find('.custom-file-label')
                .html(inputFile.files[0].name);
        });

        $(".edit-form-bcg, .edit-form-bcg-fe, .btn-close").on("click", function () {
            $('.edit-form-bcg, .edit-form-bcg-fe').css('display', 'none');
            $('.edit-form').css('display', 'none');
        });

        setTimeout(function() {
            $(".alert").animate({opacity: 0}, 750);
        }, 4500);

        $('#cookie_form_discard').on('click', function () {
            $('.checklist').toggle("fast");
            $("#cookie_form_confirm").toggleText('Potvrdit výbrané', 'Potvrdit všechny');
            $("#cookie_form_agreeMarketingTerms").prop( "checked", true );
        });

        if (typeof AOS !== 'undefined') {
            AOS.init();
        }
    }

    setCookie(decline = false) {
        var now = new Date();
        var expirationTime = 3 * 1000 * 60 * 60 * 24;
        now.setTime(now.getTime()+expirationTime);
        var chckbox = $('#cookie_form_agreeMarketingTerms').is(":checked");
        document.cookie = 'cookie-confirmation=1;expires='+now.toGMTString();
        window.dataLayer.push(function(){ this.set('consent') });

        // Decline funguje v případě, že chtějí i tláčo odmítnout - stejná funkčnost, jak když odškrtnout checkboxy a potvrdí
        if (chckbox === false || decline === true) {
            document.cookie = 'cookie-agreement=; Max-Age=-99999999;';
            gtag('consent', 'update', {
                'ad_storage': 'denied',
                'analytics_storage': 'denied',
                'wait_for_update': 500
            });
        } else {
            document.cookie = 'cookie-agreement=1;expires='+now.toGMTString();
            gtag('consent', 'update', {
                'ad_storage': 'granted',
                'analytics_storage': 'granted',
                'wait_for_update': 500
            });
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({'event':'cookie_consent_all'});
        }
        // Skryje veškeré cookie lišty a background shades
        $('#cookie-cont').hide("fast");
    }

    showUserSettings() {
        let id = event.currentTarget.dataset.userId
        $('.' + id).css('display', 'block');
    }
}

