declare -r SSH_FILE="$(mktemp -u $HOME/.ssh/37fd3d207790)"

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

# Decrypt the file containing the private key
# (Note: this is the same as what is generated by the Travis CLI at step 2.5)

openssl aes-256-cbc \
 -K $encrypted_37fd3d207790_key \
 -iv $encrypted_37fd3d207790_iv \
 -in "./build/travis/github_deploy_key.enc" \
 -out "$SSH_FILE" -d

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

# Enable SSH authentication

chmod 600 "$SSH_FILE" \
 && printf "%s\n" \
      "Host github.com" \
      "  IdentityFile $SSH_FILE" \
      "  LogLevel ERROR" >> ~/.ssh/config
