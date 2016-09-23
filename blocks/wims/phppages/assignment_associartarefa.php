<?php
global $CFG;
//10.1.0.144
include_once('../scripts/functions.php');
include_once('../../../config.php');
require_once("ClienteWim.php");
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
	echo '<br/><br/>';
	if(empty($_GET['associa'])){
		$client = new ClienteWim();
		$resMessage = $client->importarSheets($id_curso);

		if ($resMessage)
		{
			$simplexml = new SimpleXMLElement($resMessage->str);
			if($simplexml->retornaSheet != 'false'){
				$i=0;
				foreach ($simplexml->sheet as $sheet){
					$assignment_id = $_GET['assignment'];

					$sqlassignment_existente	= "SELECT id FROM {$CFG->prefix}assignment_sheetwims  WHERE id_sheet = $sheet->id AND id_curso = $id_curso";
					$linhassignment	= get_records_sql($sqlassignment_existente);
					$assignment = current($linhassignment)->id;
					if(empty($assignment)){
						$i++;
						echo '<center>Sheets Relacionados</center>';
						echo '<center>'.'<a href="'.$CFG->wwwroot . '/blocks/wim/phppages/assignment_associartarefa.php?associa=1&sheet='.$sheet->id.'&course='.$id_curso.'&assignment='.$_GET['assignment'].'">'.$sheet->title.'</a>';
					}
				}
				if($i == 0){
					redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
				}
			}
			else{
				redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
			}
		}
		else{
			redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
		}
	}else{
		$associar_sheet_tarefa = new Object();
		$associar_sheet_tarefa->id_curso = $id_curso;
		$associar_sheet_tarefa->id_assignment = $_GET['assignment'];
		$associar_sheet_tarefa->id_sheet  = $_GET['sheet'];
		insert_record("assignment_sheetwims", $associar_sheet_tarefa);
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgsucesso', 'block_wim'));
	}
	print_footer( $course );

}
?>