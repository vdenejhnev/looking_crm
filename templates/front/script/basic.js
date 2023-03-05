// Ajax
function test(){
    $.ajax({
        url: "../../adm/ajax/none.php",
        dataType: "html",
        type: "POST",
        data: {methodName : "callback", name : name, phone : phone},
        success: function(data) {
            alert(data);
        }
    });
}