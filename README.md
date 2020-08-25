# Titus Moodle

## How do I get Moodle into this damn thing?
OK, you've created a branch off of master for your project and you've named it as follows:
[jiraprojectcode]_master.
For example, if I was working on the ACE project your new branch would be called
ACE_master.
In order to get moodle up and running you need to do the following:

a) Run npm install in the root of your project, NOT in the 'moodle' folder.
b) Run "npm run moodle" in the root of your project

This will now download moodle for you.
Question: But won't this then mean moodle ends up in my branch?
Answer: No it won't, because we use .gitignore to prevent moodle from being commited. Reverse gitignores are used for your plugins.

## How do I modify the .gitignore file?
DO NOT simply just modify .gitignore.
Instead, you need to modify package.json to tell it what plugins you will be commiting. For example, if I want to include the folder blocks/ace then I simply add "blocks/ace" to the "plugins" folder as follows:

    {
      "name": "titus-moodle",
      "version": "1.0.0",
      "moodle": {
        "flavour": "MWP",
        "branch": 38,
        "tag": "latest-38",
        "plugins": [
          "local/tlcore",
          "blocks/ace"
        ]
      },
      .....
    }

Then, to build the .gitignore file, from your project root, run "npm run gitignore"

## What other tools are there?
### Purge caches
npm run purge
### Upgrade Moodle
npm run upgrade

## Compiling SCSS / ES6 JS / VUEJS, etc
Before you can compile any of these file types you must do the following:
Make sure you are using node v8.9.4
Note: If you currently have a different version of node, you can download nvm to switch to node v8.9.4
Note: It is incredibly important that you are using v8.9.4 before you attempt the following steps:
In your project's moodle folder run "npm install"
In moodle/local/titus-core run "npm install"
Copy the contents of moodle/local/tlcore/plugin_grunt_files into your new plugin(s)
 (e.g. blocks/ace)
Run npm install in your plugin(s) folder

You should now be able to compile scss, es6, vuejs.

From the CLI:

grunt compile - compiles scss
grunt babel - compiles amd code and converts it to es5
grunt decache - decaches moodle
grunt amd - compiles amd code but DOES NOT convert it to es5
grunt vue - compiles vue js components
grunt build - compiles everything and decaches

see local/tlcore/README.md for more details.


## IMPORTANT NOTE FOR DEVS
DO NOT merge other project branches into this master branch.
This branch should just contain the bare-bones for titus project branches to start off from. For setting up CI see https://tituslearning.atlassian.net/wiki/spaces/DEV/pages/63177149/CI+setup

## CI - package.json
By default bitbucket-pipelines.yml is configured to automatically run
all php unit tests for ALL the plugins specified in the package.json plugins
section. If you wish to exclude a specific plugin or plugins please use the
phpunit_blocklist section as shown below.

    {
      "name": "titus-moodle",
      "version": "1.0.0",
      "moodle": {
        "flavour": "MWP",
        "branch": 38,
        "tag": "latest-38",
        "plugins": [
          "local/tlcore",
          "blocks/ace"
        ],
        "phpunit_blocklist": [
          "auth/saml2"
        ]
      },
      .....
    }