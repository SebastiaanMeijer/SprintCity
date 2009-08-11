<?php
require_once '../includes/master.inc.php';

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
	$db = Database::getDatabase();	
	$query = "
		INSERT INTO `team` 
			(`name` , `description` , `cpu`, `created`)
		VALUES 
			(:name, :description, :cpu, :created);";
	$args = array(
		'name' => $_REQUEST['name'], 
		'description' => $_REQUEST['description'], 
		'cpu' => isset($_REQUEST['cpu']),
		'created' => date( 'Y-m-d H:i:s'));
	$db->query($query, $args);
	header("Location: ../admin.php?view=teams&page=" . isset($_REQUEST['page']) ? $_REQUEST['page'] : 1);
}

?>