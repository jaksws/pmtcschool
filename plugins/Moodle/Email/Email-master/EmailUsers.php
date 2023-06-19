<?php
/**
 * Email Users
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

if ( User( 'PROFILE' ) === 'teacher' )
{
	$_ROSARIO['allow_edit'] = true;
}

if ( $_REQUEST['modfunc'] === 'save'
	&& AllowEdit() )
{
	if ( ! empty( $_REQUEST['st_arr'] ) )
	{
		// If $test email is set then this script will only 'go through the motions'
		// and email the results to the $test_email address instead of parents.
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

		$extra['WHERE'] = " AND s.STAFF_ID IN (" . $st_list . ")";

		$extra['SELECT'] = issetVal( $extra['SELECT'], '' );

		// SELECT s.* Custom Fields for Substitutions.
		$extra['SELECT'] .= ",s.*";

		$extra['SELECT'] .= ",s.USERNAME,s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME";

		// Select Email.
		$extra['SELECT'] .= ",s.EMAIL";

		// Add URL to image path. Useful when HTML used in email to display remote images.
		SaveTemplate( DBEscapeString( SanitizeHTML( $_POST['email_text'], '', true ) ) );

		$email_text_template = GetTemplate();

		$RET = GetStaffList( $extra );

		$LO_result = [ 0 => [] ];

		$i = 0;

		foreach ( (array) $RET as $user )
		{
			$substitutions = [
				'__FULL_NAME__' => $user['FULL_NAME'],
				'__LAST_NAME__' => $user['LAST_NAME'],
				'__FIRST_NAME__' => $user['FIRST_NAME'],
				'__MIDDLE_NAME__' =>  $user['MIDDLE_NAME'],
				'__STAFF_ID__' => $user['STAFF_ID'],
				'__USERNAME__' => $user['USERNAME'],
			];

			$substitutions += SubstitutionsCustomFieldsValues( 'staff', $user );

			$email_text = SubstitutionsTextMake( $substitutions, $email_text_template );

			$to = empty( $test_email ) ? $user['EMAIL'] : $test_email;

			$result = SendEmail(
				$to,
				$subject,
				$email_text,
				$reply_to,
				$cc,
				$attachments
			);

			$LO_result[] = [
				'staff' => $user['FULL_NAME'],
				'USERNAME' => $user['USERNAME'],
				'EMAIL' => $to,
				'RESULT' => $result ? _( 'Success' ) : _( 'Fail' ),
			];

			$i++;
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
			'staff' => _( 'User' ),
		];

		$columns += [
			'EMAIL' => _( 'Email' ),
			'RESULT' => _( 'Result' ),
		];

		ListOutput( $LO_result, $columns, 'Sending Result', 'Sending Results' );
	}
	else
	{
		$error[] = _( 'You must choose at least one user' );

		// Unset modfunc & redirect URL.
		RedirectURL( 'modfunc' );
	}
}

echo ErrorMessage( $error );

if ( ! $_REQUEST['modfunc'] )
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

		$extra['header_right'] = SubmitButton( dgettext( 'Email', 'Send Email to Selected Users' ) );

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
			'__STAFF_ID__' => sprintf( _( '%s ID' ), Config( 'NAME' ) ),
			'__USERNAME__' => _( 'Username' ),
		];

		$substitutions += SubstitutionsCustomFields( 'staff' );

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
	$extra['SELECT'] .= ",s.EMAIL";

	// Add Email column.
	$extra['columns_after']['EMAIL'] = _( 'Email' );

	$extra['SELECT'] .= ",s.STAFF_ID AS CHECKBOX";
	$extra['link'] = [ 'FULL_NAME' => false ];
	$extra['functions'] = [ 'CHECKBOX' => '_makeChooseCheckbox' ];
	$extra['columns_before'] = [ 'CHECKBOX' => MakeChooseCheckbox( 'Y', 'STAFF_ID', 'st_arr' ) ];

	$extra['options']['search'] = false;
	$extra['new'] = true;

	Search( 'staff_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' .
			SubmitButton( dgettext( 'Email', 'Send Email to Selected Users' ) ) . '</div></form>';
	}
}

function _makeChooseCheckbox( $value, $title )
{
	global $THIS_RET;

	if ( filter_var( $THIS_RET['EMAIL'], FILTER_VALIDATE_EMAIL ) ) {

		// Has email and is valid email, show checkbox.
		return '<input type="checkbox" name="st_arr[]" value="' . ( function_exists( 'AttrEscape' ) ? AttrEscape( $value ) : htmlspecialchars( $value, ENT_QUOTES ) ) . '" checked />';
	}
}
