// This shows an specific layout for loading data from ajaxize on done method
$(document).ajaxSend(function() {
    $("#overlay").fadeIn(300);
});

// Ajax for automatic refresh of scforeboard
// $(document).ready(function () {
//     setInterval(function() {
//         $('.update-scoreboard').click();
//     }, 4000)
// });

$(document).on('click', '.update-scoreboard', function(e) {
    e.preventDefault();
    $.ajax({
        url: '/scoreboard-update',
        method: 'POST'
    }).then(function(response) {
        $('.scoreboard-container').html(response);
    });
});

// Ajax for fetching scoresaber data for exact user action
$(document).on('click', '.user-card-cont', function(e) {
    e.preventDefault();
    let userId = $(this).data('user-id');
    $.ajax({
        url: '/player-data-ajaxize/'+userId,
        method: 'POST'
    }).then(function(response) {
        $('.modal-content').html(response);
    }).done(function () {
        setTimeout(function(){
            $("#overlay").fadeOut(300);
        },350);
    });
});

$(document).on('click', '#show-more-users', function(e) {
    e.preventDefault();
    let from = parseInt($(this).attr('data-from'));
    let step = parseInt($(this).attr('data-step'));
    $(this).attr('data-from', (from+step));
    $.ajax({
        url: '/players-data-ajaxize/'+from+'/'+step,
        method: 'POST'
    }).then(function(response) {
        $('#show-more-users-container').append(response);
        if ($(response).find('span').length < step) {
            $('#show-more-users').hide();
        }
    }).done(function () {
        setTimeout(function(){
            $("#overlay").fadeOut(300);
        },350);
    });
});