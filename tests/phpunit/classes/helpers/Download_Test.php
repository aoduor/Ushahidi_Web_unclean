<?php

class Download_Helper_Test extends PHPUnit_Framework_TestCase{
	
	protected function setUp()
	{
		// Set up post variable
		$this->post = array(
			'format' =>'xml',
			'data_active'   => array(0, 1),
			'data_verified'   => array(0, 1),
			'data_include' => array( 4, 5),
			'from_date'	   => '',
			'to_date'	   => '',
		);
		
		// Categories object : Limit it to one category only
		$this->category = ORM::factory('category')
							->join('category_lang', 'category.id', 'category_lang.category_id', 'inner')
							->where('parent_id !=', 0)
							->limit(1)
							->find_all();

		// Incidents object : Limit it to one incident only
		$this->incident = ORM::factory('incident')->limit(1)->find_all();
		
		// Custom forms object : Limit it to one custom form only
		//$this->custom_forms = ORM::factory('form')->limit(1)->find_all();
		$this->custom_forms = ORM::factory('form')->join('form_field','form_field.form_id', 'form.id', 'inner')->limit(1)->find_all();
	}
	
	public function tearDown()
	{
		unset($this->post, $this->category, $this->incident, $this->custom_forms);
	}
	
	/**
	 * Data Provider for testGenerateArrayMap
	 * @dataProvider
	 */
	public function providerTestGenerateArrayMap()
	{
		/* Category Element/Attribute maps */
		// Select a random category
		$category = ORM::factory('category', testutils::get_random_id('category'));
		
		// Category map
		$category_map = array(
			'attributes' => array(
				'color' => 'category_color',
				'visible' => 'category_visible',
				'trusted' => 'category_trusted'
			),
			'elements' => array(
				'title' => 'category_title',
				'description' => 'category_description'
			)
		);

		// Expected category array map
		$category_element_map = array(
			'attributes' => array(
				'color' => $category->category_color,
				'visible' => $category->category_visible,
				'trusted' => $category->category_trusted
			),
			'elements' => array(
				'title' => $category->category_title,
				'description' => $category->category_description
			)
		);
							
		/* Category translation Element/Attribute maps */
		// Translation ORM Object
		$translation = ORM::factory('category_lang', testutils::get_random_id('category_lang', 'WHERE category_id ='.$category->id.''));
		
		// Translation map
		$translation_map = array(
			'attributes' => array(
				'locale' => 'locale',
			),
			'elements' => array(
				'transtitle' => 'category_title',
				'transdescription' => 'category_description'
			)
		);

		// Expected translation array map
		$translation_element_map = array(
			'attributes' => array(
				'locale' => $translation->locale,
			),
			'elements' => array(
				'transtitle' => $translation->category_title,
				'transdescription' => $translation->category_description
			)
		);
		
		/* Form element/attribute maps */
		// Select a random form
		$form = ORM::factory('form', testutils::get_random_id('form'));
		
		// Forms map
		$form_map = array(
			'attributes' => array(
				'active' => 'form_active'
				),
			'elements' => array(
				'title' => 'form_title',
				'description' => 'form_description'
				)
			);
						
		// Expected form array map
		$form_element_map = array(
			'attributes' => array(
				'active' => $form->form_active
			),
			'elements' => array(
				'title' => $form->form_title,
				'description' => $form->form_description
			)
		);
		
		/* Reports element/attribute maps */
		// Select a random incident
		$incident = ORM::factory('incident', testutils::get_random_id('incident'));
		
		// Report map
		$report_map = array(
			'attributes' => array(
				'id' => 'id',
				'approved' => 'incident_active',
				'verified' => 'incident_verified',
				'mode' => 'incident_mode',
			),
			'elements' => array(
				'title' => 'incident_title',
				'date' => 'incident_date',
				'dateadd' => 'incident_dateadd',
				'description' => 'incident_description'
			)
		);
					
		// Expected report array map
		$report_element_map = array(
			'attributes' => array(
				'id' => $incident->id,
				'approved' => $incident->incident_active,
				'verified' => $incident->incident_verified,
				'mode' => $incident->incident_mode,
			),
			'elements' => array(
				'title' => $incident->incident_title,
				'date' => $incident->incident_date,
				'dateadd' => $incident->incident_dateadd,
				'description' => $incident->incident_description
			)
		);
		
		/* Report Location */
		// Report location ORM object
		$location = $incident->location;
		
		// Location Map
		$location_map = array(
			'attributes' => array(),
			'elements' => array(
				'name' => 'location_name',
				'longitude' => 'longitude',
				'latitude' => 'latitude'		
			)
		);

		// Expected location array map
		$location_element_map = array(
			'attributes' => array(),
			'elements' => array(
				'name' => $location->location_name,
				'longitude' => $location->longitude,
				'latitude' => $location->latitude		
			)
		);
								
		/* Report Media */
		// Report Media ORM Object
		$media = ORM::factory('media', testutils::get_random_id('media', 'WHERE incident_id ='.$incident->id.''));
		
		// Media Map
		$media_map = array(
			'attributes' => array(
				'type' => 'media_type',
				'active' => 'media_active',
				'date' => 'media_date'
			),
			'elements' => array()
		);

		// Expected media array map
		$media_element_map = array(
			'attributes' => array(
				'type' => $media->media_type,
				'active' => $media->media_active,
				'date' => $media->media_date
			),
			'elements' => array()
		);
		
		/* Report personal info */
		// Personal info ORM Object
		$person = $incident->incident_person;
		
		// Personal info map
		$person_map = array(
			'attributes' => array(),
			'elements' => array(
				'firstname' => 'person_first',
				'lastname' => 'person_last',
				'email' => 'person_email'	
			)
		);
		
		// Expected personal info array map
		$person_element_map = array(
			'attributes' => array(),
			'elements' => array(
				'firstname' => $person->person_first,
				'lastname' => $person->person_last,
				'email' => $person->person_email	
			)
		);		
		
		/* Incident Categories */
		// Incident Category ORM Object
		$incident_cat = ORM::Factory('category')
						->join('incident_category','incident_category.category_id','category.id','inner')
						->where('incident_category.incident_id', $incident->id)
						->limit(1)
						->find();
								
		// Incident Category map
		$incident_cat_map = array(
			'attributes' => array(),
			'elements' => array(
				'category' => 'category_title',
			)
		);
		
		// Expected incident category array Map
		$incident_cat_element_map = array(
			'attributes' => array(),
			'elements' => array(
				'category' => $incident_cat->category_title,
			)
		);							
							
		return array(
			array($category_map, $category_element_map, $category, 'Category'),
			array($translation_map, $translation_element_map, $translation, 'Category Translation'),
			array($form_map, $form_element_map, $form, 'Form'),
			array($report_map, $report_element_map, $incident, 'Report'),
			array($location_map, $location_element_map, $location, 'Report Location'),
			array($media_map, $media_element_map, $media, 'Report Media'),
			array($person_map, $person_element_map, $person, 'Reporter'),
			array($incident_cat_map, $incident_cat_element_map, $incident_cat, 'Incident category')
		);
	}
	
