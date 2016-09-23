<?php
global $CFG;
//10.1.0.144
include_once('../../../config.php');
include_once('../scripts/functions.php');
$id_curso	= $_GET['course'];

$info_turma = pegar_info_classe_wims($id_curso);
$referencia = current($info_turma)->ref_classe_wims;

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (empty($referencia)) {
	error(' Invalid Access! ');
}else if(!isadmin($USER->id)){
	error(' Invalid role! ');
}
else {

	$navlinks[ 0 ] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);
	print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );

	if (deletar_turma_moodle($id_curso))
	{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgsucesso', 'block_wim'));
	}
	else{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
	}

	print_footer( $course );

}
?>