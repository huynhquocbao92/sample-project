<?php

/**
 * Custom sanitizeFileName
 * @author baohq
 * @date 2017-10-30
 */
class elFinderPluginMySanitizer extends elFinderPluginSanitizer
{
	protected function sanitizeFileName($filename, $opts, $allows = array()) {
		$fileName = md5(microtime());
		$fileExt  = pathinfo($filename, PATHINFO_EXTENSION);
		return $fileName.'.'.$fileExt;
	}
}

return array(

    /*
    |--------------------------------------------------------------------------
    | Upload dir
    |--------------------------------------------------------------------------
    |
    | The dir where to store the images (relative from public)
    |
    */
    'dir' => ['uploads'],

    /*
    |--------------------------------------------------------------------------
    | Filesystem disks (Flysytem)
    |--------------------------------------------------------------------------
    |
    | Define an array of Filesystem disks, which use Flysystem.
    | You can set extra options, example:
    |
    | 'my-disk' => [
    |        'URL' => url('to/disk'),
    |        'alias' => 'Local storage',
    |    ]
    */
    'disks' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Routes group config
    |--------------------------------------------------------------------------
    |
    | The default group settings for the elFinder routes.
    |
    */

    'route' => [
        'prefix' => 'elfinder',
        'middleware' => null,
        // 'middleware' => 'replace-this-with-your-middleware', //Set to null to disable middleware filter
    ],

    /*
    |--------------------------------------------------------------------------
    | Access filter
    |--------------------------------------------------------------------------
    |
    | Filter callback to check the files
    |
    */

    'access' => 'Barryvdh\Elfinder\Elfinder::checkAccess',

    /*
    |--------------------------------------------------------------------------
    | Roots
    |--------------------------------------------------------------------------
    |
    | By default, the roots file is LocalFileSystem, with the above public dir.
    | If you want custom options, you can set your own roots below.
    |
    */

    'roots' => null,

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | These options are merged, together with 'roots' and passed to the Connector.
    | See https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options-2.1
    |
    */

    'options' => array(
        'bind' => array(
            'mkdir.pre mkfile.pre rename.pre' => array(
                'Plugin.MySanitizer.cmdPreprocess'
            ),
            'upload.presave' => array(
                'Plugin.MySanitizer.onUpLoadPreSave',
                'Plugin.AutoResize.onUpLoadPreSave'
            )
        ),
        // global configure (optional)
        'plugin' => array(
            'MySanitizer' => array(
                'enable'    => true
            ),
            'AutoResize' => array( // auto resize image when upload
                'enable'    => true,
                'maxWidth'  => 1200,
                'maxHeight' => 1200,
                'quality'   => 100
            )
        ),
        'uploadAllow' => array(
            'image/png', 'image/jpg', 'image/pjpeg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-windows-bmp', 'application/msword', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/x-msexcel', 'application/pdf', 'application/mspowerpoint', 'application/vnd.ms-powerpoint', 'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint', 'application/x-mspowerpoint', 'application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'
        ),
        'uploadDeny' => array(
            'application/octet-stream' //.exe
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | Root Options
    |--------------------------------------------------------------------------
    |
    | These options are merged, together with every root by default.
    | See https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options-2.1#root-options
    |
    */
    'root_options' => array(
        'tmbSize' => 150
    ),

);
