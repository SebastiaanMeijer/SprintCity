/* vars go here */
var max = 1;
var message = "Only " + max + " ambition may be filled in.";

function checkMax(checkgroup, limit, current)
{
	max = limit
	var checkedcount = 0
	var checkboxes = document.getElementsByName(checkgroup)
	for (var i = 0; i < checkboxes.length; i++)
	{
		checkedcount += (checkboxes[i].checked) ? 1 : 0
		if (checkedcount > limit)
		{
			alert(message)
			current.checked = false
			return false;
		}
	}
        return true;
}

function showConfirm()
{
	if (checkMax()) {
            Send.sendOVAmbition(loadAmbition);
        }
}

function loadAmbition()
{
    Load.loadOVAmbition(displayAmbition);
}

function displayAmbition(data){
    $('#ambition').text(data);
    makeMotivationReadOnly();
}

function makeMotivationReadOnly() {
    $('textarea#motivatie').attr('readonly', 'readonly');
}

