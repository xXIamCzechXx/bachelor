/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import './styles/colors.scss';
import './styles/fonts.scss';
import './styles/keyframes.scss';
import './styles/app.scss';
import './styles/gallery.scss';
import './styles/responsivity.scss';

//editor css
//import './styles/editor/material-dashboard.css';

import './js/ajax';

// start the Stimulus application
import './bootstrap';
import './controllers/pagination.js';
import './controllers/pagination.min';
require('bootstrap');

// loads the jquery package from node_modules

import jquery from 'jquery';

// import the function from greet.js (the .js extension is optional)
// ./ (or ../) means to look for a local file
import greet from './greet';

/* Intervals */
let move_object_int = 750;

function showTable($value) {
    $('.' + $value).css('display', 'block');
}

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

$(document).ready(function ()
{
    console.log('start');
    $.ajax({
        url: '/instagram-token',
        method: 'POST'
    }).then(function(response) {
        var userFeed = new Instafeed({
            get: 'user',
            target: "instafeed-container",
            resolution: 'low_resolution',
            limit: 4,
            accessToken: response
        });
        userFeed.run();
    });


    //File input js
    $('.custom-file-input').on('change', function(event) {
        var inputFile = event.currentTarget;
        $(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);
    });

    $('body').on("click", ".edit-form-bcg, .edit-form-bcg-fe, .btn-close", function () {
        $('.edit-form-bcg, .edit-form-bcg-fe').css('display', 'none');
        $('.edit-form').css('display', 'none');
    });

    let cookieAgreement = getCookie('cookie-confirmation');
    if (cookieAgreement === '1') {
        hideCookieTab();
    }
    $("#butt-for-insta").css('display', 'none');
    $("#butt-for-gallery").css('display', 'none');

    setTimeout(() => {
        $("#butt-for-insta").appendTo(".place-for-insta a").css('display', 'block');
        $("#butt-for-gallery").appendTo(".gallery-items").css('display', 'block');
        return stop;
    }, 1550);

    var scroll = new SmoothScroll('a[href*="#"]', {
        speed: 650,
        speedAsDuration: true
    });

    var scroll = new SmoothScroll('a[href*="#"]');

    setTimeout(function()
    {
        $(".alert").animate({opacity: 0}, 750);

    }, 4500);

    /*
    positionSet('.slide-from-left');
    positionSet('.slide-from-right');

    slideObject('.slide-from-left', 'margin-left');
    slideObject('.slide-from-right', 'margin-left');
    showObject('.show-object', 'opacity');
    */

    $('#cookie_form_discard').click(function () {
        $('.checklist').toggle("fast");
        $("#cookie_form_confirm").toggleText('Potvrdit výbrané', 'Potvrdit všechny');
        $("#cookie_form_agreeMarketingTerms").prop( "checked", true );
    })
    /*
    $('#cookie_form_confirm').click(function () {
        location.reload(); // Potom odstranit, aby správně měřilo
    })
     */
});

$.fn.extend({
    toggleText: function(a, b){
        return this.text(this.text() == b ? a : b);
    }
});

/*
$(window).scroll(function()
{
    slideObject('.slide-from-left', 'margin-left');
    slideObject('.slide-from-right', 'margin-left');
    showObject('.show-object', 'opacity');
});
 */

/*
function positionSet(obj)
{
    var objString = String(obj);

    $(obj).each(function (j) {
        if(objString === '.slide-from-right') {
            let rightObject = $(window).width() - $(this).offset().left;
            $(this).animate({'marginLeft':rightObject}, move_object_int).animate({'opacity':1}, 1);
        } else {
            let leftObject = -($(this).offset().left + $(this).width());
            $(this).animate({'marginLeft':leftObject}, move_object_int).animate({'opacity':1}, 1);
        }
    })
}
 */

/*
function slideObject(element)
{
    let bottom_of_window = $(window).scrollTop() + $(window).height();
    $(element).each(function(l) {
        let bottom_of_object = $(this).offset().top + 132;
        if( bottom_of_window > bottom_of_object ){
            $(this).animate({'margin-left':'0'}, move_object_int);
        }
    });
}
 */

/*
function showObject(element)
{
    let bottom_of_window = $(window).scrollTop() + $(window).height();
    $(element).each(function(l) {
        let bottom_of_object = $(this).offset().top + 50;
        if( bottom_of_window > bottom_of_object ){
            $(this).animate({'opacity':'1'}, move_object_int);
        }
    });
}
 */

