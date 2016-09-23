<?php

global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
include_once ("srd_lib.php");
include_once ("srd_service-client.php");

$id_curso = $_GET['course'];
$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;
$a = $_GET['a'];

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (empty($referencia)) {
	error(' Invalid Access! ');
}else {
	$navlinks[0] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation = build_navigation($navlinks);

	print_header_simple($COURSE->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	
	if($a)echo '<iframe name="fr_top" src="http://200.129.43.179/sr/Referencia/SemCategorizacao" frameborder="0" noresize="yes" scrolling="yes" width="100%" height="600px"></iframe>';
	else echo '<iframe name="fr_top" src="http://200.129.43.179/sr/Home" frameborder="0" noresize="yes" scrolling="yes" width="100%" height="600px"></iframe>';

}

//print_footer($COURSE);

?>