	/**
	 * Tests download helper function which generates object array maps
	 * to be used to generate XML element tags
	 * @test
	 * @dataProvider providerTestGenerateArrayMap
	 * @param array $object_map associative array map skeleton 
	 * @param array $expected_map expected output
	 * @param object $object_orm ORM object
	 * @param string $object_name
	 */
	public function testGenerateArrayMap($object_map, $expected_map, $object_orm, $object_name)
	{		
		// Get array map returned by download helper function
		$actual_map = xml::generate_element_attribute_map($object_orm, $object_map);
		
		// For the random category
		if ($object_name == 'Category')
		{
			// Check if this category has a parent
			if ($object_orm->parent_id > 0)
			{
				// Fetch the parent category
				$parent = ORM::Factory('category', $object_orm->parent_id);
				
				// Add category parent to actual_map and expected_map
				$expected_map['elements']['parent'] = $parent->category_title;
				$actual_map['elements']['parent'] = $parent->category_title;
			}	
		}
		
		// Test to ensure expected array map and actual array map match
		$this->assertEquals($expected_map, $actual_map, 'Output does not match expected array for the '.$object_name.' object');	
	}
	/**
	 * Test XML Tag generation
	 * @test
	 * @return string $xml_content
	 */
	public function testDownloadXML()
	{
		// Test to ensure validation passed
		$this->assertEquals(TRUE, download::validate($this->post), 'Report download validation failed');
		
		/* Test XML Tag generation */
		// Load XML Content into a string
		$xml_content = download::download_xml($this->post, $this->incident, $this->category, $this->custom_forms);
		
		// Make sure string holding XML Content is not empty
		$this->assertNotEmpty($xml_content, 'XML Download Failed');
		
		return $xml_content;
	}
	
