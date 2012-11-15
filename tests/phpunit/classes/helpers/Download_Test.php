<?php

class Download_Helper_Test extends PHPUnit_Framework_TestCase{
	
	public function setUp()
	{
		// Set up post variable
		$this->post = array(
			'format' =>'xml',
			'data_active'   => array(0, 1),
			'data_verified'   => array(0, 1),
			'data_include' => array(1, 2, 3, 4, 5, 6, 7),
			'from_date'	   => '',
			'to_date'	   => '',
		);
	}
	
	public function tearDown()
	{
		unset($this->post);
	}
	/**
	 * Data Provider for testGenerateArrayMap
	 * @dataProvider
	 */
	public function providerTestGenerateArrayMap()
	{
		// Select a random Category
		$this->category = ORM::factory('category', testutils::get_random_id('category'));

		// Select a random Incident
		$this->incident = ORM::factory('incident', testutils::get_random_id('incident'));

		// Select a random form
		$this->form = ORM::factory('form', testutils::get_random_id('form'));
		
		/* Category Element/Attribute maps */
		// ORM Object
		$category = $this->category;
		
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
		// Form ORM Object
		$form = $this->form;
		
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
		// Report ORM Object
		$incident = $this->incident;
		
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
			array($category_map, $category_element_map, $category),
			array($translation_map, $translation_element_map, $translation),
			array($form_map, $form_element_map, $form),
			array($report_map, $report_element_map, $incident),
			array($location_map, $location_element_map, $location),
			array($media_map, $media_element_map, $media),
			array($person_map, $person_element_map, $person),
			array($incident_cat_map, $incident_cat_element_map, $incident_cat)
		);
	}
	
	/**
	 * Tests download helper function which generates object array maps
	 * to be used to generate XML element tags
	 * @test
	 * @dataProvider providerTestGenerateArrayMap
	 * @param $object_map associative array map skeleton 
	 * @param $expected_map expected output
	 * @param $object_orm ORM object
	 */
	public function testGenerateArrayMap($object_map, $expected_map, $object_orm)
	{		
		// Get value based on download helper function
		$actual_map = download::generate_element_attribute_map($object_orm, $object_map);
		
		// Test to ensure expected array map and actual array map match
		$this->assertEquals($expected_map, $actual_map, 'Output does not match expected array');	
	}
	/**
	 * Test XML Tag generation
	 * @test
	 */
	public function testDownloadXML()
	{
		// Test to ensure validation passed
		$this->assertEquals(TRUE, download::validate($this->post), 'Report download validation failed');
		
		/* TO DO */
		// Test XML Tag generation
	}
} 

 ?>