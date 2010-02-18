<?php
/*

# Nordea to Yunoo.nl converter tool


## How to export from Nordeo Netbank

- Login 
- On the next page, select the "Konti" (Account)
- Optionally change the dates near "Periode", click "Hent"
  (This works for me for more than 200+ transactions in one go)
- Scroll down, press "Gem kontobevaegelser", and safe the resulting file


## How to convert it

   php n2y.php < poster.csv > rek1.csv

 
Import this into Yunoo, as if it were an ING account (other banks might work too, but that doens't matter)

NOTE: It uses a fixed exchange rate as defined by EXCHANGE_RATE!


Released under GPL

Author: Jilles Oldenbeuving
Email: ojilles --* at *-- gmail.com

Origin for the idea: 
http://getsatisfaction.com/yunoo/topics/nieuwe_formaten_of_standaard_import_formaat

*/

define("NOR_DATE", 0);
define("NOR_DESCRIPTION", 1);
define("NOR_INTEREST_DATE", 2);
define("NOR_AMOUNT", 3);
define("NOR_BALANCE", 4);
define("EXCHANGE_RATE", (float) 0.134352603);

define("YUNOO_HEADER", '"Datum","Begunstigde","Rekening","Tegenrekening","Mutatiecode","Af/Bij","Bedrag","Mutatiesoort","Mededelingen"'."\n");
define("YUNOO_STR", '"%s","%s","","0","BA","%s","%s","Betaalautomaat","%s"'."\n");

setlocale(LC_ALL, "nl_NL");

function changeDates($date_str) {
	$dates = explode('-', $date_str);
	return $dates[2].$dates[1].$dates[0];
}


$handle = @fopen("php://stdin", "r"); // Open file form read.
	if ($handle) {
		// skip first input line
		$buffer = fgets($handle); // Read a line.
		$buffer = fgets($handle); // Read a line.
		echo YUNOO_HEADER;
		while (!feof($handle)) // Loop till end of file.
		{
			$buffer = fgets($handle, 4096); // Read a line.
			if (strlen($buffer) < 3) continue; // Skip any lines that are too short

			$strs = explode(';',$buffer);
		
			// Change individual strings	
			$strs[NOR_DATE] = changeDates($strs[NOR_DATE]);
			$strs[NOR_INTEREST_DATE] = changeDates($strs[NOR_INTEREST_DATE]);

			$strs[NOR_AMOUNT] = round(((float) str_replace(",",".",$strs[NOR_AMOUNT])) * EXCHANGE_RATE, 2);
			if ($strs[NOR_AMOUNT] < 0) {
				$add_substract = "Af";
				$strs[NOR_AMOUNT] = $strs[NOR_AMOUNT] * -1;
			} else {
				$add_substract = "Bij";
			}

			// Write back out in Yunoo format
			printf(YUNOO_STR, $strs[NOR_DATE],
				          $strs[NOR_DESCRIPTION],
					  $add_substract,
				          $strs[NOR_AMOUNT],
					  ""
			);
		}
		fclose($handle); // Close the file.
	}
?>
