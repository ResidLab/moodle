<?php

global $CFG;
include_once('../../../config.php');
include_once('../scripts/functions.php');
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

	print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';

	$check_value = current($info_turma)->ativa_sr ? 0 : 1;

	if (current($info_turma)->ativa_sr)
		$submit = 'Desativar SR';
	else
		$submit = 'Ativar SR';

	echo '<center><form id="ajax_form"  class="form-signin" method="post" role="form" action="">
			  <input type="hidden" name="id_curso" value="' . current($info_turma)->id_curso . '">
			  <input type="hidden" name="ref_classe_wims" value="' . current($info_turma)->ref_classe_wims . '">
			  <input type="hidden" name="senha_professor" value="' . current($info_turma)->senha_professor . '">
			  <input id="check-form" type="hidden" name="ativa_sr" value="' . $check_value . '">
			  <button id="button-form" class="btn btn-lg btn-primary btn-block" type="submit">' . $submit . '</button>
	         </form></center>';
	echo '<div id="info">';
	echo '</div>';	

}

print_footer( $course );

?>

<!-- <link href="../css/bootstrap.css" rel="stylesheet"> -->
<script src="../js/jquery.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#ajax_form').submit(function() {
			var dados = $(this).serialize();
			$.ajax({
				type : "POST",
				url : "srd_save.php",
				data : dados,
				success : function(data) {
					//alert(data);
					//location.href = "index.php"
					$('#info').html(data);
					if (($('#check-form').val() == 0)) {
						$('#button-form').html('Ativar SR');
						$('#check-form').val(1);
					} else {
						$('#button-form').html('Desativar SR');
						$('#check-form').val(0);
					}
				}
			});

			return false;
		});
	}); 
</script>