<?php
/**
 * Menu.php file
 * Required
 * - Menu entries for the Quiz module
 * - Add Menu entries to other modules
 *
 * @package Quiz module
 */

/**
 * Use dgettext() function instead of _() for Module specific strings translation
 * see locale/README file for more information.
 */
$module_name = dgettext( 'Quiz', 'Quiz' );

// Menu entries for the Quiz module.
if ( empty( $menu['Quiz']['admin'] ) ) // If Premium module loaded first, admin menu already set.
{
	$menu['Quiz']['admin'] = [ // Admin menu.
		'title' => dgettext( 'Quiz', 'Quiz' ),
		'default' => 'Quiz/Quizzes.php', // Program loaded by default when menu opened.
		'Quiz/Quizzes.php' => dgettext( 'Quiz', 'Quizzes' ),
		'Quiz/Questions.php' => dgettext( 'Quiz', 'Questions' ),
	];
}

$menu['Quiz']['teacher'] = [ // Teacher menu.
	'title' => dgettext( 'Quiz', 'Quiz' ),
	'default' => 'Quiz/Quizzes.php', // Program loaded by default when menu opened.
	'Quiz/Quizzes.php' => dgettext( 'Quiz', 'Quizzes' ),
	'Quiz/Questions.php' => dgettext( 'Quiz', 'Questions' ),
];

$menu['Quiz']['parent'] = [ // Parent & student menu.
	'title' => dgettext( 'Quiz', 'Quiz' ),
	'default' => 'Quiz/StudentQuizzes.php', // Program loaded by default when menu opened.
	'Quiz/StudentQuizzes.php' => dgettext( 'Quiz', 'Quizzes' ),
];
