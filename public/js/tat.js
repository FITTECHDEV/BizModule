$(function() {
    /** TAT ASSIGN */
    $('#tat_assign').on('change', function() {
        var opt = $(this).val();
        if (opt == 'tat_assign_range') {
            $('#tat_assign_v2').attr('placeholder', 'Max');
            $('#tat_assign_v2').before("<input name='tat_assign_v1' id='tat_assign_v1' style='width:55px; margin-right:2px;'placeholder='Min'/>");
        }else{
            $('#tat_assign_v1').remove();
            $('#tat_assign_v2').attr('placeholder', 'Value');
        }
    });

    $('#tat_solve').on('change', function() {
        var opt = $(this).val();
        if (opt == 'tat_solve_range') {
            $('#tat_solve_v2').attr('placeholder', 'Max');
            $('#tat_solve_v2').before("<input name='tat_solve_v1' id='tat_solve_v1' style='width:55px; margin-right:2px;'placeholder='Min'/>");
        }else{
            $('#tat_solve_v1').remove();
            $('#tat_solve_v2').attr('placeholder', 'Value');
        }
    });

    $('#tat_close').on('change', function() {
        var opt = $(this).val();
        if (opt == 'tat_close_range') {
            $('#tat_close_v2').attr('placeholder', 'Max');
            $('#tat_close_v2').before("<input name='tat_close_v1' id='tat_close_v1' style='width:55px; margin-right:2px;'placeholder='Min'/>");
        }else{
            $('#tat_close_v1').remove();
            $('#tat_close_v2').attr('placeholder', 'Value');
        }
    });

    $('#tat_reopen').on('change', function() {
        var opt = $(this).val();
        if (opt == 'tat_reopen_range') {
            $('#tat_reopen').after("<input name='tat_reopen_v1' id='tat_reopen_v1' style='width:55px; margin-right:2px;'placeholder='Min'/>");
            $('#tat_reopen_v2').attr('placeholder', 'Max');
        }else{
            $('#tat_reopen_v1').remove();
            $('#tat_reopen_v2').attr('placeholder', 'Value');
        }
    });
});
