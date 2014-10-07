<?php
require_once './includes/Excel/reader.php';
require_once './includes/master.inc.php';

define('SHEET_USER_DATA', 2);
define('SHEET_COMMON_DATA', 2);
define('SHEET_CURRENCY', 3);
define('SHEET_AVERAGES', 4);
define('SHEET_PBC_COEFF', 5);

define('HEADER_USER_DATA_ROW', 1);
define('COLUMN_USER_DATA_COMPANY', "Company");
define('COLUMN_USER_DATA_YEAR', "Year");
define('COLUMN_USER_DATA_RD_COST', "R&D Expenditures");
define('COLUMN_USER_DATA_RD_CURRENCY', "currency R&D");
define('COLUMN_USER_DATA_PD_AMOUNT', "PD#");
define('COLUMN_USER_DATA_LIC_INCOME', "License Income");
define('COLUMN_USER_DATA_LIC_CURRENCY', "currency LicInc");
define('COLUMN_USER_DATA_IPBC', "IP Building Costs");
define('COLUMN_USER_DATA_IPBC_CURRENCY', "currency IPBC");

define('HEADER_COMMON_DATA_ROW', 1);
define('COLUMN_COMMON_DATA_COMPANY', "companies");
define('COLUMN_COMMON_DATA_YEAR', "years");
define('COLUMN_COMMON_DATA_RD_COST', "R&DEUR_cost");
define('COLUMN_COMMON_DATA_RD_CURRENCY', "R&D_cost currency");
define('COLUMN_COMMON_DATA_PD_AMOUNT', "PD#");
define('COLUMN_COMMON_DATA_LIC_INCOME', "LicInc");
define('COLUMN_COMMON_DATA_LIC_CURRENCY', "LicInc currency");

define('HEADER_CURRENCY_ROW', 1);
define('COLUMN_CURRENCY_YEAR', "years");
define('COLUMN_CURRENCY_DATA_START_NUM', 2);

define('HEADER_AVERAGE_ROW', 1);
define('COLUMN_AVERAGE_YEAR', "years");
define('COLUMN_AVERAGE_RD_COST', "R&DEURa");
define('COLUMN_AVERAGE_PD_AMOUNT', "PD#a");
define('COLUMN_AVERAGE_LIC_INCOME', "LicInca");

define('HEADER_PBC_COEFF_ROW', 1);
define('COLUMN_PBC_COEFF_YEAR', "Year");
define('COLUMN_PBC_COEFF_DATA_START_NUM', 2);

function ImportExcelCommonData($file)
{
	error_reporting(E_ALL ^ E_NOTICE);
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('ISO-8859-1//TRANSLIT//IGNORE');
	$data->read($file);
	if (!ImportCurrency($data))
	{
		echo '<span style="color:red; font-weight:bold">';
		echo 'Error: Importing currency and exchange rates failed. This will also corrup the commen and user data which is dependant on currency data.';
		echo '</span><br />';
	}
	if (!ImportCommonData($data))
	{
		echo '<span style="color:red; font-weight:bold">';
		echo 'Error: Importing common data failed.';
		echo '</span><br />';
	}
	if (!ImportAverage($data))
	{
		echo '<span style="color:red; font-weight:bold">';
		echo 'Error: Importing averages failed. This will result in missing axes in diagram drawing.';
		echo '</span><br />';
	}
	if (!ImportIPBCCoeff($data))
	{
		echo '<span style="color:red; font-weight:bold">';
		echo 'Error: Importing IPBC Coefficients failed. This will result in failing calculations.';
		echo '</span><br />';
	}
	
	//PrintAll($data);
}

function ImportExcelUserData($file)
{
	error_reporting(E_ALL ^ E_NOTICE);
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('ISO-8859-1//TRANSLIT//IGNORE');
	$data->read($file);
	
	//PrintAll($data);
	
	return ImportUserData($data);
}

