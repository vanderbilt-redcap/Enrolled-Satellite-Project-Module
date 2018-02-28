<?php
namespace Vanderbilt\EnrolledSatelliteProjectExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

class EnrolledSatelliteProjectExternalModule extends AbstractExternalModule
{
	function redcap_every_page_top($project_id) {
		if(empty($project_id)) {
			return;
		} else {
			if(PAGE == "DataEntry/record_home.php") {
				echo $this->addCentralProjectLink($project_id, $_GET['id']);
			}
			$this->addSatelliteFunctions($project_id, $record, $instrument);
		}
	}

	function redcap_add_edit_records_page($project_id, $record, $instrument, $event_id) {
		ob_start();
		?>
			<style>
				table td.data button {
					display: none;
				}
			</style>
			<script type='text/javascript'>
				$(document).ready(function(){
					$('table td.data button:contains("Add new record")').parents('tr').remove();
					$('.projhdr:contains("Add / Edit Records")').next('p').html('You may view an existing record/response by selecting it from the drop-down lists below.');
					$('.projhdr:contains("Add / Edit Records")').html($('.projhdr:contains("Add / Edit Records")').html().replace('Add', 'View'));
				});
			</script>
		<?php
		$output = ob_get_contents();
		ob_clean();
		echo $output;
	}

	function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id) {
		echo $this->addCentralProjectLink($project_id, $record);
	}

	function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
		$module_settings = ExternalModules::getProjectSettingsAsArray([$this->PREFIX], $project_id);
		if(!empty($module_settings['piped-form']['value']) && is_array($module_settings['piped-form']['value']) && in_array($instrument, $module_settings['piped-form']['value'])) {
			/*echo $project_id.'<br>';
			echo $record.'<br>';
			echo $instrument.'<br>';
			echo $event_id.'<br>';
			echo '<hr>';
			die();*/
		}
	}

	/**
	 * Nicely formatted var_export for checking output .
	 */
	function pDump($value, $die = false) {
		highlight_string("<?php\n\$data =\n" . var_export($value, true) . ";\n?>");
		echo '<hr>';
		if($die) {
			die();
		}
	}

	/**
	 * Add enroll buttons (or view buttons if the record has already been enrolled) for applicable satellite projects.
	 */
	public function addSatelliteFunctions($project_id, $record, $instrument) {
		echo $this->enrolledSatelliteJS();
	}

	public function addCentralProjectLink($project_id, $record) {
		$module_settings = ExternalModules::getProjectSettingsAsArray([$this->PREFIX], $project_id);
		if(!empty($module_settings['central-project-id']['value'])) {
			$satPID = db_real_escape_string($module_settings['central-project-id']['value']);
			$sql = "SELECT project_id, project_name, app_title FROM redcap_projects WHERE project_id = ".$satPID;
			$results = $this->query($sql);
			$projData = db_fetch_assoc($results);

			ob_start();
			?>
				<script>
					$(document).ready(function(){
						var html = '<div style="padding-bottom: 20px; max-width: 800px;"><h4 style="border-bottom: 1px solid #d0d0d0">Central Project:</h4><a href="<?php echo APP_PATH_WEBROOT."DataEntry/record_home.php?pid=".$projData['project_id']."&arm=1&id=".$record; ?>"><button>Return to <?php echo $projData['app_title']; ?></button></a></div>';

						if($('#record_display_name').length) {
							$('#record_display_name').before(html);
						} else {
							$('#form').before(html);
						}
					});
				</script>
			<?php
			$output = ob_get_contents();
			ob_clean();
			return $output;
		}
	}

	/**
	 * 
	 */
	public function enrolledSatelliteJS() {
		ob_start();
		?>
			<script type='text/javascript'>
				$(document).ready(function(){
					$('.menubox a:contains("Add / Edit Records")').text('View / Edit Records');
				});
			</script>
		<?php
		$output = ob_get_contents();
		ob_clean();
		return $output;
	}
}
