<?php
	/**
	 * GIT DEPLOYMENT SCRIPT
	 *
	 * Used for automatically deploying websites via github or bitbucket, more deets here:
	 *
	 *		https://gist.github.com/1809044
	 */

	/**
          
         * This script is intended for deployments to test and production servers and only pulls code from the origin. 
         * The initial install of a deployment is a manual git clone from your choice of origin. 
         * To stand up a server for a Healthfirst deployment, IT should do the following:
         * 
         *      # mkdir /var/www/<new site folder>
         *      # chown apache:apache /var/www/<new site folder>
         *      # cd /var/www/<new site folder> 
         *      # sudo -Hu apache git clone ssh://gitter@phabricator.healthfirstfinancial.com/source/<repo>.git /var/www/<new site folder>
         * 
         * 	  Assuming the SetEnv variables are defined (see index.php) in the Apache config, and the site functions
         *   then subsequent updates of this site are done via a browser as follows:
         * 
         *   https://<URL> (i.e. hf1.healthfirstfinancial.com)/gitdeploy.php to deploy from production stable master...this would be the default for a live server.
         *   https://<URL>/gitdeploy.php?dev=1 to deploy from develop HEAD
         *   https://<URL>/gitdeploy.php?branch=<branchname> to deploy from a specific branch
         *   https://<URL>/gitdeploy.php?tag=<tagname> to deploy a specfic tag
         *   https://<URL>/gitdeploy.php?status=1 to get information about the current deployed state
         * 
         */


        // The commands
        $mode='Not Set...defaulting to master HEAD';

        $commands = array(
        		'find . -type f -exec chmod 640 {} \;',
        		'find . -type d -exec chmod 750 {} \;',
                'echo $PWD',
                'whoami',
                'git fetch --all');


        $tag = htmlspecialchars($_GET['tag']);
        $branch = htmlspecialchars($_GET['branch']);
        $dev = htmlspecialchars($_GET['dev']);
        $status = htmlspecialchars($_GET['status']);

        if($status == '1'){
            unset($commands);
            $mode = "STATUS";
            $commands[]= "git status";
        }else{
            if(!empty($tag)){
                $mode="Tag: $tag";
                $commands[]= "git checkout -f tags/$tag";
            }elseif(!empty($branch)){
                $mode="Branch: $branch";
                $commands[]= "git checkout -f $branch";
            }elseif($dev==='1'){
                $mode = "DEV";
                $commands[]= "git checkout -f develop";
            }else{
                $commands[]= "git checkout -f master";
            }

            $commands[]='git pull';
            $commands[]='git clean -xdf';
            $commands[]='git status';
            $commands[]='git submodule sync';
            $commands[]='git submodule update';
            $commands[]='git submodule status';
//            $commands[]='find . -type f -exec chmod 440 {} \;';
//            $commands[]='find . -type d -exec chmod 550 {} \;';     
            $commands[]='chmod 750 ./assets ./protected/runtime';     
            $commands[]='chmod 640 ./protected/runtime/*';
            $commands[]='if [ -d "./protected/data" ]; then chmod 750 ./protected/data; chmod 640 ./protected/data/*; fi';     
            $commands[]='if [ -d "./protected/upload_files" ]; then chmod 777 ./protected/upload_files; chmod 640 ./protected/upload_files/*; fi';  
        }

        // Run the commands for output
        $output = "Mode:$mode\n";//var_dump($_GET);var_dump($commands);die();
        foreach($commands AS $command){
                // Run it
                $tmp = shell_exec($command);
                // Output
                $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
                $output .= htmlentities(trim($tmp)) . "\n";
        }

        // Make it pretty for manual user access (and why not?)
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
        <meta charset="UTF-8">
        <title>GIT DEPLOYMENT SCRIPT</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
 .  ____  .    ____________________________
 |/      \|   |                            |
[| <span style="color: #FF0000;">&hearts;    &hearts;</span> |]  | Git Deployment Script v0.1 |
 |___==___|  /              &copy; oodavid 2012 |
              |____________________________|

<?php echo $output; ?>
</pre>
</body>
</html>