function ImportUserData($data)
{
	global $Auth;
	
	$result = array(
		'success' => false,
		'companies' => 0, 
		'rows_succeeded' => 0, 
		'rows_failed' => 0, 
		'message' => '');
	
	// find columns
	$columns = array();
	$column_company = 0;
	
	for ($i = 0; $i <= $data->sheets[SHEET_USER_DATA]['numCols']; $i++)
	{
		$value = $data->sheets[SHEET_USER_DATA]['cells'][HEADER_USER_DATA_ROW][$i];
		
		if ($value == COLUMN_USER_DATA_COMPANY)
			$column_company = $i;
		if ($value == COLUMN_USER_DATA_YEAR || 
			$value == COLUMN_USER_DATA_RD_COST ||
			$value == COLUMN_USER_DATA_RD_CURRENCY || 
			$value == COLUMN_USER_DATA_PD_AMOUNT || 
			$value == COLUMN_USER_DATA_LIC_INCOME || 
			$value == COLUMN_USER_DATA_LIC_CURRENCY || 
			$value == COLUMN_USER_DATA_IPBC || 
			$value == COLUMN_USER_DATA_IPBC_CURRENCY)
			$columns[$value] = $i;
	}
	
	// error handling
	if (sizeof($columns) != 8 || $column_company == 0)
	{
		$result['success'] = false;
		$result['companies'] = 0;
		$result['rows_succeeded'] = 0;
		$result['rows_failed'] = 'All';
		$result['message'] .= 'Excel sheet format unreconized, download the proper file from the website, fill in your data, save and upload this file.';
		return $result;
	}
	
	// insert data in database
	$usd = Currency::GetId("USD");
	$company = NULL;
	$rd_currency_id = NULL;
	$lic_currency_id = NULL;
	$ipbc_currency_id = NULL;
	$default_currency_id = $usd;
	$years = array();
	$has_first_company = false;
	
	for ($i = HEADER_USER_DATA_ROW + 1; $i <= $data->sheets[SHEET_USER_DATA]['numRows']; $i++)
	{
		$values = array();
		
		// switch company if given
		if (!is_null($data->sheets[SHEET_USER_DATA]['cells'][$i][$column_company]))
		{
			// fix missing years
			AddMissingYears($years, $company);
			$years = array();
			// switch company
			$company = $data->sheets[SHEET_USER_DATA]['cells'][$i][$column_company];
			$result['companies']++;
			// if this is the first company, it is the user's company
			if (!$has_first_company)
			{
				$Auth->user->SetCompany(Company::GetCompanyId($company));
				$has_first_company = true;
			}
			// another company: another currency is assumed
			$rd_currency_id = NULL;
			$lic_currency_id = NULL;
			$ipbc_currency_id = NULL;
			$default_currency_id = $usd;
		}
		
		// gather data from row
		foreach ($columns as $key => $value)
		{
			$val = $data->sheets[SHEET_USER_DATA]['cells'][$i][$value];
			if ($val != "")
				$values[$key] = $val;
		}
		
		// set default currency
		if (!is_null($ipbc_currency_id))
			$default_currency_id = $ipbc_currency_id;
		
		// find currency id's
		if (isset($values[COLUMN_USER_DATA_RD_CURRENCY]))
			$rd_currency_id = Currency::GetId($values[COLUMN_USER_DATA_RD_CURRENCY]);
		if (isset($values[COLUMN_USER_DATA_LIC_CURRENCY]))
			$lic_currency_id = Currency::GetId($values[COLUMN_USER_DATA_LIC_CURRENCY]);
		if (isset($values[COLUMN_USER_DATA_IPBC_CURRENCY]))
			$ipbc_currency_id = Currency::GetId($values[COLUMN_USER_DATA_IPBC_CURRENCY]);
		
		// compensate for not entered currencies
		$currencies = BleedValues(array($rd_currency_id, $lic_currency_id, $ipbc_currency_id), $default_currency_id);
		$rd_currency_id = $currencies[0];
		$lic_currency_id = $currencies[1];
		$ipbc_currency_id = $currencies[2];
		
		// find year
		$year = $values[COLUMN_USER_DATA_YEAR];
		
		if (!is_null($year) && !is_null($company))
		{
			// keep track of the year range
			if (!isset($years[$year]))
			{
				$years[] = $year;
				
				$user_data = new UserData();
				
				// add new record
				$user_data->SetData(
					$Auth->id, $company, $year, 
					$values[COLUMN_USER_DATA_RD_COST], $rd_currency_id, NULL, 
					$values[COLUMN_USER_DATA_PD_AMOUNT], NULL, 
					$values[COLUMN_USER_DATA_LIC_INCOME], $lic_currency_id, NULL, 
					$values[COLUMN_USER_DATA_IPBC], $ipbc_currency_id, NULL);
				$result['rows_succeeded']++;
			}
			else
			{
				$user_data = UserData::GetDataByUserCompanyYear($Auth->id, $company, $year);
				// overwrite existing record with newer data
				$user_data->SupplementData(
					$values[COLUMN_USER_DATA_RD_COST], $rd_currency_id, NULL, 
					$values[COLUMN_USER_DATA_PD_AMOUNT], NULL, 
					$values[COLUMN_USER_DATA_LIC_INCOME], $lic_currency_id, NULL, 
					$values[COLUMN_USER_DATA_IPBC], $ipbc_currency_id, NULL);
				$result['rows_succeeded']++;
			}
		}
		else
		{
			$result['rows_failed']++;
		}
	}
	
	// fix missing years last company
	AddMissingYears($years, $company);
	
	// produce all missing data
	InterpolateMissingUserData();
	
	$result['success'] = true;
	$result['message'] = 'Import successful.';
	return $result;
}

