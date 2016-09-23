<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL ^ E_DEPRECATED);


$config['wsdl'] = 'http://200.129.43.182/srd/service-server.php?wsdl';
#$config['wsdl'] = 'http://192.168.250.240/srd/service-server.php?wsdl';
$config['wsdl_server'] = 'http://200.129.43.179/servicosr/Servicos.asmx?wsdl';


$config_srd['wsdl'] = 'http://200.129.43.182/srd/service-server.php?wsdl';
#$config_srd['wsdl'] = 'http://192.168.250.240/srd/service-server.php?wsdl';
$config_srd['wsdl_server'] = 'http://200.129.43.179/servicosr/Servicos.asmx?wsdl';


function dbg($array) {
	echo '<pre>';
	print_r($array);
	echo '</pre>';

}
?>
