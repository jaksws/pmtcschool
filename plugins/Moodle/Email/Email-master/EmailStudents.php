<?php
/**
 * Email Students
 *
 * @package Email module
 */

require_once 'ProgramFunctions/Substitutions.fnc.php';
require_once 'ProgramFunctions/MarkDownHTML.fnc.php';
require_once 'ProgramFunctions/SendEmail.fnc.php';
require_once 'ProgramFunctions/Template.fnc.php';
require_once 'ProgramFunctions/FileUpload.fnc.php';
require_once 'modules/Email/includes/Email.fnc.php';

DrawHeader( ProgramTitle() );

if ( isset( $_SESSION['email_field_key'] ) && empty( $email_field_key ) )
{
	$email_field_key = $_SESSION['email_field_key'];
}
elseif ( isset( $_POST['email_field_key'] ) )
{
	$email_field_key = $_SESSION['email_field_key'] = $_REQUEST['email_field_key'];
}

if ( ! empty( $email_field_key )
	&& $email_field_key !== 'USERNAME' )
{
	$email_field_key = 'CUSTOM_' . $email_field_key;
}

if ( empty( $email_field_key ) )
{
	echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname']  ) . '" method="POST">';

	$students_email_field_RET = DBGet( "SELECT ID,TITLE
		FROM custom_fields
		WHERE TYPE='text'
		AND CATEGORY_ID=1" );

	// Display SELECT input.
	$select_html = dgettext( 'Email', 'Select Student email field' ) . ': <select id="email_field_key" name="email_field_key">';

	$selected = Config( 'STUDENTS_EMAIL_FIELD' ) === 'USERNAME' ? ' selected' : '';

	$select_html .= '<option value="USERNAME"' . $selected . '>' . _( 'Username' ) . '</option>';

	foreach ( (array) $students_email_field_RET as $field )
	{
		$selected = Config( 'STUDENTS_EMAIL_FIELD' ) === $field['ID'] ? ' selected' : '';

		$select_html .= '<option value="' . ( function_exists( 'AttrEscape' ) ? AttrEscape( $field['ID'] ) : htmlspecialchars( $field['ID'], ENT_QUOTES ) ) . '"' . $selected . '>' .
			ParseMLField( $field['TITLE'] ) . '</option>';
	}

	$select_html .= '</select>';

	DrawHeader( '', '', $select_html );

	echo '<br /><div class="center">' . Buttons( dgettext( 'Email', 'Select Student email field' ) ) . '</div>';
	echo '</form>';
}

if ( User( 'PROFILE' ) === 'teacher' )
{
	$_ROSARIO['allow_edit'] = true;
}

if ( $_REQUEST['modfunc'] === 'save'
	&& AllowEdit()
	&& ! empty( $email_field_key ) )
{
	if ( ! empty( $_REQUEST['st_arr'] ) )
	{
		// If $test email is set then this script will only 'go through the motions'
		// and email the results to the $test_email address instead of students.
		$test_email = $_REQUEST['test_email'];

		// Set the from and cc emails here - the emails can be comma separated list of emails.
		$reply_to = $cc = '';

		// File attached.
		$attachments = [];

		// @deprecated since 7.8 Use FileUploadMultiple().
		foreach ( EmailFileUploadMultiple( 'files_attached' ) as $file_input )
		{
			// Upload file attached.
			$file_attached = FileUpload(
				$file_input,
				sys_get_temp_dir() . DIRECTORY_SEPARATOR, // Temporary directory.
				FileExtensionWhiteList(),
				24, // 24MB max: Gmail is 25MB, Outlook is 34MB.
				$error
			);

			if ( $error )
			{
				ErrorMessage( $error, 'fatal' );
			}

			$attachments[] = $file_attached;
		}

		if ( filter_var( User( 'EMAIL' ), FILTER_VALIDATE_EMAIL ) )
		{
			$reply_to = User( 'NAME' ) . ' <' . User( 'EMAIL' ) . '>';
		}
		elseif ( ! filter_var( $test_email, FILTER_VALIDATE_EMAIL ) )
		{
			$error[] = _( 'You must set the <b>test mode email</b> or have a user email address to use this script.' );

			ErrorMessage( $error, 'fatal' );
		}

		$subject = isset( $_REQUEST['subject'] ) ? strip_tags( $_POST['subject'] ) : '';

		$st_list = "'" . implode( "','", $_REQUEST['st_arr'] ) . "'";

		$extra['WHERE'] = " AND s.STUDENT_ID IN (" . $st_list . ")";

		$extra['SELECT'] = issetVal( $extra['SELECT'], '' );

		// SELECT s.* Custom Fields for Substitutions.
		$extra['SELECT'] .= ",s.*";

		$extra['SELECT'] .= ",s.FIRST_NAME AS NICK_NAME";

		// Select Email.
		$extra['SELECT'] .= ",s." . DBEscapeIdentifier( $email_field_key ) . " AS EMAIL";

		if ( $email_field_key !== 'USERNAME' )
		{
			$extra['SELECT'] .= ",s.USERNAME";
		}

		if ( User( 'PROFILE' ) === 'admin' )
		{
			if ( isset( $_REQUEST['w_course_period_id_which'] )
				&& $_REQUEST['w_course_period_id_which'] == 'course_period'
				&& $_REQUEST['w_course_period_id'] )
			{
				$extra['SELECT'] .= ",(SELECT " . DisplayNameSQL( 'st' ) . "
				FROM staff st,course_periods cp
				WHERE st.STAFF_ID=cp.TEACHER_ID
				AND cp.COURSE_PERIOD_ID='" . (int) $_REQUEST['w_course_period_id'] . "') AS TEACHER";

				$extra['SELECT'] .= ",(SELECT cp.ROOM FROM course_periods cp WHERE cp.COURSE_PERIOD_ID='" . (int) $_REQUEST['w_course_period_id'] . "') AS ROOM";
			}
			else
			{
				// FJ multiple school periods for a course period.
				// SQL Replace AND p.ATTENDANCE='Y' with AND cp.DOES_ATTENDANCE IS NOT NULL.
				$extra['SELECT'] .= ",(SELECT " . DisplayNameSQL( 'st' ) . "
				FROM staff st,course_periods cp,school_periods p,schedule ss,course_period_school_periods cpsp
				WHERE cp.COURSE_PERIOD_ID=cpsp.COURSE_PERIOD_ID
				AND cp.DOES_ATTENDANCE IS NOT NULL
				AND st.STAFF_ID=cp.TEACHER_ID
				AND cpsp.PERIOD_id=p.PERIOD_ID
				AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID
				AND ss.STUDENT_ID=s.STUDENT_ID
				AND ss.SYEAR='" . UserSyear() . "'
				AND ss.MARKING_PERIOD_ID IN (" . GetAllMP( 'QTR', GetCurrentMP( 'QTR', DBDate(), false ) ) . ")
				AND (ss.START_DATE<='" . DBDate() . "' AND (ss.END_DATE>='" . DBDate() . "' OR ss.END_DATE IS NULL))
				ORDER BY p.SORT_ORDER IS NULL,p.SORT_ORDER LIMIT 1) AS TEACHER";

				// SQL Replace AND p.ATTENDANCE='Y' with AND cp.DOES_ATTENDANCE IS NOT NULL.
				$extra['SELECT'] .= ",(SELECT cp.ROOM
				FROM course_periods cp,school_periods p,schedule ss,course_period_school_periods cpsp
				WHERE cp.COURSE_PERIOD_ID=cpsp.COURSE_PERIOD_ID
				AND cp.DOES_ATTENDANCE IS NOT NULL
				AND cpsp.PERIOD_id=p.PERIOD_ID
				AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID
				AND ss.STUDENT_ID=s.STUDENT_ID
				AND ss.SYEAR='" . UserSyear() . "'
				AND ss.MARKING_PERIOD_ID IN (" . GetAllMP( 'QTR', GetCurrentMP( 'QTR', DBDate(), false ) ) . ")
				AND (ss.START_DATE<='" . DBDate() . "' AND (ss.END_DATE>='" . DBDate() . "' OR ss.END_DATE IS NULL))
				ORDER BY p.SORT_ORDER IS NULL,p.SORT_ORDER LIMIT 1) AS ROOM";
			}
		}
		else
		{
			$extra['SELECT'] .= ",(SELECT " . DisplayNameSQL( 'st' ) . "
			FROM staff st,course_periods cp
			WHERE st.STAFF_ID=cp.TEACHER_ID
			AND cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "') AS TEACHER";

			$extra['SELECT'] .= ",(SELECT cp.ROOM FROM course_periods cp WHERE cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "') AS ROOM";
		}

		if ( empty( $_REQUEST['_search_all_schools'] ) )
		{
			// School Title.
			$extra['SELECT'] .= ",(SELECT sch.TITLE FROM schools sch
				WHERE ssm.SCHOOL_ID=sch.ID
				AND sch.SYEAR='" . UserSyear() . "') AS SCHOOL_TITLE";
		}

		// Add URL to image path. Useful when HTML used in email to display remote images.
		SaveTemplate( DBEscapeString( SanitizeHTML( $_POST['email_text'], '', true ) ) );

		$email_text_template = GetTemplate();

		$RET = GetStuList( $extra );

		$LO_result = [ 0 => [] ];

		$i = 0;

		foreach ( (array) $RET as $student )
		{
			$substitutions = [
				'__FULL_NAME__' => $student['FULL_NAME'],
				'__LAST_NAME__' => $student['LAST_NAME'],
				'__FIRST_NAME__' => $student['FIRST_NAME'],
				'__MIDDLE_NAME__' =>  $student['MIDDLE_NAME'],
				'__STUDENT_ID__' => $student['STUDENT_ID'],
				'__USERNAME__' => $student['USERNAME'],
				'__SCHOOL_TITLE__' => $student['SCHOOL_TITLE'],
				'__GRADE_ID__' => $student['GRADE_ID'],
				'__TEACHER__' => $student['TEACHER'],
				'__ROOM__' => $student['ROOM'],
			];

			$substitutions += SubstitutionsCustomFieldsValues( 'STUDENT', $student );

			$email_text = SubstitutionsTextMake( $substitutions, $email_text_template );

			$to = empty( $test_email ) ? $student['EMAIL'] : $test_email;

			$result = dgettext( 'Email', 'Already sent' );

			if ( ! EmailAlreadySentTo( $to, $email_text ) )
			{
				$result = SendEmail(
					$to,
					$subject,
					$email_text,
					$reply_to,
					$cc,
					$attachments
				);

				$result = $result ? _( 'Success' ) : _( 'Fail' );
			}

			$LO_result[] = [
				'STUDENT' => $student['FULL_NAME'],
				'USERNAME' => $student['USERNAME'],
				'EMAIL' => $to,
				'RESULT' => $result,
			];

			$i++;
		}

		if ( empty( $test_email )
			&& ( ! empty( $_REQUEST['admin_emails_copy'] )
				|| ! empty( $_REQUEST['teacher_emails_copy'] ) ) )
		{
			$emails_copy = array_merge(
				(array) $_REQUEST['admin_emails_copy'],
				(array) $_REQUEST['teacher_emails_copy']
			);

			$subject = dgettext( 'Email', '[Copy]' ) . ' ' . $subject;

			$email_text = $email_text_template;

			// Verify emails array and build TO.
			$to_emails = [];

			foreach ( (array) $emails_copy as $email )
			{
				if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) )
				{
					$to_emails[] = $email;
				}
			}

			if ( $to_emails )
			{
				// Email To.
				$to = implode( ', ', $to_emails );

				$result = SendEmail(
					$to,
					$subject,
					$email_text,
					$reply_to,
					$cc,
					$attachments
				);
			}
		}

		if ( ! empty( $attachments ) )
		{
			// Delete files attached.
			foreach ( $attachments as $attachment )
			{
				unlink( $attachment );
			}
		}

		// Display errors above results list.
		if ( $error )
		{
			echo ErrorMessage( $error );

			$error = [];
		}

		unset( $LO_result[0] );

		$columns = [
			'STUDENT' => _( 'Student' ),
		];

		if ( $email_field_key !== 'USERNAME' )
		{
			$columns['USERNAME'] = _( 'Username' );
		}

		$columns += [
			'EMAIL' => _( 'Email' ),
			'RESULT' => _( 'Result' ),
		];

		// Cannot save or search because $_SESSION['email_field_key'] is unset below.
		$LO_options = [ 'save' => false, 'search' => false ];

		ListOutput(
			$LO_result,
			$columns,
			'Sending Result',
			'Sending Results',
			[],
			[],
			$LO_options
		);
	}
	else
	{
		$error[] = _( 'You must choose at least one student.' );

		// Unset modfunc & redirect URL.
		RedirectURL( 'modfunc' );
	}

	// Reset $email_field_key var.
	unset( $_SESSION['email_field_key'], $email_field_key );
}

