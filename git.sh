CURRENT_TIME=$(date +"%Y.%m.%d-%H.%M")
PARENDIR="$(dirname "$PWD")"
git add *
git commit -m "dev $CURRENT_TIME"
git push origin dev
sudo cp PARENDIR/$PWD /var/www/html/wordpress/wp-content/plugins/