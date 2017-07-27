function clearData(url)
{
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            password: $('#clear-password').val()
        },
        success: function(data) {
            $('#clear-password').val('');

            notification(data['title'], data['msg']);

            if(data['status']=='success')
            {
                window.location.href = data['redirect'];
            }
        }, error: function(data) {
            notification(data['title'], data['msg']);
        }
    });
}