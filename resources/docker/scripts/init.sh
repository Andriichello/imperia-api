#!/bin/bash

# Replace file endings
dos2unix resources/docker/scripts/permissions.sh
dos2unix resources/docker/scripts/setup.sh

# Run scripts
sh resources/docker/scripts/permissions.sh
sh resources/docker/scripts/setup.sh
