#!/usr/bin/env bash
cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

. ./base.sh

BRANCH=`get_moodle branch`
TAG=`get_moodle tag`
PLUGINS=`get_moodle plugins`
NUM=`get_number_of_plugins`

# backup the plugins defined in package.json first
info "Taking a backup of custom plugins and config..."
for i in `seq 0 $(($NUM-1))`
do
    PLUGIN=`get_moodle plugins[$i]`

    if [ -e "$ROOT/moodle/$PLUGIN" ]; then
        rm -vrf "$BUILD/backup/$PLUGIN"
        mkdir -p "$BUILD/backup/$PLUGIN"
        cp -r "$ROOT/moodle/$PLUGIN/." "$BUILD/backup/$PLUGIN"
    fi
done

# backup the config.php
if [ -e "$ROOT/moodle/config.php" ]; then
    cp "$ROOT/moodle/config.php" "$BUILD/backup/config.php"
fi

# backup whole moodle just in case
if [ -e "$ROOT/moodle" ]; then
    info "Taking a backup of the current Moodle..."
    tar -czf "$BUILD/backup/moodle.tar.gz" "$ROOT/moodle"
fi

# grab Moodle and unpack in root of project
cd $ROOT
info "Downloading Moodle $TAG..."
if [ -e moodle-$TAG.zip ]; then
    rm moodle-$TAG.zip
fi
wget -q --no-check-certificate https://download.moodle.org/download.php/direct/stable$BRANCH/moodle-$TAG.zip
if [ $? -gt 0 ]; then
    fail "Failed to download Moodle"
    exit 1
fi
if [ ! -e moodle-$TAG.zip ]; then
    fail "No archive file for Moodle found"
    exit 1
fi

rm -rf "$ROOT/moodle"
info "Downloaded Moodle $TAG, unpacking..."
unzip -q moodle-$TAG.zip
if [ $? -gt 0 ]; then
    fail "Failed to unpack Moodle"
    exit 1
fi
rm moodle-$TAG.zip

for i in `seq 0 $(($NUM-1))`
do
    PLUGIN=`get_moodle plugins[$i]`

    if [ -e "$BUILD/backup/$PLUGIN" ]; then
        mkdir -p "$ROOT/moodle/$PLUGIN"
        cp -r "$BUILD/backup/$PLUGIN/." "$ROOT/moodle/$PLUGIN" 
    fi
done

# restore config.php, or copy a new one
if [ -e "$BUILD/backup/config.php" ]; then
    cp "$BUILD/backup/config.php" "$ROOT/moodle/config.php"
else
    cp "$BUILD/config/moodle/config.dev.php" "$ROOT/moodle/config.php"
fi

success "Moodle $TAG ready, custom plugins restored."
