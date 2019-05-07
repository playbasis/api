#!/bin/bash

set -m

./startMongoDB.sh &
./startPhpFpm.sh &
./startNginx.sh &

fg %1