# Welcome!
Racktables is a datacenter management solution that provides an SQL database
backend along with a graphical front end to provide detailed views of your 
rackspace and equipment. This documentation is given as a reference to deploy
RackTables on a RHEL based 7.0 Linux distribution. The original documentation
can be found at the respective links at [project's web-site](http://racktables.org).

# How This Clone is Different
This clone is for use by Clemson University Information Technology. It has been
modified to fill specific requirements, including changes to rack layout design,
color scheme, and contains several plugins by default. Please see the section
titled "Customizations" for a list of the changes made to the vanilla code, and
the section titled "Plugins" for a list of plugins provided by default.

# How to Install RackTables

## 1. Prepare the Server

RackTables uses a web-server with PHP (5.5.0 or newer) for front-end and a
MySQL/MariaDB server version 5 for back-end. The most commonly used web-server
for RackTables is Apache httpd.

### 1.1. Install PHP
   1. Download and install the latest epel-release repository
      ```
      yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
      ```
   2. Download and install the latest remirepo. This repo contains latest php packages. We
      will be installing php 7.
      ```
      yum install https://rpms.remirepo.net/enterprise/remi-release-7.rpm
      ```
   3. Enable the latest version of php available in /etc/yum.repos.d/. For this example it
      it 7.3. (Note if you do not have yum-utils installed, install it now.)
      - If yum-utils is already installed, you can skip the first yum command.
      ```
      yum install yum-utils
      yum-config-manager --enable remi-php73
      ```
   4. Install php packages required for racktables
      ```
      yum install php php-mysql php-pdo php-gd php-snmp php-mbstring php-bcmath
      ```

### 1.2. Install and configure MySQL Server
   1. Install mariadb on the server
      ```
      yum install mariadb mariadb-libs mariadb-server
      ```
   2. Run secure setup to set root password. Follow the prompts for setup
      ```
      /usr/bin/mysql_secure_installation
      ```
   3. Enable unicode in sql server instance
      ```
      printf "[mysqld]\ncharacter-set-server=utf8\n" > /etc/my.cnf.d/mysqld-charset.cnf
      ```
   4. Enable the mariadb service to start at system boot
      ```
      systemctl enable mariadb.service
      ```
   5. Start mariadb service
      ```
      systemctl start mariadb.service
      ```

### 1.3. Install Apache httpd
   1. This guide is going to provide the basics to get a server up and running. Please
      review your policies or other guides on how to secure and configure Apache httpd.
      ```
      yum install httpd
      ```
   2. Enable the httpd service to start at system boot
      ```
      systemctl enable httpd
      ```

### 1.4. Extract RackTables to Apache Web Directory
   1. Download the latest version of RackTables from this repository, OR get the original
      unmodified code from [project's web-site](http://racktables.org).
   2. We will be assuming the default apache directories are being used. Extract the tarball
      to /var/www/html/ .Be careful with your permissions here. Follow your
      institution's guidelines on security and best practices when standing up a web server.
      ```
      tar -xvf --directory /var/www/html/
      ```
   3. A. Create a symbolic link between the wwwroot of the extracted directory to /var/www/html/.
      Please note, the name you use for the symlink will be the path you access from the browser.
      ```
      ln -s /var/www/html/<extracted_dir>/wwwroot/<link_name>
      ```
      B. For Apache users, a configuration file may be added in place of creating symbolic links.
      ```
      AddType  application/x-httpd-php         .php
      AddType  application/x-httpd-php-source  .phps

      <Directory /usr/local/www/<extracted_dir>/wwwroot>
	      DirectoryIndex index.php
	      Require all granted
      </Directory>
      Alias /<web_path> /usr/local/www/<extracted_dir>/wwwroot
      ```
   4. Start httpd service and test you are able to access. In this example, our symlink is simply
      named racktables, and our server address is myserver.mydomain.com.
      ```
      systemctl start httpd
      ```
      Attempt to access http://myserver.mydomain.com/racktables via web browser of your choice.
   5. An error is normal here. You may see a Configuration Error, and a prompt to launch the
      installer. Go ahead an launch the installer and proceed to the next section.

## 2. RackTables Instance Setup
   1. You should now be at the racktables installation screen in your web browser.If you are not, 
      you should be able to access via http://<your_server>/<path_to_rt>/?module=installer
   2. Go ahead and click proceed at step 1
   3. If you followed the guide at this point, you should pass the majority of checks. This guide
      does not go into LDAP setup, PCNTL configuration, or HTTPS. To enable HTTPS, please follow
      your organizations policies and procedures to properly encrypt your web traffic. This is
      highly recommended for any instance that will be accessible from outside your organization.
   4. At step 3, execute the provided command and hit retry. This should now pass. Next you will
      setup the SQL server database.
      1. Login to the database using the root password you setup in step 1.2.2.
      2. Execute the commands given. Be sure to change the fields for password for racktables user
         Set the database names and user names to desired if you do not want to use defaults.
      3. Hit next, the settings should be successfully written to the secrets file.
   5. Change the permissions of the secrets file. Follow the restrictions provided by the installer.
      An example is provided of permissions that are acceptible.
      ```
      chown apache:apache /var/www/html/<link_name>/inc/secret.php; chmod 440 /var/www/html/<link_name>/inc/secret.php
      ```
      Click retry and verify RackTables reports correctly set permissions.
   6. At step 5, verify the database is successfully initialized. If there is a failure, review the SQL
      connection settings set in step 3. If you need to return to step 3, remove the secret.php file
      so a new one can be generated.
   7. At step 6, enter the administrator password and hit retry.
   8. You are now ready to login for the first time!

# Code Changes
The code changes in this distribution mostly revolve around appearance changes... For now. Things 
planned in the future include spare parts inventory, better handling of breakout cables, and 
potential other changes. List of changes are below:
   1. Color Scheme.
   2. Rack layout changed from "Front, Interior, and Back" to "Left, Interior, and Right".
   3. Framework for AutoLogger (more about that later).

## How to change color scheme
For this guide we will be assuming you are starting at the top level of the extracted files from
the racktables tarball.

### pi.css
This file is located in wwwroot/css/. This file will dictate the color scheme used throughout.
To keep this short, only the objects changed will appear here with their default values.

   - Change text color in main windows (for example, text for categories in Configuration).
     Modification='color: #522D80;', Original='color: #3c78b5;'
   ```
   a {
           font-family: Verdana, sans-serif;
           color: #522D80;
           text-decoration: none;
   }
   ```
   - Change the color of the top menu bar containing the Objects, IPv4 space, and Rackspace quicklinks.
     Modification='background-color: #522D80;' Original='background-color: black;'
   ```
   .mainheader {
           background-color: #522D80;
           color: white;
           padding: 5px;
   }
   ```
   - Change the color of the second title bar, the bar which contains the search bar.
     Modification='background-color : #F66733;' Original='background-color : 3c78b5;'
   ```
   .menubar {
           padding: 5px 3px;
           font-size: 17px;
           line-height: 20px;
           background-color : #F66733;

           font-family: Verdana, arial, sans-serif;
           color : #ffffff;
   }
   ```
   - Change the color of the tab bar(navigation bar).
     Modification='background-color: #86898C;' Original='background-color: #f0f0f0;'
   ```
   .greynavbar {
           background-color: #86898C;
           border-top: 1px solid #86898C;
           margin-top: 0px;
   }
   ```
   - Change the color of the border on the tabs at the top of the screen.
     Modification='border: 1px solid #86898C;' Original='border: 1px solid #3c78b5;'
   ```
   #foldertab li a {
   padding: 3px 0.5em;
   margin-left: 3px;
   border: 1px solid #86898C;
   text-decoration: none;
   }
   ```
   - Change the color of an inactive tab.
     Modification='background: #522D80;' Original='background: #3c78b5;'
   ```
   #foldertab li a.std {
   border-bottom: none;
   background: #522D80;
   }
   ```
   - Change the color of a tab on hover.
     Modification='background: #3A4958;' Original='background: #003366;'
     Modification='border-color: #86898C;' Original='border-color: #003366;'
   ```
   #foldertab li a.std:hover {
   color: white;
   background: #3A4958;
   border-color: #86898C;
   }
   ```
   - Change the text color on the main menu.
     Modification='color: #522D80;' Original='color: #254a6f;'
   ```
   .mainmenu a {
           color: #522D80;
           text-decoration: none;
   }
   ```
   - Change the color for an object in problem state to make it more visible.
     Modification='.state_Tw  { background-color: #f00; }' Original='.state_Tw  { background-color: #804040; }'
   ```
   .state_Tw  { background-color: #f00; }
   ```
