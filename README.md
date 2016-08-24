# Simple File Manager

Simple file management for PHP.

## Methods

### zip( $source, $destination )

```php
Sfm::zip( $source, $destination )
```

Creates a zip file from a file or a folder recursively without a full nested folder structure inside the zip file.

#### Parameters
$source      The path of the folder you want to zip

$destination The path of the zip file you want to create

#### Return Values
Returns TRUE on success or FALSE on failure.


### unzip( $source, $destination, $overwrite = false )
```php
Sfm::unzip( $source, $destination, $overwrite = false )
```
* Extracts a zip file to given folder. Overwrite deletes an existing destination folder and replaces it with the content of the zip file.
	 * @param       string   $source      The path of the zip file you want to extract
	 * @param       string   $destination The path of the folder you want to extract to
	 * @param       bool     $overwrite   (Optional) Whether to overwrite an existing destination folder
	 * @return      bool     Returns TRUE on success or FALSE on failure.


### delete( $source )
```php
Sfm::delete( $source )
```
* Delete a file, or recursively delete a folder and it's contents
	 * Based on: http://stackoverflow.com/a/15111679/3073849
	 * @param       string   $source The path of the file or folder
	 * @return      bool     Returns TRUE on success or if file already deleted or FALSE on failure.


### copy( $source, $destination, $excludes = array() )
```php
Sfm::copy( $source, $destination, $excludes = array() )
```
* Copy a file, or recursively copy a folder and its contents
	 * Based on: http://stackoverflow.com/a/12763962/3073849
	 * @param       string   $source      Source path
	 * @param       string   $destination Destination path
	 * @param       array    $excludes    (Optional) Name of files and folders to exclude from copying
	 * @return      bool     Returns true on success, false on failure

### mkdir( $path, $permissions = SFM_DEFAULT_PERMISSIONS )
```php
Sfm::mkdir( $path, $permissions = SFM_DEFAULT_PERMISSIONS )
```

* Creates a folder recursively.
	 * @param  		string 	$path        The path of the folder to create
	 * @param  		int 	$permissions (Optional) The mode given for the folder. The default mode (0764) is less permissive than the php default of (0777).
	 * @return      bool    Returns true if the folder already existed or if it was created on successfully, false on failure.
