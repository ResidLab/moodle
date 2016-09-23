<?php
global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");

$id_curso = $_GET['course'];
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


	$sql = "SELECT mas.id_sheet, mas.id_assignment, ma.name FROM {$CFG->prefix}assignment_sheetwims AS mas, {$CFG->prefix}assignment as ma WHERE mas.id_assignment = ma.id AND mas.id_curso = {$id_curso}";
	$linhasheets = get_records_sql($sql);
	echo '<fieldset><legend>Sheets</legend>';
	echo '<center><table style="width:80%;align:center;">
		<tr style="text-align:center;">
		  <!--<th>Id</th>-->
		  <th>Status</th>
		  <th>T&iacute;tulo</th> 
		  <th> </th>
		</tr>';
	foreach($linhasheets as $sheet){
		$sql2 = "SELECT COUNT(id_curso) as N FROM  {$CFG->prefix}wim_sheet_categoria WHERE id_sheet ={$sheet->id_sheet} AND id_curso = {$id_curso}";
		$linha2 = get_records_sql($sql2);
		$n = current($linha2)->N; 
		if($n > 0) $str_categorizado = 'CATEGORIZADO';
		else $str_categorizado = 'N&Atilde;O CATEGORIZADO';
		echo '<tr style="text-align:center;">';
		echo '<!--<td>'.$sheet->id_assignment.'</td>-->';
		echo '<td>'.$str_categorizado.'</td>';		
		echo '<td>'.$sheet->name.'</td>';
		echo '<td><a href="'.$CFG->wwwroot.'/blocks/wim/phppages/srd_categorizacao_questao.php?sheet='.$sheet->id_sheet.'&assignment='.$sheet->id_assignment.'&course='.$id_curso.'">Categorizar Exerc&iacute;cios</a></td>';
		echo '</tr>';
	}
	echo '</table></fieldset>';

}
print_footer($COURSE);

?>