// tries to fill NULL values with neighboring values
function BleedValues($array, $default)
{
	$filler = NULL;
	$first_value = -1;
	// bleed out from left to right
	for ($i = 0; $i < sizeof($array); $i++)
	{
		if (is_null($array[$i]))
			$array[$i] = $filler;
		else
		{
			$filler = $array[$i];
			if ($first_value == -1)
				$first_value = $i;
		}
	}
	
	// bleed out from right to left
	if ($first_value > -1)
	{
		for ($i = $first_value; $i > -1; $i--)
		{
			if (is_null($array[$i]))
				$array[$i] = $filler;
			else
				$filler = $array[$i];
		}
	}
	else
	{
		// if no values present, fill everything with the default value
		foreach ($array as $key => $value)
			$array[$key] = $default;
	}
	
	return $array;
}

function AddMissingYears($years, $company)
{
	global $Auth;
	
	sort($years);
	$i = 0;
	$year = $years[0];
	while ($i < sizeof($years))
	{
		if ($years[$i] == $year)
		{
			$i++;
			$year++;
		}
		else
		{
			$last_year = UserData::GetDataByUserCompanyYear($Auth->id, Company::GetCompanyId($company), $year - 1);
			$user_data = new UserData();
			$user_data->SetData(
				$Auth->id, $company, $year,
				NULL, $last_year->RD_cost_currency_id, NULL, 
				NULL, NULL, 
				NULL, $last_year->Lic_income_currency_id, NULL, 
				NULL, $last_year->IPBC_currency_id, NULL);
			$year++;
		}
	}
}