var w = c.width = window.innerWidth,
    h = c.height = window.innerHeight - 14,
    ctx = c.getContext( '2d' ),

    opts = {

        len: 50,
        count: 80,
        baseTime: 10,
        addedTime: 8,
        dieChance: .005,
        spawnChance: 3,
        sparkChance: .4,
        sparkDist: 1,
        sparkSize: 1,

        color: 'hsl(hue,100%,light%)',
        baseLight: 50,
        addedLight: 10, // [50-10,50+10]
        shadowToTimePropMult: 6,
        baseLightInputMultiplier: .01,
        addedLightInputMultiplier: .02,

        cx: w / 2,
        cy: h / 2,
        repaintAlpha: .04,
        hueChange: .1
    },

    tick = 0,
    lines = [],
    dieX = w / 2 / opts.len,
    dieY = h / 2 / opts.len,

    baseRad = Math.PI * 2 / 6;

ctx.fillStyle = 'black';
ctx.fillRect( 0, 0, w, h );

function loop() {

    window.requestAnimationFrame( loop );

    ++tick;

    ctx.globalCompositeOperation = 'source-over';
    ctx.shadowBlur = 0;
    ctx.fillStyle = 'rgba(0,0,0,alp)'.replace( 'alp', opts.repaintAlpha );
    ctx.fillRect( 0, 0, w, h );
    ctx.globalCompositeOperation = 'lighter';

    if( lines.length < opts.count && Math.random() < opts.spawnChance )
        lines.push( new Line );

    lines.map( function( line ){ line.step(); } );
}
function Line(){

    this.reset();
}
Line.prototype.reset = function(){

    this.x = 0;
    this.y = 0;
    this.addedX = 0;
    this.addedY = 0;

    this.rad = 0;

    this.lightInputMultiplier = opts.baseLightInputMultiplier + opts.addedLightInputMultiplier * Math.random();

    this.color = opts.color.replace( 'hue', tick * opts.hueChange );
    this.cumulativeTime = 0;

    this.beginPhase();
}
Line.prototype.beginPhase = function(){

    this.x += this.addedX;
    this.y += this.addedY;

    this.time = 0;
    this.targetTime = ( opts.baseTime + opts.addedTime * Math.random() ) |0;

    this.rad += baseRad * ( Math.random() < .5 ? 1 : -1 );
    this.addedX = Math.cos( this.rad );
    this.addedY = Math.sin( this.rad );

    if( Math.random() < opts.dieChance || this.x > dieX || this.x < -dieX || this.y > dieY || this.y < -dieY )
        this.reset();
}
Line.prototype.step = function(){

    ++this.time;
    ++this.cumulativeTime;

    if( this.time >= this.targetTime )
        this.beginPhase();

    var prop = this.time / this.targetTime,
        wave = Math.sin( prop * Math.PI / 2  ),
        x = this.addedX * wave,
        y = this.addedY * wave;

    ctx.shadowBlur = prop * opts.shadowToTimePropMult;
    ctx.fillStyle = ctx.shadowColor = this.color.replace( 'light', opts.baseLight + opts.addedLight * Math.sin( this.cumulativeTime * this.lightInputMultiplier ) );
    ctx.fillRect( opts.cx + ( this.x + x ) * opts.len, opts.cy + ( this.y + y ) * opts.len, 2, 2 );

    if( Math.random() < opts.sparkChance )
        ctx.fillRect( opts.cx + ( this.x + x ) * opts.len + Math.random() * opts.sparkDist * ( Math.random() < .5 ? 1 : -1 ) - opts.sparkSize / 2, opts.cy + ( this.y + y ) * opts.len + Math.random() * opts.sparkDist * ( Math.random() < .5 ? 1 : -1 ) - opts.sparkSize / 2, opts.sparkSize, opts.sparkSize )
}
loop();

window.addEventListener( 'resize', function(){

    w = c.width = window.innerWidth;
    h = c.height = window.innerHeight - 14;
    ctx.fillStyle = 'rgba(0,0,0,0.6)';
    ctx.fillRect( 0, 0, w, h );

    opts.cx = w / 2;
    opts.cy = h / 2;

    dieX = w / 2 / opts.len;
    dieY = h / 2 / opts.len;
});

AOS.init();

// Cookies
$(document).on('click', '#cookie_form_confirm', function (e) {
    e.preventDefault();
    setCookie();
})

function setCookie(decline = false) {
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
    hideCookieTab();
}

function hideCookieTab() {
    $('#cookie-cont').hide("fast");
}