$(document).ready(function() {
    $("#tabs").tabs();

    /* Long polling for year here */
    poll();
});

function poll(){
   
    $.ajax({
        url: "pages/services/roundname.php", 
        success: function(data){
            if ($('#round-name').text() != data)
            {
                refresh();
                $('#round-name').text(data);
            }
        }, 
        dataType: "json", 
        complete: function() {
            setTimeout(poll, 1000);
        }, 
        timeout: 30000
    });
}