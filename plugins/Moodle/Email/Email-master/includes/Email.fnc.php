<?php
/**
 * Email functions
 *
 * @package Email module
 */

/**
 * Email already sent to
 *
 * @since 5.0
 *
 * @param string $to         Email address to.
 * @param string $email_text Email body text.
 *
 * @return bool True if same email text sent to same email address ($to).
 */
function EmailAlreadySentTo( $to, $email_text )
{
	static $to_global = [];

	static $email_text_tmp;

	if ( ! in_array( $to, $to_global ) )
	{
		$to_global[] = $to;

		$email_text_tmp = $email_text;

		return false;
	}

	if ( $email_text_tmp == $email_text )
	{
		return true;
	}

	$email_text_tmp = $email_text;

	return false;
}


/**
 * Handle `multiple` files attribute for FileUpload().
 * Move $_FILES[ $input ][...][ $i ] to $_FILES[ {$input}_{$i} ] so FileUpload() can handle it.
 *
 * @deprecated since 7.8 Use FileUploadMultiple().
 *
 * @example foreach ( FileUploadMultiple( 'files_attached' ) as $input ) { FileUpload( $input ) }
 *
 * @param string $input Input name, without square brackets [].
 *
 * @return array Emtpy if no files. $input if not multiple. {$input}_{$i} if multiple.
 */
function EmailFileUploadMultiple( $input )
{
	if ( function_exists( 'FileUploadMultiple' ) )
	{
		return FileUploadMultiple( $input );
	}

	if ( ! isset( $_FILES[ $input ] ) )
	{
		return [];
	}

	if ( ! is_array( $_FILES[ $input ]['name'] ) )
	{
		return [ $input ];
	}

	$inputs = [];

	$files = [];

	foreach ( $_FILES[ $input ] as $attribute => $files_info )
	{
		foreach ( $files_info as $i => $file_info )
		{
			if ( ! isset( $files[ $i ] ) )
			{
				$files[ $i ] = [];
			}

			$files[ $i ][ $attribute ] = $file_info;
		}
	}

	foreach ( $files as $i => $file )
	{

		$input_new_index = $input . '_' . $i;

		$inputs[] = $input_new_index;

		// Move $_FILES[ $input ][...][ $i ] to $_FILES[ {$input}_{$i} ] so FileUpload() can handle it.
		$_FILES[ $input_new_index ] = $file;
	}

	unset( $_FILES[ $input ] );

	return $inputs;
}
