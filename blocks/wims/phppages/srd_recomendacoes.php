<?php
global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");

$id_curso = $_GET['course'];
echo $id_user = $USER->id;
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
	$navlinks[0] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);

	print_header_simple($COURSE->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';

	$recomendacoes = get_records('wim_recomendacoes', 'id_curso', $id_curso, 'id_user', $id_user);
	dbg($recomendacoes, 'html');
	exit;
	echo '<center><table style="width:80%;align:center;">
		<tr style="text-align:left;">
		  <th>Nome Aluno</th>
		  <th>Tipo de Observa&ccedil;&atilde;o</th> 
		  <th>Ver Recomenda&ccedil;&otilde;es</th> 
		</tr>';
	
	foreach($recomendacoes as $recomendacao){ 
		//dbg($recomendacao, 'html');exit;		
		$dados_recomendacao[$recomendacao->id_user][$recomendacao->id_sheet]['observacao'] = $recomendacao->observacao;
	}
	
	dbg($dados_recomendacao, 'html');
	exit;
	foreach($dados_recomendacao as $observacoes){ 
		$str = '';		
		$sqlusers = "SELECT * FROM {$CFG->prefix}user u WHERE u.id = {$id_user}";
		$user = get_records_sql($sqlusers);
		$userid = $recomendacao->id_user;
        		$userfirstname = current($user)->firstname;
        		$userlastname = current($user)->lastname;
        		$userpic = current($user)->picture;
        		//$imagem = print_user_picture($userid, $id_curso, $userpic, false, true);
		foreach($observacoes as $id_sheet => $observacao){
			$str .= '| sheet: '. $id_sheet .' : '. $observacao['observacao'] . '	'; 
		} 		
		$str .= ' |';

		echo '<tr style="text-align:left;">';
		echo '<td>'.ucwords(strtolower($userfirstname)) .' '.ucwords(strtolower($userlastname)) .'</td>';
		echo '<td>'.$str.'</td>';
		echo '<td><a href="srd_ver_recomendacao_prof.php?course='.$id_curso.'&id_aluno='.$recomendacao->id_user.'">Acessar</td>';
		echo '</tr>';
	}
	echo '</table>';

}
print_footer($COURSE);
?>