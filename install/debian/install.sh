#!/bin/bash

SCRIPT_START_SECONDS=$(date +%s)
SCRIPT_START_DATE=$(date +%T)

DOCKER_COMPOSE=1.29.0

NORMAL='\033[0m'
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
DEBIAN_VERSION=$(cat /etc/debian_version | head -c 1)

apt install wget curl lsb-release -y

RELEASE=$(lsb_release -cs)

tput sgr0

if (( $DEBIAN_VERSION == 9 ))
then
    echo -e "${YELLOW} Debian 9 'Stretch' installing... ${NORMAL}"
elif (( $RELEASE == 'buster' ))
then
    echo -e "${YELLOW} Debian 10 'Buster' installing... ${NORMAL}"
else
    echo -e "${RED} BAD Debian version ${NORMAL}"
    exit
fi

# Docker
curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add -
apt-key fingerprint 0EBFCD88
add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian ${RELEASE} stable"

# Nginx
wget --quiet -O - http://nginx.org/keys/nginx_signing.key | apt-key add -
printf "deb http://nginx.org/packages/mainline/debian/ ${RELEASE} nginx\ndeb-src http://nginx.org/packages/mainline/debian/ ${RELEASE} nginx" > /etc/apt/sources.list.d/nginx.list

sed -i s/'# ru_RU.UTF-8 UTF-8'/'ru_RU.UTF-8 UTF-8'/g /etc/locale.gen
locale-gen ru_RU.UTF-8
localectl set-locale LANG=ru_RU.UTF-8
update-locale LANG=ru_RU.UTF-8
dpkg-reconfigure tzdata
#dpkg-reconfigure locales

apt update
apt upgrade -y

apt install software-properties-common dirmngr apt-transport-https ca-certificates -y
apt install acl bash-completion certbot python-certbot-nginx colordiff fail2ban net-tools gnupg gnupg2 htop make mailutils mc mlocate sudo supervisor time tmux zip -y
apt install nginx -y
apt install docker-ce docker-ce-cli containerd.io -y

curl -L "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Configs
if [ ! -f ~/.bashrc_old ]
then
    mv ~/.bashrc ~/.bashrc_old
    cp -R configs/etc / -v
    cp -R configs/root / -v
fi

apt clean
apt autoremove -y
mkdir /var/www

ssh-keygen -A

SCRIPT_END_DATE=$(date +%T)

echo "Install started  at: ${SCRIPT_START_DATE}"
echo "Install finished at: ${SCRIPT_END_DATE}"
echo "Total time elapsed: $(date -ud "@$(($(date +%s) - $SCRIPT_START_SECONDS))" +%T)"
