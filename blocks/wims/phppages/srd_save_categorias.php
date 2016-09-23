<?php
global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");

$id_curso = $_POST['cursoMoodle'];
$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (empty($referencia)) {
	error(' Invalid Access! ');
}
else {
	$navlinks[1] = array('name' => $COURSE->shortname,'link' => "#",'type' => 'misc');
	$navlinks[0] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');
	$navigation = build_navigation($navlinks);
	print_header_simple($COURSE->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';

	$sqldata = "SELECT * FROM {$CFG->prefix}wim_sheet_categoria WHERE id_curso = {$id_curso} AND id_sheet = {$_POST['folha']}";
	$data = get_records_sql($sqldata);
	if(!empty($data)){
		delete_records('wim_sheet_categoria', 'id_curso' , $id_curso, 'id_sheet' , $_POST['folha']);
	}

	foreach($_POST['nivel'] as $key => $nivel){
		$a = new Object();
		$a->id_curso = $id_curso;
		$a->id_sheet = $_POST['folha'];
		$a->id_questao = $key;
		$a->id_categoria = $_POST['categoria'][$key];
		$a->id_nivel = $nivel;
		if (insert_record("wim_sheet_categoria" ,$a)) {
			//echo '<a href="'.$link.'">'.$id_usuario.' <span class="badge">'.$link.'</span></a>'; 
			//echo '<br><br>';
		}else {
			echo 'Erro ao salvar'; exit;
		}
		$array[$key]['id_questao'] =   $key;
		$array[$key]['id_nivel'] = $nivel;
		$array[$key]['id_categoria'] =$_POST['categoria'][$key];
	}
	
	//print_r($array);
	//echo json_encode($array);
	//exit;
	global $config;
	$del = "/";
	require_once ('nusoap-0.9.5' . $del . 'lib' . $del . 'nusoap.php');
	$wsdl = $config['wsdl_server'];
	$cliente = new nusoap_client($wsdl, true);
	$err = $cliente -> getError();
	if ($err) {	echo "Erro no construtor<pre>" . $err . "</pre>";}
	//$categorias = $cliente -> call('Mapear',array());
	$cliente ->call('Mapear', array('cursoMoodle' => $id_curso, 'salaWims' => $referencia, 'folha' => $_POST['folha'], 'json' => json_encode($array)));
	if ($cliente -> fault) {
			echo "Falha<pre>" . print_r($result) . "</pre>";
		} else {

			$err = $cliente -> getError();
			if ($err) {
				echo "Erro<pre>" . $err . " ... </pre>";
			} 
		}
	//print_r($categorias);
	//file_put_contents('srd_categ/'.$id_curso.'_'.$referencia,json_encode($_POST));
	@redirect("$CFG->wwwroot/blocks/wim/phppages/srd_categorizacao.php?course=$id_curso", 'Dados salvos com sucesso');
}

?>