echo ErrorMessage( $error );

if ( ! $_REQUEST['modfunc'] && ! empty( $email_field_key ) )
{
	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<form action="' . ( function_exists( 'URLEscape' ) ?
			URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] .
				'&modfunc=save&include_inactive=' . issetVal( $_REQUEST['include_inactive'] ) .
				'&_search_all_schools=' . issetVal( $_REQUEST['_search_all_schools'] ) ) :
			_myURLEncode( 'Modules.php?modname=' . $_REQUEST['modname'] .
				'&modfunc=save&include_inactive=' . $_REQUEST['include_inactive'] .
				'&_search_all_schools=' . $_REQUEST['_search_all_schools'] ) ) . '" method="POST">';

		$extra['header_right'] = SubmitButton( dgettext( 'Email', 'Send Email to Selected Students' ) );

		$extra['extra_header_left'] = '<table>' . issetVal( $extra['search'] ) . '</table>';

		$extra['search'] = '';

		// Subject field.
		$extra['extra_header_left'] .= '<table class="width-100p"><tr><td>' .
			TextInput(
				'',
				'subject',
				dgettext( 'Email', 'Subject' ),
				'required maxlength="100" size="50"',
				false
			) .
			'</td></tr>';

		// FJ add TinyMCE to the textarea.
		$extra['extra_header_left'] .= '<tr><td>' .
			TinyMCEInput(
				GetTemplate(),
				'email_text',
				_( 'Email' )
			) .
			'</textarea></td></tr>';

		$substitutions = [
			'__FULL_NAME__' => _( 'Display Name' ),
			'__LAST_NAME__' => _( 'Last Name' ),
			'__FIRST_NAME__' => _( 'First Name' ),
			'__MIDDLE_NAME__' =>  _( 'Middle Name' ),
			'__STUDENT_ID__' => sprintf( _( '%s ID' ), Config( 'NAME' ) ),
			'__USERNAME__' => _( 'Username' ),
			'__SCHOOL_TITLE__' => _( 'School' ),
			'__GRADE_ID__' => _( 'Grade Level' ),
		];

		if ( User( 'PROFILE' ) === 'admin' )
		{
			$substitutions += [
				'__TEACHER__' => _( 'Attendance Teacher' ),
				'__ROOM__' => _( 'Attendance Room' ),
			];
		}
		else
		{
			$substitutions += [
				'__TEACHER__' => _( 'Your Name' ),
				'__ROOM__' => _( 'Your Room' ),
			];
		}

		$substitutions += SubstitutionsCustomFields( 'STUDENT' );

		$extra['extra_header_left'] .= '<tr class="st"><td class="valign-top">' .
			SubstitutionsInput( $substitutions ) .
		'</td></tr>';

		$extra['extra_header_left'] .= '<tr class="st"><td class="valign-top">' .
			FileInput(
				'files_attached[]',
				dgettext( 'Email', 'Files Attached' ),
				'multiple',
				24 // 24MB max: Gmail is 25MB, Outlook is 34MB.
			) . '</td></tr>';

		$users_RET = DBGet( "SELECT STAFF_ID," . DisplayNameSQL() . " AS FULL_NAME,
			EMAIL,PROFILE
			FROM staff
			WHERE SYEAR='" . UserSyear() . "'
			AND (SCHOOLS IS NULL OR position('," . UserSchool() . ",' IN SCHOOLS)>0)
			AND PROFILE IN ('admin','teacher')
			ORDER BY FULL_NAME" );

		// Send copy to: Administrators and/or Teachers.
		// Get Administrators & Teachers with valid emails:
		$emailadmin_options = $emailteacher_options = [];

		foreach ( (array) $users_RET as $user )
		{
			if ( filter_var( $user['EMAIL'], FILTER_VALIDATE_EMAIL ) )
			{
				if ( $user['PROFILE'] === 'admin' )
				{
					$emailadmin_options[$user['EMAIL']] = $user['FULL_NAME'];
				}
				elseif ( $user['PROFILE'] === 'teacher' )
				{
					$emailteacher_options[$user['EMAIL']] = $user['FULL_NAME'];
				}
			}
		}

		$extra['extra_header_left'] .= '<tr><td>' . dgettext( 'Email', 'Send copy to' ) . ':<br />';

		$value = $allow_na = $div = false;

		// @since RosarioSIS 10.7 Use Select2 input instead of Chosen, fix overflow issue.
		$select_input_function = function_exists( 'Select2Input' ) ? 'Select2Input' : 'ChosenSelectInput';

		$extra['extra_header_left'] .= '<table><tr class="st"><td>' . $select_input_function(
			$value,
			'admin_emails_copy[]',
			_( 'Administrators' ),
			$emailadmin_options,
			$allow_na,
			'multiple', // Multiple select inputs.
			$div
		) . '</td>';

		$extra['extra_header_left'] .= '<td>' . $select_input_function(
			$value,
			'teacher_emails_copy[]',
			_( 'Teachers' ),
			$emailteacher_options,
			$allow_na,
			'multiple', // Multiple select inputs.
			$div
		) . '</td></tr></table></td></tr>';

		$extra['extra_header_left'] .= '<tr class="st"><td class="valign-top"><hr />' .
			_( 'Test Mode' ) . ':' . '<br />' .
			TextInput(
				'',
				'test_email',
				_( 'Email' ),
				'maxlength=255 type="email" placeholder="' . ( function_exists( 'AttrEscape' ) ? AttrEscape( _( 'Email' ) ) : htmlspecialchars( _( 'Email' ), ENT_QUOTES ) ) . '" size="24"',
				false
			) . '</td></tr>';

		$extra['extra_header_left'] .= '</table>';
	}

	$extra['SELECT'] = issetVal( $extra['SELECT'], '' );

	// Select Email.
	$extra['SELECT'] .= ",s." . DBEscapeIdentifier( $email_field_key ) . " AS EMAIL";

	// Add Email column.
	$extra['columns_after']['EMAIL'] = _( 'Email' );

	$extra['SELECT'] .= ",s.STUDENT_ID AS CHECKBOX";
	$extra['link'] = [ 'FULL_NAME' => false ];
	$extra['functions'] = [ 'CHECKBOX' => '_makeChooseCheckbox' ];
	$extra['columns_before'] = [ 'CHECKBOX' => MakeChooseCheckbox( 'Y', 'STUDENT_ID', 'st_arr' ) ];

	$extra['options']['search'] = false;
	$extra['new'] = true;

	Search( 'student_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' .
			SubmitButton( dgettext( 'Email', 'Send Email to Selected Students' ) ) . '</div></form>';
	}
}

function _makeChooseCheckbox( $value, $title )
{
	global $THIS_RET;

	if ( isset( $THIS_RET['EMAIL'] )
		&& filter_var( $THIS_RET['EMAIL'], FILTER_VALIDATE_EMAIL ) ) {

		// Has email and is valid email, show checkbox.
		return '<input type="checkbox" name="st_arr[]" value="' . ( function_exists( 'AttrEscape' ) ? AttrEscape( $value ) : htmlspecialchars( $value, ENT_QUOTES ) ) . '" checked />';
	}
}
