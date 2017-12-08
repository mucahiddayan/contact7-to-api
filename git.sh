CURRENT_TIME=$(date +"%Y.%m.%d-%H.%M")
PARENDIR="$(dirname "$PWD")"
if [ "$1" == "git" ];then
    echo "git called"
    git add *
    git commit -m "dev $CURRENT_TIME"
    git push origin dev
fi
sudo cp -r $PWD/ /var/www/html/wordpress/wp-content/plugins/
sudo chown www-data:www-data  -R /var/www/html/wordpress/wp-content/plugins/ # Let Apache be owner
sudo find /var/www/html/wordpress/wp-content/plugins/ -type d -exec chmod 755 {} \;  # Change directory permissions rwxr-xr-x
sudo find /var/www/html/wordpress/wp-content/plugins/ -type f -exec chmod 644 {} \;
