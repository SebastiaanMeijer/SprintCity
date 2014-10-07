<?php

$disabled;
$form_fields_stats;
$form_fields_profile;
$form_fields_area;
$form_fields_transform;


function FormInit($inUse)
{
	$disabled = "";
	
	global $form_fields_stats, $form_fields_profile, $form_fields_area, $form_fields_transform;
	$form_fields_stats = array
	(
		0 => array
		(
			"tag" => "input",
			"name" => "code",
			"label" => "Code",
			"description" => "Station code (1 to 4 letters). For Dutch stations, see <a href=\"http://nl.wikipedia.org/wiki/Lijst_van_spoorwegstations_in_Nederland\" target=\"_blank\">wikipedia</a> for official list.",
			"type" => "text",
			"maxlength" => 5,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "name",
			"label" => "Name",
			"description" => "When station name is long, use <i>&#38;shy;</i> where break should be (this is used when there is little space for station names in the time table graph).",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		),
		2 => array
		(
			"tag" => "input",
			"name" => "variant",
			"label" => "Version",
			"description" => "Only visible in the back-end. This can be used to make versions of the same station, with different properties.",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		),
		3 => array
		(
			"tag" => "input",
			"name" => "town",
			"label" => "Municipality",
			"description" => "",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		), 
		4 => array
		(
			"tag" => "input",
			"name" => "region",
			"label" => "Region",
			"description" => "",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		), 
		5 => array
		(
			"tag" => "textarea",
			"name" => "description_background",
			"label" => "Spatial perspective",
			"description" => "Use [n] for 'Enter'.",
			"rows" => 12,
			"disabled" => ""
		),
		6 => array
		(
			"tag" => "textarea",
			"name" => "description_future",
			"label" => "Mobility perspective",
			"description" => "Use [n] for 'Enter'.",
			"rows" => 12,
			"disabled" => ""
		)
	);

	$form_fields_profile = array
	(
		0 => array
		(
			"tag" => "input",
			"name" => "POVN",
			"label" => "POVN",
			"description" => "Initial public transport network value",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "PWN",
			"label" => "PWN",
			"description" => "Initial road transport network value",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		2 => array
		(
			"tag" => "input",
			"name" => "IWD",
			"label" => "IWD",
			"description" => "Initial residents and jobs density",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		3 => array
		(
			"tag" => "input",
			"name" => "MNG",
			"label" => "MNG",
			"description" => "Initial function mix",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		)
	);

	$form_fields_area = array
	(
		0 => array
		(
			"tag" => "input",
			"name" => "area_cultivated_home",
			"label" => "Built up area residential",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "area_cultivated_work",
			"label" => "Built up area businesses",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		2 => array
		(
			"tag" => "input",
			"name" => "area_cultivated_mixed",
			"label" => "Built up area amenities",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		3 => array
		(
			"tag" => "input",
			"name" => "area_undeveloped_urban",
			"label" => "Urban vacant space",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		4 => array
		(
			"tag" => "input",
			"name" => "area_undeveloped_rural",
			"label" => "Urban expansion area",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		5 => array
		(
			"tag" => "input",
			"name" => "count_home_total",
			"label" => "Number of dwellings",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		6 => array
		(
			"tag" => "input",
			"name" => "count_work_total",
			"label" => "Total business floor space",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		7 => array
		(
			"tag" => "input",
			"name" => "count_worker_total",
			"label" => "Number of jobs",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		)
	);

	$form_fields_transform = array
	(
		0 => array
		(
			"tag" => "input",
			"name" => "transform_area_cultivated_home",
			"label" => "Built up area residential",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "transform_area_cultivated_work",
			"label" => "Built up area businesses",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		2 => array
		(
			"tag" => "input",
			"name" => "transform_area_cultivated_mixed",
			"label" => "Built up area amenities",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		3 => array
		(
			"tag" => "input",
			"name" => "transform_area_undeveloped_urban",
			"label" => "Urban vacant space",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		4 => array
		(
			"tag" => "input",
			"name" => "transform_area_undeveloped_rural",
			"label" => "Urban expansion area",
			"description" => "In hectares",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		5 => array
		(
			"tag" => "input",
			"name" => "count_home_transform",
			"label" => "Number of dwellings",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		6 => array
		(
			"tag" => "input",
			"name" => "count_work_transform",
			"label" => "Total business floor space",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		7 => array
		(
			"tag" => "input",
			"name" => "count_worker_transform",
			"label" => "Number of jobs",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		)
	);
}
?>
