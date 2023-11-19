#!/bin/bash
#
# Build the App
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail


BIN_SELF=$(readlink -f "$0")
APP_ROOT=$(dirname "$BIN_SELF")

cd "$APP_ROOT"

composer update --no-ansi --no-dev --no-progress --quiet --classmap-authoritative

npm install --quiet

. vendor/openthc/common/lib/lib.sh

copy_bootstrap
copy_fontawesome
copy_jquery

# cp node_modules/htmx.org/dist/htmx.min.js webroot/vendor/
# cp node_modules/plotly.js/dist/plotly.min.js webroot/vendor/
