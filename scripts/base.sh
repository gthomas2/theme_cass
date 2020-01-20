#!/usr/bin/env bash

SUDO=sudo
RED='\033[0;31m'
GREEN='\033[0;32m'
GREY='\033[0;36m'
NC='\033[0m' # No Color
SCRIPTS=`pwd`
ROOT="$SCRIPTS/../"

success () { echo -e "${GREEN}$1${NC}"; }

fail () { echo -e "${RED}$1${NC}"; }

info () { echo -e "${GREY}$1${NC}"; }

get_moodle () {
  CONFIG=`cat $ROOT/package.json`

  if [ -z $1 ]; then
    echo $CONFIG
    return
  fi

  echo $CONFIG | jq -r ".moodle.$1"
}

get_vagrant () {
  CONFIG=`cat $ROOT/package.json`

  if [ -z $1 ]; then
    echo $CONFIG
    return
  fi

  echo $CONFIG | jq -r ".vagrant.$1"
}

get_number_of_plugins () {
  echo `get_moodle | jq ".moodle.plugins | length"`
}

get_plugin_by_index () {
  echo `get_moodle plugins[$1]`
}