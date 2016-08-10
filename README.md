# PhotoBackup PHP server

A minimal PhotoBackup API endpoint developed in PHP.


## Goals

1. **Easy to run.** Minimal configuration and the widest possible server
   compatibility make this PhotoBackup implementation a great starting point!

2. **Easy to review.** All the code is extensively described through [PSR-5][]
   DocComments and should be easy to read along.

3. **Easy to integrate.** Server is written as both a standalone server and 
   a Composer-friendly library, so it can be easily integrated into third party
   applications. (Composer is not required for standalone use.)

[PSR-5]: https://github.com/phpDocumentor/fig-standards/tree/master/proposed


## Setting up

1. Download the latest release from GitHub:

   https://github.com/PhotoBackup/server-php/releases/latest

2. Copy `index.php.example` to `index.php` (so your configuration will not be
   overwritten on upgrade).

3. Open `index.php` and change the value of `$Password` to the password you want
   to use for PhotoBackup. Alternatively you can include external configuration
   file located outside document root of your web server (see example in the
   `index.php` file).

4. Upload everything (or at least `index.php`, `class` folder and the `photos`
   folder) to your web host.

5. Make sure your web server can write to the `photos` folder.

6. Configure the server address in your PhotoBackup client to match the URL for
   your `index.php` file. E.g. `http://example.com/photobackup/index.php`.


## Upgrade

Your configuration is stored in the `index.php` file which is not under version
control, therefore you can simply use Git to pull a new version and then upload
everything to your web server.


## Use as a Library

To integrate this server to your application, see the `Server` class in
`class/Server.php`. This server library can be loaded using Composer and its
PSR-4 class loader. In this case simply ignore the `index.php` stuff.


## License

The PhotoBackup PHP server is licensed under the OSI version of the MIT license.
It lets you do anything with the code as long as proper attribution is given.
Please see LICENSE.

