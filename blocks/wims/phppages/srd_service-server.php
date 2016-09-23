<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);*/

include_once('../../../config.php');

$del = "/";
require_once ('nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
$server = new soap_server;
$server -> configureWSDL('server.score', 'urn:server.score');
$server -> wsdl -> schemaTargetNamespace = 'urn:server.score';
//$server->xml_encoding = 'utf-8';
$server->soap_defencoding = 'utf-8';

$server -> register('envia_nomes', //nome do m�todo
array('curso' => 'xsd:string'), //par�metros de entrada
array('return' => 'xsd:string'), //par�metros de sa�da
'urn:server.score', //namespace
'urn:server.score#envia_nomes', //soapaction
'rpc', //style
'literal', //use
'Retorna o nome' //documenta��o do servi�o
);

function envia_nomes($id_curso) {
	//header('Content-Type: text/xml; charset=UTF8');
	global $CFG;
	$context = get_context_instance(CONTEXT_COURSE, $id_curso);

	$sqlusers = "SELECT u.id, u.firstname, u.lastname FROM {$CFG->prefix}role_assignments r, {$CFG->prefix}user u
				WHERE r.contextid = $context->id
				AND u.id = r.userid
				AND r.roleid = 5
				ORDER BY u.firstname ";
	$linhausers = get_records_sql($sqlusers);
	
	//@file_put_contents('/var/www/lero', $sqlusers);
	//return '{"1517":{"firstname":"Adrielly","lastname":"Maria Mendonça de Paiva Sousa","id":1517}}';//
	return json_encode($linhausers);
}

// requisi��o para uso do serviço
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server -> service($HTTP_RAW_POST_DATA);
?>


