<?php
/**
 * Menu.php file
 *
 * Required
 * - Add Menu entries to other modules
 *
 * @package Students Import module
 */

// Use dgettext() function instead of _() for Module specific strings translation.
// See locale/README file for more information.
$module_name = dgettext( 'Students_Import', 'Students Import' );

// Add a Menu entry to the Students module.
if ( $RosarioModules['Students'] ) // Verify Students module is activated.
{
	// Place Students Import program right after Utilities separator.
	$utilities_pos = array_search( 3, array_keys( $menu['Students']['admin'] ) );

	if ( $utilities_pos )
	{
		$menu['Students']['admin'] = array_replace(
			array_slice( $menu['Students']['admin'], 0, $utilities_pos + 1 ),
			[ 'Students_Import/StudentsImport.php' => dgettext( 'Students_Import', 'Students Import' ) ],
			array_slice( $menu['Students']['admin'], $utilities_pos + 1 )
		);
	}
	else
	{
		$menu['Students']['admin'] += [
			'Students_Import/StudentsImport.php' => dgettext( 'Students_Import', 'Students Import' ),
		];
	}
}
