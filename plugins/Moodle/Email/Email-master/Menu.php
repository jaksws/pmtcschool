<?php
/**
 * Menu.php file
 * Required
 * - Add Menu entries to other modules
 *
 * @package Email module
 */

// Use dgettext() function instead of _() for Module specific strings translation
// See locale/README file for more information.

// Add a Menu entry to the Students module.
if ( $RosarioModules['Students'] ) // Verify Students module is activated.
{
	$menu['Students']['admin']['Email/EmailStudents.php'] = dgettext( 'Email', 'Send Email' );

	$menu['Students']['teacher']['Email/EmailStudents.php'] = dgettext( 'Email', 'Send Email' );
}

// Add a Menu entry to the Users module.
if ( $RosarioModules['Users'] ) // Verify Users module is activated.
{
	$menu['Users']['admin']['Email/EmailUsers.php'] = dgettext( 'Email', 'Send Email' );

	$menu['Users']['teacher']['Email/EmailUsers.php'] = dgettext( 'Email', 'Send Email' );
}
