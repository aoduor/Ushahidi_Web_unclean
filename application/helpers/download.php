<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Reports Download helper class.
 *
 * @package	   Admin
 * @author	   Ushahidi Team
 * @copyright  (c) 2012 Ushahidi Team
 * @license	   http://www.ushahidi.com/license.html
 */
class download_Core {
	
	/**
	 * Download Reports in CSV format
	 * @param Validation $post Validation object with the download criteria 
	 * @param array $incidents Reports to be downloaded
	 * @param array $custom_forms Custom form field structure and values
	 */
	public static function download_csv($post, $incidents, $custom_forms)
	{
		// Column Titles
		ob_start();
		echo "#,INCIDENT TITLE,INCIDENT DATE";
		$item_map = array(
		    1 => 'LOCATION',
		    2 => 'DESCRIPTION',
		    3 => 'CATEGORY',
		    4 => 'LATITUDE',
		    5 => 'LONGITUDE',
		    7 => 'FIRST NAME, LAST NAME, EMAIL'
		);
		
		foreach($post->data_include as $item)
		{		
			if ( (int)$item == 6)
			{
				foreach($custom_forms as $field_name)
				{
					echo ",".$field_name['field_name'];
				}

			}
			else if ( array_key_exists($item, $item_map))
			{
			    echo sprintf(",%s", $item_map[$item]);
			}
		}

		echo ",APPROVED,VERIFIED";

		// Incase a plugin would like to add some custom fields
		$custom_headers = "";
		Event::run('ushahidi_filter.report_download_csv_header', $custom_headers);
		echo $custom_headers;

		echo "\n";

		foreach ($incidents as $incident)
		{
			echo '"'.$incident->id.'",';
			echo '"'.self::_csv_text($incident->incident_title).'",';
			echo '"'.$incident->incident_date.'"';

			foreach($post->data_include as $item)
			{
				switch ($item)
				{
					case 1:
					echo ',"'.self::_csv_text($incident->location->location_name).'"';
					break;

					case 2:
					echo ',"'.self::_csv_text($incident->incident_description).'"';
					break;

					case 3:
					echo ',"';

					foreach($incident->incident_category as $category)
					{
						if ($category->category->category_title)
						{
							echo self::_csv_text($category->category->category_title) . ", ";
						}
					}
					echo '"';
					break;

					case 4:
					echo ',"'.self::_csv_text($incident->location->latitude).'"';
					break;

					case 5:
					echo ',"'.self::_csv_text($incident->location->longitude).'"';
					break;

					case 6:
					$incident_id = $incident->id;
					$custom_fields = customforms::get_custom_form_fields($incident_id,'',false);
					if ( ! empty($custom_fields))
					{
						foreach($custom_fields as $custom_field)
						{
							echo',"'.self::_csv_text($custom_field['field_response']).'"';
						}
					}
					else
					{
						$custom_field = customforms::get_custom_form_fields('','',false);
						foreach ($custom_field as $custom)
						{
							echo',"'.self::_csv_text("").'"';
						}
					}
					break;

					case 7:
					$incident_person = $incident->incident_person;
					if($incident_person->loaded)
					{
						echo',"'.self::_csv_text($incident_person->person_first).'"'.',"'.self::_csv_text($incident_person->person_last).'"'.
							',"'.self::_csv_text($incident_person->person_email).'"';
					}
					else
					{
						echo',"'.self::_csv_text("").'"'.',"'.self::_csv_text("").'"'.',"'.self::_csv_text("").'"';
					}
					break;
				}
			}

			if ($incident->incident_active)
			{
				echo ",YES";
			}
			else
			{
				echo ",NO";
			}

			if ($incident->incident_verified)
			{
				echo ",YES";
			}
			else
			{
				echo ",NO";
			}

			// Incase a plugin would like to add some custom data for an incident
			$event_data = array("report_csv" => "", "incident" => $incident);
			Event::run('ushahidi_filter.report_download_csv_incident', $event_data);
			echo $event_data['report_csv'];
			echo "\n";
		}
		$report_csv = ob_get_clean();

		// Output to browser
		header("Content-type: text/x-csv");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=" . time() . ".csv");
		header("Content-Length: " . strlen($report_csv));
		echo $report_csv;
		exit;
	}
	