function InterpolateMissingUserData()
{
	global $Auth;
	
	// gather data and format it in $data
	$years = array();
	$given_data = array();
	$raw_data = UserData::GetDataOfUser($Auth->id);
	foreach ($raw_data as $key => $value)
	{
		$years[$value->company_id][] = $value->year;
		
		if (!is_null($value->RD_cost))
			$given_data[$value->company_id]['RD_cost'][$value->year] = $value->RD_cost;
		if (!is_null($value->PD_amount))
			$given_data[$value->company_id]['PD_amount'][$value->year] = $value->PD_amount;
		if (!is_null($value->Lic_income))
			$given_data[$value->company_id]['Lic_income'][$value->year] = $value->Lic_income;
		if (!is_null($value->IPBC))
			$given_data[$value->company_id]['IPBC'][$value->year] = $value->IPBC;
	}
	
	$columns = array('RD_cost', 'PD_amount', 'Lic_income', 'IPBC');
	$values = array();
	foreach ($years as $company => $year_array)
	{
		foreach ($year_array as $key => $year)
		{
			foreach ($columns as $index => $column)
			{
				if (isset($given_data[$company][$column][$year]))
				{
					$values[$column] = $given_data[$company][$column][$year];
					$values[$column.'_source'] = 'given';
				}
				else
				{
					$values[$column] = LinearInterpolation($given_data[$company][$column], $year);
					$values[$column.'_source'] = !is_null($values[$column]) ? 'estimate' : 'given';
				}
			}
						
			$ud = UserData::GetDataByUserCompanyYear($Auth->id, $company, $year);
			$ud->SupplementData(
				$values['RD_cost'], NULL, $values['RD_cost_source'], 
				$values['PD_amount'], $values['PD_amount_source'],  
				$values['Lic_income'], NULL, $values['Lic_income_source'], 
				$values['IPBC'], NULL, $values['IPBC_source']);
		}
	}
}

function ImportCurrency($data)
{
	echo '<h1>Currency</h1>';
	
	// find columns
	$columns = array();
	for ($i = 0; $i <= $data->sheets[SHEET_CURRENCY]['numCols']; $i++)
	{
		$value = $data->sheets[SHEET_CURRENCY]['cells'][HEADER_CURRENCY_ROW][$i];
		if ($i >= COLUMN_CURRENCY_DATA_START_NUM &&
			$value != "")
			$columns[$value] = $i;
		else if ($value == COLUMN_CURRENCY_YEAR)
			define('COLUMN_NUM_CURRENCY_YEAR', $i);
	}
	
	// error handling
	if (!defined('COLUMN_NUM_CURRENCY_YEAR'))
	{
		echo '<span style="color:red; font-weight:bold">';
		echo 'Error: No column named "'. COLUMN_CURRENCY_YEAR . '" found, stopped reading currency data, some other data will not import because of this.';
		echo '</span><br />';
		return false;
	}
	
	// insert data in database
		// insert currencies
	echo 'Data imported: <br />';
	echo '<table border=1>';
	echo '<tr>';
	echo '<th>year</th>'; 
	ExchangeRate::EmptyTable();
	foreach ($columns as $key => $value)
	{
		$currency = new Currency();
		$currency->NewCurrency($key);
		echo '<th>' . $key . '</th>';
	}
	echo '</tr>';

		// insert exchange rates per year
	for ($i = HEADER_CURRENCY_ROW + 1; $i <= $data->sheets[SHEET_CURRENCY]['numRows']; $i++)
	{
		$year = $data->sheets[SHEET_CURRENCY]['cells'][$i][COLUMN_NUM_CURRENCY_YEAR];
		if($year != "")
		{
			echo '<tr>';
			echo '<td>' . $year . '</td>';
			foreach ($columns as $key => $column)
			{
				$currency = $key;
				$rate = $data->sheets[SHEET_CURRENCY]['cells'][$i][$column];
				
				// if no rate found, search up
				if ($rate == "")
				{
					for ($j = $i; $j > HEADER_CURRENCY_ROW; $j--)
					{
						$rate = $data->sheets[SHEET_CURRENCY]['cells'][$j][$column];
						if ($rate != "")
							break;
					}
				}
				
				// if still not found, search down
				if ($rate == "")
				{
					for ($j = $i; $j <= $data->sheets[SHEET_CURRENCY]['numRows']; $j++)
					{
						$rate = $data->sheets[SHEET_CURRENCY]['cells'][$j][$column];
						if ($rate != "")
							break;
					}
				}
				
				// create record
				if ($rate != "")
				{
					$exchangeRate = new ExchangeRate();
					$exchangeRate->NewExchangeRate($currency, $year, $rate);
				}
				echo '<td>'. $rate . '</td>';
			}
			echo '</tr>';
		}
	}
	echo '</table>';
	
	return true;
}

