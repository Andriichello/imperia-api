#!/bin/bash

# Replace file endings
sed -i 's/\r$//' resources/docker/scripts/permissions.sh
sed -i 's/\r$//' resources/docker/scripts/setup.sh

# Run scripts
sh resources/docker/scripts/permissions.sh
sh resources/docker/scripts/setup.sh
