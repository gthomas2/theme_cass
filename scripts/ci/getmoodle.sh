#!/usr/bin/env bash
cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

ROOT=/opt/atlassian/pipelines/agent/build

RED='\033[0;31m'
GREEN='\033[0;32m'
GREY='\033[0;36m'
NC='\033[0m' # No Color
SCRIPTS=`pwd`

success () { echo -e "${GREEN}$1${NC}"; }

fail () { echo -e "${RED}$1${NC}"; }

info () { echo -e "${GREY}$1${NC}"; }

get_moodle () {
  CONFIG=`cat /opt/atlassian/pipelines/agent/build/package.json`

  if [ -z $1 ]; then
    echo $CONFIG
    return
  fi

  echo $CONFIG | jq -r ".moodle.$1"
}

if [ -z "$1" ]
  then
    fail "No argument supplied for bb user"
    exit 1
fi

if [ -z "$2" ]
  then
    fail "No argument supplied for bb app pwd"
    exit 1
fi

BB_USER=$1
BB_PWD=$2

BRANCH=`get_moodle branch`
TAG=`get_moodle tag`
FLAVOUR=`get_moodle flavour`

# grab Moodle and unpack in root of project
cd $ROOT
ls -all
info "Downloading $FLAVOUR $TAG..."
if [ -e moodle-$TAG.zip ]; then
    rm moodle-$TAG.zip
fi

if [[ "${FLAVOUR}" =~ "MWP" ]]; then
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

info "Downloaded $FLAVOUR $TAG, unpacking..."
unzip -qq moodle-$TAG.zip
if [ $? -gt 0 ]; then
    fail "Failed to unpack $FLAVOUR"
    exit 1
fi
rm moodle-$TAG.zip

if [[ "${FLAVOUR}" =~ "MWP" ]]; then
    cd $ROOT;
    rsync -a titus-learning-moodle_workplace-*/* ${ROOT}/moodle
    rm -rf titus-learning-moodle_workplace-*
fi

ls -all $ROOT;