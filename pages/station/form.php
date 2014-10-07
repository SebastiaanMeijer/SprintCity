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
			"description" => "Telegrafische afkorting. Zie <a href=\"http://nl.wikipedia.org/wiki/Lijst_van_spoorwegstations_in_Nederland\" target=\"_blank\">wikipedia</a> voor een overzicht.",
			"type" => "text",
			"maxlength" => 5,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "name",
			"label" => "Naam",
			"description" => "Gebruik wanneer nodig <i>&#38;shy;</i> op plaatsen waar het mogelijk is de naam op te breken (dit wordt gebruikt wanneer er weinig ruimte voor de station naam is in de OV applicatie grafiek).",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		),
		2 => array
		(
			"tag" => "input",
			"name" => "variant",
			"label" => "Variant",
			"description" => "Alleen zichtbaar hier in de back-end. Hiermee kan er onderscheid gemaakt worden tussen gelijke stations met afwijkende eigenschappen.",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		),
		3 => array
		(
			"tag" => "input",
			"name" => "town",
			"label" => "Gemeente",
			"description" => "",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		), 
		4 => array
		(
			"tag" => "input",
			"name" => "region",
			"label" => "Regio",
			"description" => "",
			"type" => "text",
			"maxlength" => 255,
			"disabled" => ""
		), 
		5 => array
		(
			"tag" => "textarea",
			"name" => "description_background",
			"label" => "Achtergrond omschrijving",
			"description" => "Gebruik [n] voor 'Enter'.",
			"rows" => 12,
			"disabled" => ""
		),
		6 => array
		(
			"tag" => "textarea",
			"name" => "description_future",
			"label" => "Toekomst omschrijving",
			"description" => "Gebruik [n] voor 'Enter'.",
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
			"description" => "Positie in het openbaar vervoersnetwerk in 2010",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "PWN",
			"label" => "PWN",
			"description" => "Positie in het wegennetwerk in 2010",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		2 => array
		(
			"tag" => "input",
			"name" => "IWD",
			"label" => "IWD",
			"description" => "Inwoners- en werknemersdichtheid in 2010",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		3 => array
		(
			"tag" => "input",
			"name" => "MNG",
			"label" => "MNG",
			"description" => "Mengingsintensiteit in 2010",
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
			"label" => "Bebouwd gebied wonen",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "area_cultivated_work",
			"label" => "Bebouwd gebied werken",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		2 => array
		(
			"tag" => "input",
			"name" => "area_cultivated_mixed",
			"label" => "Bebouwd gebied mixed use",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		3 => array
		(
			"tag" => "input",
			"name" => "area_undeveloped_urban",
			"label" => "Onbebouwd gebied binnenstedelijk",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		4 => array
		(
			"tag" => "input",
			"name" => "area_undeveloped_rural",
			"label" => "Onbebouwd gebied uitleg",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		5 => array
		(
			"tag" => "input",
			"name" => "count_home_total",
			"label" => "Totaal aantal huizen",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		6 => array
		(
			"tag" => "input",
			"name" => "count_work_total",
			"label" => "Totaal bvo werk",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		7 => array
		(
			"tag" => "input",
			"name" => "count_worker_total",
			"label" => "Totaal aantal werknemers",
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
			"label" => "Bebouwd gebied wonen",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		1 => array
		(
			"tag" => "input",
			"name" => "transform_area_cultivated_work",
			"label" => "Bebouwd gebied werken",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		2 => array
		(
			"tag" => "input",
			"name" => "transform_area_cultivated_mixed",
			"label" => "Bebouwd gebied mixed use",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		3 => array
		(
			"tag" => "input",
			"name" => "transform_area_undeveloped_urban",
			"label" => "Onbebouwd gebied binnenstedelijk",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		4 => array
		(
			"tag" => "input",
			"name" => "transform_area_undeveloped_rural",
			"label" => "Onbebouwd gebied uitleg",
			"description" => "In hectare",
			"type" => "text",
			"maxlength" => 4,
			"disabled" => $disabled
		),
		5 => array
		(
			"tag" => "input",
			"name" => "count_home_transform",
			"label" => "Totaal aantal huizen",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		6 => array
		(
			"tag" => "input",
			"name" => "count_work_transform",
			"label" => "Totaal bvo werk",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		),
		7 => array
		(
			"tag" => "input",
			"name" => "count_worker_transform",
			"label" => "Totaal aantal werknemers",
			"description" => "",
			"type" => "text",
			"maxlength" => 7,
			"disabled" => $disabled
		)
	);
}
?>
