$(function() {
    $("ul.droptrue").sortable({
        connectWith: "ul"
    });
    $("ul.dropfalse").sortable({
        connectWith: "ul",
        dropOnEmpty: false
    });
    $("#sortable1, #sortable2, #sortable3").disableSelection();
    $('#im_exp_form').on('submit', function() {
        if ($(this).parent().children('#file').val() === "") {
            alert("Вы не выбрали прайс-лист");
            return false;
        }
        if ($('.select_tpl select option:selected').val() == "none") {
            if ($('#sortable3').children().length < 2) {
                alert("Нужно выбрать хотя бы 1 поле кроме 'Код товара'");
                return false;
            }
        }
        var i = 0;
        $('#sortable3 input').each(function() {
            $(this).val(i + 1);
            i++;
        });
        $(this).submit();
        return false;
    });
    $('#saveorder').on('click', function() {
        if ($('#sortable1').children().length) {
            alert("Вы не выбрали все обезательные поля");
            return false;
        }
        var i = 0;
        $('#sortable3 input').each(function() {
            $(this).val(i + 1);
            i++;
        });
        $.ajax({
            type: 'POST',
            url: $(this).attr('href'),
            data: $('.im_exp').serialize(),
            success: function(data) {
                alert(data);
            }
        });
        return false;
    });
    $('.select_tpl select').on('change', function() {
        if ($('.select_tpl select option:selected').val() != "none") {
            $('.list').hide();
            $('#del_tpl').show();
        } else {
            $('.list').show();
            $('#del_tpl').hide();
        }
    });
    $('#del_tpl').on('click', function() {
        if (confirm('Вы уверены что хотите удалить выбраный шаблон?')) {
            $.ajax({
                type: 'POST',
                url: $(this).attr('href'),
                data: $('.im_exp').serialize(),
                success: function(data) {
                    $('.select_tpl select option:selected').remove();
                    $('.list').show();
                    alert(data);
                }
            });
        }
        return false;
    });

    $('#im_exp_submit').on('click', function() {
        $('#im_exp_form')[0].submit();
    });
});
