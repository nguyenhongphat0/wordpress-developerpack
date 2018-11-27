<?php
/*
 *  @author nguyenhongphat0 <nguyenhongphat28121998@gmail.com>
 *  @license https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0
 */

function response( $data ) {
	echo json_encode( $data );
	wp_die();
}

function listFiles( $path ) {
	$project = realpath( $path );
	$directory = new RecursiveDirectoryIterator( $project );
	$files = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::LEAVES_ONLY );
	return $files;
}

function archive( $regex, $output, $maxsize, $timeout ) {
	// Extend excecute limit
	if ( isset( $timeout ) ) {
		set_time_limit( $timeout );
	}

	// Get files in directory
	$project = realpath( '..' );
	$files = listFiles( $project );

	// Initialize archive object
	$zip = new ZipArchive();
	$zip->open( $output, ZipArchive::CREATE | ZipArchive::OVERWRITE );

	foreach ( $files as $name => $file )
	{
		// Skip directories ( they would be added automatically )
		$ok = ( preg_match( $regex, $name ) ) && ( !$file->isDir() ) && ( $file->getSize() < $maxsize );
		if ( $ok )
		{
			// Get real and relative path for current file
			$filePath = $file->getRealPath();
			$relativePath = substr( $filePath, strlen( $project ) + 1 );

			// Add current file to archive
			$zip->addFile( $filePath, $relativePath );
		}
	}

	// Zip archive will be created only after closing object
	return $zip->close();
}

function implodeOptions( $options ) {
	function escape( $path )
	{
		$project = realpath( '..' );
		if ( substr( $path, 0, 1 ) === "/" ) {
			$path = $project.$path;
		}
		$path = str_replace( '.', '\.', $path );
		$path = str_replace( '/', '\/', $path );
		return( $path );
	}
	$options = array_map( "escape", $options );
	$regex = implode( '|', $options );
	return $regex;
}

function includeFiles( $includes ) {
	$regex = implodeOptions( $includes );
	$regex = '/^.*('.$regex.').*$/i';
	return $regex;
}

function excludeFiles( $excludes ) {
	$regex = implodeOptions( $excludes );
	$regex = '/^((?!'.$regex.').)*$/i';
	return $regex;
}

add_action( 'wp_ajax_developerpack_zip', 'developerpack_zip' );
function developerpack_zip()
{
	$files = $_POST['files'];
	$timeout = $_POST['timeout'];
	if ( isset( $_POST['maxsize'] ) ) {
		$maxsize = $_POST['maxsize'];
	} else {
		$maxsize = 1000000;
	}
	$response = array(
		'status' => 400,
	);
	$empty = empty( $files );
	if ( $empty ) {
		$response['message'] = 'Not enough parameters';
	}
	foreach ( $files as $file ) {
		if ( $file === '' ) {
			$response['message'] = 'Empty rules are not allowed';
		}
	}
	$rules = $_POST['rule'];
	switch ( $rules ) {
	case 'include':
		$regex = includeFiles( $files );
		break;

	case 'exclude':
		$regex = excludeFiles( $files );
		break;

	default:
		$response['message'] = 'Invalid rule';
		break;
	}
	if ( ! isset( $response['message'] ) ) {
		$output = dirname( __FILE__ ) . '/zip/' . $_POST['output'];
		$success = archive( $regex, $output, $maxsize, $timeout );
		if ( $success ) {
			$response['status'] = 200;
			$response['message'] = 'File created successfully';
			$response['output'] = $_POST['output'];
		} else {
			$response['message'] = 'Permission denied!';
		}
	}
	response( $response );
}

function humanFileSize( $size, $unit="" ) {
	if( ( !$unit && $size >= 1<<30 ) || $unit == "GB" )
		return number_format( $size/( 1<<30 ),2 )." GB";
	if( ( !$unit && $size >= 1<<20 ) || $unit == "MB" )
		return number_format( $size/( 1<<20 ),2 )." MB";
	if( ( !$unit && $size >= 1<<10 ) || $unit == "KB" )
		return number_format( $size/( 1<<10 ),2 )." KB";
	return number_format( $size )." bytes";
}

add_action( 'wp_ajax_developerpack_zipped', 'developerpack_zipped' );
function developerpack_zipped() {
	$path = dirname( __FILE__ ) . '/zip/';
	$project = realpath( '..' );
	$relative = substr( $path, strlen( $project ) + 1 );
	$files = array_diff( scandir( $path ), array( '.', '..', '.keep' ) );
	$res = array();
	foreach ( $files as $file ) {
		$res[] = array(
			'name' => $file,
			'path' => $relative . $file,
			'size' => humanFileSize( filesize( $path . $file ) )
		);
	}
	response( $res );
}

add_action( 'wp_ajax_developerpack_analize', 'developerpack_analize' );
function developerpack_analize() {
	$start = microtime( true );
	$project = realpath( '..' );
	$files = listFiles( $project );
	$size = $d = 0;
	foreach ( $files as $name => $file ) {
		$size += $file->getSize();
		$d++;
	}
	response( array(
		'total' => $d . ' files and directories',
		'size' => humanFileSize( $size ),
		'execution_time' => ( microtime( true ) - $start ) . 's'
	) );
}

add_action( 'wp_ajax_developerpack_open', 'developerpack_open' );
function developerpack_open() {
	$project = realpath( '..' );
	$filename = $_POST['file'];
	$file = $project.'/'.$filename;
	$res = array(
		'status' => 404,
		'message' => 'List directory success'
	);
	if ( $filename !== '' && is_file( $file ) ) {
		$file = $project.'/'.$_POST['file'];
		$res['content'] = file_get_contents( $file );
		$res['status'] = 200;
		$res['message'] = 'OK';
	}
	if ( !is_dir( $file ) ) {
		$file = dirname( $file );
		if ( $res['status'] != 200 ) {
			$res['message'] = 'File or directory not found';
		}
	} else {
		$res['status'] = 204;
	}
	$res['pwd'] = $file;
	$ls = scandir( $file );
	$res['ls'] = $ls;
	response( $res );
}

add_action( 'wp_ajax_developerpack_save', 'developerpack_save' );
function developerpack_save() {
	$project = realpath( '..' );
	$filename = $_POST['file'];
	$content = stripslashes( $_POST['content'] );
	$file = $project.'/'.$filename;
	if ( $filename !== '' ) {
		$success = file_put_contents( $file, $content );
		if ( $success !== false ) {
			if ( is_file( $file ) ) {
				$res = array(
					'status' => 200,
					'message' => 'File saved successfully!'
				);
			} elseif ( !is_dir( $file )  ) {
				$res = array(
					'status' => 201,
					'message' => 'File created successfully!'
				);
			}
		} else {
			$res = array(
				'status' => 403,
				'message' => 'Permission denied!'
			);
		}
	} else {
		$res = array(
			'status' => 404,
			'message' => 'Error saving file!'
		);
	}
	response( $res );
}

add_action( 'wp_ajax_developerpack_delete', 'developerpack_delete' );
function developerpack_delete() {
	$project = realpath( '..' );
	$filename = $_POST['file'];
	$file = $project.'/'.$filename;
	if ( $filename !== '' && is_file( $file ) ) {
		$success = unlink( $file );
		if ( $success ) {
			$res = array(
				'status' => 200,
				'message' => 'File deleted successfully!'
			);
		} else {
			$res = array(
				'status' => 403,
				'message' => 'Permission denied!'
			);
		}
	} else {
		$res = array(
			'status' => 404,
			'message' => 'Nothing has been deleted!'
		);
	}
	response( $res );
}
