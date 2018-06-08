function secureDelete(id, url, redirect)
{
    swal({   
        title: "SECURE DELETE",   
        text: "If you are sure, type in your password:", 
        type: "input",
        inputType: "password",
        allowEscapeKey: true,
        showCancelButton: true,   
        closeOnConfirm: true,   
        animation: "slide-from-top",   
        inputPlaceholder: "Please insert your password"
    }, 

    function(inputValue){   
        if (inputValue === false) return false;      
        if (inputValue === "") {     
            notification('Error','Please insert your password correctly');
            return false   
        }
        $.ajax({
            url: url,
            type: 'post',
            data: {
                id: id,
                redirect: redirect,
                password: inputValue
            },
            success: function(data) {
                if(data['status']=='success'){
                    if(data['redirect']!='') {
                        window.location.href = data['redirect'];
                    } else if(data['target'] == 'coa') {
                        loadIndex();
                    } else {
                        swal(data['title'], data['msg']);
                        $('#list-'+id).hide();
                    }
                } else {
                    swal(data['title'], data['msg']);
                }
            }, error: function(data) { 
                notification(data['title'], data['msg']);
            }
        }); 
    });
}

function secureCancelForm(url, formulir_id, permission_slug)
{
    swal({   
        title: "CANCEL YOUR FORM",
        text: "If you are sure, type in your password:", 
        type: "input",
        inputType: "password",
        allowEscapeKey: true,
        showCancelButton: true,   
        closeOnConfirm: true,   
        animation: "slide-from-top",   
        inputPlaceholder: "Please input your password"
    }, 

    function(inputValue){   
        if (inputValue === false) return false;      
        if (inputValue === "") {     
            notification('Error','Please insert your password correctly');
            return false   
        }
        $.ajax({
            url: url,
            type: 'post',
            data: {
                formulir_id: formulir_id,
                permission_slug: permission_slug,
                password: inputValue
            },
            success: function(data) {
                if(data['status']=='success'){
                    window.location.reload();
                } else {
                    swal(data['title'], data['msg']);
                }
            }, error: function(data) { 
                notification(data['title'], data['msg']);
            }
        }); 
    });
}

function secureRequestCancelForm(url, formulir_id, permission_slug)
{
    $.ajax({
        url: url,
        type: 'post',
        data: {
            formulir_id: formulir_id,
            permission_slug: permission_slug
        },
        always: function(data){
            notification(data['title'], data['msg']);
        }
    });
}

function secureCloseForm(id, url)
{
    swal({   
        title: "CLOSE FORM",   
        text: "If you are sure, type in your password:", 
        type: "input",
        inputType: "password",
        allowEscapeKey: true,
        showCancelButton: true,   
        closeOnConfirm: true,   
        animation: "slide-from-top",   
        inputPlaceholder: "Please insert your password"
    }, 

    function(inputValue){   
        if (inputValue === false) return false;      
        if (inputValue === "") {     
            notification('Error','Please insert your password correctly');
            return false   
        }
        $.ajax({
            url: url,
            type: 'post',
            data: {
                id: id,
                password: inputValue
            },
            success: function(data) {
                if(data['status']=='success'){
                    window.location.reload(); 
                } else {
                    swal(data['title'], data['msg']);
                }
            }, error: function(data) { 
                notification(data['title'], data['msg']);
            }
        }); 
    });
}

function secureReopenForm(id, url)
{
    swal({   
        title: "REOPEN FORM",   
        text: "If you are sure, type in your password:", 
        type: "input",
        inputType: "password",
        allowEscapeKey: true,
        showCancelButton: true,   
        closeOnConfirm: true,   
        animation: "slide-from-top",   
        inputPlaceholder: "Please insert your password"
    }, 

    function(inputValue){   
        if (inputValue === false) return false;      
        if (inputValue === "") {     
            notification('Error','Please insert your password correctly');
            return false   
        }
        $.ajax({
            url: url,
            type: 'post',
            data: {
                id: id,
                password: inputValue
            },
            success: function(data) {
                if(data['status']=='success'){
                    window.location.reload(); 
                } else {
                    swal(data['title'], data['msg']);
                }
            }, error: function(data) { 
                notification(data['title'], data['msg']);
            }
        }); 
    });
}
