# 5. Setup dev tools

## Purpose:

There are a number of tools of various sorts that make development in a WordPress environment much easier:

This step will introduce those, show you how to install, and use them.

## BASH or other UNIX shell

### BASH is usually the default shell in any UNIX, Linux, or Mac system.  

Many developers use VS Code IDE (interactive development environment) or some equivalent such as PHP Storm, Web Storm. All of those have BASH terminal built in, but it is possible to do without an IDE. 

### Web based IDE

If your repository is in GitHub, then there is a slick web only IDE built it to GitHub called Codespace. When viewing your project in GitHub, in the upper row of tools toward the right there is a button with a "+" sign to create new things like issues and repositories. That also has an entry for creating a new codespace. Click that and all you have to do is select your repository, branch, region, and machine type. Then click 'Create codespace'.  The next thing you know, it has spun up a virtual machine, installed your project,  and asked you to login with your GitHub credentials.  Suddenly you have a VS Code equivalent with your repository already cloned.  

Google offers something similar called [Google Firebase Studio](https://firebase.google.com/docs/studio)

### XAMPP 
XAMPP is a cross platform application that contains a complete stack (Apache, MySQL, PHP, and Perl) pre packaged and configured, ready to run. It has a nice control panel front end.  

If your stack is hosted under XAMPP then there are BASH tools already installed. 

* Linux or Mac:  simply add XAMPP/xamppfiles/bin to your shell path.
* Windows: [see this page](https://wpcrux.com/blog/how-to-access-the-command-line-for-xampp-on-windows) which shows you how to start a shell from the XAMPP control panel. 

### Local by Flywheel: 
[An alternative to XAMPP which some prefer ](https://localwp.com)

## Database tools
### XAMPP has [MariaDB](https://en.wikipedia.org/wiki/MariaDB) built in (which is essentially MySQL).   

XAMPP's main page has phpMyAdmin built into it's menu bar, and it already knows of your local databases. Also there are phpMyAdmin plugins that you can install inside of the dashboard of your WP site. 

## Shell control of WordPress
There is a project called [wp-cli](https://wp-cli.org) which provides shell control of your WP site.  You can install it easily.  The [WP-GitHub](https://github.com/MichaelKentBurns/WP-GitHub.git) tool I am developing uses this. So go ahead and install wp-cli. 

## WordPress plugins
There a number of useful plugins that you might need.
Consult the WordPress documentation or explore in your WP instance's dashboard the "Plugins" tool in the sidebar.  It contains a vast array of plugins and makes it really easy to install and activate them.  Be a bit skeptical of them as presented.  Sometimes there are many similar but some are good and some are not.  Read the reviews and star ratings.   Here are some that I use and recommend: 

### Classic Editor
is a simple one that gives you the option when editing pages and posts of choosing whether you use the modern block editor, or the older but still useful classic editor.   

### Ultimate Markdown
[UMD](https://daext.com/ultimate-markdown/) is a cool but somewhat quirky plugin that lets you import .md files and convert them to WP pages and posts. It also allows you to export your otherwise written pages and posts to roughly equivalent .md files. I'm exploring that currently and will refer to this in a future step. 

### Anti-Spam by CleanTalk
[Anti-Spam](https://cleantalk.org) is a service that has plugins to WordPress, Joomla, and Drupal content management systems.   I use this for my MichaelKentBurns.com training site.  It intercepts comments from readers, and other login attemts and analyzes them.  I installed this because I was getting many random spam comments, and even intrusion attempts.  I'm talking 20 or more a week that I was having to accept or reject manually.   

### PHP compatibility checker
[See this page:](https://wordpress.org/plugins/php-compatibility-checker/)
WordPress sites obviously require the use of PHP, and PHP comes in many versions.  WordPress itself, and many themes and plugins have specific requirements on the version of PHP you have installed.  When you are using an older version and WP itself or one of your plugins requires a newer version, your dashboard will tell you that you need to upgrade.  But, don't instantly go out and install the latest greatest, because some of your plugins may not yet be compatible.   This plugin will scan all of your themes and plugins and give you a report of which versions of PHP they are or are not compatible with.  Once you check the report you will know which is the most recent version that is compatible with your WP site. 
