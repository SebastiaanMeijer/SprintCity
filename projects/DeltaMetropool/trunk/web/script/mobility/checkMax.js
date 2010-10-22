/* vars go here */
var MAX = 3;
var MESSAGE = "Laten we maximaal " + MAX + " ambities aanhouden. ";


function checkMax()
{
	var checkBoxForm = document.ambitions;
	var total = 0;

	for(var i = 0; i < checkBoxForm.checkbox.length; i++)
	{
		if ( checkBoxForm.checkbox[i].checked )
			total++;
		if ( total > MAX)
		{
			alert(MESSAGE);
			checkBoxForm.checkbox[i].checked = false;
			return;
		}
	}
}