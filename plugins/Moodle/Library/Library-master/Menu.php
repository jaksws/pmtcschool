<?php
/**
 * Menu.php file
 * Required
 * - Menu entries for the Library module
 * - Add Menu entries to other modules
 *
 * @package Library module
 */

/**
 * Use dgettext() function instead of _() for Module specific strings translation
 * see locale/README file for more information.
 */
$module_name = dgettext( 'Library', 'Library' );

if ( empty( $RosarioModules['Library_Premium'] ) )
{
	// Menu entries for the Library module.
	$menu['Library']['admin'] = [ // Admin menu.
		'title' => dgettext( 'Library', 'Library' ),
		'default' => 'Library/Library.php', // Program loaded by default when menu opened.
		'Library/Library.php' => dgettext( 'Library', 'Library' ),
		'Library/Loans.php' => dgettext( 'Library', 'Loans' ),
	];

	$menu['Library']['teacher'] = [ // Teacher menu.
		'title' => dgettext( 'Library', 'Library' ),
		'default' => 'Library/Library.php', // Program loaded by default when menu opened.
		'Library/Library.php' => dgettext( 'Library', 'Library' ),
		'Library/Loans.php' => dgettext( 'Library', 'Loans' ),
	];

	$menu['Library']['parent'] = [ // Parent & student menu.
		'title' => dgettext( 'Library', 'Library' ),
		'default' => 'Library/Library.php', // Program loaded by default when menu opened.
		'Library/Library.php' => dgettext( 'Library', 'Library' ),
		'Library/Loans.php' => dgettext( 'Library', 'Loans' ),
	];
}
