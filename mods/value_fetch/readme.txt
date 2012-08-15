Value Fetcher Mod - Updated by ChaseTheLasers 05/2009 - 0.1 Beta
Original work by Beansman, with updates done by Penthux. Original Readme below.


DISABLE ANY OTHER PRICE UPDATING MODS BEFORE RUNNING



Q. So whats changed?

A. Not a lot. Sadly the site at http://svn.nsbit.dk/itemfetch/ has been down for a while
making prices on the killboard outdated. I have created my own version of this site from
scratch so the files can be used with the "value_fetch" and "value_fetch_pwu" mods.

Corrections have been made to fetch_values.php (line 8) and cron.php (line 23) to stop 
it calling the wrong mod directory. If you use the old version of value_fetch_pwu it
will make calls to the default 'value_fetch' mod.


Otherwise all that has changed in this 'release' is the URL locations to fetch the files from
my server. Updating the prices in your killboard is now a matter of hitting "fetch" again.

Hurrah!





Q. I can't use fopen() on URLs as my host blocks it. Can I still update the values?

Q. I get errors like:
Warning: fopen() [function.fopen]: URL file-access is disabled in the server configuration in <location> on line 40



A. Sure can =)

Download the "local update" zip from http://www.ekchu.com/dev/ and unzip the
contents to the root of your killboard. This means if your killboard is located
at http://www.yourcorp.com/killboard/ you should be able to access the files by
typing in http://www.yourcorp.com/killboard/items.xml and
http://www.yourcorp.com/killboard/items.xml.gzphp in your browser. Once these files
are uploaded, open up fetcher.php from the "value_fetch_pwu" mod and change lines 21
and 22 to look like this:

	var $compfile = "items.xml.gzphp";
	var $uncompfile = "items.xml";

No other changes are needed.
Save the fetcher.php file and upload it to the "value_fetch_pwu" mod directory, overwriting the
current one.

The "local update package" contains exactly the same 2 files that are hosted on
my server (items.xml and items.xml.gzphp) apart from they are zipped to save bandwidth.
Please use the zipped version if you plan to reupload them to your server =)

ChaseTheLasers =)




=====================================
Value Fetcher Mod - personal webspace update version by Penthux (penthux.co.uk)
revision date: 2008.12.21


Based on Value Fetcher Mod by beansman - http://svn.nsbit.dk/itemfetch/

Credits: eve-dev.net (Eve Dev Killboard original code) - http://www.eve-dev.net
         beansman (Value fetcher Mod) - http://svn.nsbit.dk/itemfetch/


=====================================

What does Value Fetcher - 'personal webspace update version' do?

Same thing as the original version does! It updates the values in your eve-dev.net 
killboard for ships and faction items but it updates from your own domain rather
than beanman's http://svn.nsbit.dk/itemfetch/ site.


Why modifiy existing files if they are already working?

In the event that your hosting company, or ISP, (like mine) has firewall rules 
in place which do not allow you to download files from remote locations/servers, 
or has a restriction on the size of files that you can download, this can severely
compromise the ability to use the Value Fetch Mod to update values on your killboard. 


What's changed?

fetcher.php - Added some brief comments on prerequisites before using this mod. 
URL paths to items.xml.gzphp and items.xml (after editing) now point to a location 
on your personal webspace instead of http://svn.nsbit.dk/itemfetch/

fetch_values.php - Rephrased some of the message text after the update. Added a 
"back" button so it's easier to navigate if the import fails, or for any other 
reason(s).

settings.php - The link to the forum thread (Mod version) was out of date so it was 
updated this to the current correct URL. Also added a link "Check for latest updates" 
which points to beanman's site (http://svn.nsbit.dk/itemfetch/) for ease of downloading 
the XML files so they can be uploaded to your own personal webspace for importing. 

** The rest of the code in all files is unchanged.


=====================================

How to use this modification:

You need to unpack the files and open fetcher.php in your favourite text editor. Then 
near the top of the file you will see this code:

// download the items.xml.gzphp and items.xml files from http://svn.nsbit.dk/itemfetch/
// upload them to a directory on your own webspace 
// modify the two paths below to point to the downloaded files
var $compfile = "http://<your_path_to>/items.xml.gzphp";
var $uncompfile = "http://<your_path_to>/items.xml"; 

Download the XML files from beanman's website, upload them to your own webspace and 
change the <your_path_to> part to suit your own domain/path to the files. Then upload
the entire value_fetch folder to your /killboard/mods/ directory.

Login to your killboard as admin and click on the modules link. You will see there is 
now a value_fetch mod available in the list. Tick the box on the right to activate the
mod and then click the"SAVE" button at the bottom. The page will refresh and then you 
should click on the "settings" link next to value_fetch in the mod list.

Now you are presented with the value_fetch interface. Make sure you select the correct 
version of PHP installed on your domains server before you click the "Fetch" button. 
If you have uploaded the XML files, and changed the paths in fetcher.php to point to 
those two files correctly, you should have a successful update.
 
==================


[Personal Notes]

The reason I made this modification is because two other people I know, and myself,
have issues with our hosting companies/ISPs who block our ability to use PHP in order
to access remote information or download files on our domains. I had to grab files from http://svn.nsbit.dk/itemfetch/ and upload them to my own webspace and modify the existing 
PHP code to import them into the MySQL database to get around this problem. This 
modification is a work-around to that problem and the end result is the same - updated 
values on your killboard. The only difference really is that you'll be using up your own 
bandwidth instead of beanman's, which i'm sure he will not be unhappy about. LOL! :D

If there are any errors found I apologise in advance. This modification of value_fetch 
has been tested and works perfectly on my own killboard installation. Please let me know 
if you find any errors and I will endeavour to correct them asap.

If you find this modification usefull then a couple of hours on a Sunday afternoon have
been very well spent. Enjoy! :)


Penthux
2008.12.21











