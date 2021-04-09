#!/bin/bash

POSITIONAL=()
set -- "${POSITIONAL[@]}" # restore positional parameters

if [[ $CI = true ]] ; then
	# are we in a CI environment?
	echo 'forcing non-interactive mode for CI environment'
	INTERACTIVE='NO'
else
	# not in a CI environment, default to interactive mode
	INTERACTIVE=${INTERACTIVE:-'YES'}
fi

# Install and configure WordPress if we haven't already
main() {
  BOLD=$(tput bold)
  NORMAL=$(tput sgr0)

	# We do this for now to maintain compatibility with VVV. If in the future
	# we migrate over to Lando completely, Composer can do this for us with
	# custom installer paths.
  WP_DIR="$LANDO_MOUNT/wordpress"

  if ! [[ -d "$WP_DIR"/wp-content/plugins/advanced-custom-fields ]] ; then
    echo 'Linking timber plugin directory...'
    ln -s "../../../wp-content/plugins/advanced-custom-fields/" "$WP_DIR"/wp-content/plugins/advanced-custom-fields
  fi

  if ! [[ -d "$WP_DIR"/wp-content/plugins/co-authors-plus ]] ; then
    echo 'Linking timber plugin directory...'
    ln -s "../../../wp-content/plugins/co-authors-plus" "$WP_DIR"/wp-content/plugins/co-authors-plus
  fi

  echo 'Checking for WordPress config...'
  if wp_configured ; then
    echo 'WordPress is configured'
  else
    read -d '' extra_php <<'EOF'
// log all notices, warnings, etc.
error_reporting(E_ALL);

// enable debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
EOF

    # create a wp-config.php
    wp config create \
      --dbname="wordpress" \
      --dbuser="wordpress" \
      --dbpass="wordpress" \
      --dbhost="database" \
      --path="$WP_DIR" \
      --extra-php < <(echo "$extra_php")
  fi

  echo 'Checking for WordPress installation...'
  if wp_installed ; then
    echo 'WordPress is installed'
  else
    # install WordPress
    wp core install \
      --url="https://timber.lndo.site" \
      --title="Timber Dev" \
      --admin_user="timber" \
      --admin_password="timber" \
      --admin_email="timber@example.com" \
      --skip-email \
      --path="$WP_DIR"
  fi

  # configure plugins and theme
  uninstall_plugins hello akismet
  wp --quiet --path="$WP_DIR" plugin activate advanced-custom-fields
  wp --quiet --path="$WP_DIR" plugin activate co-authors-plus

  wp option set permalink_structure '/%postname%/' --path="$WP_DIR"
  wp rewrite flush --path="$WP_DIR"

}


# Detect whether WP has been configured already
wp_configured() {
  [[ $(wp config path --path="$WP_DIR" 2>/dev/null) ]] && return
  false
}

# Detect whether WP is installed
wp_installed() {
  wp --quiet core is-installed --path="$WP_DIR"
  [[ $? = '0' ]] && return
  false
}

uninstall_plugins() {
  for plugin in $@ ; do
    wp --path="$WP_DIR" plugin is-installed $plugin  2>/dev/null
    if [[ "$?" = "0" ]] ; then
      wp --path="$WP_DIR" plugin uninstall $plugin
    fi
  done
}


main
