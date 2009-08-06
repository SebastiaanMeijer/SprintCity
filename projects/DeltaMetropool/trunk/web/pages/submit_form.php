<?php

// TODO: Add admin check

switch( $_REQUEST['Action'] )
{
	case "new_team":
		NewTeam();
		break;
	default:
}

function NewTeam()
{
	echo 'New Team!!';
}

?>