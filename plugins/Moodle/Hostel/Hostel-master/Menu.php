<?php
/**
 * Menu.php file
 * Required
 * - Menu entries for the Hostel module
 * - Add Menu entries to other modules
 *
 * @package Hostel module
 */

/**
 * Use dgettext() function instead of _() for Module specific strings translation
 * see locale/README file for more information.
 */
$module_name = dgettext( 'Hostel', 'Hostel' );



// Menu entries for the Hostel module.
if ( empty( $menu['Hostel']['admin'] ) ) // If Premium module loaded first, admin menu already set.
{
	$menu['Hostel']['admin'] = [ // Admin menu.
		'title' => dgettext( 'Hostel', 'Hostel' ),
		'default' => 'Hostel/Hostel.php', // Program loaded by default when menu opened.
		'Hostel/Hostel.php' => dgettext( 'Hostel', 'Rooms' ),
		1 => _( 'Reports' ),
		'Hostel/RoomList.php' => dgettext( 'Hostel', 'Room List' ),
	];
}

$menu['Hostel']['teacher'] = [ // Teacher menu.
	'title' => dgettext( 'Hostel', 'Hostel' ),
	'default' => 'Hostel/Hostel.php', // Program loaded by default when menu opened.
	'Hostel/Hostel.php' => dgettext( 'Hostel', 'Rooms' ),
];

$menu['Hostel']['parent'] = [ // Parent & student menu.
	'title' => dgettext( 'Hostel', 'Hostel' ),
	'default' => 'Hostel/Hostel.php', // Program loaded by default when menu opened.
	'Hostel/Hostel.php' => dgettext( 'Hostel', 'Rooms' ),
];
