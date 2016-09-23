<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

include_once ("srd_lib.php");

global $config;

$del = "/";
require_once ('nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
$wsdl = $config['wsdl'];
$client = new nusoap_client($wsdl, true);
$err = $client -> getError();
if ($err) {
	echo "Erro no construtor<pre>" . $err . "</pre>";
}

//action = 1 => ativar SR e incluir os dados no banco
//action = 0 => desativar SR e excluir dados no banco
function ativa_sr($id_curso, $ref_classe_wims, $senha_professor, $action = 1) {
	global $client;
	$result = $client -> call('ativa_sr', array($id_curso, $ref_classe_wims, $senha_professor, $action));

	if ($client -> fault) {
		echo "Falha<pre>" . print_r($result) . "</pre>";
	} else {

		$err = $client -> getError();
		if ($err) {
			echo "Erro<pre>" . $err . "</pre>";
		} else {
			return $result;
		}
	}
}

function recebe_recomendacao($id_curso, $ref_classe_wims) {
	global $client;
	$result = $client -> call('envia_recomendacoes', array($id_curso, $ref_classe_wims));

	if ($client -> fault) {
		echo "Falha<pre>" . print_r($result) . "</pre>";
	} else {

		$err = $client -> getError();
		if ($err) {
			echo "Erro<pre>" . $err . " ... </pre>";
		} else {
			return $result;
		}
	}
}

function atribuir_nota_recomendacao($id_recomendacao, $nota) {
	global $config;
 	$del = "/";
	require_once ('nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
	$wsdl = $config['wsdl_server'];
	$clientesrv = new nusoap_client($wsdl, true);
	$err = $clientesrv -> getError();
	if ($err) {
		echo "Erro no construtor<pre>" . $err . "</pre>";
	}
	
	//dbg($id_recomendacao);
	//dbg($nota);
	$result = $clientesrv -> call('AtribuirNotaUmAUm', array('recomendacaoId' => $id_recomendacao, 'nota' => $nota));

	if ($clientesrv -> fault) {
		echo "Falha<pre>" . print_r($result) . "</pre>";
	} else {
		$err = $clientesrv -> getError();
		if ($err) {
			echo "Erro<pre>" . $err . " ... </pre>";
		} else{
			print_r($result);
		}
	}
}

function envia_link_recomendacao($link) {
	global $config;
 	$del = "/";
	require_once ('nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
	$wsdl = $config['wsdl_server'];
	$clientesrv = new nusoap_client($wsdl, true);
	$err = $clientesrv -> getError();
	if ($err) {
		echo "Erro no construtor<pre>" . $err . "</pre>";
	}
	
	//dbg($id_recomendacao);
	//dbg($nota);
	$result = $clientesrv -> call('CadastrarReferencia', array('link' => $link));

	if ($clientesrv -> fault) {
		echo "Falha<pre>" . print_r($result) . "</pre>";
	} else {
		$err = $clientesrv -> getError();
		if ($err) {
			echo "Erro<pre>" . $err . " ... </pre>";
		} else{
			print_r($result);
		}
	}
}



//echo recebe_recomendacao(29, 6970319);
?>

