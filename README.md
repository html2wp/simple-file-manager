# Simple File Manager

Simple file management for PHP. When you're looking for simple syntax to do complex things. It does everything recursively by default and with a minimum amount of complaining.

## Methods

### zip

```php
Sfm::zip( string $source, string $destination )
```

Creates a zip file from a file or a folder recursively (without a full nested folder structure inside the zip file).

#### Parameters
$source The path of the folder you want to zip

$destination The path of the zip file you want to create

#### Return Values
Returns TRUE on success or FALSE on failure


### unzip
```php
Sfm::unzip( string $source, string $destination, bool $overwrite = false )
```

Extracts a zip file to a given folder. If optional overwrite is true, then the method deletes an existing destination folder and replaces it with the contents of the zip file.

#### Parameters
$source The path of the zip file you want to extract

$destination The path of the folder you want to extract to

$overwrite (Optional) Whether to overwrite an existing destination folder

#### Return Values
Returns TRUE on success or FALSE on failure


### rm
```php
Sfm::rm( string $path )
```

Delete a file, or recursively delete a folder and it's contents

#### Parameters
$path The path of the file or folder

#### Return Values
Returns TRUE on success or if file already deleted or FALSE on failure


### copy
```php
Sfm::copy( string $source, string $destination, array $excludes = array() )
```
Copy a file, or recursively copy a folder and its contents

#### Parameters
$source Source path

$destination Destination path

$excludes (Optional) An array containing the names of files and folders to exclude from copying as strings

#### Return Values
Returns TRUE on success, FALSE on failure

### mkdir
```php
Sfm::mkdir( string $path, int $permissions = SFM_DEFAULT_PERMISSIONS )
```

Creates a folder recursively.

#### Parameters
$path The path of the folder to create

$permissions (Optional) The mode given for the folder. The default mode (0774) is less permissive than the php default of (0777).

#### Return Values
Returns TRUE if the folder already existed or if it was created on successfully, FALSE on failure.

## Constants

### SFM_DEFAULT_PERMISSIONS

The default mode used by the methods. The default mode (0774) is less permissive than the php default of (0777).

