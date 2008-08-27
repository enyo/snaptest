README - SnapTest
=================

Introduction
------------
SnapTest is a powerful unit testing framework for PHP 5+, leveraging PHP's unique runtime language to simplify the unit test process without sacrificing the agility tests provide.

SnapTest is a free software project licensed under the Mozilla Public License.

**NOTE** Starting with SnapTest 1.2.0, SnapTest will be licensed under the BSD License.

Getting Started
---------------
(from http://code.google.com/p/snaptest/wiki/QuickStart)

Place Snap wherever you want, and run a self test:

   1. if you have php in an obvious location (path, /usr/bin, /usr/local/bin, /opt/local/bin), run the command ./snaptest.sh ./ from inside the snaptest directory.
   2. if php is not in an obvious location, you can run ./snaptest.sh ./ --php<path> where <path> indicates the location of your php binary.
   3. if shell scripting for whatever reason isn't working, you can also use the PHP binary directly by calling <php> snaptest.php --path=<php> ./ where <php> is the location of your php binary. 

When ran, you should see output like the following:

<code>
User@Host ~/snaptest> ./snaptest.sh ./
...............................................................................................
______________________________________________________________________
Total Cases:    37
Total Tests:    95
Total Pass:     95
Total Defects:  0
Total Failures: 0
</code>

If you don't get any failures (marked with an F followed by information about the error, you're ready to go!

From here, check out http://code.google.com/p/snaptest/wiki/QuickStart Step 2 to start writing your own tests.

License
-------
  * SnapTest < 1.2.0 is licensed under the Mozilla Public license (MPL)

  * SnapTest >= 1.2.0 is licensed under the new BSD License (please see LICENSE for full terms)