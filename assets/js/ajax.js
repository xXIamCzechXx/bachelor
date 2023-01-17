

// This shows an specific layout for loading data from ajaxize on done method
$(document).ajaxSend(function() {
    $("#overlay").fadeIn(300);
});

// Ajax for automatic refresh of scforeboard
$(document).ready(function () {
    setInterval(function() {
        $('.update-scoreboard').click();
    }, 4000)
});
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