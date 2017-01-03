<?php

class Sfm {

	/**
	* Creates a zip file from a file or a folder recursively (without a full nested folder structure inside the zip file).
	* @see    http://stackoverflow.com/a/1334949/3073849
	* @param  string $source      The path of the folder you want to zip
	* @param  string $destination The path of the zip file you want to create
	* @return bool   Returns TRUE on success or FALSE on failure.
	*/
	public static function zip( $source, $destination ) {

		// Check that zip extensions is loaded and the source file/folder exists
		if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
			return false;
		}

		// Create the zip
		$zip = new ZipArchive();

		if ( ! $zip->open( $destination, ZIPARCHIVE::CREATE ) ) {
			return false;
		}

		// Clean up the path
		$source = str_replace( '\\', '/', realpath( $source ) );

		// If the source is a folder, add the files recursively
		if ( is_dir( $source ) ) {

			// Loop through the source folder
			foreach ( new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST ) as $path ) {

				$path = str_replace( '\\', '/', $path );
				$path = realpath( $path );

				if ( is_dir( $path ) ) {
					// If a folder, add a coresponding placeholder folder to the zip
					$zip->addEmptyDir( str_replace( "$source/", '', "$path/" ) );
				} elseif ( is_file( $path ) ) {
					// If a file, add it to the zip
					$zip->addFile( $path, str_replace( "$source/", '', $path ) );
				}
			}
		} elseif ( is_file( $source ) ) {
			// Otherwise if the source is a file add it to the zip alone
			$zip->addFile( $source, basename( $source ) );
		}

		// Finish up
		return $zip->close();
	}

	/**
	 * Extracts a zip file to a given folder. If optional overwrite is true, then the method deletes
	 * an existing destination folder and replaces it with the contents of the zip file.
	 * @param  string $source      The path of the zip file you want to extract
	 * @param  string $destination The path of the folder you want to extract to
	 * @param  bool   $overwrite   (Optional) Whether to overwrite an existing destination folder
	 * @return bool   Returns TRUE on success or FALSE on failure.
	 **/
	public static function unzip( $source, $destination, $overwrite = false ) {

		// Check that zip extensions is loaded and the source file/folder exists
		if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
			return false;
		}

		// Create the zip
		$zip = new ZipArchive();

		// Check if able to open zip
		if ( ! $zip->open( $source ) ) {
			return false;
		}

		// If the destination folder doesn't exist try to create it
		if ( ! is_dir( $destination ) ) {

			// Try to create the destination folder
			if ( ! self::mkdir( $destination ) ) {
				return false;
			}

		} elseif ( $overwrite ) {

			// If the destination folder needs to be overwritten, delete it
			self::rm( $destination );

			// Try to re-create the folder
			if ( ! self::mkdir( $destination ) ) {
				return false;
			}
		}

		// Exctract the zip to the destination folder
		$zip->extractTo( $destination );

		// If we have a resource fork, get rid of it
		$resource_fork = $destination . '/__MACOSX/';

		if ( file_exists( $resource_fork ) ) {
			self::rm( $resource_fork );
		}

		// Finish up
		return $zip->close();
	}

	/**
	 * Delete a file, or recursively delete a folder and it's contents
	 * @see    http://stackoverflow.com/a/15111679/3073849
	 * @param  string $path The path of the file or folder
	 * @return bool   Returns TRUE on success or if file already deleted or FALSE on failure.
	 **/
	public static function rm( $path ) {

		// If file doesn't exist we've achieved what is needed
		if ( ! file_exists( $path ) ) {
			return true;
		}

		// If the given path is a folder, we'll delete the contents recursively
		if ( is_dir( $path ) ) {

			// Loop through the folder starting from the childs (rmdir only works on empty folders)
			foreach ( new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST ) as $child_path ) {

				if ( $child_path->isDir() && ! $child_path->isLink() ) {
					// Remove a folder, which is now empty
					rmdir( $child_path->getPathname() );
				} else {
					// Remove a file
					unlink( $child_path->getPathname() );
				}
			}

			// Finally remove the given folder
			return rmdir( $path );

		} else {
			// Otherwise if the given path is a file, delete the file
			return unlink( $path );
		}

	}

	/**
	 * Alias for self:rm
	 */
	public static function delete( $source ) {
		return self:rm( $source );
	}

	/**
	 * Copy a file, or recursively copy a folder and its contents
	 * @see    http://stackoverflow.com/a/12763962/3073849
	 * @param  string $source      Source path
	 * @param  string $destination Destination path
	 * @param  array  $excludes    (Optional) An array containing the names of files and folders to exclude from copying as strings
	 * @return bool   Returns TRUE on success, FALSE on failure
	 **/
	public static function copy( $source, $destination, $excludes = array() ) {

		// Check if in excludes list
		if ( in_array( basename( $source ), $excludes ) ) {
			return false;
		}

		// Check for symlinks
		if ( is_link( $source ) ) {
			return symlink( readlink( $source ), $destination );
		}

		// Simple copy for a file
		if ( is_file( $source ) ) {
			return copy( $source, $destination );
		}

		// Make destination directory
		if ( ! is_dir( $destination ) ) {
			self::mkdir( $destination, fileperms( $source ) );
		}

		// Loop through the folder
		$dir = dir( $source );

		while ( false !== $entry = $dir->read() ) {

			// Skip pointers
			// TODO: use recursive iterators
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			// Deep copy directories
			self::copy( "$source/$entry", "$destination/$entry", $excludes );
		}

		// Finish up
		$dir->close();

		return true;
	}

	/**
	 * Alias for self:copy
	 */
	public static function cp( $source, $destination, $excludes = array() ) {
		return self:copy( $source, $destination, $excludes = array() );
	}

	/**
	 * Creates a folder recursively.
	 * @param  string $path        The path of the folder to create
	 * @param  int    $permissions (Optional) The mode given for the folder. The default mode (0774) is less permissive than the php default of (0777).
	 * @return bool   Returns TRUE if the folder already existed or if it was created on successfully, FALSE on failure.
	 */
	public static function mkdir( $path, $permissions = SFM_DEFAULT_PERMISSIONS ) {

		// Folder exists already, return true
		if ( is_dir( $path ) ) {
			return true;
		}

		// Create the folder
		return mkdir( $path, $permissions, true );

	}

}

// Set default file/folder permission mode if not already defined
if ( ! defined( 'SFM_DEFAULT_PERMISSIONS' ) ) {
	define( 'SFM_DEFAULT_PERMISSIONS', 0774 );
}