# Welcome!
Racktables is a datacenter management solution that provides an SQL database
backend along with a graphical front end to provide detailed views of your 
rackspace and equipment. This documentation is given as a reference to deploy
RackTables on a RHEL based 7.0 Linux distribution. The original documentation
can be found at the respective links at the [project's web-site](http://racktables.org).

# How This Clone is Different
This clone is for use by Clemson University Information Technology. It has been
modified to fill specific requirements, including changes to rack layout design,
color scheme, and contains several plugins by default. Please see the section
titled "Customizations" for a list of the changes made to the vanilla code, and
the section titled "Plugins" for a list of plugins provided by default. There is
now an additional section entitled Custom Scripts which pertains to new custom
scripts written to make administration of RackTables easier for system admins
and to integrate with xcat.

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

## Changing the Rack View
In this section we will discuss how to change the appearance of the rack view in
racktables. We will be mainly focusing on changing title text to fit a model more
usable for datacenter environments employing high density compute resources.

### interface.php
This file is located in wwwroot/inc/. This file is modified to tweak the way racks are drawn 
in racktables. These changes include modifying the width of each column in the rack view
and changing the headers of the columns to be "Front, Interior, Left".

   - Change the function renderRack. There is one section we are concerned with, see below for
     the modified code:
   ```
   echo "</h2></td></tr></table>\n";
   echo "<table class=rack border=0 cellspacing=0 cellpadding=1>\n";
   echo "<tr><th width='10%'>&nbsp;</th><th width='30%'>Left</th>";
   echo "<th width='30%'>Interior</th><th width='30%'>Right</th></tr>\n";
   ```
   Original Code:
   ```
   echo "</h2></td></tr></table>\n";
   echo "<table class=rack border=0 cellspacing=0 cellpadding=1>\n";
   echo "<tr><th width='10%'>&nbsp;</th><th width='20%'>Front</th>";
   echo "<th width='50%'>Interior</th><th width='20%'>Back</th></tr>\n";
   ```
   - Change the function renderGridForm. This is the grid displayed when adding\removing
     objects from the rack. Modified code:
   ```
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '0', ${rackData['height']})\">Left</a></th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '1', ${rackData['height']})\">Interior</a></th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '2', ${rackData['height']})\">Right</a></th></tr>\n";
   ```
   Original Code:
   ```
   echo "<th width='20%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '0', ${rackData['height']})\">Front</a></th>";
   echo "<th width='50%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '1', ${rackData['height']})\">Interior</a></th>";
   echo "<th width='20%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '2', ${rackData['height']})\">Back</a></th></tr>\n";
   ```
   - Change the function renderRackSpaceForObject. This function is fairly self explanatory.
     This will draw the rackspace when a specific object is selected. Similar changes are made
     to the other functions listed thus far. Modified code:
   ```
   echo "<tr><th width='10%'>&nbsp;</th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '0', ${rackData['height']})\">Left</a></th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '1', ${rackData['height']})\">Interior</a></th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '2', ${rackData['height']})\">Right</a></th></tr>\n";
   renderAtomGrid ($rackData, $is_ro);
   echo "<tr><th width='10%'>&nbsp;</th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '0', ${rackData['height']})\">Left</a></th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '1', ${rackData['height']})\">Interior</a></th>";
   echo "<th width='30%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '2', ${rackData['height']})\">Right</a></th></tr>\n";
   echo "</table>\n<br>\n";
   ```
   Original code:
   ```
   echo "<tr><th width='10%'>&nbsp;</th>";
   echo "<th width='20%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '0', ${rackData['height']})\">Front</a></th>";
   echo "<th width='50%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '1', ${rackData['height']})\">Interior</a></th>";
   echo "<th width='20%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '2', ${rackData['height']})\">Back</a></th></tr>\n";
   renderAtomGrid ($rackData, $is_ro);
   echo "<tr><th width='10%'>&nbsp;</th>";
   echo "<th width='20%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '0', ${rackData['height']})\">Front</a></th>";
   echo "<th width='50%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '1', ${rackData['height']})\">Interior</a></th>";
   echo "<th width='20%'><a href='javascript:;' onclick=\"toggleColumnOfAtoms('${rack_id}', '2', ${rackData['height']})\">Back</a></th></tr>\n";
   echo "</table>\n<br>\n";
   ```
   - Change the function renderMolecule. Modified code:
   ```
   echo "<tr><th width='10%'>&nbsp;</th><th width='30%'>Left</th><th width='30%'>Interior</th><th width='30%'>Right</th></tr>\n";
   ```
   Original Code:
   ```
   echo "<tr><th width='10%'>&nbsp;</th><th width='20%'>Front</th><th width='50%'>Interior</th><th width='20%'>Back</th></tr>\n";
   ```

These changes mostly amount to searching for any instance of Front of Back in the interface.php file
and changing Front to Left and Back to Right. The other changes to be made are the width percentages
to all be 30% as opposed to 20%,50%,20% in the original implementation. This is a cosmetic only change
and does not change how data is stored or entered into the database.

# Plugins
By default, this clone of RackTables comes with the AutoLogger framework, and CSV import capabilities.
The CSV import plugin was originally written by Erik Ruiter, SURFsara BV, Amsterdam, The Netherlands
in 2014. The original copy of this plugin can be found (here)[https://github.com/sara-nl/racktables-contribs/blob/master/csv_import.php]
This function has been expanded to add some additional capabilities.

## Auto Logger
The Auto Logger allows for systems or people to update the logs for a target object in racktables. Usage for this would
include systems running self checks and reports problems detected back to racktables, or administrators adding administrative
notes to bulk number of systems for maintenance or problem resolution. To configure the Auto Logger, follow the steps below:
   - Login to the SQL server instance and create a new user with the following permissions
      ```
      GRANT SELECT, UPDATE ON <racktables_db>.Object TO '<username>'@'localhost';
      GRANT SELECT ON <racktables_db>.RackSpace TO '<username>'@'localhost';
      GRANT SELECT, UPDATE ON <racktables_db>.RackThumbnail TO '<username>'@'localhost';
      GRANT ALL PRIVILEGES ON <racktables_db>.ObjectLog TO '<username>'@'localhost';
      ```
   - From the wwwroot directory, edit the autologger.ini.php file and update the fields using the username and password created
     previously, the server host address for the database (localhost if running locally), and the database name for the racktables
     instance.

NOTES: For security purposes, the config.ini is wrapped in php which will hide these parameters from an improperly configured web
server. The recommended procedure is to move the autologger/config.ini.php file to a location outside of the root web directory to
prevent apache from serving this file at all. Another solution if this is not possible is to limit access to the file using htaccess
or the configuration file for Apache.

# Custom Scripts
The custom_scripts directory will continually be upgraded as more scripts and functionality are added. These scripts are provided
free of cost, but are to be used at your own risk.

## rt_to_xcat_nodepos.sh
This script requires [xCAT](https://xcat.org/) to be installed. It relies on the nodels function. The script will take an object
name or group name to get a list of objects to update. the xCAT table nodepos will be updated. The script will query the RackTables
database and update the rack, unit, and parent chassis of the object in xCAT using the data found in RackTables. Recommended a
read-only sql account be used to access the database, and an account that has write access to xCAT databases must be used.
