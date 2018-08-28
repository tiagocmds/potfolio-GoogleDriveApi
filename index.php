<?php

$url_do_arquivo = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ-J4JhsR1TFU9iKPJFPOE77U2VqYECdGp9eiP1_XRbUaLEoZu8PjrdODrxxSSsl7mBQRks3T_Y9WTy/pub?output=csv";

if (($identificador = fopen($url_do_arquivo, 'r')) !== FALSE) {
    echo '<pre>';
    
    while (($linha_do_arquivo = fgetcsv($identificador, 0, ",")) !== FALSE) {
        
       
        echo $linha_do_arquivo[2]."<br>";
        //print_r(explode('/',$linha_do_arquivo[3]))  ;
        //file_put_contents($linha_do_arquivo[1],file_get_contents($linha_do_arquivo[3]));
    }
    echo '</pre>';
    
    fclose($identificador);
} else {
    
    echo 'Não foi possível abrir o arquivo';
}