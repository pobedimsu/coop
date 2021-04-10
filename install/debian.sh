#!/bin/bash

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")

DOCKER_COMPOSE=1.29.0

SCRIPT_START_SECONDS=$(date +%s)
SCRIPT_START_DATE=$(date +%T)
NORMAL='\033[0m'
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'

if [ ! -f /etc/debian_version ]
then
    echo -e "${RED} This installer only for Debian Linux (9 and 10) ${NORMAL}"
    exit
fi

RELEASE=$(lsb_release -cs)

if [[ "$RELEASE" == "stretch" ]]
then
    echo -e "${YELLOW} Debian 9 'Stretch' installing... ${NORMAL}"
elif [[ "$RELEASE" == "buster" ]]
then
    echo -e "${YELLOW} Debian 10 'Buster' installing... ${NORMAL}"
else
    echo -e "${RED} BAD Debian version ${NORMAL}"
    exit
fi

apt update -qq
apt upgrade -qq -y -o Dpkg::Options::=--force-confnew --allow-change-held-packages

export DEBIAN_FRONTEND=noninteractive
export UCF_FORCE_CONFFNEW=1

apt install gnupg gnupg2 software-properties-common dirmngr apt-transport-https ca-certificates -qq -y -o Dpkg::Options::=--force-confnew

tput sgr0

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
ln -fs /usr/share/zoneinfo/Europe/Moscow /etc/localtime
dpkg-reconfigure --frontend noninteractive tzdata

#dpkg-reconfigure tzdata
#dpkg-reconfigure locales

apt update -qq

apt install acl sudo time tmux zip -qq -y
apt install bash-completion colordiff fail2ban net-tools htop make mailutils mc mlocate supervisor -qq -y
apt install nginx docker-ce docker-ce-cli containerd.io -qq -y
apt install certbot python-certbot-nginx php-cli -qq -y

curl -L "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

if [ ! -f ~/.bashrc_old ]
then
    mv ~/.bashrc ~/.bashrc_old
    cp -R ${SCRIPTPATH}/configs/debian/etc / -v
    cp -R ${SCRIPTPATH}/configs/debian/root / -v
fi

apt clean
apt autoremove -y
mkdir /var/www

ssh-keygen -A

SCRIPT_END_DATE=$(date +%T)

echo "Install started  at: ${SCRIPT_START_DATE}"
echo "Install finished at: ${SCRIPT_END_DATE}"
echo "Total time elapsed: $(date -ud "@$(($(date +%s) - $SCRIPT_START_SECONDS))" +%T)"
