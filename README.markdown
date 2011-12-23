# Soares' JobMine Interview Notifier

## License

Copyright (c) 2009-2011 Michael A. Soares <me@mikesoares.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Description

This is a PHP-based interview/application status notifier I hacked together a few years ago for the University of Waterloo's job posting site, JobMine (<http://jobmine.uwaterloo.ca>).

It is really, **REALLY** hacked together so there is **DEFINITELY** a lot that can be improved upon. I've only kept the functionality up-to-date with UW's various URL changes, new status additions for jobs, and SMS functionality that has been pretty flaky (I've been using Tropo as a gateway).

## Basic Setup

- Create a MySQL database and import the structure in *soares_jmnotify.sql*.
- Modify *includes/config.php* with your own settings.
- In *gogopowerranges.php* (I have no idea why I called it that), define the correct path in the first *require*.
- Run *chmod 755 gogopowerrangers.php* so that the script is executable.
- Grab PHPMailer from <http://sourceforge.net/projects/phpmailer/> and place the two class files in the *includes* folder; be sure to configure that accordingly.
- Create the following cron job so that the script polls JobMine every half hour (adjust accordingly): **/30 8-23 * * * php /path/to/jobmine_notifer/gogopowerrangers.php >/dev/null 2>&1*

## Optional Setup

### SMS Notifications

If you want SMS notifications to work, browse to *includes/useful_funcs.php* and uncomment the *sendSms* function's contents. If you're using Tropo, make sure you have a script setup somewhere that will handle your outgoing SMSes and you've set your Tropo token in *includes/config.php*. If you're using another gateway, rewrite *sendSms* with whatever API calls that are required.

### Logging

By default, the scripts log a bunch of stats to the database. You can see all of the logging calls in *validate.php* and *gogopowerrangers.php*. The data from here is then graphed using *JpGraph*. If you want to be able to see such data, grab *JpGraph* from <http://www.jpgraph.net/> and place the contents in the *jpgraph* folder. You can access the stats by visiting whatever URL you will be hosting this under /stats.php.

### SSL

SSL is **HIGHLY RECOMMENDED** or else users will be sending their passwords unencrypted over the Internet. The latter is bad. Don't be a n00b - set up SSL.

## Questions & Suggestions

If you have any questions, feel free to send them to <me@mikesoares.com>.

If you have any suggestions, feel free to create a branch, implement them and open up a pull request.