function ImportCommonData($data)
{
	echo '<h1>Common Data</h1>';
	
	// find columns
	$columns = array();
	for ($i = 0; $i <= $data->sheets[SHEET_COMMON_DATA]['numCols']; $i++)
	{
		$value = $data->sheets[SHEET_COMMON_DATA]['cells'][HEADER_COMMON_DATA_ROW][$i];
		if ($value == COLUMN_COMMON_DATA_COMPANY || 
			$value == COLUMN_COMMON_DATA_YEAR || 
			$value == COLUMN_COMMON_DATA_RD_COST ||
			$value == COLUMN_COMMON_DATA_RD_CURRENCY || 
			$value == COLUMN_COMMON_DATA_PD_AMOUNT || 
			$value == COLUMN_COMMON_DATA_LIC_INCOME || 
			$value == COLUMN_COMMON_DATA_LIC_CURRENCY)
			$columns[$value] = $i;
	}
	
	// error handling
	if (!array_key_exists(COLUMN_COMMON_DATA_COMPANY, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_COMPANY . '" found!</span><br />';
	if (!array_key_exists(COLUMN_COMMON_DATA_YEAR, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_YEAR . '" found!</span><br />';
	if (!array_key_exists(COLUMN_COMMON_DATA_RD_COST, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_RD_COST . '" found!</span><br />';
	if (!array_key_exists(COLUMN_COMMON_DATA_RD_CURRENCY, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_RD_CURRENCY . '" found!</span><br />';
	if (!array_key_exists(COLUMN_COMMON_DATA_PD_AMOUNT, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_PD_AMOUNT . '" found!</span><br />';
	if (!array_key_exists(COLUMN_COMMON_DATA_LIC_INCOME, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_LIC_INCOME . '" found!</span><br />';
	if (!array_key_exists(COLUMN_COMMON_DATA_LIC_CURRENCY, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_COMMON_DATA_LIC_CURRENCY . '" found!</span><br />';
	
	// insert data in database
		// echo headers
	echo 'Data imported: <br />';
	echo '<table border=1>';
	echo '<tr>';
	foreach ($columns as $key => $value)
		echo '<th>' . $key . '</th>';
	echo '</tr>';

		// insert common data records
	CommonData::EmptyTable();
	for ($i = HEADER_COMMON_DATA_ROW + 1; $i <= $data->sheets[SHEET_COMMON_DATA]['numRows']; $i++)
	{
		$values = array();
		foreach ($columns as $key => $value)
		{
			$name = $data->sheets[SHEET_COMMON_DATA]['cells'][$i][$value];
			if ($name != "")
				$values[$key] = $name;
		}
		
		$rd_currency_id = Currency::GetId($values[COLUMN_COMMON_DATA_RD_CURRENCY]);
		$lic_currency_id = Currency::GetId($values[COLUMN_COMMON_DATA_LIC_CURRENCY]);
		if (sizeof($values) < sizeof($columns) && sizeof($values) > 0)
		{
			echo '<tr>';
				echo '<td colspan=7>';
					echo '<span style="color:red; font-weight:bold">';
					echo 'Error: Row ' . $i . ' is incomplete, and will therefore not be imported.<br />';
					echo 'Data gathered: ';
					print_r($values);
					echo '<br />Data needed: ';
					print_r(array_keys($columns));
					echo '</span><br />';
				echo '</td>';
			echo '</tr>';
		}
		else if ((is_null($rd_currency_id) || is_null($lic_currency_id)) && sizeof($values) > 0)
		{
			echo '<tr>';
				echo '<td colspan=7>';
					echo '<span style="color:red; font-weight:bold">';
					echo 'Error on row ' . $i . ': Either or both "R&D cost currency" and/or "License income currency" could not be found in the currency exchange table, and will therefore not be imported.<br />';
					echo 'R&D cost currency: "' . $values[COLUMN_COMMON_DATA_RD_CURRENCY] . '"<br />';
					echo 'License income currency: "' . $values[COLUMN_COMMON_DATA_LIC_CURRENCY] . '"';
					echo '</span><br />';
				echo '</td>';
			echo '</tr>';
		}
		else if (sizeof($values) > 0)
		{
			if ($rd_currency_id)
			$common_data = new CommonData();
			$common_data->NewCommonData(
				$values[COLUMN_COMMON_DATA_COMPANY], 
				$values[COLUMN_COMMON_DATA_YEAR], 
				$values[COLUMN_COMMON_DATA_RD_COST], 
				$rd_currency_id, 
				$values[COLUMN_COMMON_DATA_PD_AMOUNT], 
				$values[COLUMN_COMMON_DATA_LIC_INCOME], 
				$lic_currency_id);
			echo '<tr>';
			foreach ($values as $key => $value)
				echo '<td>' . $value . '</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
	return true;
}

function ImportAverage($data)
{
	echo '<h1>Averages</h1>';
	
	// find columns
	$columns = array();
	for ($i = 0; $i <= $data->sheets[SHEET_AVERAGES]['numCols']; $i++)
	{
		$value = $data->sheets[SHEET_AVERAGES]['cells'][HEADER_AVERAGE_ROW][$i];
		if ($value == COLUMN_AVERAGE_YEAR || 
			$value == COLUMN_AVERAGE_RD_COST || 
			$value == COLUMN_AVERAGE_PD_AMOUNT || 
			$value == COLUMN_AVERAGE_LIC_INCOME)
			$columns[$value] = $i;
	}
	
	// error handling
	if (!array_key_exists(COLUMN_AVERAGE_YEAR, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_AVERAGE_YEAR . '" found!</span><br />';
	if (!array_key_exists(COLUMN_AVERAGE_RD_COST, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_AVERAGE_RD_COST . '" found!</span><br />';
	if (!array_key_exists(COLUMN_AVERAGE_PD_AMOUNT, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_AVERAGE_PD_AMOUNT . '" found!</span><br />';
	if (!array_key_exists(COLUMN_AVERAGE_LIC_INCOME, $columns))
		echo '<span style="color:red; font-weight:bold">Error: No column named "'. COLUMN_AVERAGE_LIC_INCOME . '" found!</span><br />';
	
	// insert data in database
		// echo headers
	echo 'Data imported: <br />';
	echo '<table border=1>';
	echo '<tr>';
	foreach ($columns as $key => $value)
		echo '<th>' . $key . '</th>';
	echo '</tr>';

		// insert averages
	Average::EmptyTable();
	for ($i = HEADER_AVERAGE_ROW + 1; $i <= $data->sheets[SHEET_AVERAGES]['numRows']; $i++)
	{
		$values = array();
		foreach ($columns as $key => $value)
		{
			$name = $data->sheets[SHEET_AVERAGES]['cells'][$i][$value];
			if ($name != "")
				$values[$key] = $name;
		}
		
		if (sizeof($values) < sizeof($columns) && sizeof($values) > 0)
		{
			echo '<tr>';
				echo '<td colspan=4>';
					echo '<span style="color:red; font-weight:bold">';
					echo 'Error: Row ' . $i . ' is incomplete, and will therefore not be imported.<br /> Data gathered:';
					print_r($values);
					echo '</span><br />';
				echo '</td>';
			echo '</tr>';
		}
		else if (sizeof($values) > 0)
		{
			$average = new Average();
			$average->NewAverage(
				$values[COLUMN_AVERAGE_YEAR], 
				$values[COLUMN_AVERAGE_RD_COST], 
				$values[COLUMN_AVERAGE_PD_AMOUNT], 
				$values[COLUMN_AVERAGE_LIC_INCOME]);
			echo '<tr>';
			foreach ($values as $key => $value)
				echo '<td>' . $value . '</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
	return true;
}

function ImportIPBCCoeff($data)
{
	echo '<h1>IPBC Coefficient</h1>';
	
	// find columns
	$end_column_index = 0;
	for ($i = 0; $i <= $data->sheets[SHEET_PBC_COEFF]['numCols']; $i++)
	{
		$value = $data->sheets[SHEET_PBC_COEFF]['cells'][HEADER_PBC_COEFF_ROW][$i];
		if ($i >= COLUMN_PBC_COEFF_DATA_START_NUM &&
			$value == "")
		{
			$end_column_index = $i;
			break;
		}
		else if ($value == COLUMN_PBC_COEFF_YEAR)
			define('COLUMN_NUM_PBC_COEFF_YEAR', $i);
	}
	
	if ($end_column_index == 0)
		$end_column_index = $data->sheets[SHEET_PBC_COEFF]['numCols'];
	
	// error handling
	if (!defined('COLUMN_NUM_PBC_COEFF_YEAR'))
	{
		echo '<span style="color:red; font-weight:bold">';
		echo 'Error: No column named "'. COLUMN_PBC_COEFF_YEAR . '" found, stopped reading pbc coefficient data!';
		echo '</span><br />';
		return false;
	}
	
	// insert data in database
		// echo headers
	echo 'Data imported: <br />';
	echo '<table border=1>';
	echo '<tr>';
		echo '<th>' . COLUMN_CURRENCY_YEAR . '</th>';
	for ($i = 1; $i <= $end_column_index - COLUMN_PBC_COEFF_DATA_START_NUM + 1; $i++)
		echo '<th>' . $i . '</th>';
	echo '</tr>';

		// insert coefficients
	IPBCCoefficient::EmptyTable();
	for ($i = HEADER_PBC_COEFF_ROW + 1; $i <= $data->sheets[SHEET_PBC_COEFF]['numRows']; $i++)
	{
		$year = $data->sheets[SHEET_PBC_COEFF]['cells'][$i][COLUMN_NUM_PBC_COEFF_YEAR];
		if ($year != "")
		{
			echo '<tr>';
			echo '<th>' . $year . '</th>';
			for ($j = COLUMN_PBC_COEFF_DATA_START_NUM; $j <= $end_column_index; $j++)
			{
				$lifetime = $j - COLUMN_PBC_COEFF_DATA_START_NUM + 1;
				$cost = $data->sheets[SHEET_PBC_COEFF]['cells'][$i][$j];
				if ($cost != "")
				{
					$coefficient = new IPBCCoefficient();
					$coefficient->NewCoefficient($year, $lifetime, $cost);
					echo '<td>' . $cost . '</td>';
				}
				else
				{
					echo '<td><span style="color:red; font-weight:bold">Error: No value found, this will break calculations.</span></td>';
				}
			}
			echo '</tr>';
		}
	}
	echo '</table>';
	return true;
}

function PrintAll($data)
{
	for ($k = 0; $k < sizeof($data->sheets); $k++)
	{
		echo "<h2>sheet " . $k . "</h2>";
		echo "<table border=1>";
		for ($i = 1; $i <= $data->sheets[$k]['numRows']; $i++) {
			echo "<tr>";
			for ($j = 1; $j <= $data->sheets[$k]['numCols']; $j++) {
				echo "<td>";
				if (isset($data->sheets[$k]['cells'][$i][$j]))
					echo $data->sheets[$k]['cells'][$i][$j];
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</table><br/><br/><br/>";
	}
}
?>