<?php

require_once 'HTML/Table.php';

$url_sbudget = "https://search-spar.spar-ics.com/fact-finder/rest/v4/search/products_lmos_at?query=S-Budget&q=S-Budget&hitsPerPage=800&page=1";
$file_path_sbudget = "dataSbudget.json";

// write to a file for testing
$data_sbudget = file_get_contents($url_sbudget);
$data_sbudget = json_decode($data_sbudget, true);
file_put_contents($file_path_sbudget, json_encode($data_sbudget));

// read from saved file
$file_data_sbudget = file_get_contents($file_path_sbudget);
$file_data_sbudget = json_decode($file_data_sbudget, true);

$url_clever = "https://shop.billa.at/api/search/full?searchTerm=clever&storeId=00-10&pageSize=800";
$file_path_clever = "dataclever.json";

// write to a file for testing
$data_clever = file_get_contents($url_clever);
$data_clever = json_decode($data_clever, true);
file_put_contents($file_path_clever, json_encode($data_clever));

// read from saved file
$file_data_clever = file_get_contents($file_path_clever);
$file_data_clever = json_decode($file_data_clever, true);

$products_clever = [];
foreach ($file_data_clever['tiles'] as $item) {
  $p = array();
  $p[0] = '/Clever/';
  $p[1] = '/Clever /';
  $name = preg_replace($p, "", $item['data']['name']);
  $name = preg_replace('/\s+/', ' ', $name);
  $name = ltrim($name);
  $name = rtrim($name);
  $name = ltrim($name, ".");
  $product = [
    "name" =>  $name,
    "price" => $item['data']['price']['normal']
  ];
  $products_clever[] = $product;
  #  }
}

# echo str_pad("\n\nProdukt", 40) . "Preis\n";
# echo str_repeat("-", 45) . "\n";
$c = 0;
foreach ($products_clever as $product) {
  $c++;
  # echo str_pad($product["name"], 40) . " € " . $product["price"] . "\n";
}

$products_sbudgets = [];
foreach ($file_data_sbudget['hits'] as $item) {
  $p = array();
  $p[0] = '/S-BUDGET /';
  $p[1] = '/S-Budget /';
  $p[2] = '/S-Bu./';
  $p[3] = '/S-BU./';
  $p[4] = '/S-Bu. /';
  $p[5] = '/S-BU. /';
  $p[6] = '/S-BUDGET/';
  $p[7] = '/S-Budget/';
  $p[8] = '/SBUDGET/';
  $p[9] = '/SBUDGET /';
  $p[10] = '/-/';
  $name = preg_replace($p, "", $item['masterValues']['name']);
  $name = str_replace("oe", "ö", $name);
  $name = str_replace("ae", "ä", $name);
  $name = str_replace("ue", "ü", $name);
  $name = str_replace("Oe", "Ö", $name);
  $name = str_replace("Ae", "Ä", $name);
  $name = str_replace("Ue", "Ü", $name);
  $name = preg_replace('/\s+/', ' ', $name);
  $name = ltrim($name);
  $name = ltrim($name, ".");
  $name = rtrim($name);
  $product = [
    "name" => $name,
    "price" => $item['masterValues']['price']
  ];
  $products_sbudgets[] = $product;
  #  }
}

#echo str_pad("Produkt", 40) . "Price\n";
#echo str_repeat("-", 45) . "\n";
$s = 0;
foreach ($products_sbudgets as $product) {
  $s++;
  #echo str_pad($product["name"], 40) . " € " . $product["price"] . "\n";
}

echo "\n\nAnzahl S-Budget Produkte: " . $s . "\n<BR>";
echo "Anzahl Clever Produkte:   " . $c . "\n\n<BR>";


# echo str_pad("S_BUDGET", 40) . " : Preis : " . str_pad("Clever", 40) . "\n\n";
$products_compare = [];
$f = 0;
foreach ($products_sbudgets as $product_b) {
  foreach ($products_clever as $product_c) {
    # $stri = substr($product_c["name"], 0, 5);
    $stri =  explode(" ", $product_c["name"]);
    # $stri = $product_c["name"];
    # echo $stri[0] . "\n";
    $position = strpos($product_b["name"], $stri[0]);
    if ($position !== false && $product_b["price"] == $product_c["price"]) {
      # echo str_pad($product_b["name"], 40) . " : " . $product_b["price"] . " : " . str_pad($product_c["name"], 40) .  " : " . str_pad($stri[0], "20") . "\n";

      $product = [
        "sbudget" => $product_b["name"],
        "price" => $product_b["price"],
        "clever" => $product_c["name"],
        "searchstring" => $stri[0],
      ];
      $products_compare[] = $product;

      $f++;
      break;
    }
  }
}
echo "Zufälle ca. " . $f . " (+/-5%)\n<BR>";


$table = new HTML_Table(array('border' => '1', 'padding' => '20', 'cellspacing' => '10', 'cellpadding' => '5'));
$table->addRow(array('S-Budget', 'Preis', 'Clever', 'Suchstring'));

foreach ($products_compare as $product) {
  $table->addRow(array($product['sbudget'], $product['price'], $product['clever'], $product['searchstring']));
}

$html = $table->toHtml();
echo $html;
