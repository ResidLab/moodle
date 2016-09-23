<?php

include_once("../../../config.php");
include_once("../scripts/functions.php");
include_once ("srd_lib.php");
include_once ("srd_service-client.php");
global $CFG;

$id_aluno = $_POST['id_aluno'];
$link = $_POST['link'];
$id_curso = $_POST['id_curso'];

/*
$a = new Object();
$a->link = $link;
$a->id_aluno = $USER->id;
$a->id_curso = $id_curso;
insert_record("wim_recomendacoes_sugeridas",$a);
*/

envia_link_recomendacao($link);

?>