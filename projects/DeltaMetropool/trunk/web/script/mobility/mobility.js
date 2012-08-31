$(document).ready(function() {
    $("#tabs").tabs();
	
    $("#motivatie").click(function(){
        if($("#motivatie").attr("clicked") != "clicked")
        {
            $("#motivatie").text("");   
            
            $("#motivatie").attr("clicked", "clicked");
        }
        
        
    });
    
    $("#doorvoeren").click(function() {
        $(this).text("Verzonden.");
        $("#motivatie").attr("clicked", "clicked");
        $("#motivatie").attr("readonly", "readonly");
        $("#motivatie").css({
            color: "gray"
        });
    });
    
//    getAmbition();
    
    /* Long polling for year here */
    poll();
});

function getAmbition() {
    $.ajax({
        url: "pages/mobility/mobility_value.php", 
        success: function(data){
            if(data != false){
                $('#ambition').text(data);
            } else {
                $('#ambition').text('hallo');                                
            }
        }, 
        dataType: "json",
        timeout: 30000
    });
}

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


