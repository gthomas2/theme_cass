#!/usr/bin/env bash
cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

. ./base.sh

BRANCH=`get_moodle branch`
TAG=`get_moodle tag`
PLUGINS=`get_moodle plugins`
NUM=`get_number_of_plugins`
FLAVOUR=`get_moodle flavour`

if [[ "${FLAVOUR}" =~ "MWP" ]]; then
    while [[ -z "$BB_USER" ]]; do
        read -s -p "Enter bitbucket username: " BB_USER
    done
    while [[ -z "$BB_PWD" ]]; do
        read -s -p "Enter bitbucket password: " BB_PWD
    done
else
    FLAVOUR="Moodle"
fi

# backup the plugins defined in package.json first
info "Taking a backup of custom plugins and config..."
for i in `seq 0 $(($NUM-1))`
do
    PLUGIN=`get_moodle plugins[$i]`

    if [ -e "$ROOT/moodle/$PLUGIN" ]; then
        rm -vrf "$ROOT/backup/$PLUGIN"
        mkdir -p "$ROOT/backup/$PLUGIN"
        cp -r "$ROOT/moodle/$PLUGIN/." "$ROOT/backup/$PLUGIN"
    fi
done

# backup the config.php
if [ -e "$ROOT/moodle/config.php" ]; then
    cp "$ROOT/moodle/config.php" "$ROOT/backup/config.php"
fi

# backup whole moodle just in case
if [ -e "$ROOT/moodle" ]; then
    info "Taking a backup of the current $FLAVOUR..."
    tar -czf "$ROOT/backup/moodle.tar.gz" "$ROOT/moodle"
fi

# grab Moodle and unpack in root of project
cd $ROOT
info "Downloading $FLAVOUR $TAG..."
if [ -e moodle-$TAG.zip ]; then
    rm moodle-$TAG.zip
fi

if [[ "${FLAVOUR}" =~ "MWP" ]]; then
    while [[ -z "$BB_USER" ]]; do
        read -s -p "Enter bitbucket username: " BB_USER
    done
    while [[ -z "$BB_PWD" ]]; do
        read -s -p "Enter bitbucket password: " BB_PWD
    done
    curl -s -k -X GET https://$BB_USER:$BB_PWD@bitbucket.org/titus-learning/moodle_workplace/get/master.zip > moodle-$TAG.zip
else
    curl -s -k -X GET https://download.moodle.org/download.php/direct/stable$BRANCH/moodle-$TAG.zip > moodle-$TAG.zip
fi




if [ $? -gt 0 ]; then
    fail "Failed to download $FLAVOUR"
    exit 1
fi
if [ ! -e moodle-$TAG.zip ]; then
    fail "No archive file for $FLAVOUR found"
    exit 1
fi

rm -rf "$ROOT/moodle"
info "Downloaded $FLAVOUR $TAG, unpacking..."
unzip -q moodle-$TAG.zip
if [ $? -gt 0 ]; then
    fail "Failed to unpack $FLAVOUR"
    exit 1
fi
rm moodle-$TAG.zip

if [[ "${FLAVOUR}" =~ "MWP" ]]; then
    cd $ROOT;
    rsync -a titus-learning-moodle_workplace-*/* moodle
    rm -rf titus-learning-moodle_workplace-*
fi

for i in `seq 0 $(($NUM-1))`
do
    PLUGIN=`get_moodle plugins[$i]`

    if [ -e "$ROOT/backup/$PLUGIN" ]; then
        mkdir -p "$ROOT/moodle/$PLUGIN"
        cp -a "$ROOT/backup/$PLUGIN/." "$ROOT/moodle/$PLUGIN"
    fi
done

# restore config.php, or copy a new one
if [ -e "$ROOT/backup/config.php" ]; then
    cp "$ROOT/backup/config.php" "$ROOT/moodle/config.php"
else
    cp "$SCRIPTS/config/moodle/config.dev.php" "$ROOT/moodle/config.php"
fi

success "$FLAVOUR $TAG ready, custom plugins restored."
