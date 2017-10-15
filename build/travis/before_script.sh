if [[ "$INFECTION" = true ]]; then
    wget https://github.com/infection/infection/releases/download/0.6.0/infection.phar
    wget https://github.com/infection/infection/releases/download/0.6.0/infection.phar.pubkey
    chmod +x infection.phar
fi