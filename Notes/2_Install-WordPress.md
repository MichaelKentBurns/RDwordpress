# 2. Install WordPress

## Purpose: 
In step 1 you created a backend on your local development computer where you can also author and debug code in both PHP and JavaScript.

In this step you will install a new WP instance inside that environment.

This will get you started in the world of not only using WP but will teach you things about how WP works internally, and how it can be customized.

Inside your XAMPP setup, you will have an htdocs folder. The pathname will be something like this:

/Applications/XAMPP/xamppfiles/htdocs

You will now install WP into a directory name of your choosing (I call mine RDwordpress).

By the end of this step you will have your very own WP instance.

## Steps:
1. Read [WordPress.org](https://wordpress.org).
2. Read [the download page](https://wordpress.org/download).
3. Read [the before you install page](https://developer.wordpress.org/advanced-administration/before-install/howto-install/)
4. My install on my laptop is at /Applications/XAMPP/xamppfiles/htdocs/RDwordpress
5. Make sure you have git tools to turn RDwordpress into a git repository and add it to GitHub. If you don't already have those tools see our [Work globally page](https://michaelkentburns.com/index.php/work-globally/).
6. When creating the repository GitHub Desktop will give you the option of selecting a .gitignore file.  Since this is a WP directory, select the **WordPress** option. However, once you do that very little of the WP content will be tracked as evidence of using a ‘git status’ command. You should edit that .gitignore as suggested in the next step.
7. I commented out many of the files and directories except leaving /wp-content/themes/twenty*/ and /wp-content/plugins/hello.php, *.log, and .htaccess . This is because while doing research and development I might choose to modify some of the original files and I want to track those changes.
8. My GitHub repository is [https://github.com/MichaelKentBurns/RDwordpress.git](https://github.com/MichaelKentBurns/RDwordpress.git) so you can consult it and see exactly what I changed during this install and future research and development work.
9. Before you start making modifications to your configuration files first read this section in the [WP advanced-administration page](https://developer.wordpress.org/advanced-administration/wordpress/wp-config/)
10. While editing the wpconfig.php file with database name, username
and password and when you commit and push that to GitHub, you
should get an email from ‘GitGuardian’ telling you you just exposed
some secret information to the world. Take a look at that and follow
some of the links in the email so you understand the issue. Normally,
this is a serious matter so don’t ignore it. If someone were to peek
into my public repository they would have valuable secrets that would
let them viciously attack my new wordpress site. HOWEVER, in this
specific case where I am creating a wordpress site inside XAMPP on
my local and private laptop, I’m safe for several reasons. 1) This is a
learning exercise and not a production wordpress site there is nothing
sensitive in it, and in fact I’m committing everything to GitHub so I can
detect any malicious changes. 2) All of this is on my private laptop that is password protected, and physically under my control at all times, so
nobody can access it at all. (I do not provide any network access to this machine inside the firewall of my private network, and I don’t take this laptop to public places. So learn this lesson, and heed these
warnings whenever you encounter them. If you hope to get work to deal with other people’s WordPress sites, you are responsible for their privacy and it is a serious matter that could affect your career.
11. Once you have edited your wp-config you are ready to activate the
self configuration script. As it says in the howto-install doc
mentioned above, simply point your browser to your localhost
wordpress wp-admin/install.php page, which in my case is https://
localhost/RDwordpress/wp-admin/install.php That page will ask a
few questions and then it will quickly configure your wordpress and
end up in the wp-admin dashboard. Two of the questions will be the
**username** for a new admin account in your WP install and the
**password** to go with it. Record those in whatever password vault you use because you WILL need them. Also and don’t use a password that you use for anything else. *(I need to go through that again and screen
capture the details here)*. After it is finished you will be able to login
to your new wordpress site admin panel using that username/
password. The URL will be something like https://localhost/RDwordpress/wp-admin/ . From there you can look down the tool bar on the left for the ‘Users’ page. There you will find yourself with the role of Administrator. Keep that username and password private. Logging in as Administrator allows you full control of every detail of your wordpress instance!!
12. At this point you should be able to use a shell or GitHub Desktop and
see that there are ‘No local changes’. But, where did all the details
like the admin username/pw go? If it’s not in any files just setup, then
where is it? The answer is that all of that, and a lot more, are stored in
the mySQL database which is not itself a file in the WordPress
directory. So, how do you see it? How does that get into your Git
repository? Good Question!
13. Remember the phpMyAdmin that you used to create the database?
Go there in a new tab that you open to the XAMPP dashboard page
(https://localhost/). Then look at the tabs in the blue banner and find
phpMyAdmin. Find the RDwordpress database in the left navigation
bar. Open that and see the data tables.
14. When you click on one of the tables you will see the records. Try
RDwp_options. That table contains the main options that control
your site. Also look at RDwp_users and see your admin user
account.
15. Let’s dump that data into files in your git repository. Use a shell to
create three hidden directories next to the .git directory:
`mkdir .data .pages .posts*`
16. Use `ls -la` to see that directory and you should see things like 
wp-content, index.php and a bunch of other wp-* files and directories.
You should probably make those directories private: 
`chmod 700 .data .pages .posts`
17. Back at the table view of phpMyAdmin under the table of rows, you
will see Query results operations. Choose **Export**. In that form, take
all the defaults until you get to Export method: For that choose
**Custom** and that will open up a number of other options, one of which
will be ‘Output:’ First, just select ‘**View output as text**’ and click the Export button at the bottom. This will show you what the dump will
look like. 
18. After browsing that some, change the output to ‘`Save output to a file.`’ You should find that file in the Downloads directory that your browser
defaults to. Repeat this for each table. You will notice that initially
some of the tables will not have any rows, and thus the Export won’t
work. But if there are rows then you can export. Don’t forget to
change the Export method: to “Custom”. You should end up with
about 9 output files.
19. Copy those from your downloads directory to the .data directory in
your wp instance.
20. Now go back to GitHub Desktop and you should see those files show
up as changes. Commit those with the comment ‘Initial data dump’
and push it. Then find them in your GitHub repository. You have
now exported your database.
21. Later on we will automate that data dump process.

## You now have a full server stack with a WordPress instance installed on your development machine.

Take some time to celebrate by exploring your wp-admin panel, including all of the tabs on the side navigation bar.  Figure out how to create and publish both a page and a post. 

Don't forget to comment in the Issue #2 page that you finished, what you liked or were confused about.  Especially make notes about how your experienced differed from mine.  I hope you captured screen captures and saved them somewhere. 
