$(document).ready(function() {

    $('#select-tags').selectize({
        createOnBlur: true,
        plugins: ['restore_on_backspace', 'remove_button'],
        create: function(input) {
            return {
                value: input,
                text: input
            }
        }
    });

    $('.show-preview').click(function(e) {

        e.preventDefault();

        $('#preview').attr('src', $(this).data('url'));
        $('#preview').css('height', $(window).innerHeight() - 5);

    });

});
