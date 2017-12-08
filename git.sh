CURRENT_TIME=date +"%Y.%m.%d-%H.%M"
git add *
git commit -m "dev $CURRENT_TIME"
git push origin dev
sudo cp * /var/www/html/wordpress/wp-content/plugins/$PWD