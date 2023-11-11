# Create /var/log if it doesn't exist
if [ ! -d "/var/log" ]; then
    mkdir -p /var/log
fi

# Create /var/run if it doesn't exist
if [ ! -d "/var/run" ]; then
    mkdir -p /var/run
fi

# Create /var/run/php if it doesn't exist
if [ ! -d "/var/run/php" ]; then
    mkdir -p /var/run/php
fi
