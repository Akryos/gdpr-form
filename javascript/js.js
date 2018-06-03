$(document).ready(function() {
    $('#gdprYes, #gdprNo').on('click', function() {
        $('#fieldsetOptions, #fieldsetContact').toggleClass('hide');
    });
    
    if($('input[name=gdpr]:checked').val() === "0") {
        $('#gdprNo').trigger('click');
    }
});