	/**
	 * Load XML Content generated and check for Categories, Custom Forms and Reports tags
	 * @test
	 * @depends testDownloadXML
	 * @param string $xml_content
	 */
	public function testReadDownloadXML($xml_content)
	{
		// XML Reader
		$reader = new DOMDocument('1.0');
		
		// Load XML string into reader
		$reader->loadXML($xml_content);
		
		// Ensure that the XML String is loaded
		$this->assertTrue(@$reader->loadXML($xml_content), 'XML Content loading failed');
		
		// Check for categories, customforms and reports elements
		$d_categories = $reader->getElementsByTagName('categories');
		$d_customforms = $reader->getElementsByTagName('customforms');
		$d_reports = $reader->getElementsByTagName('reports');
		
		// Ensure that at least one of the elements i.e categories, customforms OR reports exist
		$tag_exists = ($d_categories->length == 0 AND $d_customforms->length == 0 AND $d_reports->length == 0) 
						? FALSE
						: TRUE;
		$this->assertTrue($tag_exists, 'XML content must have at least one of the following: Categories, Custom forms or Reports');	
		
		return array($d_categories, $d_customforms, $d_reports);
		
	}
	
	/**
	 * Tests whether XML Category element matches ORM objects provided for download
	 * @test
	 * @depends testReadDownloadXML
	 * @param array $dom_nodes DOMNodeList Objects
	 */
	public function testCheckCategoryXML(array $dom_nodes)
	{
		/* Category check */
		// Categories DOMNodeList Object 
		$d_categories = $dom_nodes[0];
		
		// When category option is not selected, make sure the categories element does not exist
		if ( ! in_array(3, $this->post['data_include']))
		{
			$this->assertEquals(0, $d_categories->length, 'The "categories" element should not exist');
		}
		
		// Download of categories option was provided by the user
		else
		{
			// Make sure the categories element exists
			$this->assertGreaterThan(0, $d_categories->length, 'The "categories" element SHOULD exist');
			
			// Contents of <categories> element
			$categories_element = $d_categories->item(0);
			
			// If we have no categories on this deployment
			if (count($this->category) == 0)
			{
				// Ensure the categories element has the following message
				$this->assertEquals('There are no categories on this deployment.', $categories_element->nodeValue);
			}
			
			// We have categories on this deployment
			else
			{
				// Individual category
				$cat = $this->category[0];
				
				// Grab contents of <category> element
				$category_element = $categories_element->getElementsByTagName('category');
				
				// Test to see if category element exists
				$this->assertGreaterThan(0, $category_element->length, 'Category element does not exist for deployment with existing categories');
			
				// Test category Color
				$color = xml::get_node_text($category_element->item(0), 'color', FALSE);
				$this->assertEquals($cat->category_color, $color, 'Category Color does not match/ Color attribute does not exist');
				
				// Test category Visible
				$visible = xml::get_node_text($category_element->item(0), 'visible', FALSE);
				$this->assertEquals($cat->category_visible, $visible, 'Category visible status does not match/attribute does not exist');
				
				// Test category Trusted
				$trusted = xml::get_node_text($category_element->item(0), 'trusted', FALSE);
				$this->assertEquals($cat->category_trusted, $trusted, 'Category trusted status does not match/attribute does not exist');
				
				// Test category Title
				$title = xml::get_node_text($category_element->item(0), 'title');
				$this->assertEquals($cat->category_title, $title, 'Category title does not match/ title element does not exist');
				
				// Test category Description
				$description = xml::get_node_text($category_element->item(0), 'description');
				$this->assertEquals($cat->category_description, $description, 'Category description does not match/the element does not exist');
				
				// Test category Parent
				if ($cat->parent_id > 0)
				{
					// Fetch the parent category
					$parent = ORM::Factory('category', $cat->parent_id);
					$parent_title = xml::get_node_text($category_element->item(0), 'parent');
					$this->assertEquals($parent->category_title, $parent_title, 'Category parent title does not match/parent element does not exist');
				}
				
				/* Translation Check */
				// Grab contents of <translations> element
				$translations_element = $categories_element->getElementsByTagName('translations');
				
				// Grab the category translations
				$translations = ORM::Factory('category_lang')->where('category_id', $cat->id)->find_all();
				$translation_count = count($translations);
				
				// If we actually have translations for this category
				if ( $translation_count > 0)
				{
					// Translation index
					$index = rand(0, $translation_count-1);
						
					// Pick out a random translation
					$translation = $translations[$index];
					
					// Test to see if the translations element exists
					$this->assertGreaterThan(0, $translations_element->length, 'Translations element does not exist for category with translations');
					
					// Grab contents of individual <translation> elements
					$translation_element = $translations_element->item(0)->getElementsByTagName('translation');
					
					// Test to see if the <translation> element exists
					$this->assertGreaterThan(0, $translation_element->length, 'Translation element does not exist for category with translations');
					
					// Test Translation locale
					$locale = xml::get_node_text($translation_element->item($index), 'locale', FALSE);
					$this->assertEquals($translation->locale, $locale, 'Translation locales do not match/ attribute does not exist');
					
					// Test Translation category title
					$transtitle = xml::get_node_text($translation_element->item($index), 'transtitle');
					$this->assertEquals($translation->category_title, $transtitle, 'Translation titles do not match/ element does not exist');
					
					// Test Translation category description
					$transdescription = xml::get_node_text($translation_element->item($index), 'transdescription');
					$this->assertEquals($translation->category_description, $transdescription, 'Translation descriptions do not match/ element does not exist');
				}
				
				// If we don't have translations for this category
				else
				{
					// Test to ensure that the translations element does NOT exist
					$this->assertEquals(0, $translations_element->length, 'Translations element should not exist for category with no translations');
				}
			}
		}
	}
	
