<?php

class Easyfile 
{
	/**
	* Creates a zip file from a file or a folder recursively without a full nested folder structure inside the zip file
	* Based on: http://stackoverflow.com/a/1334949/3073849
	* @param $source			The path of the folder you want to zip
	* @param $destination		The path of the zip file you want to create
	* @return Returns TRUE on success or FALSE on failure.
	*/
	public static function zip( $source, $destination )
    {
		if ( !extension_loaded( 'zip' ) || !file_exists( $source ) )
	        return false;

	    $zip = new ZipArchive();

	    if ( !$zip->open( $destination, ZIPARCHIVE::CREATE ) )
	        return false;

	    $source = str_replace( '\\', '/', realpath( $source ) );

	    if ( is_dir( $source ) )
	    {
	        $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

	        foreach ( $files as $file )
	        {
	            $file = str_replace( '\\', '/', $file );

	            // Ignore "." and ".." folders
	            if( in_array( substr( $file, strrpos( $file, '/' )+1 ), array( '.', '..' ) ) )
	                continue;

	            $file = realpath( $file );

	            if ( is_dir( $file ) )
	                $zip->addEmptyDir( str_replace( $source . '/', '', $file . '/') );

	            else if ( is_file( $file ) )
	                $zip->addFromString( str_replace( $source . '/', '', $file ), file_get_contents( $file ) );

	        }
	    }

	    else if ( is_file( $source ) )
	        $zip->addFromString( basename( $source ), file_get_contents( $source ) );

	    return $zip->close();
    }
    
    /**
     * Extracts a zip file to given folder. Overwrite deletes an existing destination folder and replaces it with the content of the zip file.
     * @param $source 			The path of the zip file you want to extract
     * @param $destination 		The path of the folder you want to extract to
     * @param $overwrite 		Whether to overwrite an existing destination folder
     * @return Returns TRUE on success or FALSE on failure.
     **/
 	public static function unzip( $source, $destination, $overwrite = false )
    {
		if ( !extension_loaded( 'zip' ) || !file_exists( $source ) )
	        return false;

	    $zip = new ZipArchive();

	    if ( !$zip->open( $source ) )
	    	return false;

    	if ( !is_dir( $destination ) )
		{
			if ( !mkdir( $destination ) )
	    		return false;
		}

		else if ( $overwrite )
		{
			self::delete( $destination );

			if ( !mkdir( $destination ) )
	    		return false;
		}

	    $zip->extractTo( $destination );

	    // If we have a resource fork, get rid of it
	    $resource_fork = $destination . '/__MACOSX/';

	    if ( file_exists( $resource_fork ) )
	    	self::delete( $resource_fork );

	    return $zip->close();
    }

    /**
     * Delete a file, or recursively delete a folder and it's contents
     * Based on: http://stackoverflow.com/a/15111679/3073849
     * @param $source 			The path of the file or folder
     * @return Returns TRUE on success or FALSE on failure.
     **/
 	public static function delete( $source )
    {
    	if ( is_dir( $source ) ) {

	    	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST ) as $path )
	        	$path->isDir() && !$path->isLink() ? rmdir( $path->getPathname() ) : unlink( $path->getPathname() );

			return rmdir( $source );
    	}

    	else
    		return unlink( $source );

	}

	/**
	 * Copy a file, or recursively copy a folder and its contents
	 * Based on: http://stackoverflow.com/a/12763962/3073849
	 * @param       string   $source    Source path
	 * @param       string   $destination      Destination path
	 * @param       string   $permissions New folder creation permissions
	 * @return      bool     Returns true on success, false on failure
	 **/
	public static function copy( $source, $destination, $permissions = 0755 )
	{
	    // Check for symlinks
	    if ( is_link( $source ) )
	        return symlink( readlink( $source ), $destination );

	    // Simple copy for a file
	    if ( is_file( $source ) )
	        return copy($source, $destination);

	    // Make destination directory
	    if ( !is_dir( $destination ) )
	        mkdir($destination, $permissions);

	    // Loop through the folder
	    $dir = dir( $source );
	    while ( false !== $entry = $dir->read() ) {

	        // Skip pointers
	        if ( $entry == '.' || $entry == '..' )
	            continue;

	        // Deep copy directories
	        self::copy( "$source/$entry", "$destination/$entry", $permissions );
	    }

	    // Clean up
	    $dir->close();

	    return true;
	}

	/**
	 * Creates a folder recursively.
	 * @param  		string 	$path The path of the folder to create
	 * @param  		int 	$mode The mode given for the folder. The default mode (0764) used by the method is less permissive than the php default of (0777).
	 * @return      bool    Returns true if the folder already existed or if it was created on successfully, false on failure.
	 */
	public static function mkdir( $path, $mode = 0764 ) {

		// Folder exists already, return true
		if ( file_exists( $path ) )
			return true;

		return mkdir( $path, $mode, true );

	}
}

