<?
header('Content-type: text/html; charset=iso-8859-1');
include_once ("srd_lib.php");

global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
$id_curso = $_POST['id_curso'];
$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;

include_once ("srd_service-client.php");
echo '<center>';
if ($_POST['ativa_sr']) {
	$a = new Object();
	$a->id = key($info_turma);
	$a-> atualizado = strtotime(date("Y-m-d H:i:s")) ;
	$a->ativa_sr=1;
	update_record("config_wim",$a);

	if (ativa_sr($_POST['id_curso'], $_POST['ref_classe_wims'], $_POST['senha_professor']))
		echo '<br> Dados enviados com sucesso';
	else
		echo '<br> Dados n‹o foram enviados com sucesso';
} else {
	$a = new Object();
	$a->id = key($info_turma);
	$a-> atualizado = 0 ;
	$a->ativa_sr=0;
	update_record("config_wim",$a);
	
	if (ativa_sr($_POST['id_curso'], $_POST['ref_classe_wims'], $_POST['senha_professor'], 0))
		echo '<br> Dados enviados com sucesso';
	else
		echo '<br> Dados n‹o foram enviados com sucesso';
}
echo '</center>';
?>