	/**
	 * Tests whether XML Custom form element matches ORM objects provided for download
	 * @test
	 * @depends testReadDownloadXML
	 * @param array $domnodes DOMNodeList Objects
	 */
	
	public function testCheckCustomFormXML(array $dom_nodes)
	{
		/* Custom form check */
		$d_customforms = $dom_nodes[1];
		
		// When custom forms option is not selected, make sure the custom forms element does not exist
		if ( ! in_array(6, $this->post['data_include']))
		{
			$this->assertEquals(0, $d_customforms->length, 'The "customforms" element should not exist');
		}
		
		// Custom forms option is selected
		else
		{
			// Test to make sure <customforms> element exists
			$this->assertGreaterThan(0, $d_customforms->length, 'The "customforms" element SHOULD exist');
			
			// Contents of <customforms> element
			$forms_element = $d_customforms->item(0);
			
			// If we don't have custom forms on this deployment
			if (count($this->custom_forms) == 0)
			{
				// Ensure the customforms element has the following message
				$this->assertEquals('There are no custom forms on this deployment.', $d_customforms->item(0)->nodeValue);
			}
			
			// We have custom forms on this deployment
			else
			{
				// Grab individual form
				$form = $this->custom_forms[0];
				
				// Grab contents of <form> element
				$form_element = $forms_element->getElementsByTagName('form');
				
				// Test to see if the <form> element exists
				$this->assertGreaterThan(0,$form_element->length, 'The "form" element does not exist for a deployment with forms');
				
				// Test Form active status
				$active = xml::get_node_text($form_element->item(0), 'active', FALSE);
				$this->assertEquals($form->form_active, $active, 'Form active status does not match/attribute does not exist');
				
				// Test Form title
				$title = xml::get_node_text($form_element->item(0), 'title');
				$this->assertEquals($form->form_title, $title, 'Form title does not match/element does not exist');
				
				// Test Form description
				$description = xml::get_node_text($form_element->item(0), 'description');
				$this->assertEquals($form->form_description, $description, 'Form description does not match/element does not exist');
				
				/* Custom fields check */
				
			}
		}
	}
	
