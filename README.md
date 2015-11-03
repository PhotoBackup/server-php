# PhotoBackup PHP server

A minimal PhotoBackup API endpoint developed in PHP.

## Goals

1. **Easy to run.** Minimal configuration and the widest possible server
   compatibility make this PhotoBackup implementation a great starting point!

2. **Easy to review.** All the code is extensively described through [PSR-5][]
   DocComments and should be easy to read along.

[PSR-5]: https://github.com/phpDocumentor/fig-standards/tree/master/proposed

## Setting up

1. Download the latest release from GitHub:

   https://github.com/PhotoBackup/server-php/releases/latest

2. Open `index.php` and change the value of `$Password` to the password you want
   to use for PhotoBackup.

3. Upload `index.php` and the `photos` folder to your web host.

4. Configure the server address in your PhotoBackup client to match the URL for
   your `index.php`-file. E.g. `http://example.com/photobackup/index.php`.

## License

The PhotoBackup PHP server is licensed under the OSI version of the MIT license.
It lets you do anything with the code as long as proper attribution is given.
Please see LICENSE.
