# 1. Find or install a LAMPP, WAMPP, or XAMPP stack. (XAMPP is my recommendation because it works on Windows, Linux, and Mac)

## Purpose: 
This step will create a usable backend on your personal computer. It is a development setup that will allow you to be able to work on the frontend, a WordPress site, and the complete backend all of which will get captured in your own GitHub repository for future use. That will also allow me to see your progress.
This kind of setup will allow you to update your WordPress site (step 2) with new pages, debug its PHP code, and JavaScript running in your local browser.

## Steps:

### Download XAMPP installer from: [XAMPP downloads page](https://sourceforge.net/projects/xampp/)

* On a Mac you may have to consult this: 
[fixing-macos-cannot-verify-app-free-malware](https://www.macobserver.com/tips/how-to/fixing-macos-cannot-verify-app-free-malware/)
This is because MacOS has tight security and it usually prevents you from installing appications you just find on the internet.  They prefer you use applications installed from the Mac App Store because they are carefully vetted to not contain malware.  There is a a workaround that will allow you to proceed with the install.  We trust XAMPP.
* If you have NGINX installed you might see this: 

> Welcome to nginx!
If you see this page, the ngin web server is successfully installed and working. Further configuration is required.
For online documentation and support please refer tonginx.org.
Commercial support is available at nginx.com.
Thank you for using nginx.

NGINX is an alternative to Apache that can also be used as a reverse proxy to divert some requests off to other servers.  For more information see the [NGINX page](https://nginx.org).  You can temporarily get around this by stopping NGINX server and coninuing on.  The XAMPP installer will configure the Apache server built in XAMPP in it's place. 

* When you run XAMPP it will present a control panel.  The Windows and Linux versions look different from the Mac version, but they all offer the same functionality. 
* In the control panel you should be able to select and start the three main services:
	- MySQL Database
 	- ProFTPD
 	- Apache Web Server.
* Go ahead and start all three. 
* There are also buttons on the panel that let you see the server logs.
* Finally there is a button labeled "Go to Application" that should take you to the XAMPP dashboard. 
* That dashboard page has menu items along the top:
	- Apache Friends
	- FAQs
	- HOW-TO Guides
	- PHPInfo - This shows lots of details about the installation and configuration of PHP.  You will need to consult that from time to time as a developer. 
	- phpMyAdmin - This is an essential tool for managing the SQL database. Remember how you found it, because you will need it soon.

* Now explore your file tree under the XAMPP application (for Mac: /Applications/XAMPP) In that directory you will find this: 
 * bin (all of the shell commands that XAMPP uses internally. You might find them handy)
 * cgi-bin (binaries used for executing things under Apache)
 * etc (configuration files for the services running under XAMPP)
 * htdocs (the public_html directory holding all web pages)
 * logs (where your Apache and other services put their logs)
 * For Mac: manager-osx.app (the control panel application) 
 * uninstall ( a script to erase XAMPP ) 
 * xamppfiles ( sometimes many of the files listed above are links into this directory for the most common publicly useful directories.  But there are a lot of other directories and files that YOU SHOULD not casually mess with.)
	
**A note about htdocs:** This is where you will install WP, but you can also install any web pages or apps that you have created or discovered.  To access that you can edit the htdocs/applications.html.  You can add links to your applications individually or a link to your own myApplications.html where you can link to your applications.

### Now that you have XAMPP running you are ready to start using it, but before you run off to do that, take some time to explore XAMPP some and read the FAQs and HOW-TO Guides, PHPInfo, and phpMyAdmin.  These are important background that you will need in the next steps.

Don't forget to leave a comment in Issue 1 to show you have completed step 1.
I hope you who do this on a Windows or Linux machine have also kept notes about differences and special tricks you used.   

