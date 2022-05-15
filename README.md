# Installation
1. First assign the `$share_url` variable with your onedrive share link. example: `$share_url = "<your share link>";`
2. Place index.php file inside the DocumentRoot folder of apache or nginx. Make sure php is enabled..
3. You need to redirect all routes to the index.php file and put the path in a parameter called path.
    * If you are using apache, you can add following rewrite rules in your virualhost config file:
        ---
            RewriteEngine on
            RewriteRule ^(.*)$ /index.php?path=$1 [PT,L,QSA]
        ---
    * If you are using nginx, please search the equivalent configuration on the internet
        ---
        ---