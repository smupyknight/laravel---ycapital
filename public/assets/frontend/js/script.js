$(".clickable").on('click', function() {
     if ($(this).attr('aria-expanded') === "true") {
         $(this).find('.sub_panel_control').text('+');
         $(this).find('td').removeClass('active_row');
     } else {
         $(this).find('.sub_panel_control').text('-');
         $(this).find('td').addClass('active_row');
     }
});
