$(document).ready(function() {
    $("#tabs").tabs();
	
    $("#motivatie").click(function(){
        if($("#motivatie").attr("clicked") != "clicked")
        {
            $("#motivatie").text("");   
            
            $("#motivatie").attr("clicked", "clicked");
        }
        
        
    });


    
    /* Long polling for year here */
    poll();
});


function poll(){
   
    $.ajax({
        url: "pages/mobility/roundname.php", 
        success: function(data){
            $('#round-name').text(data);
        }, 
        dataType: "json", 
        complete: function() {
            setTimeout(poll, 1000);
        }, 
        timeout: 30000
    });
}


