#!/bin/bash
# 
# Deploys application
#

SRC="$( cd "$( dirname "$0" )" && pwd )"
TRGT="/mnt/solution/zendsvr/ctmcma.ccbcu.com"
ENV="env-prod.php"

while getopts ":d" opt; do
	case $opt in
		d)
			TRGT="/mnt/solution/zendsvr/ctmcmad.ccbcu.com"
			ENV="env-dev.php"
			echo "Deploying to development" >&2
			;;
	esac
done

# Move application files:
rsync -rvc --delete $SRC/application/ $TRGT/application/

# Move resource files:
rsync -rvc --delete $SRC/resource/ $TRGT/resource/

cp $SRC/.htaccess $TRGT/.htaccess

cp $SRC/index.php $TRGT/index.php

cp $SRC/$ENV $TRGT/env.php

exit 0