	/**
	 * Tests whether XML Report element matches ORM objects provided for download
	 * @test
	 * @depends testReadDownloadXML
	 * @param array $domnodes DOMNodeList Objects
	 */
	public function testCheckReportsXML(array $domnodes)
	{
		$d_reports = $domnodes[2];
		// Ensure that the DOMNodeList Object is not empty
		if ($d_reports->length == 0)
		{
			$this->markTestSkipped('Reports element does not exist');
		}
		// Contents of <Reports> element
		$reports_element = $d_reports->item(0);
		
		/* Report Check */
		// If we have no reports on this deployment
		if (count($this->incident) == 0)
		{
			// Ensure the customforms element has the following message
			$this->assertEquals('There are no reports on this deployment.', $d_reports->item(0)->nodeValue);
		}
		
		// We have reports on this deployment
		else
		{
			// Grab individual Report
			$incident = $this->incident[0];
			
			// Grab contents of <report> element
			$report_element = $reports_element->getElementsByTagName('report');
			
			// Test to see if the <report> element exists
			$this->assertGreaterThan(0, $report_element->length, 'Report element does not exist for deployment with reports');
			
			/* Report Check */
			// Test report id
			$id = $report_element->item(0)->getAttribute('id');
			$this->assertEquals($incident->id, $id, 'Report id does not match/attribute does not exist');
			
			// Test Report approval status
			$approved = $report_element->item(0)->getAttribute('approved');
			$this->assertEquals($incident->incident_active, $approved, 'Report active status does not match/attribute does not exist');
			
			// Test Report verified status
			$verified = $report_element->item(0)->getAttribute('verified');
			$this->assertEquals($incident->incident_verified, $verified, 'Report verified status does not match/attribute does not exist');
			
			// Test Report mode status
			$mode = $report_element->item(0)->getAttribute('mode');
			$this->assertEquals($incident->incident_mode, $mode, 'Report mode does not match/attribute does not exist');
			
			// Test Report form_id
			$form_id = xml::get_node_text($report_element->item(0), 'form_id', FALSE);
			$incident_form = ORM::factory('form')->where('form_title', $form_id)->find();
			$this->assertEquals($incident_form->form_title, $form_id, 'Report form_id does not match/attribute does not exist');
			
			// Test Report Title
			$title = xml::get_node_text($report_element->item(0), 'title');
			$this->assertEquals($incident->incident_title, $title, 'Report title does not match/element does not exist');
			
			// Test Report Date
			$date = xml::get_node_text($report_element->item(0), 'date');
			$this->assertEquals($incident->incident_date, $date, 'Report date does not match/element does not exist');
			
			// Test Report Dateadd
			$date_add = xml::get_node_text($report_element->item(0), 'dateadd');
			$this->assertEquals($incident->incident_dateadd, $date_add, 'Report dateadd does not match/element does not exist');
			
			// Test report description
			$description = xml::get_node_text($report_element->item(0), 'description');
			
			// If download report description option is selected by user
			if (in_array(2, $this->post['data_include']))
			{
				$this->assertEquals($incident->incident_description, $description, 'Report description does not match/element does not exist');
			}
			
			else
			{
				$this->assertEquals(FALSE, $description, 'Report description element should not exist');
			}
			
			/* Location Check */
			$locations_element = $report_element->item(0)->getElementsByTagName('location');
			if (in_array(1, $this->post['data_include']))
			{
				
			}
			else
			{
				$this->assertEquals(0, $locations_element->length, "Report location element should not exist");
			}
		
			/* Media Check */
		
			/* Personal info check */
			$person_info_element = $report_element->item(0)->getElementsByTagName('personal-info');
			if (in_array(7, $this->post['data_include']))
			{
				
			}
			else
			{
				$this->assertEquals(0, $person_info_element->length, "Report personal-info element should not exist");
			}
		
			/* Incident Category check */
			$report_cat_element = $report_element->item(0)->getElementsByTagName('reportcategories');
			if (in_array(3, $this->post['data_include']))
			{
				
			}
			else
			{
				$this->assertEquals(0, $report_cat_element->length, "Report categories element should not exist");
			}
		
			/* Custom response check */
			$custom_responses_element = $report_element->item(0)->getElementsByTagName('customfields');
			if (in_array(6, $this->post['data_include']))
			{
				
			}
			else
			{
				$this->assertEquals(0, $custom_responses_element->length, "Report custom responses element should not exist");
			}
		}	
	}
} 

 ?>