	/**
	 * Download Reports in XML format
	 * @param Validation $post Validation object with the download criteria 
	 * @param array $incidents reports to be downloaded
	 * @param array $categories deployment categories
	 * @param array $custom_forms Custom form field structure and values
	 */
	public static function download_xml($post, $incidents, $categories, $custom_forms)
	{
		// Adding XML Content
		header('Content-type: text/xml; charset=UTF-8');
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=" . time() . ".xml");
		$writer = new XMLWriter;
		$writer->openMemory();
		$writer->startDocument('1.0', 'UTF-8');
		$writer->setIndent(true);

		/* Start Import Tag*/
		$writer->startElement('import');
		
		foreach ($post->data_include as $item)
		{
			switch($item)
			{
				case 3:
				/* Start Categories element */
				$writer->startElement('categories');
				if(count($categories) > 0)
				{
					foreach ($categories as $category)
					{
						$writer->startElement('category');
				
						/* Add Category attributes */
						// ID
						$writer->startAttribute('id');
							$writer->text($category->id);

						// Color
						$writer->startAttribute('color');
							$writer->text($category->category_color);

						// Visible or hidden?
						$writer->startAttribute('visible');
							$writer->text($category->category_visible);	

						// Category position
						$writer->startAttribute('position');
							$writer->text($category->category_position);

						/* Add Category elements */
						// Category Title
						$writer->startElement('title');
							$writer->text($category->category_title);
						$writer->endElement();

						// Category Description
						$writer->startElement('description');
							$writer->text($category->category_description);
						$writer->endElement();

						// Category's parent
						$writer->startElement('parent');
							$writer->text($category->parent_id);
						$writer->endElement();


						// Category Translation
						$translations = ORM::factory('category_lang')->where('category_id', $category->id)->find_all();
				
						// If translations exist
						if (count($translations) > 0)
						{
							$writer->startElement('translations');
							foreach ($translations as $translation)
							{
								$writer->startElement('translation');
			
								// Translation localization?
									$writer->startAttribute('locale');
										$writer->text($translation->locale);
									$writer->endAttribute();

									// Translation for this Category Title
									$writer->startElement('title');
										$writer->text($translation->category_title);
									$writer->endElement();
					
									// Translation for this Category description
									$writer->startElement('description');
										$writer->text($translation->category_description);
									$writer->endElement();
								$writer->endElement();
							}
							$writer->endElement();
						}	
						$writer->endElement();
					}	
				}
		
				// If there are no categories
				else
				{
					$writer->text("There are no categories on this deployment");
				}
		
				/* Close Categories Element */
				$writer->endElement();
				break;
				
				case 6:
				/* Start Customforms Element */
				$writer->startElement('customforms');
			
				// If we have custom forms
				if (count($custom_forms) > 0)
				{
					$forms = ORM::factory('form')->find_all();
					foreach ($forms as $form)
					{	
						// Custom Form element
						$writer->startElement('form');

						/* Custom Form attributes */
						// Form ID
						$writer->startAttribute('id');
							$writer->text($form->id);

						// Form Active?
						$writer->startAttribute('active');
							$writer->text($form->form_active);

						/* Custom form elements */	
						// Form Title
						$writer->startElement('title');
							$writer->text($form->form_title);
						$writer->endElement();

						// Form Description
						$writer->startElement('description');
							$writer->text($form->form_description);
						$writer->endElement();
					
						// Get custom fields associated with this form
						$customfields = customforms::get_custom_form_fields('',$form->id,false);
						foreach ($customfields as $field)
						{
							// Custom Form Fields
							$writer->startElement('field');

							/* Custom Form Field Attributes */
							// Field_id
							$writer->startAttribute('id');
								$writer->text($field['field_id']);
								
							// Field Type i.e radio button, checkbox, date field, textfield, textarea, dropdown?
							$writer->startAttribute('type');
								$writer->text($field['field_type']);
						
							// Field required?
							$writer->startAttribute('required');
								$writer->text($field['field_required']);
								
							/* Get custom form field options */
							$options = ORM::factory('form_field_option')->where('form_field_id',$field['field_id'])->find_all();
							foreach ($options as $option)
							{
								if ($option->option_name == 'field_datatype')
								{
									// Data type i.e Free, Numeric, Email, Phone?
									$writer->startAttribute('datatype');
										$writer->text($option->option_value);
								}
								if ($option->option_name == 'field_hidden')
								{
									// Hidden Field?
									$writer->startAttribute('hidden');
										$writer->text($option->option_value);
								}
							}

							// Visible by i.e anyone, member, admin, superadmin?
							$writer->startAttribute('visible-by');
								$writer->text($field['field_ispublic_visible']);

							// Submit by i.e anyone, member, admin, superadmin?
							$writer->startAttribute('submit-by');
								$writer->text($field['field_ispublic_submit']);

							/* Custom Form Field elements */
							// Field name
							$writer->startElement('name');
								$writer->text($field['field_name']);
							$writer->endElement();

							// Default Value
							$writer->startElement('default');
								$writer->text($field['field_default']);
							$writer->endElement();

							// Close Custom form field element	
							$writer->endElement();
						} 
				
						// Close Custom Form Element
						$writer->endElement();	
					}	
				}
		
				// We have no Custom forms
				else
				{
					$writer->text("There are no custom forms on this deployment");
				}
		
				/* End Custom Forms Element */
				$writer->endElement();
				break;
			}
		}
				
		/* Start Reports Element*/
		$writer->startElement('reports');
			
		// If we have reports on this deployment
		if (count($incidents) > 0)
		{
			foreach ($incidents as $incident)
			{
				// Start Individual report
				$writer->startElement('report');
				
				/* Add report attributes */
				$writer->startAttribute('id');
					$writer->text($incident->id);
				$writer->startAttribute('approved');
					$writer->text($incident->incident_active);
				$writer->startAttribute('verified');
					$writer->text($incident->incident_verified);
				$writer->startAttribute('mode');
					$writer->text($incident->incident_mode);
				$writer->startAttribute('form_id');
					$writer->text($incident->form_id);

				/* Add Report Elements	*/	
				// Report Title
				$writer->startElement('title');
					$writer->text($incident->incident_title);
				$writer->endElement();

				// Report Date
				$writer->startElement('date');
					$writer->text($incident->incident_date);
				$writer->endElement();

				// Report Add Date
				$writer->startElement('dateadd');
					$writer->text($incident->incident_dateadd);
				$writer->endElement();
				
				
				// Report Description
				$writer->startElement('description');
					$writer->text($incident->incident_description);
				$writer->endElement();
					
				foreach($post->data_include as $item)
				{
					switch($item)
					{
						case 1:
						
						// Report Location
						$writer->startElement('location');
							$writer->startElement('name');
								$writer->text($incident->location->location_name);
							$writer->endElement();
							$writer->startElement('longitude');
								$writer->text($incident->location->longitude);
							$writer->endElement();
							$writer->startElement('latitude');
								$writer->text($incident->location->latitude);
							$writer->endElement();
						$writer->endElement();
						break;

						// Report Media
						$reportmedia = $incident->media;
					
						if (count($reportmedia) > 0)
						{
							$writer->startElement('media');
							foreach ($reportmedia as $media)
							{
								// Videos and news links only
								if ($media->media_type == 2 OR $media->media_type == 4)
								{
									$writer->startElement('item');
										$writer->startAttribute('type');
											$writer->text($media->media_type);
										$writer->startAttribute('active');
											$writer->text($media->media_active);
										$writer->startAttribute('date');
											$writer->text($media->media_date);
										$writer->endAttribute();
										$writer->text($media->media_link);
									$writer->endElement();
								}
							}
							$writer->endElement();
						}

						case 7:
						
						// Report Personal information
						$incident_person = $incident->incident_person;
						if ($incident_person->loaded)
						{
							$writer->startElement('personal-info');
								$writer->startElement('firstname');
									$writer->text($incident_person->person_first);
								$writer->endElement();
								$writer->startElement('lastname');
									$writer->text($incident_person->person_last);
								$writer->endElement();
								$writer->startElement('email');
									$writer->text($incident_person->person_email);
								$writer->endElement();
							$writer->endElement();
						}
						break;

						case 3:
						
						// Report Category
						$writer->startElement('reportcategories');
						foreach($incident->incident_category as $category)
						{
							$writer->startElement('category');
								$writer->text($category->category->category_title);
							$writer->endElement();
						}
						$writer->endElement();
						break;

						case 6:
						
						// Report Fields
						$customresponses = customforms::get_custom_form_fields($incident->id,'',false);
						if ( ! empty($customresponses))
						{
							$writer->startElement('customfields');
							foreach($customresponses as $customresponse)
							{
								// If we don't have an empty form response
								if ($customresponse['field_response'] != '')
								{
									$writer->startElement('field');
										$writer->startAttribute('name');
											$writer->text($customresponse['field_name']);
										$writer->endAttribute();
										$writer->text($customresponse['field_response']);
									$writer->endElement();
								}
							}
							$writer->endElement();
						}
						break;
					}
				}
				
				// Close individual report	
				$writer->endElement();
			}
		}
		else
		{
			$writer->text("There are no reports on this deployment");
		}
		
		/* Close reports Element */	
		$writer->endElement();

		/* Close import tag */
		$writer->endElement();

		// Close the document
		$writer->endDocument();

		// Print
		echo $writer->outputMemory(TRUE);
		exit;
	}
	
	private function _csv_text($text)
	{
		$text = stripslashes(htmlspecialchars($text));
		return $text;
	}
}
?>