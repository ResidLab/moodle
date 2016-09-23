<?php
global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");

$id_curso = $_GET['course'];
$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;
$id_user = $_GET['id_aluno'];

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
		$sqlrecomendacoes = "SELECT * FROM {$CFG->prefix}wim_recomendacoes WHERE id_curso = {$id_curso} AND id_user = {$id_user}";
		$recomendacoes = get_records_sql($sqlrecomendacoes);

		echo '<fieldset><legend>Recomenda&ccedil;&otilde;es</legend>';
		//echo    '<form action="#" method="post">';
		echo	'<center><table style="width:95%;align:center;">
  			<tr style="text-align:center;">
			      <th>Folha</th> 
			      <th>Tipo de Interven&ccedil;&atilde;o</th> 
			      <th>Categoria</th> 
			      <th>Recomenda&ccedil;&atilde;o</th> 
			       <th>Avalia&ccedil;&atilde;o Aluno</th> 
            </tr>';
		foreach($recomendacoes as $recomendacao){ 	
				//dbg($recomendacao,'html');
			    echo '<tr style="text-align:center;">';
  			    echo '<td>'.$recomendacao->id_sheet. '   </td>';
			    echo '<td>'.utf8_decode($recomendacao->tipo_intervencao).'</td>';
			    echo '<td>'.utf8_decode($recomendacao->categoria).'</td>';
  			    echo '<td> <a target="_blank"href="'.$recomendacao->link_recomendacao.'">'.$recomendacao->link_recomendacao.'</a></td>';
			    echo '<td>'.$recomendacao->avaliacao_aluno. '</td>';
			 echo '</tr>';
		}
		echo '</table>';
		//echo '<br/><input type="submit" value="Salvar"></form>';
		echo '</fieldset>';
}
print_footer($COURSE);
?>