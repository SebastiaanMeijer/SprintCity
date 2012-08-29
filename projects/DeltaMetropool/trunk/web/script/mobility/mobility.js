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
    
    /* Long polling for year here */
    poll();
    
});

function poll(){
    console.log('bla)');
        $.ajax({
            url: "pages/mobility/roundname.php", 
            success: function(data){
                $('#round-name').text(data);
            }, 
            dataType: "json", 
            complete: function() {
                setTimeout(poll, 2000);
            }, 
            timeout: 30000
        });
    }


