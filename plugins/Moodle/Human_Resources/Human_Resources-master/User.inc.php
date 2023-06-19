<?php

require_once 'modules/Human_Resources/includes/Qualifications.fnc.php';


if ( ( ( isset( $_POST['values'] )
		&& $_REQUEST['values'] )
	|| ( isset( $_REQUEST['month_values'] )
		&& $_REQUEST['month_values'] ) )
	&& AllowEdit() )
{
	// Fix SQL error: check Required / NOT NULL columns.
	if ( ! empty( $_REQUEST['values']['human_resources_skills']['new'] )
		&& empty( $_REQUEST['values']['human_resources_skills']['new']['TITLE'] ) )
	{
		$_REQUEST['values']['human_resources_skills']['new'] = false;
	}

	if ( ! empty( $_REQUEST['values']['human_resources_education']['new'] )
		&& ( empty( $_REQUEST['values']['human_resources_education']['new']['QUALIFICATION'] )
			|| empty( $_REQUEST['values']['human_resources_education']['new']['INSTITUTE'] ) ) )
	{
		$_REQUEST['values']['human_resources_education']['new'] = false;

		$_REQUEST['month_values']['human_resources_education']['new'] = false;
	}

	if ( ! empty( $_REQUEST['values']['human_resources_certifications']['new'] )
		&& ( empty( $_REQUEST['values']['human_resources_certifications']['new']['TITLE'] )
			|| empty( $_REQUEST['values']['human_resources_certifications']['new']['INSTITUTE'] ) ) )
	{
		$_REQUEST['values']['human_resources_certifications']['new'] = false;

		$_REQUEST['month_values']['human_resources_certifications']['new'] = false;
	}

	if ( ! empty( $_REQUEST['values']['human_resources_languages']['new'] )
		&& empty( $_REQUEST['values']['human_resources_languages']['new']['TITLE'] ) )
	{
		$_REQUEST['values']['human_resources_languages']['new'] = false;
	}

	SaveData(
		[
			'human_resources_skills' => "ID='__ID__'",
			'human_resources_education' => "ID='__ID__'",
			'human_resources_certifications' => "ID='__ID__'",
			'human_resources_languages' => "ID='__ID__'",
			'fields' => [
				'human_resources_skills' => 'STAFF_ID,',
				'human_resources_education' => 'STAFF_ID,',
				'human_resources_certifications' => 'STAFF_ID,',
				'human_resources_languages' => 'STAFF_ID,',
			],
			'values' => [
				'human_resources_skills' => "'" . UserStaffID() . "',",
				'human_resources_education' => "'" . UserStaffID() . "',",
				'human_resources_certifications' => "'" . UserStaffID() . "',",
				'human_resources_languages' => "'" . UserStaffID() . "',",
			],
		]
	);

	// Unset values, month_values & redirect URL.
	RedirectURL( [ 'values', 'month_values' ] );
}

if ( $_REQUEST['modfunc'] === 'delete_qualification'
	&& AllowEdit() )
{
	if ( DeletePrompt( $_REQUEST['title'] ) )
	{
		DBQuery( "DELETE FROM " . DBEscapeIdentifier( $_REQUEST['table'] ) . "
			WHERE ID='" . (int) $_REQUEST['id'] . "'" );

		// Unset modfunc & ID & table & title & redirect URL.
		RedirectURL( [ 'modfunc', 'id', 'table', 'title' ] );
	}
}

if ( ! $_REQUEST['modfunc']
	&& UserStaffID() )
{
	// Fix CSS responsive List width: do NOT use the .fixed-col class, use pure CSS.
	echo '<table class="width-100p valign-top" style="table-layout: fixed;"><tr><td>';

	HumanResourcesSkillsListOutput( UserStaffID() );

	echo '</td></tr><tr><td>';

	HumanResourcesEducationListOutput( UserStaffID() );

	echo '</td></tr><tr><td>';

	HumanResourcesCertificationsListOutput( UserStaffID() );

	echo '</td></tr><tr><td>';

	HumanResourcesLanguagesListOutput( UserStaffID() );

	echo '</td></tr></table>';
}
