<?php

include_once("../../../config.php");
include_once("../scripts/functions.php");
include_once ("srd_lib.php");
include_once ("srd_service-client.php");
global $CFG;

$avaliacao = $_POST['avaliacao'];
$id_rec = $_POST['id_rec'];
$id_user = $_POST['id_user'];


$sqlavals = "SELECT * FROM {$CFG->prefix}wim_avaliacao_recomendacao WHERE id_user = {$id_user} AND id_recomendacao = {$id_rec}";
$avals = get_records_sql($sqlavals);
if(empty($avals)){
	$a = new Object();
	$a->id_recomendacao = $id_rec;
	$a->id_user = $USER->id;
	$a->avaliacao = $avaliacao;
	//print_object($a);
	insert_record("wim_avaliacao_recomendacao",$a);
}else{
	$a = new Object();
	$a->id = current($avals)->id;
	$a->avaliacao = $avaliacao;
	//print_object($a);
	update_record("wim_avaliacao_recomendacao",$a);
}

atribuir_nota_recomendacao($id_rec, $avaliacao);

?>