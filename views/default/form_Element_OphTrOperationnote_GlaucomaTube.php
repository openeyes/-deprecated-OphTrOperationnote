<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

$layoutColumns=$form->layoutColumns;
$form->layoutColumns=array('label'=>3,'field'=>9);
?>
	<div class="element-fields">
		<div class="row eyedraw-row trabectome">
			<div class="fixed column">
				<?php
				$this->widget('application.modules.eyedraw.OEEyeDrawWidget', array(
								'doodleToolBarArray' => array(
										0 => array('TubeExtender','Patch', 'PI', 'Supramid', 'TubeLigation'),
								),
								'onReadyCommandArray' => array(
										array('addDoodle', array('AntSeg')),
										array('addDoodle', array('Tube')),
										array('deselectDoodles', array()),
								),

								'bindingArray' => array(
										'Tube' => array(
												'platePosition' => array('id' => 'Element_OphTrOperationnote_GlaucomaTube_plate_position_id', 'attribute' => 'data-value'),
										),
								),
								'side'=>$this->selectedEyeForEyedraw->shortName,
								'idSuffix' => $element->elementType->id,
								'mode'=>'edit',
								'width'=>300,
								'height'=>300,
								'model'=>$element,
								'attribute'=>'eyedraw',
								'offsetX' => 10,
								'offsetY' => 10,
								'scale' => 0.72,
								'template' =>  'OEEyeDrawWidget_InlineToolbar'
						));
				?>
			</div>
			<div class="fluid column">
				<?php
				$plate_positions = OphTrOperationnote_GlaucomaTube_PlatePosition::model()->activeOrPk($element->plate_position_id)->findAll();
				$html_options = array('label' => $element->getAttributeLabel('plate_position_id'), 'options' => array());
				foreach ($plate_positions as $pp) {
					$html_options['options'][$pp->id] = array('data-value' => $pp->eyedraw_value);
				}
				echo $form->dropDownList($element, 'plate_position_id', CHtml::listData($plate_positions,'id','name'),$html_options,false,array('field'=>3))?>
				<div id="div_Element_OphTrOperationnote_GlaucomaTube_plate_limbus" class="row field-row">
					<div class="large-3 column">
						<label for="Element_OphTrOperationnote_GlaucomaTube_plate_limbus"><?= $element->getAttributeLabel('plate_limbus'); ?></label>
					</div>
					<div class="large-2 column">
						<?php echo CHtml::activeTextField($element, 'plate_limbus', array('class' => 'clearWithEyedraw')); ?>
					</div>
					<div class="large-1 column end field-info postfix align">
						mm
					</div>
				</div>

				<?php echo $form->dropDownList($element, 'tube_position_id', CHtml::listData(OphTrOperationnote_GlaucomaTube_TubePosition::model()->activeOrPk($element->tube_position_id)->findAll(),'id','name'),array('empty'=>'- Please select -'),false,array('field'=>3))?>
				<?php echo $form->checkbox($element, 'stent', array('class' => 'clearWithEyedraw'))?>
				<?php echo $form->checkbox($element, 'slit', array('class' => 'clearWithEyedraw'))?>
				<?php echo $form->checkbox($element, 'visco_in_ac', array('class' => 'clearWithEyedraw'))?>
				<?php echo $form->checkbox($element, 'flow_tested', array('class' => 'clearWithEyedraw'))?>
				<?php echo $form->textArea($element, 'description', array('rows' => 4, 'class' => 'autosize clearWithEyedraw'))?>
				<div class="row field-row">
					<div class="large-3 column">&nbsp;</div>
					<div class="large-4 column end">
						<button class="ed_report secondary small">Report</button>
						<button class="ed_clear secondary small">Clear</button>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php $form->layoutColumns=$layoutColumns;?>