<? 
	$company_string = explode(' ', 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ИНСТРОЙТЕХКОМ"');
            if ((strtolower($company_string[0]) == 'индивидуальный') && (strtolower($company_string[1]) == 'предприниматель')) {
                $company_name = 'ИП ';

                foreach ($company_string as $key => $company_item) {
                    if ($key > 1) {
                        $company_name .= $company_item . ' ';
                    }
                }
            } else if ((strtolower($company_string[0]) == 'общество') && (strtolower($company_string[3]) == 'ответственностью')) {
                $company_name = 'ООО ';

                foreach ($company_string as $key => $company_item) {
                    if ($key > 3) {
                        $company_name .= $company_item . ' ';
                    }
                }
            } else {
                foreach ($company_string as $key => $company_item) {
                    $company_name .= $company_item  . ' ';
                }
            }
    print_r(mb_strtolower($company_string[0]));
	$token = "04e24a48faf5e9dc41b1179f6e22b332e4cccbaa";

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Accept: application/json", "Authorization: Token " . $token . ""));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => '5404193927']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    print_r(json_decode($output)->suggestions[0]->value);



