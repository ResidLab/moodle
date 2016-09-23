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
}
else {

	$navlinks[ 0 ] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);
	print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	$url = base64_decode($_GET['acc']);
	$assig = $_GET['assig'];
	$mod = $_GET['mod'];

	if ($url)
	{
		//icons gnome-power-statistic gnome-tetravex.svg gnome-tali.svg gnome-sticky-notes-applet.svg gsd-xrandr.svg
		$url_volta = "$CFG->wwwroot/mod/assignment/view.php?id=$mod";
		echo "<input name='voltar' type='button' value='Voltar' onclick='window.location=\"$url_volta\";'>";
		echo '<iframe name="<?php echo(COOK_PREF); ?>_top" src="'.$url.'" frameborder="0" noresize="yes" scrolling="no" width="100%" height="900px"></iframe>';
		echo "<input name='voltar' type='button' value='Voltar' onclick='window.location=\"$url_volta\";'>";

	}
	else{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
	}

	print_footer( $course );

}
?>
