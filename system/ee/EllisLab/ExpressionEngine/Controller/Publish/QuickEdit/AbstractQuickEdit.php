<?php
/**
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2018, EllisLab, Inc. (https://ellislab.com)
 * @license   https://expressionengine.com/license
 */

namespace EllisLab\ExpressionEngine\Controller\Publish\QuickEdit;

use CP_Controller;

/**
 * Abstract Publish Controller
 */
abstract class AbstractQuickEdit extends CP_Controller {

	public function __construct()
	{
		parent::__construct();

		ee()->lang->loadfile('content');

		$this->assigned_channel_ids = array_keys(ee()->session->userdata('assigned_channels'));
	}

	/**
	 * Renders the Fluid UI markup for a given set of fields
	 *
	 * @param Array $displayed_fields Fields that should be displayed on load
	 * @param Array $template_fields Fields to keep off screen as available templates
	 * @param Array $filter_fields Fields to display in the Add menu
	 * @param Result $errors Validation result for the given fields, or NULL
	 * @return String HTML markup of Fluid UI
	 */
	protected function getFluidMarkupForFields($displayed_fields, $template_fields, $filter_fields, $errors = NULL)
	{
		$filters = '';
		if ( ! empty($filter_fields))
		{
			$filters = ee('View')->make('fluid_field:filters')->render(['fields' => $filter_fields]);
		}

		$displayed_fields_markup = '';
		foreach ($displayed_fields as $field_name => $field)
		{
			$displayed_fields_markup .= ee('View')->make('fluid_field:field')->render([
				'field' => $field,
				'field_name' => $field_name,
				'filters' => '',
				'errors' => $errors,
				'reorderable' => FALSE,
				'show_field_type' => FALSE
			]);
		}

		$template_fields_markup = '';
		foreach ($template_fields as $field_name => $field)
		{
			$template_fields_markup .= ee('View')->make('fluid_field:field')->render([
				'field' => $field,
				'field_name' => $field_name,
				'filters' => '',
				'errors' => NULL,
				'reorderable' => FALSE,
				'show_field_type' => FALSE
			]);
		}

		return ee('View')->make('fluid_field:publish')->render([
			'fields'          => $displayed_fields_markup,
			'field_templates' => $template_fields_markup,
			'filters'         => $filters,
		]);
	}

	/**
	 * Given an entry, returns the FieldFacades for the available FieldFacades
	 * for that entry
	 *
	 * @param ChannelEntry $entry Channel entry object to render fields from
	 * @return Array Associative array of FieldFacades
	 */
	protected function getCategoryFieldsForEntry($entry)
	{
		$fields = [];
		foreach ($entry->Channel->CategoryGroups->getIds() as $cat_group)
		{
			$fields[] = 'categories[cat_group_id_'.$cat_group.']';
		}

		$field_facades = $this->getFieldsForEntry($entry, $fields);
		foreach ($field_facades as $field)
		{
			// Cannot edit categories in this view
			$field->setItem('editable', FALSE);
			$field->setItem('editing', FALSE);
		}

		return $field_facades;
	}

	/**
	 * Given an entry, returns the FieldFacades for the given field names
	 *
	 * @param ChannelEntry $entry Channel entry object to render fields from
	 * @param Array $fields Array of field short names to render
	 * @return Array Associative array of FieldFacades
	 */
	protected function getFieldsForEntry($entry, $fields)
	{
		$field_facades = [];
		foreach ($fields as $field)
		{
			$field_facades[$field] = $entry->getCustomField($field);
		}

		return $field_facades;
	}

	/**
	 * Given a Collection of channels, returns a channel entry object assigned
	 * to an intersected channel
	 *
	 * @param Collection $channels Collection of channels
	 * @return ChannelEntry
	 */
	protected function getMockEntryForIntersectedChannels($channels)
	{
		$entry = ee('Model')->make('ChannelEntry');
		$entry->entry_id = 0;
		$entry->author_id = ee()->session->userdata('member_id');
		$entry->Channel = $this->getIntersectedChannel($channels);

		return $entry;
	}

	/**
	 * Given a Collection of channels, returns a channel object with traits each
	 * channel has in common, currently category groups and statuses
	 *
	 * @param Collection $channels Collection of channels
	 * @return Channel
	 */
	protected function getIntersectedChannel($channels)
	{
		$channels = $channels->intersect();

		// All entries belong to the same channel, easy peasy!
		if ($channels->count() < 2)
		{
			return $channels->first();
		}

		$channel = ee('Model')->make('Channel');
		$channel->cat_group = implode(
			'|',
			$channels->CategoryGroups->intersect()->getIds()
		);
		$channel->Statuses = $channels->Statuses->intersect();

		return $channel;
	}

}

// EOF
