$(document).ready(function() { 

    // disable autocomplete
    $('.form-control').attr('autocomplete', 'off');

    // disable submit button when already submitted
    $('form').submit(function(){
        $(this).find(':submit').attr('disabled','disabled');
    }); 

    // disable enter to submit
    $('form').keydown(function (e) {
        if (e.keyCode == 13 && !$(e.target).is("textarea")) {
            var inputs = $(this).parents("form").eq(0).find(":input");
            if (inputs[inputs.index(this) + 1] != null) {
                inputs[inputs.index(this) + 1].focus();
            }
            e.preventDefault();
            return false;
        }
    });

    /**
     * Select ALL when on focus in inputtext
     */

    // $("textarea").on("click", function () {
    //    $(this).select();
    // });

    // $("input[type='text']").on("click", function () {
    //    $(this).select();
    // });

    // selectize
    initSelectize('.selectize');

    // hoverable row
    initHoverable();
});

function initSelectize(id) {
    return $(id).selectize({
        preload: true,
        searchField: ['text'],
        sortField: [
            {
                field: 'code',
                direction: 'asc'
            },
            {
                field: '$score'
            }
        ],
        maxOptions: 20,
        initData: true
    });
}

function initHoverable() {
    // block row with different color on hover
    $(".hoverable").hover(
        function() {
            $(this).css("background-color","#eee");
        }, function() {
            $(this).css("background-color","#fff");
        }
    );
}
