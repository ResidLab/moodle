<?php
global $CFG;
include_once('../../../config.php');
require_once("ClienteWim.php");
$id_curso = $_GET['course'];

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else {

	$navlinks[ 0 ] = array('name' => $course->fullname,'link' => "$CFG->wwwroot/course/view.php?id=$id_curso",'type' => 'misc');
	$navlinks[ 1 ] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);

	print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );

	$client = new ClienteWim();
	$resMessage = $client->exportarUsuarios($id_curso);
	if ($resMessage)
	{
		$simplexml = new SimpleXMLElement($resMessage->str);
		if($simplexml->cadastroSucesso == 'true'){
			redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgsucesso', 'block_wim'));
			print_footer( $course );
		}else{
			redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
		}
	}
	print_footer( $course );
}
?>
