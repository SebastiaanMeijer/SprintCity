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
        $("#motivatie").css({color: "gray"});
    });
});
